<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Site;
use App\Models\Universe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductImportService
{
    protected array $sites = [];
    protected array $universes = [];

    public function __construct()
    {
        $this->sites = Site::pluck('id', 'slug')->toArray();
        $this->universes = Universe::pluck('id', 'slug')->toArray();
    }

    public function generateTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produits');

        $headers = [
            'A1' => 'site',
            'B1' => 'universe',
            'C1' => 'name',
            'D1' => 'slug',
            'E1' => 'reference',
            'F1' => 'description',
            'G1' => 'price_ht',
            'H1' => 'vat_rate',
            'I1' => 'stock',
            'J1' => 'moq',
            'K1' => 'pack_size',
            'L1' => 'material',
            'M1' => 'finish',
            'N1' => 'quality_grade',
            'O1' => 'tag',
            'P1' => 'is_new',
            'Q1' => 'active',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Example row
        $sheet->setCellValue('A2', 'bijoux');
        $sheet->setCellValue('B2', 'colliers');
        $sheet->setCellValue('C2', 'Exemple Collier');
        $sheet->setCellValue('D2', '');
        $sheet->setCellValue('E2', '');
        $sheet->setCellValue('F2', 'Description du produit exemple');
        $sheet->setCellValue('G2', 29.99);
        $sheet->setCellValue('H2', 20);
        $sheet->setCellValue('I2', 50);
        $sheet->setCellValue('J2', 3);
        $sheet->setCellValue('K2', 1);
        $sheet->setCellValue('L2', 'Laiton doré');
        $sheet->setCellValue('M2', 'Finition mate');
        $sheet->setCellValue('N2', '');
        $sheet->setCellValue('O2', '');
        $sheet->setCellValue('P2', 1);
        $sheet->setCellValue('Q2', 1);

        // Valid values sheet
        $ws = $spreadsheet->createSheet();
        $ws->setTitle('Valeurs valides');
        $ws->setCellValue('A1', 'Sites disponibles');
        $ws->setCellValue('B1', 'Univers disponibles');
        $ws->getStyle('A1:B1')->getFont()->setBold(true);

        $row = 2;
        foreach ($this->sites as $slug => $id) {
            $ws->setCellValue("A{$row}", $slug);
            $row++;
        }
        $row = 2;
        foreach ($this->universes as $slug => $id) {
            $ws->setCellValue("B{$row}", $slug);
            $row++;
        }

        foreach (range('A', 'B') as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function import(string $filePath): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
            'total' => 0,
        ];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            return ['success' => 0, 'errors' => ["Impossible de lire le fichier: {$e->getMessage()}"], 'total' => 0];
        }

        if (count($rows) < 2) {
            return ['success' => 0, 'errors' => ['Le fichier est vide']];
        }

        $headers = array_map('trim', array_map('strtolower', $rows[0]));
        $required = ['site', 'name'];
        foreach ($required as $r) {
            if (!in_array($r, $headers)) {
                return ['success' => 0, 'errors' => ["Colonne obligatoire manquante: '{$r}'"], 'total' => 0];
            }
        }

        $hSite = array_search('site', $headers);
        $hUniverse = array_search('universe', $headers);
        $hName = array_search('name', $headers);
        $hSlug = array_search('slug', $headers);
        $hRef = array_search('reference', $headers);
        $hDesc = array_search('description', $headers);
        $hPrice = array_search('price_ht', $headers);
        $hVat = array_search('vat_rate', $headers);
        $hStock = array_search('stock', $headers);
        $hMoq = array_search('moq', $headers);
        $hPack = array_search('pack_size', $headers);
        $hMat = array_search('material', $headers);
        $hFinish = array_search('finish', $headers);
        $hQuality = array_search('quality_grade', $headers);
        $hTag = array_search('tag', $headers);
        $hNew = array_search('is_new', $headers);
        $hActive = array_search('active', $headers);

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $results['total']++;
            $lineNum = $i + 1;

            $siteSlug = $hSite !== false ? trim((string)($row[$hSite] ?? '')) : '';
            $name = $hName !== false ? trim((string)($row[$hName] ?? '')) : '';

            if (empty($name)) {
                $results['errors'][] = "Ligne {$lineNum}: le champ 'name' est obligatoire";
                continue;
            }

            if (empty($siteSlug)) {
                $results['errors'][] = "Ligne {$lineNum}: le champ 'site' est obligatoire";
                continue;
            }

            $siteId = $this->sites[$siteSlug] ?? null;
            if (!$siteId) {
                $results['errors'][] = "Ligne {$lineNum}: site '{$siteSlug}' invalide";
                continue;
            }

            $slug = $hSlug !== false ? trim((string)($row[$hSlug] ?? '')) : '';
            $reference = $hRef !== false ? trim((string)($row[$hRef] ?? '')) : '';

            $universeId = null;
            if ($hUniverse !== false) {
                $universeSlug = trim((string)($row[$hUniverse] ?? ''));
                if (!empty($universeSlug)) {
                    $universeId = $this->universes[$universeSlug] ?? null;
                    if (!$universeId) {
                        $results['errors'][] = "Ligne {$lineNum}: univers '{$universeSlug}' invalide";
                        continue;
                    }
                }
            }

            try {
                $data = [
                    'site_id' => $siteId,
                    'universe_id' => $universeId,
                    'name' => $name,
                    'description' => $hDesc !== false ? trim((string)($row[$hDesc] ?? '')) : '',
                    'price_ht' => $hPrice !== false ? (float)($row[$hPrice] ?? 0) : 0,
                    'vat_rate' => $hVat !== false ? (float)($row[$hVat] ?? 20) : 20,
                    'stock' => $hStock !== false ? (int)($row[$hStock] ?? 0) : 0,
                    'moq' => $hMoq !== false ? (int)($row[$hMoq] ?? 1) : 1,
                    'pack_size' => $hPack !== false ? (int)($row[$hPack] ?? 1) : 1,
                    'material' => $hMat !== false ? trim((string)($row[$hMat] ?? '')) : null,
                    'finish' => $hFinish !== false ? trim((string)($row[$hFinish] ?? '')) : null,
                    'quality_grade' => $hQuality !== false ? trim((string)($row[$hQuality] ?? '')) : null,
                    'tag' => $hTag !== false ? trim((string)($row[$hTag] ?? '')) : null,
                    'is_new' => $hNew !== false ? (bool)(int)($row[$hNew] ?? 0) : false,
                    'active' => $hActive !== false ? (bool)(int)($row[$hActive] ?? 1) : true,
                    'retail_ttc' => 0,
                ];

                if (!empty($slug)) {
                    $data['slug'] = $slug;
                }
                if (!empty($reference)) {
                    $data['reference'] = $reference;
                }

                $product = Product::create($data);
                $product->retail_ttc = round($product->price_ht * (1 + $product->vat_rate / 100), 2);
                $product->save();

                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = "Ligne {$lineNum}: {$e->getMessage()}";
            }
        }

        return $results;
    }
}
