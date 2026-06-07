<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use ZipArchive;

class AdmsSpreadsheet
{
    /**
     * @return array<int, array<string, string|null>>
     */
    public static function rows(UploadedFile $file): array
    {
        return strtolower($file->getClientOriginalExtension()) === 'xlsx'
            ? self::xlsxRows($file)
            : self::csvRows($file);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private static function csvRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [];
        }

        $headers = null;
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = self::headers($line);
                continue;
            }

            self::appendRow($rows, $headers, $line);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private static function xlsxRows(UploadedFile $file): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [];
        }

        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            return [];
        }

        $sharedStrings = self::sharedStrings($zip);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheet === false) {
            return [];
        }

        $xml = simplexml_load_string($sheet);

        if ($xml === false) {
            return [];
        }

        $headers = null;
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $values = [];

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $column = self::columnIndex($reference);
                $value = (string) ($cell->v ?? '');

                if ((string) $cell['t'] === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }

                $values[$column] = $value;
            }

            if ($headers === null) {
                ksort($values);
                $headers = self::headers($values);
                continue;
            }

            self::appendRow($rows, $headers, $values);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private static function sharedStrings(ZipArchive $zip): array
    {
        $contents = $zip->getFromName('xl/sharedStrings.xml');

        if ($contents === false) {
            return [];
        }

        $xml = simplexml_load_string($contents);

        if ($xml === false) {
            return [];
        }

        $strings = [];

        foreach ($xml->si as $item) {
            $strings[] = trim((string) ($item->t ?? $item->r->t ?? ''));
        }

        return $strings;
    }

    /**
     * @param array<int, string|null> $line
     * @return array<int, string>
     */
    private static function headers(array $line): array
    {
        return array_map(fn (?string $header): string => str((string) $header)->trim()->snake()->toString(), $line);
    }

    /**
     * @param array<int, array<string, string|null>> $rows
     * @param array<int, string> $headers
     * @param array<int, string|null> $line
     */
    private static function appendRow(array &$rows, array $headers, array $line): void
    {
        if (count(array_filter($line, fn ($value): bool => filled($value))) === 0) {
            return;
        }

        $rows[] = collect($headers)
            ->mapWithKeys(fn (string $header, int $index): array => [$header => self::cellValue($line[$index] ?? null)])
            ->all();
    }

    private static function cellValue(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function columnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($reference)) ?: 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }
}
