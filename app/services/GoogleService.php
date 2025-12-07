<?php
    declare(strict_types=1);

    /**
     * Commenting convention:
     * - Docblocks summarize function intent along with key inputs/outputs.
     * - Inline context comments precede major initialization, configuration, or external calls.
     */

    require_once __DIR__ . '/Logger.php';
    
    /**
        * Google API helpers for Sheets and Gmail via OAuth 2.0.
        * Token storage path is controlled from app/config.php via:
        * 'token_base_dir' => '/home3/youraccount',
        * 'token_subdir'   => 'tokenStorage',
        * // optional: 'token_file' => 'google-api-oauth-token.json'
    */
    
    /* ============================================================================
        * CONFIG HELPERS
    * ==========================================================================*/
    
    /**
        * Normalize OAuth configuration array from app.php.
        *
        * @param array<string,mixed> $googleConfig
        * @return array{
        * client_id:string,
        * client_secret:string,
        * redirect_uri:string,
        * scopes:array<int,string>,
        * token_base_dir:string,
        * token_subdir:string,
        * token_file:string
        * }
    */
    function google_normalize_oauth_config(array $googleConfig): array
    {
        $clientId     = $googleConfig['oauth_client_id']     ?? $googleConfig['client_id']     ?? '';
        $clientSecret = $googleConfig['oauth_client_secret'] ?? $googleConfig['client_secret'] ?? '';
        $redirectUri  = $googleConfig['oauth_redirect_uri']  ?? $googleConfig['redirect_uri']  ?? '';
        $scopes       = $googleConfig['oauth_scopes']        ?? $googleConfig['scopes']        ?? [];
        $tokenBaseDir = $googleConfig['token_base_dir']      ?? dirname(__DIR__, 2);
        $tokenSubdir  = $googleConfig['token_subdir']        ?? 'tokenStorage';
        $tokenFile    = $googleConfig['token_file']          ?? 'google-api-oauth-token.json';
        
        if (!$clientId || !$clientSecret || !$redirectUri || empty($scopes)) {
            throw new RuntimeException('Missing required OAuth configuration fields.');
        }
        
        return [
        'client_id'      => $clientId,
        'client_secret'  => $clientSecret,
        'redirect_uri'   => $redirectUri,
        'scopes'         => is_array($scopes) ? $scopes : preg_split('/\s+/', (string) $scopes),
        'token_base_dir' => $tokenBaseDir,
        'token_subdir'   => $tokenSubdir,
        'token_file'     => $tokenFile,
        ];
    }
    
    /**
        * Compute the token directory path (above web root).
    */
    function google_get_token_dir(array $googleConfig): string
    {
        $oauth = google_normalize_oauth_config($googleConfig);
        
        $dir = rtrim($oauth['token_base_dir'], DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . trim($oauth['token_subdir'], DIRECTORY_SEPARATOR);
        
        // CHANGED: 0700 -> 0755 to ensure cron/group users can traverse into this folder
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return $dir;
    }
    
    /**
        * Full path to the token JSON file.
    */
    function google_get_token_path(array $googleConfig): string
    {
        $oauth = google_normalize_oauth_config($googleConfig);
        
        return google_get_token_dir($googleConfig)
        . DIRECTORY_SEPARATOR
        . $oauth['token_file'];
    }
    
    /* ============================================================================
        * TOKEN HANDLERS
    * ==========================================================================*/
    
    /**
        * @return array<string,mixed>|null
    */
    function google_load_token(array $googleConfig): ?array
    {
        $path = google_get_token_path($googleConfig);
        if (!file_exists($path)) {
            return null;
        }
        
        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
    
    /**
        * @param array<string,mixed> $token
    */
    function google_save_token(array $googleConfig, array $token): void
    {
        $path = google_get_token_path($googleConfig);
        $dir  = dirname($path);
        
        // CHANGED: 0700 -> 0755 for directory creation
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $json = json_encode($token, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($path, $json, LOCK_EX);
        
        // CHANGED: 0600 -> 0644 to ensure Group (cron) can read the file
        @chmod($path, 0644);
    }
    
    /**
        * Get a valid access token, refreshing if needed.
    */
    function google_get_or_refresh_access_token(array $googleConfig): string
    {
        $token = google_load_token($googleConfig);
        
        if ($token && !empty($token['access_token']) && ($token['expires_at'] ?? 0) > time()) {
            return $token['access_token'];
        }
        
        if ($token && !empty($token['refresh_token'])) {
            return google_refresh_access_token($googleConfig, $token);
        }
        
        throw new RuntimeException(
        'No valid OAuth token found. Run authorize.php or oauth2Callback.php flow to generate one.'
        );
    }
    
    /**
        * Exchange an authorization code for access/refresh tokens and persist them.
        *
        * Used by oauth2Callback.php (and optionally authorize.php).
        *
        * @return array<string,mixed> Full token payload from Google (plus expires_at).
    */
    function google_exchange_code_for_tokens(array $googleConfig, string $code): array
    {
        $oauth = google_normalize_oauth_config($googleConfig);
        
        $post = [
        'code'          => $code,
        'client_id'     => $oauth['client_id'],
        'client_secret' => $oauth['client_secret'],
        'redirect_uri'  => $oauth['redirect_uri'],
        'grant_type'    => 'authorization_code',
        ];
        
        $resp = google_http_raw(
        'https://oauth2.googleapis.com/token',
        $post,
        'POST',
        'application/x-www-form-urlencoded'
        );
        
        $data = json_decode($resp, true);
        
        if (!is_array($data) || !isset($data['access_token'])) {
            throw new RuntimeException('OAuth exchange failed: ' . $resp);
        }
        
        // Normalise expiry and persist the full payload so refresh can reuse it.
        $data['expires_at'] = time() + ((int) ($data['expires_in'] ?? 3600)) - 60;
        
        google_save_token($googleConfig, $data);
        
        return $data;
    }
    
    /**
        * Refresh an access token using a stored refresh token.
        *
        * @param array<string,mixed> $oldToken
    */
    function google_refresh_access_token(array $googleConfig, array $oldToken): string
    {
        $oauth = google_normalize_oauth_config($googleConfig);
        
        $post = [
        'client_id'     => $oauth['client_id'],
        'client_secret' => $oauth['client_secret'],
        'refresh_token' => $oldToken['refresh_token'] ?? '',
        'grant_type'    => 'refresh_token',
        ];
        
        $result = google_http_raw(
        'https://oauth2.googleapis.com/token',
        $post,
        'POST',
        'application/x-www-form-urlencoded'
        );
        
        $data = json_decode($result, true);
        if (!is_array($data) || !isset($data['access_token'])) {
            throw new RuntimeException('Failed to refresh access token: ' . $result);
        }
        
        $token = [
        'access_token'  => $data['access_token'],
        // Keep the original refresh token; Google often does not resend it.
        'refresh_token' => $oldToken['refresh_token'] ?? '',
        'expires_at'    => time() + ((int) ($data['expires_in'] ?? 3600)) - 60,
        ];
        
        google_save_token($googleConfig, $token);
        
        return $token['access_token'];
    }
    
    /* ============================================================================
        * HTTP HELPERS
    * ==========================================================================*/
    
    /**
        * Low-level HTTP wrapper using cURL.
        *
        * @param array<string,mixed>|null $body
        * @param array<int,string>        $headers
    */
    function google_http_raw(
    string $url,
    ?array $body = null,
    string $method = 'GET',
    string $contentType = 'application/x-www-form-urlencoded',
    array $headers = [],
    ?AppLogger $logger = null
    ): string {
        if ($logger !== null) {
            $logger->info('Calling Google endpoint', ['url' => $url, 'method' => $method]);
        }
        $ch = curl_init($url);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($body !== null) {
            // Encode the request body consistently and add the corresponding content type.
            if ($contentType === 'application/json') {
                $payload = json_encode($body);
                } else {
                $payload = http_build_query($body);
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers[] = 'Content-Type: ' . $contentType;
        }
        
        $headers[] = 'Accept: application/json';
        $headers[] = 'Expect:'; // Avoid "100-continue" delays.
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            if ($logger !== null) {
                $logger->error('cURL error', ['url' => $url, 'error' => $err]);
            }
            throw new RuntimeException('cURL error: ' . $err);
        }
        
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        
        if ($status >= 400) {
            if ($logger !== null) {
                $logger->error('HTTP error from Google', ['status' => $status, 'response' => $response]);
            }
            throw new RuntimeException("HTTP {$status}: {$response}");
        }
        
        return $response;
    }
    
    /**
        * Higher-level JSON helper that automatically attaches the OAuth token.
        *
        * @param array<string,mixed>|null $body
        * @param array<string,mixed>      $googleConfig
        * @return array<string,mixed>
    */
    function google_http_request(
    string $url,
    array $params = [],
    ?array $body = null,
    string $method = 'GET',
    string $contentType = 'application/json',
    array $googleConfig = [],
    ?AppLogger $logger = null
    ): array {
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        
        $token   = google_get_or_refresh_access_token($googleConfig);
        $headers = ['Authorization: Bearer ' . $token];
        
        $resp = google_http_raw($url, $body, $method, $contentType, $headers, $logger);
        $json = json_decode($resp, true);
        
        return is_array($json) ? $json : [];
    }
    
    /* ============================================================================
        * DRIVE HELPERS
    * ==========================================================================*/
    
    /**
        * Build a public-ish image URL for Drive-hosted images.
        * Your site already uses the googleusercontent "d/<id>" pattern elsewhere,
        * so this matches your existing approach.
    */
    function google_drive_build_image_url(string $fileId, int $width = 1600): string
    {
        $safeWidth = max(200, $width);
        return 'https://lh3.googleusercontent.com/d/' . $fileId . '=w' . $safeWidth;
    }
    
    /**
        * List image files contained directly in a Drive folder.
        *
        * @param array<string,mixed> $googleConfig
        * @return array<int, array<string,mixed>>
    */
    function google_drive_list_images_in_folder(
    array $googleConfig,
    string $folderId,
    int $pageSize = 50,
    ?AppLogger $logger = null
    ): array {
        if ($folderId === '') 
        {
            return [];
        }
        
        $q = sprintf("'%s' in parents and trashed = false and mimeType contains 'image/'", $folderId);
        
        $params = [
        'q' => $q,
        'pageSize' => max(1, min(200, $pageSize)),
        'orderBy' => 'createdTime desc',
        'fields' => 'files(id,name,mimeType,createdTime,modifiedTime,thumbnailLink,webViewLink),nextPageToken',
        'supportsAllDrives' => 'true',
        'includeItemsFromAllDrives' => 'true',
        ];
        
        $resp = google_http_request(
        'https://www.googleapis.com/drive/v3/files',
        $params,
        null,
        'GET',
        'application/json',
        $googleConfig,
        $logger
        );
        
        $files = $resp['files'] ?? [];
        return is_array($files) ? $files : [];
    }
    
    /* ============================================================================
        * SHEETS + GMAIL HELPERS
    * ==========================================================================*/
    
    /**
        * Fetch values from a Google Sheet.
        *
        * @param array<string,mixed>  $googleConfig
        * @param array<string,string> $sheetConfig   e.g. ['spreadsheet_id' => '...', 'range' => 'Sheet!A:B']
        * @param string|null          $rangeOverride Optional A1 range override.
        * @return array<int,array<int,string>>
    */
    function google_sheets_get_values(
    array $googleConfig,
    array $sheetConfig,
    ?string $rangeOverride = null,
    ?AppLogger $logger = null
    ): array {
        $spreadsheetId = $sheetConfig['spreadsheet_id'] ?? '';
        $defaultRange  = $sheetConfig['range'] ?? '';
        $range         = ($rangeOverride !== null && $rangeOverride !== '')
        ? $rangeOverride
        : $defaultRange;
        
        if ($spreadsheetId === '' || $range === '') {
            throw new RuntimeException('Missing spreadsheet_id or range for google_sheets_get_values().');
        }
        
        $url = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
        rawurlencode($spreadsheetId),
        rawurlencode($range)
        );
        
        $response = google_http_request(
        $url,
        [],
        null,
        'GET',
        'application/json',
        $googleConfig,
        $logger
        );
        
        $values = $response['values'] ?? [];
        
        return is_array($values) ? $values : [];
    }
    
    /**
        * Append a single row to a Sheet.
        *
        * @param array<string,mixed> $googleConfig
        * @param array<int,string>   $values
    */
    function google_sheets_append_row(
    array $googleConfig,
    string $spreadsheetId,
    string $range,
    array $values,
    ?AppLogger $logger = null
    ): void {
        if ($spreadsheetId === '' || $range === '') {
            throw new RuntimeException('Missing spreadsheetId or range for google_sheets_append_row().');
        }
        
        $url = sprintf(
        'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append',
        rawurlencode($spreadsheetId),
        rawurlencode($range)
        );
        
        google_http_request(
        $url,
        ['valueInputOption' => 'USER_ENTERED', 'insertDataOption' => 'INSERT_ROWS'],
        ['values' => [$values]],
        'POST',
        'application/json',
        $googleConfig,
        $logger
        );
    }
    
    /**
        * Send a plaintext email via Gmail API.
        *
        * @param array<string,mixed> $googleConfig
        * @param array<int,string>   $to
    */
    function gmail_send_message(
    array $googleConfig,
    string $from,
    array $to,
    string $subject,
    string $body,
    ?AppLogger $logger = null
    ): void {
        // Build raw RFC 2822 message.
        $headers = [
        sprintf('From: %s', $from),
        sprintf('To: %s', implode(', ', $to)),
        sprintf('Subject: %s', $subject),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        ];
        
        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
        
        // URL-safe base64.
        $encoded = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
        
        google_http_request(
        'https://gmail.googleapis.com/gmail/v1/users/me/messages/send',
        [],
        ['raw' => $encoded],
        'POST',
        'application/json',
        $googleConfig,
        $logger
        );
    }    