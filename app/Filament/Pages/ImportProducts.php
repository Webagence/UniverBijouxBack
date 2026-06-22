<?php

namespace App\Filament\Pages;

use App\Services\ProductImportService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportProducts extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Import Produits';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.import-products';

    public $excelFile = null;

    public ?array $result = null;

    public function downloadTemplate()
    {
        $service = app(ProductImportService::class);
        $spreadsheet = $service->generateTemplate();

        $filename = 'template-produits.xlsx';
        $tempPath = storage_path("app/temp/{$filename}");
        $dir = dirname($tempPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function import()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $path = $this->excelFile->store('imports');

        $service = app(ProductImportService::class);
        $this->result = $service->import(storage_path("app/{$path}"));

        Storage::delete($path);

        if ($this->result['success'] > 0) {
            Notification::make()
                ->title("{$this->result['success']} produit(s) importé(s)")
                ->success()
                ->send();
        }

        if (!empty($this->result['errors'])) {
            $firstErrors = array_slice($this->result['errors'], 0, 10);
            foreach ($firstErrors as $error) {
                Notification::make()
                    ->title($error)
                    ->warning()
                    ->send();
            }
            if (count($this->result['errors']) > 10) {
                Notification::make()
                    ->title("... et " . (count($this->result['errors']) - 10) . " autre(s) erreur(s)")
                    ->warning()
                    ->send();
            }
        }

        $this->excelFile = null;
    }
}
