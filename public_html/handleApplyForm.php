<?php
declare(strict_types=1);

$container = require_once __DIR__ . '/app/bootstrap.php';
$config    = $container['config'] ?? [];
$logger    = $container['logger'] ?? null;

$HEADERLOCATION = 'Location: apply.php#applyStatus';

function redirect_with_message(string $message, string $type, string $headerLocation): void
{
    $_SESSION['message']     = $message;
    $_SESSION['messageType'] = $type;

    header($headerLocation);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('Invalid request method. Please use the form to submit data.', 'error', $HEADERLOCATION);
}

$requiredFields = [
    'applicantFirstName',
    'applicantLastName',
    'applicantEmail',
    'applicantPhone',
    'applicantPhoneType',
    'addressOne',
    'city',
    'state',
    'zipCode',
    'vegan',
    'vegetarian',
    'organiaztion',
    'currentTitle',
    'sponsorName',
    'sponsorEmail',
    'sponsorPhone',
    'refferalQuestion',
    'questionOne',
    'questionTwo',
    'questionThree',
    'questionFour',
];

foreach ($requiredFields as $fieldName) {
    if (!isset($_POST[$fieldName])) {
        redirect_with_message('Invalid request parameters. One or more required form parameters is missing.', 'error', $HEADERLOCATION);
    }
}

$payload = [
    // Map HTML field names to the exact keys FormService expects. Keeping the mapping
    // here prevents silent mismatches from breaking validation if the HTML changes.
    'applicantFirstName'     => filter_input(INPUT_POST, 'applicantFirstName', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'applicantLastName'      => filter_input(INPUT_POST, 'applicantLastName', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'applicantPreferredName' => filter_input(INPUT_POST, 'applicantPreferredName', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'applicantPronouns'      => filter_input(INPUT_POST, 'applicantPronouns', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'applicantEmail'         => filter_input(INPUT_POST, 'applicantEmail', FILTER_SANITIZE_EMAIL) ?: '',
    'applicantPhone'         => filter_input(INPUT_POST, 'applicantPhone', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'applicantPhoneType'     => filter_input(INPUT_POST, 'applicantPhoneType', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'addressOne'             => filter_input(INPUT_POST, 'addressOne', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'addressTwo'             => filter_input(INPUT_POST, 'addressTwo', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'city'                   => filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'state'                  => filter_input(INPUT_POST, 'state', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'zipCode'                => filter_input(INPUT_POST, 'zipCode', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'vegan'                  => filter_input(INPUT_POST, 'vegan', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'vegetarian'             => filter_input(INPUT_POST, 'vegetarian', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'dietaryRestrictions'    => filter_input(INPUT_POST, 'dietaryRestrictions', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'accessibilityNeeds'     => filter_input(INPUT_POST, 'accessibilityNeeds', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'applicantOrganiaztion'  => filter_input(INPUT_POST, 'organiaztion', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'currentTitle'           => filter_input(INPUT_POST, 'currentTitle', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'sponsorName'            => filter_input(INPUT_POST, 'sponsorName', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'sponsorEmail'           => filter_input(INPUT_POST, 'sponsorEmail', FILTER_SANITIZE_EMAIL) ?: '',
    'sponsorPhone'           => filter_input(INPUT_POST, 'sponsorPhone', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'questionOne'            => filter_input(INPUT_POST, 'questionOne', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'questionTwo'            => filter_input(INPUT_POST, 'questionTwo', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'questionThree'          => filter_input(INPUT_POST, 'questionThree', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'questionFour'           => filter_input(INPUT_POST, 'questionFour', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    // The referral field is stored as questionFive in the spreadsheet schema.
    'questionFive'           => filter_input(INPUT_POST, 'refferalQuestion', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    // Scholarship answers are stored under the scholarship* keys consumed by FormService.
    'scholarshipQuestion'    => filter_input(INPUT_POST, 'partialScholarship', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'scholarshipAmount'      => filter_input(INPUT_POST, 'assistAmount', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
];

if ($logger instanceof AppLogger && $logger->isEnabled()) {
    $logger->info('Application form handler invoked', [
        'request_id' => $logger->getRequestId(),
        'payload_preview' => [
            'first' => $payload['applicantFirstName'],
            'last'  => $payload['applicantLastName'],
            'email' => $payload['applicantEmail'],
        ],
    ]);
}

try {
    handle_application_submission($config, $payload, $logger);

    redirect_with_message('Application submitted successfully! Thank you.', 'success', $HEADERLOCATION);
} catch (InvalidArgumentException $e) {
    redirect_with_message($e->getMessage(), 'error', $HEADERLOCATION);
} catch (Throwable $e) {
    if ($logger instanceof AppLogger) {
        $logger->error('Application submission failed', [
            'request_id' => $logger->getRequestId(),
            'error' => $e->getMessage(),
        ]);
    }

    redirect_with_message('There was a problem submitting the application. Please try again later.', 'error', $HEADERLOCATION);
}
