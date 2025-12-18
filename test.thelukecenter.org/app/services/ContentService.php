<?php
    
    declare(strict_types=1);
    
    /**
        * Commenting convention:
        * - Docblocks summarize function intent along with key inputs/outputs.
        * - Inline context comments precede major initialization, configuration, or external calls.
    */
    
    require_once __DIR__ . '/GoogleService.php';
    require_once __DIR__ . '/Logger.php';
    
    /**
        * Location for file-based content cache.
    */
    function site_content_cache_path(array $config): string
    {
        $dir = APP_ROOT . '/storage/cache';
        
        $environment = (string) ($config['environment'] ?? APP_ENV_PROD);
        $suffix = $environment === APP_ENV_TEST ? '-test' : '-prod';
        
        if (!is_dir($dir))
        {
            mkdir($dir, 0775, true);
        }
        
        return $dir . '/site-content-cache' . $suffix . '.json';
    }
    
    /**
        * Read the cached payload from disk when available.
        *
        * @param array<string, mixed> $config
        * @return array<string, mixed>|null
    */
    function site_content_load_cached_payload(array $config, ?AppLogger $logger = null): ?array
    {
        $path = site_content_cache_path($config);
        
        if (!is_file($path))
        {
            return null;
        }
        
        $raw = file_get_contents($path);
        if ($raw === false)
        {
            return null;
        }
        
        $data = json_decode($raw, true);
        if (!is_array($data))
        {
            return null;
        }
        
        if ($logger instanceof AppLogger && $logger->isEnabled())
        {
            $logger->info('Loaded site content cache', [
            'path' => $path,
            'generated_at' => $data['generated_at'] ?? null,
            ]);
        }
        
        return $data;
    }
    
    /**
        * Persist the aggregated payload for runtime reads.
        *
        * @param array<string, mixed> $payload
    */
    function site_content_save_cache(array $config, array $payload, ?AppLogger $logger = null): void
    {
        $path = site_content_cache_path($config);
        
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($path, (string) $json, LOCK_EX);
        
        if ($logger instanceof AppLogger && $logger->isEnabled())
        {
            $logger->info('Site content cache refreshed', [
            'path' => $path,
            'values_count' => count($payload['values'] ?? []),
            'testimonials_count' => count($payload['testimonials'] ?? []),
            'images_count' => count($payload['images'] ?? []),
            'alumni_images_count' => count($payload['alumniImages'] ?? []),
            ]);
        }
    }
    
    /**
        * Pull key/value content directly from Google Sheets.
        *
        * Sheet shape (2 columns):
        * Col A: key
        * Col B: value
        *
        * @param array<string, mixed> $config
        * @return array<string, string>
    */
    function site_content_fetch_values_from_google(array $config, ?AppLogger $logger = null): array
    {
        $googleConfig = $config['google'] ?? [];
        
        $environment = (string) ($config['environment'] ?? APP_ENV_PROD);
        $spreadsheetId = (string) ($config['google']['site_values_spreadsheet_id'] ?? '');
        $range         = (string) ($config['google']['site_values_range'] ?? 'Values!A:B');
        
        if ($spreadsheetId === '')
        {
            $hint = $environment === APP_ENV_TEST
            ? 'Set GOOGLE_SITE_VALUES_SHEET_ID_TEST (or GOOGLE_SITE_VALUES_SHEET_ID).'
            : 'Set GOOGLE_SITE_VALUES_SHEET_ID_PROD (or GOOGLE_SITE_VALUES_SHEET_ID).';
            
            throw new RuntimeException('Missing Google Sheet ID for site values. ' . $hint);
        }
        
        if ($range === '')
        {
            $hint = $environment === APP_ENV_TEST
            ? 'Set GOOGLE_SITE_VALUES_RANGE (or GOOGLE_SITE_VALUES_RANGE_TEST).'
            : 'Set GOOGLE_SITE_VALUES_RANGE (or GOOGLE_SITE_VALUES_RANGE_PROD).';
            
            throw new RuntimeException('Missing range for site values Google Sheet. ' . $hint);
        }
        
        // Build the sheet configuration array expected by google_sheets_get_values()
        $siteSheetConfig = [
        'spreadsheet_id' => $spreadsheetId,
        'range'          => $range,
        ];
        
        // 2-argument call; function in google.php accepts an optional 3rd param
        $values = google_sheets_get_values($googleConfig, $siteSheetConfig, null, $logger);
        
        // Debug logging to diagnose missing keys
        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->info('Fetched raw rows from Google Sheets', ['count' => count($values)]);
        }
        
        $mapped = [];
        $skipped = [];
        
        foreach ($values as $index => $row)
        {
            // Debug skipped rows
            if (!isset($row[0])) {
                $skipped[] = "Row {$index}: Missing Key (Col A)";
                continue;
            }
            
            // Normalize key: lowercase and trim
            $key = trim((string)$row[0]);
            
            if ($key === '') {
                $skipped[] = "Row {$index}: Empty Key";
                continue;
            }
            
            // NOTE: We do NOT skip if row[1] is unset; we default to empty string to ensure keys exist
            $value = isset($row[1]) ? trim((string)$row[1]) : '';
            
            $mapped[$key] = $value;
        }
        
        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            $logger->info('Processed Sheet Values', [
            'keys_found' => array_keys($mapped), // Check this list in logs to see if developer keys are present
            'rows_skipped' => $skipped
            ]);
        }
        
        return $mapped;
    }
    
    /**
        * Resolve the content payload, preferring the on-disk cache and falling back
        * to fresh Google requests if necessary.
        *
        * @param array<string, mixed> $config
        * @return array<string, mixed>
    */
    function site_content_resolve_payload(array $config, ?AppLogger $logger = null): array
    {
        // Hold per-environment payloads in-memory to avoid repeated disk or API reads.
        static $payloads = [];
        $env = (string) ($config['environment'] ?? APP_ENV_PROD);
        
        if (array_key_exists($env, $payloads))
        {
            return $payloads[$env];
        }
        
        // Prefer on-disk cache first, then fall back to fresh Google fetches.
        $cached = site_content_load_cached_payload($config, $logger);
        if ($cached !== null)
        {
            $payloads[$env] = $cached;
            return $payloads[$env];
        }
        
        $payloads[$env] = site_content_fetch_payload($config, $logger);
        site_content_save_cache($config, $payloads[$env], $logger);
        
        return $payloads[$env];
    }
    
    /**
        * Load key/value content from the cached payload (or Google when needed).
        *
        * @param array<string, mixed> $config
        * @return array<string, string>
    */
    function site_content_values(array $config, ?AppLogger $logger = null): array
    {
        static $cache = [];
        $env = (string) ($config['environment'] ?? APP_ENV_PROD);
        
        if (array_key_exists($env, $cache))
        {
            return $cache[$env];
        }
        
        $payload = site_content_resolve_payload($config, $logger);
        $values = $payload['values'] ?? [];
        
        $cache[$env] = is_array($values) ? $values : [];
        return $cache[$env];
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
        * One column of quotes (recommended: column A).
        *
        * @param array<string, mixed> $config
        * @return array<int, string>
    */
    function site_content_testimonials(array $config, ?AppLogger $logger = null): array
    {
        static $cache = [];
        $env = (string) ($config['environment'] ?? APP_ENV_PROD);
        
        if (array_key_exists($env, $cache)) {
            return $cache[$env];
        }
        
        $payload = site_content_resolve_payload($config, $logger);
        $items = $payload['testimonials'] ?? [];
        
        $cache[$env] = is_array($items) ? $items : [];
        return $cache[$env];
    }
    
    /**
        * Testimonials directly from Google Sheets (bypassing cache).
        *
        * @param array<string, mixed> $config
        * @return array<int, string>
    */
    function site_content_fetch_testimonials_from_google(array $config, ?AppLogger $logger = null): array
    {
        $googleConfig = $config['google'] ?? [];
        
        $spreadsheetId = $config['google']['testimonials_spreadsheet_id'] ?? '';
        $range         = $config['google']['testimonials_range'] ?? 'Values!A:A';
        
        if ($spreadsheetId === '') {
            return [];
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
        
        return $items;
    }
    
    /**
        * Drive gallery entries directly from Google (bypassing cache).
        *
        * @param array<string, mixed> $config
        * @return array<int, array<string, string>>
    */
    function site_content_fetch_gallery_images_from_google(array $config, ?AppLogger $logger = null): array
    {
        $googleConfig = $config['google'] ?? [];
        $folderId = (string) ($config['google']['gallery_folder_id'] ?? '');
        
        if ($folderId === '') {
            return [];
        }
        
        $files = google_drive_list_images_in_folder($googleConfig, $folderId, 80, $logger);
        
        // Sort by file name so the order is deterministic for caching and rotations.
        usort($files, static function (array $a, array $b): int {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });
        
        $images = [];
        foreach ($files as $f) {
            $id   = (string) ($f['id'] ?? '');
            $name = (string) ($f['name'] ?? '');
            
            if ($id === '') continue;
            
            $images[] = [
            'id' => $id,
            'name' => $name,
            'url' => google_drive_build_image_url($id, 1600),
            ];
        }
        
        // Remove duplicate IDs to prevent broken rotations when Drive contains aliases.
        return array_values(array_unique($images, SORT_REGULAR));
    }
    
    function site_content_fetch_alumni_images_from_google(array $config, ?AppLogger $logger = null): array
    {
        $googleConfig = $config['google'] ?? [];
        $folderId = (string) ($config['google']['alumni_folder_id'] ?? '');
        
        if ($folderId === '') {
            return [];
        }
        
        $files = google_drive_list_images_in_folder($googleConfig, $folderId, 80, $logger);
        
        // Sort by file name so the order is deterministic for caching and rotations.
        usort($files, static function (array $a, array $b): int {
            return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });
        
        $images = [];
        foreach ($files as $f) {
            $id   = (string) ($f['id'] ?? '');
            $name = (string) ($f['name'] ?? '');
            
            if ($id === '') continue;
            
            $images[] = [
            'id' => $id,
            'name' => $name,
            'url' => google_drive_build_image_url($id, 1600),
            ];
        }
        
        // Remove duplicate IDs to prevent broken rotations when Drive contains aliases.
        return array_values(array_unique($images, SORT_REGULAR));
    }

/**
     * Fetch the first PDF from the configured folder.
     *
     * @param array<string, mixed> $config
     * @return string
     */
    function site_content_fetch_program_flyer_pdf_from_google(array $config, ?AppLogger $logger = null): string
    {
        $googleConfig = $config['google'] ?? [];
        // Ensure 'catalytic_pdf_folder_id' is defined in your app/config.php
        $folderId = (string) ($config['google']['program_flyer_folder_id'] ?? '');

        if ($folderId === '') {
            return '';
        }

        // List files specifically looking for PDFs
        $files = google_drive_list_files_in_folder($googleConfig, $folderId, 'application/pdf', 1, $logger);

        if (empty($files)) {
            return '';
        }

        $id = (string) ($files[0]['id'] ?? '');

        if ($id === '') {
            return '';
        }

        // Return standard viewer URL
        return 'https://drive.google.com/file/d/' . $id . '/view';
    }

    /**
        * Aggregate all Google-backed content into a single payload for caching.
        *
        * @param array<string, mixed> $config
        * @return array<string, mixed>
    */
    function site_content_fetch_payload(array $config, ?AppLogger $logger = null): array
    {
        // Gather all Google-backed resources together so cache writes remain atomic.
        $values = site_content_fetch_values_from_google($config, $logger);
        $testimonials = site_content_fetch_testimonials_from_google($config, $logger);
        $programImages = site_content_fetch_gallery_images_from_google($config, $logger);
        $alumniImages = site_content_fetch_alumni_images_from_google($config, $logger);
        $programFlyerPdf = site_content_fetch_program_flyer_pdf_from_google($config, $logger);
        
        $developerUsername = (string) ($values['developer_mode_username'] ?? '');
        $developerPassword = (string) ($values['developer_mode_password'] ?? '');
        
        // Debug check for credentials before hashing
        if ($logger instanceof AppLogger && $logger->isEnabled()) {
            if ($developerUsername === '') {
                $logger->error('Missing developer_mode_username in Sheet values');
            }
            if ($developerPassword === '') {
                $logger->error('Missing developer_mode_password in Sheet values');
            }
        }
        
        return [
        'generated_at' => time(),
        'values' => $values,
        'testimonials' => $testimonials,
        'programImages' => $programImages,
        'alumniImages' => $alumniImages,
        'programFlyerPdf' => $programFlyerPdf,
        'developer_mode_username_hash' => hash('sha256', $developerUsername),
        'developer_mode_password_hash' => hash('sha256', $developerPassword),
        ];
    }
    
    /**
        * Gallery images sourced from the cached payload (or Google as a fallback).
        *
        * @param array<string, mixed> $config
        * @return array<int, array<string, string>>
    */
    function site_content_gallery_program_images(array $config, ?AppLogger $logger = null): array
    {
        $payload = site_content_resolve_payload($config, $logger);
        $programImages = $payload['programImages'] ?? [];
        
        return is_array($programImages) ? $programImages : [];
    }
    function site_content_alumni_images(array $config, ?AppLogger $logger = null): array
    {
        $payload = site_content_resolve_payload($config, $logger);
        $programImages = $payload['alumniImages'] ?? [];
        
        return is_array($programImages) ? $programImages : [];
    }
    function site_content_program_flyer_pdf(array $config, ?AppLogger $logger = null): string
        {
            $payload = site_content_resolve_payload($config, $logger);
            return (string) ($payload['programFlyerPdf'] ?? '');
        }
