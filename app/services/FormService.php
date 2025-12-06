<?php

declare(strict_types=1);

require_once __DIR__ . '/GoogleService.php';
require_once __DIR__ . '/Logger.php';

/**
 * @param array<string, mixed> $config
 * @param array<string, string> $payload
 */
function handle_contact_submission(array $config, array $payload, ?AppLogger $logger = null): void
{
    $fields = [
        'contactFirstName','contactLastName','contactEmail','contactPhone','contactPhoneType','currentOrganization','yearAttended',
    ];
    $data = sanitize_payload($payload, $fields);

    validate_required($data, ['contactFirstName','contactLastName','contactEmail','contactPhone','contactPhoneType','yearAttended']);
    validate_email($data['contactEmail'], 'contactEmail');

    $googleConfig = $config['google'] ?? [];

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Contact form submission received', [
            'request_id' => $logger->getRequestId(),
            'fields' => array_keys($data),
        ]);
    }

    $timestamp = (new DateTimeImmutable('now'))->format('c');
    google_sheets_append_row(
        $googleConfig,
        $config['google']['contact_spreadsheet_id'] ?? '',
        ($config['google']['contact_sheet_tab'] ?? 'Submissions') . '!A:H',
        [
            $timestamp,
            $data['contactFirstName'],
            $data['contactLastName'],
            $data['contactEmail'],
            $data['contactPhone'],
            $data['contactPhoneType'],
            $data['currentOrganization'],
            $data['yearAttended'],
        ],
        $logger
    );

    $recipients = $config['email']['recipients'] ?? [];
    if (!empty($recipients)) 
    {
        // Add form submitor to email recipients list.
        array_push($recipients, $data['contactEmail']);
        
        $from = $config['google']['gmail_sender'] ?? ($recipients[0] ?? '');
        $subject = sprintf('New contact info: %s %s', $data['contactFirstName'], $data['contactLastName']);
        $body = build_contact_email_body($timestamp, $data);
        gmail_send_message($googleConfig, $from, $recipients, $subject, $body, $logger);
    }

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Contact form submission completed', [
            'request_id' => $logger->getRequestId(),
            'sheet' => $config['google']['contact_spreadsheet_id'] ?? 'unknown',
        ]);
    }
}

/**
 * @param array<string, mixed> $config
 * @param array<string, string> $payload
 */
function handle_application_submission(array $config, array $payload, ?AppLogger $logger = null): void
{
    $fields = [
        'applicantFirstName','applicantLastName','applicantPreferredName','applicantPronouns','applicantEmail','applicantPhone',
        'applicantPhoneType','addressOne','addressTwo','city','state','zipCode','vegan','vegetarian','dietaryRestrictions','accessibilityNeeds','applicantOrganiaztion',
        'currentTitle','sponsorName','sponsorEmail','sponsorPhone','questionOne','questionTwo','questionThree','questionFour','questionFive',
        'scholarshipQuestion','scholarshipAmount',
    ];

    $data = sanitize_payload($payload, $fields);

    $required = [
        'applicantFirstName','applicantLastName','applicantEmail','applicantPhone','applicantPhoneType','addressOne','city',
        'state','zipCode','vegan','vegetarian','applicantOrganiaztion','currentTitle','sponsorName','sponsorEmail','sponsorPhone','questionOne',
        'questionTwo','questionThree','questionFour', 'questionFive',
    ];
    validate_required($data, $required);
    validate_email($data['applicantEmail'], 'applicantEmail');
    validate_email($data['sponsorEmail'], 'sponsorEmail');

    $googleConfig = $config['google'] ?? [];

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Application submission received', [
            'request_id' => $logger->getRequestId(),
            'fields' => array_keys($data),
        ]);
    }

    // // BEGIN LOGGING
    // $log_file = 'forms.php.log';
    // // Add a timestamp and a newline character to the message
    // $log_entry = date("[Y-m-d H:i:s]") . " - Array: " . PHP_EOL . print_r($config['google'], TRUE) . PHP_EOL;

    // // Append the log entry to the file
    // // FILE_APPEND ensures it adds to the end, LOCK_EX prevents data corruption during simultaneous writes
    // file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX); 
    // // END LOGGING

    $timestamp = (new DateTimeImmutable('now'))->format('c');
    google_sheets_append_row(
        $googleConfig,
        $config['google']['application_spreadsheet_id'] ?? '',
        ($config['google']['application_sheet_tab'] ?? 'Submissions') . '!A:AA',
        [
            $timestamp,
            $data['applicantFirstName'],
            $data['applicantLastName'],
            $data['applicantPreferredName'],
            $data['applicantPronouns'],
            $data['applicantEmail'],
            $data['applicantPhone'],
            $data['applicantPhoneType'],
            $data['addressOne'],
            $data['addressTwo'],
            $data['city'],
            $data['state'],
            $data['zipCode'],
            $data['vegan'],
            $data['vegetarian'],
            $data['dietaryRestrictions'],
            $data['accessibilityNeeds'],
            $data['applicantOrganiaztion'],
            $data['currentTitle'],
            $data['sponsorName'],
            $data['sponsorEmail'],
            $data['sponsorPhone'],
            $data['questionOne'],
            $data['questionTwo'],
            $data['questionThree'],
            $data['questionFour'],
            $data['questionFive'],
            $data['scholarshipQuestion'],
            $data['scholarshipAmount'],
        ],
        $logger
    );

    $recipients = $config['email']['recipients'] ?? [];
    if (!empty($recipients)) 
    {
        // Add form submitor to email recipients list.
        array_push($recipients, $data['applicantEmail']);
        
        $from = $config['google']['gmail_sender'] ?? ($recipients[0] ?? '');
        $subject = sprintf('New application: %s %s', $data['applicantFirstName'], $data['applicantLastName']);
        $body = build_application_email_body($timestamp, $data);
        gmail_send_message($googleConfig, $from, $recipients, $subject, $body, $logger);
    }

    if ($logger instanceof AppLogger && $logger->isEnabled()) {
        $logger->info('Application submission completed', [
            'request_id' => $logger->getRequestId(),
            'sheet' => $config['google']['application_spreadsheet_id'] ?? 'unknown',
        ]);
    }
}

/**
 * @param array<string, string> $payload
 * @param array<int, string> $allowed
 * @return array<string, string>
 */
function sanitize_payload(array $payload, array $allowed): array
{
    $data = [];
    foreach ($allowed as $field) {
        $data[$field] = trim((string) ($payload[$field] ?? ''));
    }

    return $data;
}

/**
 * @param array<string, string> $data
 * @param array<int, string> $fields
 */
function validate_required(array $data, array $fields): void
{
    foreach ($fields as $field) {
        if ($data[$field] === '') {
            throw new InvalidArgumentException('Missing required field: ' . $field);
        }
    }
}

function validate_email(string $value, string $field): void
{
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email for field: ' . $field);
    }
}

/**
 * @param array<string, string> $data
 */
function build_application_email_body(string $timestamp, array $data): string
{
    $lines = [
        'Hello ' . $data['applicantFirstName'] . ' ' . $data['applicantLastName'] . ',',
        '',
        'Thank you for your application to the upcomming Pacific Program. Your application has been recieved and will be reviewed as soon as possible. Someone will reply back with more information soon.',
        '',
        'Sincerely,',
        'The Luke Center Board',
        '',
        '----------',
        'Form Submission Data Included Below',
        '----------',
        '',
        'Timestamp: ' . $timestamp,
        'Applicant: ' . $data['applicantFirstName'] . ' ' . $data['applicantLastName'],
        'Preferred Name: ' . $data['applicantPreferredName'],
        'Pronouns: ' . $data['applicantPronouns'],
        'Email: ' . $data['applicantEmail'],
        'Phone: ' . $data['applicantPhone'] . ' (' . $data['applicantPhoneType'] . ')',
        'Address: ' . $data['addressOne'],
        'Address (line 2): ' . $data['addressTwo'],
        'City/State/Zip: ' . $data['city'] . ', ' . $data['state'] . ' ' . $data['zipCode'],
        'Vegan: ' . $data['vegan'],
        'Vegetarian: ' . $data['vegetarian'],
        'Dietary: ' . $data['dietaryRestrictions'],
        'Accessibility: ' . $data['accessibilityNeeds'],
        'Organization: ' . $data['applicantOrganiaztion'],
        'Title: ' . $data['currentTitle'],
        'Sponsor: ' . $data['sponsorName'],
        'Sponsor Email: ' . $data['sponsorEmail'],
        'Sponsor Phone: ' . $data['sponsorPhone'],
        'Referral: ' . $data['questionOne'],
        'Leadership Responsibilities: ' . $data['questionTwo'],
        'Partnership Experience: ' . $data['questionThree'],
        'Community Collaboration: ' . $data['questionFour'],
        'Desired Takeaways: ' . $data['questionFive'],
        'Partial Scholarship: ' . $data['scholarshipQuestion'],
        'Assistance Amount: ' . $data['scholarshipAmount'],
    ];

    return implode("\n", $lines);
}

/**
 * @param array<string, string> $data
 */
function build_contact_email_body(string $timestamp, array $data): string
{
    $lines = [
        'Hello ' . $data['contactFirstName'] . ' ' . $data['contactLastName'] . ',',
        '',
        'Thank you for updating your contact information with the Luke Center. Your information update has been recieved.',
        '',
        'Sincerely,',
        'The Luke Center Board',
        '',
        '----------',
        'Form Submission Data Included Below',
        '----------',
        '',
        'Timestamp: ' . $timestamp,
        'Name: ' . $data['contactFirstName'] . ' ' . $data['contactLastName'],
        'Updated Email : ' . $data['contactEmail'],
        'Updated Phone: ' . $data['contactPhone'],
        'Updated Phone Type: ' . $data['contactPhoneType'],
        'Updated Organization: ' . $data['currentOrganization'],
        'Year Attended: ' . $data['yearAttended'],

    ];

    return implode("\n", $lines);
}

