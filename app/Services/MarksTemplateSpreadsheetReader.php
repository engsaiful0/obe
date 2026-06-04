<?php

namespace App\Services;

use App\Support\SpreadsheetImportSupport;
use App\Imports\StudentMarksWorksheetImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class MarksTemplateSpreadsheetReader
{
    /**
     * @return array{0: array<int, string>, 1: Collection<int, Collection<int, mixed>>}
     */
    public function read(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->readCsv($file);
        }

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->readExcel($file);
        }

        throw ValidationException::withMessages([
            'file' => [__('File must be .xlsx, .xls, or .csv.')],
        ]);
    }

    /**
     * @return array{0: array<int, string>, 1: Collection<int, Collection<int, mixed>>}
     */
    private function readExcel(UploadedFile $file): array
    {
        if (! SpreadsheetImportSupport::zipAvailable()) {
            throw ValidationException::withMessages([
                'file' => [__('Excel import requires the PHP zip extension. Enable extension=zip in php.ini, restart Apache, or upload a .csv file.')],
            ]);
        }

        try {
            $reader = new StudentMarksWorksheetImport;
            Excel::import($reader, $file);

            $sheet = $reader->rows;
            if ($sheet->isEmpty()) {
                throw ValidationException::withMessages([
                    'file' => [__('The uploaded sheet is empty.')],
                ]);
            }

            $header = $sheet->shift()->map(fn ($cell) => trim((string) $cell))->values()->all();
            $header = $this->normalizeHeaderRow($header);

            return [$header, $sheet];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'file' => [__('Could not read the Excel file: :message', ['message' => $e->getMessage()])],
            ]);
        }
    }

    /**
     * @return array{0: array<int, string>, 1: Collection<int, Collection<int, mixed>>}
     */
    private function readCsv(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            throw ValidationException::withMessages([
                'file' => [__('Could not read the uploaded CSV file.')],
            ]);
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => [__('Could not open the CSV file.')],
            ]);
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if ($this->rowIsEmpty($data)) {
                continue;
            }
            $rows[] = collect(array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data));
        }
        fclose($handle);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => [__('The uploaded CSV file is empty.')],
            ]);
        }

        try {
            $header = $this->normalizeHeaderRow($rows[0]->values()->all());
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['file' => [$e->getMessage()]]);
        }
        array_shift($rows);

        return [$header, collect($rows)->values()];
    }

    /**
     * @param  array<int, mixed>  $header
     * @return array<int, string>
     */
    private function normalizeHeaderRow(array $header): array
    {
        try {
            return SpreadsheetImportSupport::normalizeHeaderRow($header);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['file' => [$e->getMessage()]]);
        }
    }

    /**
     * @param  array<int, mixed>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
