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
        'contactFirstName','contactLastName','contactEmail','contactPhone','contactPhoneType','currentWork','yearAttended',
    ];
    $data = sanitize_payload($payload, $fields);

    validate_required($data, ['contactFirstName','contactEmail']);
    validate_email($data['contactEmail'], 'contactEmail');

    $googleConfig = $config['google'] ?? [];

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
            $data['currentWork'],
            $data['yearAttended'],
        ],
        $logger
    );

    $recipients = $config['email']['recipients'] ?? [];
    if (!empty($recipients)) {
        $from = $config['google']['gmail_sender'] ?? ($recipients[0] ?? '');
        $subject = sprintf('New contact info: %s %s', $data['contactFirstName'], $data['contactLastName']);
        $body = sprintf(
            "Timestamp: %s\nFirst Name: %s\nLast Name: %s\nEmail: %s\nPhone: %s\nPhone Type: %s\nOrganization: %s\nProgram Year: %s\n",
            $timestamp,
            $data['contactFirstName'],
            $data['contactLastName'],
            $data['contactEmail'],
            $data['contactPhone'],
            $data['contactPhoneType'],
            $data['currentWork'],
            $data['yearAttended']
        );
        gmail_send_message($googleConfig, $from, $recipients, $subject, $body, $logger);
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
        'applicantPhoneType','addressOne','addressTwo','city','state','zip','vegan','vegetarian','diet','accommodations','org',
        'title','supName','supEmail','supPhone','refferalQuestion','questionOne','questionTwo','questionThree','questionFour',
        'partialScholarship','assistAmount',
    ];

    $data = sanitize_payload($payload, $fields);

    $required = [
        'applicantFirstName','applicantLastName','applicantEmail','applicantPhone','applicantPhoneType','addressOne','city',
        'state','zip','vegan','vegetarian','org','title','supName','supEmail','supPhone','refferalQuestion','questionOne',
        'questionTwo','questionThree','questionFour',
    ];
    validate_required($data, $required);
    validate_email($data['applicantEmail'], 'applicantEmail');
    validate_email($data['supEmail'], 'supEmail');

    $googleConfig = $config['google'] ?? [];

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
            $data['zip'],
            $data['vegan'],
            $data['vegetarian'],
            $data['diet'],
            $data['accommodations'],
            $data['org'],
            $data['title'],
            $data['supName'],
            $data['supEmail'],
            $data['supPhone'],
            $data['refferalQuestion'],
            $data['questionOne'],
            $data['questionTwo'],
            $data['questionThree'],
            $data['questionFour'],
            $data['partialScholarship'],
            $data['assistAmount'],
        ],
        $logger
    );

    $recipients = $config['email']['recipients'] ?? [];
    if (!empty($recipients)) {
        $from = $config['google']['gmail_sender'] ?? ($recipients[0] ?? '');
        $subject = sprintf('New application: %s %s', $data['applicantFirstName'], $data['applicantLastName']);
        $body = build_application_email_body($timestamp, $data);
        gmail_send_message($googleConfig, $from, $recipients, $subject, $body, $logger);
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
        'Timestamp: ' . $timestamp,
        'Applicant: ' . $data['applicantFirstName'] . ' ' . $data['applicantLastName'],
        'Preferred Name: ' . $data['applicantPreferredName'],
        'Pronouns: ' . $data['applicantPronouns'],
        'Email: ' . $data['applicantEmail'],
        'Phone: ' . $data['applicantPhone'] . ' (' . $data['applicantPhoneType'] . ')',
        'Address: ' . $data['addressOne'],
        'Address (line 2): ' . $data['addressTwo'],
        'City/State/Zip: ' . $data['city'] . ', ' . $data['state'] . ' ' . $data['zip'],
        'Vegan: ' . $data['vegan'],
        'Vegetarian: ' . $data['vegetarian'],
        'Dietary: ' . $data['diet'],
        'Accessibility: ' . $data['accommodations'],
        'Organization: ' . $data['org'],
        'Title: ' . $data['title'],
        'Sponsor: ' . $data['supName'],
        'Sponsor Email: ' . $data['supEmail'],
        'Sponsor Phone: ' . $data['supPhone'],
        'Referral: ' . $data['refferalQuestion'],
        'Leadership Responsibilities: ' . $data['questionOne'],
        'Partnership Experience: ' . $data['questionTwo'],
        'Community Collaboration: ' . $data['questionThree'],
        'Desired Takeaways: ' . $data['questionFour'],
        'Partial Scholarship: ' . $data['partialScholarship'],
        'Assistance Amount: ' . $data['assistAmount'],
    ];

    return implode("\n", $lines);
}
