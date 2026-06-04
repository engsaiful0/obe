<?php

namespace App\Support;

class SpreadsheetImportSupport
{
    public static function zipAvailable(): bool
    {
        if (extension_loaded('zip')) {
            return true;
        }

        if (! class_exists(\ZipArchive::class, false)) {
            return false;
        }

        try {
            $zip = new \ZipArchive;

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function diagnostics(): array
    {
        return [
            'zip_extension' => extension_loaded('zip'),
            'ziparchive_class' => class_exists(\ZipArchive::class, false),
            'excel_ready' => self::zipAvailable(),
            'php_ini' => php_ini_loaded_file() ?: null,
        ];
    }

    /**
     * @param  array<int, mixed>  $header
     * @return array<int, string>
     */
    public static function normalizeHeaderRow(array $header): array
    {
        $header = array_map(fn ($cell) => trim((string) $cell), $header);

        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/u', '', $header[0]);
        }

        while ($header !== [] && (string) end($header) === '') {
            array_pop($header);
        }

        if ($header === [] || trim((string) ($header[0] ?? '')) === '') {
            throw new \InvalidArgumentException(__('The file header row is empty or invalid.'));
        }

        return array_values($header);
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<int, mixed>
     */
    public static function padCellsToWidth(array $cells, int $width): array
    {
        return array_pad(array_values($cells), max(0, $width), null);
    }
}
