<?php

declare(strict_types=1);

/**
 * Application configuration.
 * This is the ONLY file that reads from getenv()/$_ENV.
 */

return [
    'google' => [
        // OAuth 2.0 client info from Google Cloud
        'oauth_client_id'     => getenv('GOOGLE_OAUTH_CLIENT_ID')     ?: '',
        'oauth_client_secret' => getenv('GOOGLE_OAUTH_CLIENT_SECRET') ?: '',
        'oauth_redirect_uri'  => getenv('GOOGLE_OAUTH_REDIRECT_URI')  ?: '',

        // Scopes needed for Sheets + Gmail
        'oauth_scopes'        => [
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/gmail.send',
        ],

        // Gmail sender address
        'gmail_sender' => getenv('GOOGLE_GMAIL_SENDER') ?: 'contact@thelukecenter.org',

        // Site values sheet (used by content.php)
        'site_values_spreadsheet_id' => getenv('GOOGLE_SITE_VALUES_SHEET_ID') ?: '',
        'site_values_range' => getenv('GOOGLE_SITE_VALUES_RANGE') ?: 'Values!A:B',

        // Contact form sheet
        'contact_spreadsheet_id' => getenv('GOOGLE_CONTACT_SHEET_ID') ?: '',
        'contact_sheet_tab' => getenv('GOOGLE_CONTACT_SHEET_TAB') ?: 'Submissions',

        // Application form sheet
        'application_spreadsheet_id' => getenv('GOOGLE_APPLICATION_SHEET_ID') ?: '',
        'application_sheet_tab' => getenv('GOOGLE_APPLICATION_SHEET_TAB') ?: 'Submissions',
        
        // Shared token location (above web roots)
        'token_base_dir' => '/home3/bnrortmy',
        'token_subdir'   => 'tokenStorage',
    ],

    'email' => [
        // Comma-separated in env: "a@x.org,b@y.org"
        'recipients' => array_values(array_filter(array_map(
            'trim',
            explode(',', getenv('EMAIL_RECIPIENTS') ?: 'contact@thelukecenter.org')
        ))),
    ],
];
