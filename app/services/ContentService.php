<?php
    
    declare(strict_types=1);
    
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
            ]);
        }
    }

    /**
        * Pull key/value content directly from Google Sheets.
        *
        * Sheet shape (2 columns):
        *   Col A: key
        *   Col B: value
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
                ? 'Set GOOGLE_SITE_VALUES_SHEET_ID_TEST (or GOOGLE_SITE_VALUES_SHEET_ID_PROD / GOOGLE_SITE_VALUES_SHEET_ID as a fallback).'
                : 'Set GOOGLE_SITE_VALUES_SHEET_ID_PROD (or GOOGLE_SITE_VALUES_SHEET_ID).';
                ? 'Set GOOGLE_SITE_VALUES_SHEET_ID_TEST (or GOOGLE_SITE_VALUES_SHEET_ID_PROD as a fallback).'
                : 'Set GOOGLE_SITE_VALUES_SHEET_ID_PROD.';

            throw new RuntimeException('Missing Google Sheet ID for site values. ' . $hint);
        }

        if ($range === '')
        {
            $hint = $environment === APP_ENV_TEST
                ? 'Set GOOGLE_SITE_VALUES_RANGE_TEST (or GOOGLE_SITE_VALUES_RANGE).'
                : 'Set GOOGLE_SITE_VALUES_RANGE.';

            throw new RuntimeException('Missing range for site values Google Sheet. ' . $hint);
        }

        // Build the sheet configuration array expected by google_sheets_get_values()
        $siteSheetConfig = [
        'spreadsheet_id' => $spreadsheetId,
        'range'          => $range,
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
        static $payloads = [];
        $env = (string) ($config['environment'] ?? APP_ENV_PROD);

        if (array_key_exists($env, $payloads))
        {
            return $payloads[$env];
        }

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
        *   One column of quotes (recommended: column A).
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

    /**
        * Aggregate all Google-backed content into a single payload for caching.
        *
        * @param array<string, mixed> $config
        * @return array<string, mixed>
    */
    function site_content_fetch_payload(array $config, ?AppLogger $logger = null): array
    {
        $values = site_content_fetch_values_from_google($config, $logger);
        $testimonials = site_content_fetch_testimonials_from_google($config, $logger);
        $images = site_content_fetch_gallery_images_from_google($config, $logger);

        return [
            'generated_at' => time(),
            'values' => $values,
            'testimonials' => $testimonials,
            'images' => $images,
        ];
    }

    /**
        * Gallery images sourced from the cached payload (or Google as a fallback).
        *
        * @param array<string, mixed> $config
        * @return array<int, array<string, string>>
    */
    function site_content_gallery_images(array $config, ?AppLogger $logger = null): array
    {
        $payload = site_content_resolve_payload($config, $logger);
        $images = $payload['images'] ?? [];

        return is_array($images) ? $images : [];
    }
