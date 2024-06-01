<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class DisbursementExport implements  FromView, ShouldAutoSize, WithStyles ,WithHeadings, WithEvents
{

    use Exportable;
    protected $data;
    // protected $search;

    public function __construct($data) {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('file-exports.disbursement', [
            'data' => $this->data,
        ]);
    }


    public function styles(Worksheet $sheet) {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A4:E4')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'dae0e0'],
        ]);

        $sheet->setShowGridlines(false);
        $styleArray = [
            'borders' => [
                'bottom' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FFFF0000']],
                'top' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FFFF0000']],
                'right' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FF00FF00']],
                'left' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FF00FF00']],
            ],
            'fillType' => 'solid',
            'rotation' => 0,
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($styleArray);
        return [
            // Define the style for cells with data
            'A1:E'.$this->data['disbursements']->count() +4 => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'], // Specify the color of the border (optional)
                    ],
                ],
            ],
        ];

    }

    public function setImage($workSheet) {
        $logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first()->value;
        $drawing = new Drawing();
        $drawing->setPath(is_file(storage_path('app/public/business/'.$logo))?storage_path('app/public/business/'.$logo):public_path('/assets/admin/img/160x160/img2.jpg'));
        $drawing->setWidth(150);
        $drawing->setHeight(62);
        $drawing->setCoordinates("D1");
        $drawing->setResizeProportional(true);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(3);

        $drawing->setWorksheet($workSheet);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:E1') // Adjust the range as per your needs
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A2:C2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A3:B3')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('C3')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('E3')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->getStyle('A4:E'.$this->data['disbursements']->count() +4)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);


                    $event->sheet->mergeCells('A1:C1');
                    $event->sheet->mergeCells('A2:C2');
                    $event->sheet->mergeCells('A3:B3');


                    $event->sheet->getDefaultRowDimension()->setRowHeight(30);
                    $event->sheet->getRowDimension(1)->setRowHeight(50);
                    $event->sheet->getRowDimension(2)->setRowHeight(50);
                    $event->sheet->getRowDimension(3)->setRowHeight(50);
                    $workSheet = $event->sheet->getDelegate();
                    $this->setImage($workSheet);
                },
        ];
    }
    public function headings(): array
    {
        return [
            '1'
        ];
    }
}

