<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CatalogController extends Controller
{
    public function export()
    {
        $products = Product::active()
            ->with('universe')
            ->orderBy('reference')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Catalogue');

        $headers = ['Référence', 'Nom', 'Prix HT (€)', 'TVA (%)', 'Stock', 'Conditionnement', 'Quantité'];
        $colLetters = range('A', 'G');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF374151']],
            ],
        ];

        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        foreach ($headers as $i => $label) {
            $sheet->setCellValue($colLetters[$i] . '1', $label);
        }

        $sheet->getRowDimension(1)->setRowHeight(22);

        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->reference);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->sale_price_ht ?? $product->price_ht);
            $sheet->setCellValue('D' . $row, $product->vat_rate);
            $sheet->setCellValue('E' . $row, $product->stock);
            $sheet->setCellValue('F' . $row, $product->pack_size);
            $sheet->setCellValue('G' . $row, '');

            $cellStyle = [
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']],
                ],
            ];

            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($cellStyle);

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('FFF9FAFB'));
            }

            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('0');

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(12);

        $filename = 'catalogue-francegems-' . date('Y-m-d') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return response()->json(['error' => 'Le fichier est vide'], 422);
        }

        $products = Product::active()->get()->keyBy('reference');

        $selections = [];

        for ($i = 1; $i < count($rows); $i++) {
            $reference = trim($rows[$i][0] ?? '');
            $quantity = intval($rows[$i][6] ?? 0);

            if (empty($reference) || $quantity <= 0) {
                continue;
            }

            $product = $products->get($reference);
            if (!$product) {
                continue;
            }

            $qty = max($quantity, $product->moq);

            $selections[] = [
                'productId' => $product->id,
                'reference' => $product->reference,
                'name' => $product->name,
                'quantity' => $qty,
                'moq' => $product->moq,
                'pack_size' => $product->pack_size,
                'price_ht' => $product->sale_price_ht ?? $product->price_ht,
            ];
        }

        return response()->json([
            'selections' => $selections,
            'count' => count($selections),
        ]);
    }
}
