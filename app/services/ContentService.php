<?php
    
    declare(strict_types=1);
    
    require_once __DIR__ . '/GoogleService.php';
    require_once __DIR__ . '/Logger.php';
    
    /**
        * Load key/value content from the Google Sheet.
        *
        * Sheet shape (2 columns):
        *   Col A: key
        *   Col B: value
        *
        * @param array<string, mixed> $config
        * @return array<string, string>
    */
    function site_content_values(array $config, ?AppLogger $logger = null): array
    {
        static $cache = null;
        
        if ($cache !== null) 
        {
            return $cache;
        }
        
        $googleConfig = $config['google'] ?? [];
        
        // Build the sheet configuration array expected by google_sheets_get_values()
        $siteSheetConfig = [
        'spreadsheet_id' => $config['google']['site_values_spreadsheet_id'] ?? '',
        'range'          => $config['google']['site_values_range'] ?? 'Values!A:B',
        ];
        
        // 2-argument call; function in google.php accepts an optional 3rd param
        $values = google_sheets_get_values($googleConfig, $siteSheetConfig, null, $logger);
        
        $mapped = [];
        foreach ($values as $row) 
        {
            if (!isset($row[0]) || !isset($row[1])) 
            {
                continue;
            }
            
            $key = trim((string)$row[0]);
            if ($key === '') 
            {
                continue;
            }
            
            $mapped[$key] = trim((string)$row[1]);
        }
        
        $cache = $mapped;
        return $cache;
    }
    
    /**
        * Directors: stored in a single cell “Name / Role; Name / Role; …”
        *
        * @param array<string, mixed> $config
        * @return array<int, array{0:string,1:string}>
    */
    function site_content_directors(array $config, ?AppLogger $logger = null): array
    {
        $values   = site_content_values($config, $logger);
        $raw      = $values['directors'] ?? '';
        $entries  = array_filter(array_map('trim', explode(';', $raw)));
        $directors = [];
        
        foreach ($entries as $entry) 
        {
            $parts      = array_map('trim', explode('/', $entry));
            $directors[] = [
            $parts[0] ?? '',
            $parts[1] ?? '',
            ];
        }
        
        return $directors;
    }
    
    /**
        * Generic role helper: stored as “Name / Role”
        *
        * @param array<string, mixed> $config
        * @return array{0:string,1:string}
    */
    function site_content_role(array $config, string $key, ?AppLogger $logger = null): array
    {
        $values = site_content_values($config, $logger);
        $raw    = $values[$key] ?? '';
        $parts  = array_map('trim', explode('/', $raw));
        
        return [
        $parts[0] ?? '',
        $parts[1] ?? '',
        ];
    }
    
    /**
        * Application open flag in sheet (“TRUE”, “YES”, “1”)
    */
    function site_content_application_open(array $config, ?AppLogger $logger = null): bool
    {
        $value = strtoupper(site_content_values($config, $logger)['enable_application'] ?? '');
        return in_array($value, ['TRUE', 'YES', '1'], true);
    }
    
    function site_content_program_name(array $config, ?AppLogger $logger = null): string
    {
        return site_content_values($config, $logger)['program_name'] ?? '';
    }
    
    function site_content_program_location(array $config, ?AppLogger $logger = null): string
    {
        return site_content_values($config, $logger)['program_location'] ?? '';
    }
    
    function site_content_program_dates(array $config, ?AppLogger $logger = null): string
    {
        return site_content_values($config, $logger)['program_dates'] ?? '';
    }
    
    
    /**
        * NEW: Testimonials / feedback strings.
        *
        * Expected sheet shape:
        *   One column of quotes (recommended: column A).
        *
        * @param array<string, mixed> $config
        * @return array<int, string>
    */
    function site_content_testimonials(array $config, ?AppLogger $logger = null): array
    {
        static $cache = null;
        
        if ($cache !== null) {
            return $cache;
        }
        
        $googleConfig = $config['google'] ?? [];
        
        $spreadsheetId = $config['google']['testimonials_spreadsheet_id'] ?? '';
        $range         = $config['google']['testimonials_range'] ?? 'Values!A:A';
        
        if ($spreadsheetId === '') {
            $cache = [];
            return $cache;
        }
        
        $sheetConfig = [
        'spreadsheet_id' => $spreadsheetId,
        'range' => $range,
        ];
        
        $rows = google_sheets_get_values($googleConfig, $sheetConfig, null, $logger);
        
        $items = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row[0] ?? ''));
            if ($value !== '') {
                $items[] = $value;
            }
        }
        
        $cache = $items;
        return $cache;
    }
