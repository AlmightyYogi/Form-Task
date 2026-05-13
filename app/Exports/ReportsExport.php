<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $reports;

    public function __construct(Collection $reports)
    {
        $this->reports = $reports;
    }

    public function collection()
    {
        return $this->reports->map(function ($report, $index) {
            $statusLabel = match((int) $report->status) {
                0 => 'Closed',
                1 => 'Open',
                2 => 'Restored',
                default => '-'
            };

            $dateTime = '-';
            if ($report->request_date && $report->report_time) {
                try {
                    $days = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
                    $months = ['January'=>'Januari','February'=>'Februari','March'=>'Maret','April'=>'April','May'=>'Mei','June'=>'Juni','July'=>'Juli','August'=>'Agustus','September'=>'September','October'=>'Oktober','November'=>'November','December'=>'Desember'];
                    $dt = Carbon::parse($report->request_date->format('Y-m-d') . ' ' . $report->report_time);
                    $dayName = $days[$dt->format('l')] ?? $dt->format('l');
                    $monthName = $months[$dt->format('F')] ?? $dt->format('F');
                    $dateTime = "{$dayName}, {$dt->format('j')} {$monthName} {$dt->format('Y')} {$dt->format('H:i')}";
                } catch (\Exception $e) {
                    $dateTime = $report->request_date->format('Y-m-d') . ' ' . $report->report_time;
                }
            }
            
            $responseTime = '-';
            if ($report->response_time && $report->created_at && $report->request_date && $report->report_time) {
                try {
                    $issueTime = Carbon::parse($report->request_date->format('Y-m-d') . ' ' . $report->report_time);
                    $diff = $report->created_at->diff($issueTime);
                    $parts = [];
                    if ($diff->d > 0) $parts[] = $diff->d . ' Hari';
                    if ($diff->h > 0) $parts[] = $diff->h . ' Jam';
                    if ($diff->i > 0) $parts[] = $diff->i . ' Menit';
                    $responseTime = !empty($parts) ? implode(' ', $parts) : '< 1 Menit';
                } catch (\Exception $e) {}
            }

            $restoredTime = 'Not yet restored';
            if ($report->servicerestored_time && $report->report_time && $report->request_date) {
                try {
                    $reportTime  = Carbon::parse($report->request_date->format('Y-m-d') . ' ' . $report->report_time);
                    $restored    = Carbon::parse($report->servicerestored_time);
                    $diff        = $restored->diff($reportTime);
                    $parts = [];
                    if ($diff->d > 0) $parts[] = $diff->d . ' Hari';
                    if ($diff->h > 0) $parts[] = $diff->h . ' Jam';
                    if ($diff->i > 0) $parts[] = $diff->i . ' Menit';
                    $restoredTime = !empty($parts) ? implode(' ', $parts) : 'Immediate';
                } catch (\Exception $e) {}
            }

            $resolvedTime = 'Not yet resolved';
            if ($report->resolved_time && $report->created_at) {
                try {
                    $diff  = $report->resolved_time->diff($report->created_at);
                    $parts = [];
                    if ($diff->d > 0) $parts[] = $diff->d . ' Hari';
                    if ($diff->h > 0) $parts[] = $diff->h . ' Jam';
                    if ($diff->i > 0) $parts[] = $diff->i . ' Menit';
                    $resolvedTime = !empty($parts) ? implode(' ', $parts) : '< 1 Menit';
                } catch (\Exception $e) {}
            }

            return [
                'no'            => $index + 1,
                'code'          => $report->incident ?? 'No Incident',
                'requestor'     => $report->requestor ?? '-',
                'datetime'      => $dateTime,
                'application'   => $report->apps ?? '-',
                'severity'      => $report->severity ?? '-',
                'response_time' => $responseTime,
                'restored_time' => $restoredTime,
                'resolved_time' => $resolvedTime,
                'status'        => $statusLabel,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Code',
            'Requestor',
            'Date & Time',
            'Application',
            'Severity',
            'Response Time',
            'Restored Time',
            'Resolved Time',
            'Status',
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
            'B' => 22,
            'C' => 22,
            'D' => 32,
            'E' => 22,
            'F' => 28,
            'G' => 22,
            'H' => 22,
            'I' => 22,
            'J' => 14,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11, 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $lastRow    = $this->reports->count() + 1;
                $lastCol    = 'J';
                $dataRange  = "A2:{$lastCol}{$lastRow}";
                $fullRange  = "A1:{$lastCol}{$lastRow}";

                $sheet->insertNewRowBefore(1, 3);

                $exportDate = Carbon::now()->translatedFormat('j F Y H:i');
                $sheet->setCellValue('A1', 'REPORT EXPORT');
                $sheet->setCellValue('A2', 'Exported at: ' . $exportDate);
                $sheet->setCellValue('A3', 'Total Records: ' . $this->reports->count());

                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');

                $titleStyle = [
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $metaStyle = [
                    'font'      => ['size' => 10, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial', 'italic' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];

                $sheet->getStyle('A1')->applyFromArray($titleStyle);
                $sheet->getStyle('A2')->applyFromArray($metaStyle);
                $sheet->getStyle('A3')->applyFromArray($metaStyle);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);

                $sheet->getRowDimension(4)->setRowHeight(22);

                $actualLastRow = $this->reports->count() + 4;
                for ($row = 5; $row <= $actualLastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(18);
                    $bgColor = ($row % 2 === 0) ? 'FFD6E4F0' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                        'font' => ['name' => 'Arial', 'size' => 10],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                }

                $sheet->getStyle("A5:A{$actualLastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F5:F{$actualLastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J5:J{$actualLastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                for ($row = 5; $row <= $actualLastRow; $row++) {
                    $statusVal = $sheet->getCell("J{$row}")->getValue();
                    $color = match($statusVal) {
                        'Open'     => 'FF198754',
                        'Closed'   => 'FFDC3545',
                        'Restored' => 'FFFFC107',
                        default    => 'FF6C757D',
                    };
                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial', 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
                    ]);
                }

                $sheet->getStyle("A4:J{$actualLastRow}")->applyFromArray([
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

                $sheet->setAutoFilter("A4:J4");
            },
        ];
    }
}