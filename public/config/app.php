<?php

declare(strict_types=1);

return [
    'google' => [
        'credentials_path' => getenv('GOOGLE_APPLICATION_CREDENTIALS') ?: __DIR__ . '/service-account.json',
        'delegated_user' => getenv('GOOGLE_DELEGATED_USER') ?: null,
        'site_values_spreadsheet_id' => getenv('GOOGLE_SITE_VALUES_SHEET_ID') ?: '',
        'site_values_range' => getenv('GOOGLE_SITE_VALUES_RANGE') ?: 'Values!A:B',
        'contact_spreadsheet_id' => getenv('GOOGLE_CONTACT_SHEET_ID') ?: '',
        'contact_sheet_tab' => getenv('GOOGLE_CONTACT_SHEET_TAB') ?: 'Submissions',
        'application_spreadsheet_id' => getenv('GOOGLE_APPLICATION_SHEET_ID') ?: '',
        'application_sheet_tab' => getenv('GOOGLE_APPLICATION_SHEET_TAB') ?: 'Submissions',
    ],
    'email' => [
        'recipients' => array_filter(array_map('trim', explode(',', getenv('APP_EMAIL_RECIPIENTS') ?: 'admin@thelukecenter.org'))),
    ],
];
