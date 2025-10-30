<?php

declare(strict_types=1);

use Google\Service\Gmail;
use Google\Service\Sheets;
use TheLukeCenter\Services\ApplicationFormHandler;
use TheLukeCenter\Services\ContactFormHandler;
use TheLukeCenter\Services\GmailMailer;
use TheLukeCenter\Services\GoogleClientFactory;
use TheLukeCenter\Services\GoogleSheetsService;
use TheLukeCenter\Services\SiteContentService;
use TheLukeCenter\Support\Config;

$rootDir = dirname(__DIR__);

$config = Config::load($rootDir . '/config/app.php');

$googleConfig = [
    'credentials_path' => $config['google']['credentials_path'] ?? '',
    'delegated_user' => $config['google']['delegated_user'] ?? null,
];

$sheetsClient = GoogleClientFactory::make(
    $googleConfig,
    [Sheets::SPREADSHEETS]
);

$gmailClient = GoogleClientFactory::make(
    $googleConfig,
    [Gmail::GMAIL_SEND]
);

$siteValuesSheets = new GoogleSheetsService(
    new Sheets($sheetsClient),
    $config['google']['site_values_spreadsheet_id'] ?? ''
);

$siteContentService = new SiteContentService(
    $siteValuesSheets,
    $config['google']['site_values_range'] ?? 'Values!A:B'
);

$contactSheets = new GoogleSheetsService(
    new Sheets($sheetsClient),
    $config['google']['contact_spreadsheet_id'] ?? ''
);

$applicationSheets = new GoogleSheetsService(
    new Sheets($sheetsClient),
    $config['google']['application_spreadsheet_id'] ?? ''
);

$gmailMailer = new GmailMailer(new Gmail($gmailClient), $config['google']['delegated_user'] ?? null);

$contactFormHandler = new ContactFormHandler(
    $contactSheets,
    $gmailMailer,
    $config['google']['contact_sheet_tab'] ?? 'Submissions',
    $config['email']['recipients'] ?? []
);

$applicationFormHandler = new ApplicationFormHandler(
    $applicationSheets,
    $gmailMailer,
    $config['google']['application_sheet_tab'] ?? 'Submissions',
    $config['email']['recipients'] ?? []
);

return [
    'config' => $config,
    'siteContentService' => $siteContentService,
    'contactFormHandler' => $contactFormHandler,
    'applicationFormHandler' => $applicationFormHandler,
];
