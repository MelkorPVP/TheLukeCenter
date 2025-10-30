<?php

declare(strict_types=1);

namespace TheLukeCenter\Services;

final class SiteContentService
{
    private GoogleSheetsService $sheets;
    private string $range;
    private ?array $cachedValues = null;

    public function __construct(GoogleSheetsService $sheets, string $range)
    {
        $this->sheets = $sheets;
        $this->range = $range;
    }

    /**
     * @return array<string, string>
     */
    public function getValues(): array
    {
        if ($this->cachedValues !== null) {
            return $this->cachedValues;
        }

        $rows = $this->sheets->getValues($this->range);

        $values = [];
        foreach ($rows as $row) {
            if (!isset($row[0]) || $row[0] === '') {
                continue;
            }
            $values[trim((string) $row[0])] = isset($row[1]) ? trim((string) $row[1]) : '';
        }

        $this->cachedValues = $values;

        return $values;
    }

    /**
     * @return array<int, array{0:string,1:string}>
     */
    public function getDirectors(): array
    {
        $raw = $this->getValues()['directors'] ?? '';
        $entries = array_filter(array_map('trim', explode(';', $raw)));
        $directors = [];
        foreach ($entries as $entry) {
            $parts = array_map('trim', explode('/', $entry));
            $directors[] = [
                $parts[0] ?? '',
                $parts[1] ?? '',
            ];
        }

        return $directors;
    }

    public function getExecutiveRole(string $key): array
    {
        $raw = $this->getValues()[$key] ?? '';
        $parts = array_map('trim', explode('/', $raw));

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }

    public function isApplicationOpen(): bool
    {
        $value = strtoupper($this->getValues()['enable_application'] ?? '');
        return in_array($value, ['TRUE', 'YES', '1'], true);
    }

    public function getProgramName(): string
    {
        return $this->getValues()['program_name'] ?? '';
    }

    public function getProgramLocation(): string
    {
        return $this->getValues()['program_location'] ?? '';
    }

    public function getProgramDates(): string
    {
        return $this->getValues()['program_dates'] ?? '';
    }
}
