<?php

namespace App\Exports;

use App\Models\User;
use App\Models\DeliveryMan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithProperties;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


// class UsersExport implements FromCollection,WithMapping
class UsersExport implements FromView, ShouldAutoSize, WithStyles,WithColumnWidths ,WithHeadings, WithEvents
// class UsersExport implements FromCollection, , , WithProperties, WithDrawings, WithHeadings
{
    use Exportable;
    protected $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function view(): View
    {
        // dd($this->data);
        return view('user-export', [
            'data' => $this->data
        ]);
    }


    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return User::all();
    // }

    public function columnWidths(): array
    {
        return [
            // 'A' => 55,
            // 'B' => 45,
            'C' => 45,
        ];
    }

    public function styles(Worksheet $sheet) {
        // $count = count($this->data);
        // $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A2:H3')->getFont()->setBold(true);
        // $sheet->getStyle('A1:C1')->applyFromArray([
        //     'borders' => [
        //         'allBorders' => [
        //             'borderStyle' => Border::BORDER_DOTTED,
        //             'color' => ['argb' => 'FFFF0000'],
        //         ],
        //     ],

        // ]);

        $sheet->getStyle('A3:H3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => '9F9F9F'],
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
$sheet->getStyle('A1:C1')->applyFromArray($styleArray);
// $sheet->setRowWidth(1, 20);
// $sheet->getStyle('A4:H4')->applyFromArray($styleArray);
return [
    // Define the style for cells with data
    'A1:H1000' => [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '000000'], // Specify the color of the border (optional)
            ],
        ],
    ],
];
// $sheet->getStyle('B5:C6')->applyFromArray($styleArray, false);
        // return [
        //     // Style the first row as bold text.
        //     1    => ['font' => ['bold' => true]],

        //     // Styling a specific cell by coordinate.
        //     'B2' => ['font' => ['italic' => true]],

        //     // Styling an entire column.
        //     'C'  => ['font' => ['size' => 16]],
        // ];
    }

    // public function properties(): array
    // {
    //     return [
    //         'creator'        => 'Patrick Brouwers',
    //         'lastModifiedBy' => 'Patrick Brouwers',
    //         'title'          => 'Invoices Export',
    //         'description'    => 'Latest Invoices',
    //         'subject'        => 'Invoices',
    //         'keywords'       => 'invoices,export,spreadsheet',
    //         'category'       => 'Invoices',
    //         'manager'        => 'Patrick Brouwers',
    //         'company'        => 'Maatwebsite',
    //     ];
    // }

    // public function collection()
    // {
    //     return DeliveryMan::get();

    // }

    // public function setImage($workSheet) {
    //     $this->data->each(function($employee,$index) use($workSheet) {
    //         $drawing = new Drawing();
    //         $drawing->setName($employee->f_name);
    //         $drawing->setDescription($employee->f_name);
    //         $drawing->setPath(is_file(storage_path('app/public/delivery-man/'.$employee->image))?storage_path('app/public/delivery-man/'.$employee->image):public_path('/assets/admin/img/logo2.png'));
    //         $drawing->setHeight(40);
    //         $index+=5;
    //         $drawing->setCoordinates("C$index");
    //         $drawing->setWorksheet($workSheet);
    //     });
    // }
    // public function registerEvents():array {
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
    //             $event->sheet->getDefaultRowDimension()->setRowHeight(60);
    //             $workSheet = $event->sheet->getDelegate();
    //             $this->setImage($workSheet);
    //         },
    //     ];
    // }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:H1') // Adjust the range as per your needs
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A2:C2') // Adjust the range as per your needs
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->getStyle('A3:H300') // Adjust the range as per your needs
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('D2:H2') // Adjust the range as per your needs
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);


                    $event->sheet->mergeCells('A1:H1');
                    $event->sheet->mergeCells('A2:C2');
                    $event->sheet->mergeCells('D2:H2');

                    $event->sheet->getDefaultRowDimension()->setRowHeight(30);
                    $event->sheet->getRowDimension(1)->setRowHeight(50);
                    $event->sheet->getRowDimension(2)->setRowHeight(40);
                    $workSheet = $event->sheet->getDelegate();
                    // $this->setImage($workSheet);
                },
        ];
    }
    public function headings(): array
    {
        return [
           '1'
        ];
    }


    // public function map($user): array
    // {
    //     // This example will return 3 rows.
    //     // First row will have 2 column, the next 2 will have 1 column
    //     return [
    //         [
    //             $user->name,
    //             $user->email,
    //         ],
    //         [
    //             $user->name,
    //         ],
    //         [
    //             $user->email,
    //         ]
    //     ];
    // }
}
