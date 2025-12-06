<?php
declare(strict_types=1);

$container = require __DIR__ . '/../app/bootstrap.php';
$config    = $container['config'] ?? [];
$logger    = $container['logger'] ?? null;

$HEADERLOCATION = 'Location: contact.php#contactStatus';

/**
 * Store a flash message in the session and redirect back to the form anchor.
 */
function redirect_with_message(string $message, string $type, string $headerLocation): void
{
    $_SESSION['message']     = $message;
    $_SESSION['messageType'] = $type;

    header($headerLocation);
    exit();
}

// Validate HTTP verb early so bots or misconfigured clients fail fast.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('Invalid request method. Please use the form to submit data.', 'error', $HEADERLOCATION);
}

$requiredFields = [
    'contactFirstName',
    'contactLastName',
    'contactEmail',
    'phone',
    'phoneType',
    'yearAttended',
];

foreach ($requiredFields as $fieldName) {
    if (!isset($_POST[$fieldName])) {
        redirect_with_message('Invalid request parameters. One or more required form parameters is missing.', 'error', $HEADERLOCATION);
    }
}

// Build the payload expected by handle_contact_submission() using sanitized inputs.
$payload = [
    'contactFirstName'    => filter_input(INPUT_POST, 'contactFirstName', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'contactLastName'     => filter_input(INPUT_POST, 'contactLastName', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'contactEmail'        => filter_input(INPUT_POST, 'contactEmail', FILTER_SANITIZE_EMAIL) ?: '',
    'contactPhone'        => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'contactPhoneType'    => filter_input(INPUT_POST, 'phoneType', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'currentOrganization' => filter_input(INPUT_POST, 'currentOrganization', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
    'yearAttended'        => filter_input(INPUT_POST, 'yearAttended', FILTER_SANITIZE_SPECIAL_CHARS) ?: '',
];

// Extra guardrail so we can trace failures in production logs.
if ($logger instanceof AppLogger && $logger->isEnabled()) {
    $logger->info('Contact form handler invoked', [
        'request_id' => $logger->getRequestId(),
        'payload_preview' => [
            'first' => $payload['contactFirstName'],
            'last' => $payload['contactLastName'],
            'email' => $payload['contactEmail'],
        ],
    ]);
}

// Validate and process the form.
try {
    handle_contact_submission($config, $payload, $logger);

    redirect_with_message('Updated contact information submitted successfully! Thank you.', 'success', $HEADERLOCATION);
} catch (InvalidArgumentException $e) {
    redirect_with_message($e->getMessage(), 'error', $HEADERLOCATION);
} catch (Throwable $e) {
    if ($logger instanceof AppLogger) {
        $logger->error('Contact form submission failed', [
            'request_id' => $logger->getRequestId(),
            'error' => $e->getMessage(),
        ]);
    }

    redirect_with_message('There was a problem submitting the form. Please try again later.', 'error', $HEADERLOCATION);
}
