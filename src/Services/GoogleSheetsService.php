<?php

declare(strict_types=1);

namespace TheLukeCenter\Services;

use Google\Service\Sheets;
use Google\Service\Sheets\AppendValuesResponse;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\ValueRange;
use Google\Service\Exception as GoogleServiceException;

final class GoogleSheetsService
{
    private Sheets $sheets;
    private string $spreadsheetId;

    public function __construct(Sheets $sheets, string $spreadsheetId)
    {
        if ($spreadsheetId === '') {
            throw new \InvalidArgumentException('Spreadsheet ID is required.');
        }

        $this->sheets = $sheets;
        $this->spreadsheetId = $spreadsheetId;
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function getValues(string $range): array
    {
        try {
            $response = $this->sheets->spreadsheets_values->get($this->spreadsheetId, $range);
        } catch (GoogleServiceException $e) {
            if (in_array($e->getCode(), [400, 404], true)) {
                return [];
            }

            throw $e;
        }

        return $response->getValues() ?? [];
    }

    /**
     * @param array<int, string> $values
     */
    public function appendRow(string $range, array $values): AppendValuesResponse
    {
        $body = new ValueRange([
            'values' => [$values],
        ]);

        return $this->sheets->spreadsheets_values->append(
            $this->spreadsheetId,
            $range,
            $body,
            [
                'valueInputOption' => 'USER_ENTERED',
            ]
        );
    }

    public function ensureSheetExists(string $title): void
    {
        $spreadsheet = $this->sheets->spreadsheets->get($this->spreadsheetId);
        foreach ($spreadsheet->getSheets() as $sheet) {
            if ($sheet->getProperties() && $sheet->getProperties()->getTitle() === $title) {
                return;
            }
        }

        $request = new BatchUpdateSpreadsheetRequest([
            'requests' => [
                ['addSheet' => ['properties' => ['title' => $title]]],
            ],
        ]);

        $this->sheets->spreadsheets->batchUpdate($this->spreadsheetId, $request);
    }
}
