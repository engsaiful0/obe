<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentMarksWorksheetImport;

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
        if (! extension_loaded('zip') && ! class_exists(\ZipArchive::class)) {
            throw ValidationException::withMessages([
                'file' => [__('Excel import requires the PHP zip extension. Enable extension=zip in php.ini or upload a .csv file.')],
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
            $header = $this->filterEmptyHeader($header);

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

        $header = $this->filterEmptyHeader($rows[0]->values()->all());
        array_shift($rows);

        return [$header, collect($rows)->values()];
    }

    /**
     * @param  array<int, mixed>  $header
     * @return array<int, string>
     */
    private function filterEmptyHeader(array $header): array
    {
        $filtered = array_values(array_filter($header, fn ($cell) => trim((string) $cell) !== ''));

        if ($filtered === []) {
            throw ValidationException::withMessages([
                'file' => [__('The file header row is empty or invalid.')],
            ]);
        }

        return array_map(fn ($cell) => trim((string) $cell), $filtered);
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
