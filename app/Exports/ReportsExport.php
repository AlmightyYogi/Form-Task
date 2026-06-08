<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ReportsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $search;
    protected $startDate;
    protected $endDate;
    protected $page;
    protected $perPage;
    protected $allData;

    public function __construct($search = null, $startDate = null, $endDate = null, $page = null, $perPage = 15, $allData = false)
    {
        $this->search    = $search;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->page      = $page;
        $this->perPage   = $perPage;
        $this->allData   = $allData;
    }

    public function query()
    {
        $query = Report::query()->orderBy('created_at', 'desc');

        if ($this->search) {
            $search      = $this->search;
            $searchLower = strtolower($search);
            $statusMap   = ['closed' => 0, 'open' => 1, 'restored' => 2, 'done' => 4, 'done partial' => 5, 'rollback' => 6];

            $query->where(function ($q) use ($search, $searchLower, $statusMap) {
                $q->where('incident', 'like', "%$search%")
                  ->orWhere('requestor', 'like', "%$search%")
                  ->orWhere('requestor_email', 'like', "%$search%")
                  ->orWhere('apps', 'like', "%$search%")
                  ->orWhere('assigned_to', 'like', "%$search%")
                  ->orWhere('severity', 'like', "%$search%");
                if (isset($statusMap[$searchLower])) {
                    $q->orWhere('status', $statusMap[$searchLower]);
                }
            });
        }

        if ($this->startDate) $query->whereDate('request_date', '>=', $this->startDate);
        if ($this->endDate)   $query->whereDate('request_date', '<=', $this->endDate);

        if (!$this->allData && $this->page) {
            $query->forPage($this->page, $this->perPage);
        }

        return $query;
    }

    public function map($report): array
    {
        $statusText = match((int) $report->status) {
            0 => 'Closed',
            1 => 'Open',
            2 => 'Restored',
            4 => 'Done',
            5 => 'Done Partial',
            6 => 'Rollback',
            default => 'Unknown',
        };

        $responseTime = '-';
        if ($report->created_at && $report->request_date && $report->report_time) {
            try {
                $issueTime    = Carbon::parse($report->request_date->format('Y-m-d') . ' ' . $report->report_time);
                $responseTime = $this->formatDuration($report->created_at->diff($issueTime));
            } catch (\Exception $e) {}
        }

        $restoredTime = '-';
        if ($report->servicerestored_time && $report->created_at) {
            try {
                $restoredTime = $this->formatDuration(
                    Carbon::parse($report->servicerestored_time)->diff($report->created_at)
                );
            } catch (\Exception $e) {}
        }

        $resolvedTime = '-';
        if ($report->closed_at && $report->created_at) {
            try {
                $resolvedTime = $this->formatDuration(
                    Carbon::parse($report->closed_at)->diff($report->created_at)
                );
            } catch (\Exception $e) {}
        }

        $restorationEvidence = '-';
        if (!empty($report->restoration_evidence) && is_array($report->restoration_evidence)) {
            $restorationEvidence = implode(', ', $report->restoration_evidence);
        }

        $fileDowntimeEvidence = '-';
        if (!empty($report->file_downtime_evidence) && is_array($report->file_downtime_evidence)) {
            $fileDowntimeEvidence = implode(', ', $report->file_downtime_evidence);
        }

        $restoredTimeRaw = '-';
        if ($report->restored_time) {
            $h = intdiv((int)$report->restored_time, 3600);
            $m = intdiv((int)$report->restored_time % 3600, 60);
            $s = (int)$report->restored_time % 60;
            $restoredTimeRaw = str_pad($h,2,'0',STR_PAD_LEFT).':'.str_pad($m,2,'0',STR_PAD_LEFT).':'.str_pad($s,2,'0',STR_PAD_LEFT);
        }

        $totalInternalDuration = '-';
        if ($report->total_internal_duration) {
            $h = intdiv((int)$report->total_internal_duration, 3600);
            $m = intdiv((int)$report->total_internal_duration % 3600, 60);
            $s = (int)$report->total_internal_duration % 60;
            $totalInternalDuration = str_pad($h,2,'0',STR_PAD_LEFT).':'.str_pad($m,2,'0',STR_PAD_LEFT).':'.str_pad($s,2,'0',STR_PAD_LEFT);
        }

        return [
            '',
            $report->incident ?? '-',
            $report->requestor ?? '-',
            $report->requestor_email ?? '-',
            $report->request_date ? $report->request_date->format('d/m/Y') : '-',
            $report->report_time ?? '-',
            $report->apps ?? '-',
            $report->type ?? '-',
            $report->severity ?? '-',
            $report->assigned_to ?? '-',
            $report->scope ?? '-',
            $report->description ?? '-',
            $report->resolution ?? '-',
            strip_tags($report->rca ?? '-'),
            $statusText,
            $report->handled_by ? 'Yes' : 'No',
            $responseTime,
            $restoredTime,
            $resolvedTime,
            $restoredTimeRaw,
            $totalInternalDuration,
            $report->servicerestored_time
                ? Carbon::parse($report->servicerestored_time)->format('d/m/Y H:i')
                : '-',
            $report->closed_at
                ? Carbon::parse($report->closed_at)->format('d/m/Y H:i')
                : '-',
            $report->created_at
                ? $report->created_at->format('d/m/Y H:i')
                : '-',
            $fileDowntimeEvidence,
            $restorationEvidence,
        ];
    }

    private function formatDuration($duration): string
    {
        $parts = [];
        if ($duration->d > 0) $parts[] = $duration->d . ' Hari';
        if ($duration->h > 0) $parts[] = $duration->h . ' Jam';
        if ($duration->i > 0) $parts[] = $duration->i . ' Menit';
        return !empty($parts) ? implode(' ', $parts) : 'Immediate';
    }

    public function headings(): array
    {
        return [
            'No',
            'Incident Code',
            'Requestor',
            'Requestor Email',
            'Request Date',
            'Report Time',
            'Application',
            'Type',
            'Severity / Priority / Impact',
            'Assigned To',
            'Scope',
            'Description',
            'Resolution',
            'Root Cause Analysis (RCA)',
            'Status',
            'Handled by External Team',
            'Response Time',
            'Restored Time',
            'Resolved Time',
            'Restored Duration (HH:MM:SS)',
            'Internal Duration (HH:MM:SS)',
            'Service Restored At',
            'Closed At',
            'Created At',
            'File Downtime Evidence',
            'Restoration Evidence',
        ];
    }

    public function title(): string
    {
        return 'Reports';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 22,
            'D' => 30,
            'E' => 16,
            'F' => 14,
            'G' => 24,
            'H' => 14,
            'I' => 28,
            'J' => 28,
            'K' => 22,
            'L' => 40,
            'M' => 40,
            'N' => 40,
            'O' => 16,
            'P' => 22,
            'Q' => 22,
            'R' => 22,
            'S' => 22,
            'T' => 26,
            'U' => 26,
            'V' => 22,
            'W' => 22,
            'X' => 22,
            'Y' => 35,
            'Z' => 35,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastCol   = 'Z';
                $totalRows = $this->query()->count();
                $lastRow   = $totalRows + 4;

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'REPORT EXPORT');
                $sheet->setCellValue('A2', 'Exported at: ' . Carbon::now()->translatedFormat('j F Y H:i'));
                $sheet->setCellValue('A3', 'Total Records: ' . number_format($totalRows, 0, ',', '.'));

                foreach (['A1', 'A2', 'A3'] as $cell) {
                    $sheet->mergeCells("{$cell}:{$lastCol}" . substr($cell, 1));
                }

                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                foreach (['A2', 'A3'] as $cell) {
                    $sheet->getStyle($cell)->applyFromArray([
                        'font'      => ['size' => 10, 'color' => ['argb' => 'FFFFFFFF'], 'italic' => true, 'name' => 'Arial'],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $sheet->getRowDimension((int)substr($cell, 1))->setRowHeight(18);
                }

                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10, 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(30);

                $colWidthMap = [
                    'A' => 6,  'B' => 18, 'C' => 22, 'D' => 30,
                    'E' => 16, 'F' => 14, 'G' => 24, 'H' => 14,
                    'I' => 28, 'J' => 28, 'K' => 22, 'L' => 40,
                    'M' => 40, 'N' => 40, 'O' => 16, 'P' => 22,
                    'Q' => 22, 'R' => 22, 'S' => 22, 'T' => 26,
                    'U' => 26, 'V' => 22, 'W' => 22, 'X' => 22,
                    'Y' => 35, 'Z' => 35,
                ];

                $skipCols = ['A', 'E', 'F', 'H', 'O', 'P', 'T', 'U', 'V', 'W', 'X'];

                for ($row = 5; $row <= $lastRow; $row++) {
                    $bgColor = ($row % 2 === 0) ? 'FFD6E4F0' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                        'font'      => ['name' => 'Arial', 'size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    $maxLines = 1;

                    foreach ($colWidthMap as $col => $colWidth) {
                        if (in_array($col, $skipCols)) continue;

                        $cellValue = (string) $sheet->getCell("{$col}{$row}")->getValue();
                        if (empty($cellValue) || $cellValue === '-') continue;

                        $lines = explode("\n", $cellValue);
                        $totalLines = 0;

                        foreach ($lines as $line) {
                            if (empty(trim($line))) {
                                $totalLines++;
                                continue;
                            }
                            $usableWidth  = max(1, ($colWidth - 2) * 1.2);
                            $lineWrapCount = (int) ceil(mb_strlen($line) / $usableWidth);
                            $totalLines   += max(1, $lineWrapCount);
                        }

                        $maxLines = max($maxLines, $totalLines);
                    }

                    $rowHeight = min(300, max(18, $maxLines * 15));
                    $sheet->getRowDimension($row)->setRowHeight($rowHeight);

                    $sheet->setCellValue("A{$row}", $row - 4);
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_TOP);
                }

                foreach (['A', 'E', 'F', 'H', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'] as $col) {
                    $sheet->getStyle("{$col}5:{$col}{$lastRow}")
                          ->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                for ($row = 5; $row <= $lastRow; $row++) {
                    $status = $sheet->getCell("O{$row}")->getValue();
                    $color  = match($status) {
                        'Open'         => 'FF198754',
                        'Closed'       => 'FFDC3545',
                        'Restored'     => 'FFFFC107',
                        'Done'         => 'FF198754',
                        'Done Partial' => 'FFFD7E14',
                        'Rollback'     => 'FFDC3545',
                        default        => 'FF6C757D',
                    };
                    $sheet->getStyle("O{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial', 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
                    ]);
                }

                $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFB0C4DE'],
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['argb' => 'FF1F4E79'],
                        ],
                    ],
                ]);

                $sheet->freezePane('A5');
                $sheet->setAutoFilter("A4:{$lastCol}4");
            },
        ];
    }
}