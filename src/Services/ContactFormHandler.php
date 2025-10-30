<?php

declare(strict_types=1);

namespace TheLukeCenter\Services;

use DateTimeImmutable;
use InvalidArgumentException;

final class ContactFormHandler
{
    private GoogleSheetsService $sheets;
    private GmailMailer $mailer;
    private string $sheetTab;
    /** @var array<int, string> */
    private array $recipients;

    public function __construct(GoogleSheetsService $sheets, GmailMailer $mailer, string $sheetTab, array $recipients)
    {
        $this->sheets = $sheets;
        $this->mailer = $mailer;
        $this->sheetTab = $sheetTab;
        $this->recipients = $recipients;
    }

    /**
     * @param array<string, string> $payload
     */
    public function handle(array $payload): void
    {
        $data = $this->sanitize($payload);
        $this->validate($data);

        $this->ensureHeader();
        $this->sheets->appendRow($this->sheetTab . '!A:G', [
            (new DateTimeImmutable('now'))->format('c'),
            $data['contactFirstName'],
            $data['contactLastName'],
            $data['contactEmail'],
            $data['contactPhone'],
            $data['contactPhoneType'],
            $data['currentWork'],
            $data['yearAttended'],
        ]);

        $this->sendEmail($data);
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, string>
     */
    private function sanitize(array $payload): array
    {
        $allowed = ['contactFirstName','contactLastName','contactEmail','contactPhone','contactPhoneType','currentWork','yearAttended'];
        $data = [];
        foreach ($allowed as $field) {
            $data[$field] = trim((string) ($payload[$field] ?? ''));
        }

        return $data;
    }

    /**
     * @param array<string, string> $data
     */
    private function validate(array $data): void
    {
        if ($data['contactFirstName'] === '' || $data['contactEmail'] === '') {
            throw new InvalidArgumentException('Name and email are required.');
        }

        if (!filter_var($data['contactEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }
    }

    private function ensureHeader(): void
    {
        $this->sheets->ensureSheetExists($this->sheetTab);
        $existing = $this->sheets->getValues($this->sheetTab . '!A1:G1');
        if (!empty($existing)) {
            return;
        }

        $this->sheets->appendRow($this->sheetTab . '!A:G', [
            'Timestamp','FirstName','LastName','Email','Phone','PhoneType','CurrentWorkOrg','ProgramYear',
        ]);
    }

    /**
     * @param array<string, string> $data
     */
    private function sendEmail(array $data): void
    {
        $subject = sprintf('New Contact Info Update: %s %s', $data['contactFirstName'], $data['contactLastName']);
        $html = sprintf(
            '<div style="font-family:Arial,sans-serif">'
            . '<h1>New submission</h1>'
            . '<p><strong>First Name:</strong> %s<br><strong>Last Name:</strong> %s<br><strong>Email:</strong> %s<br><strong>Phone:</strong> %s<br>'
            . '<strong>Phone Type:</strong> %s<br><strong>Current Organization:</strong> %s<br><strong>Program Year:</strong> %s</p>'
            . '</div>',
            htmlspecialchars($data['contactFirstName'], ENT_QUOTES),
            htmlspecialchars($data['contactLastName'], ENT_QUOTES),
            htmlspecialchars($data['contactEmail'], ENT_QUOTES),
            htmlspecialchars($data['contactPhone'], ENT_QUOTES),
            htmlspecialchars($data['contactPhoneType'], ENT_QUOTES),
            htmlspecialchars($data['currentWork'], ENT_QUOTES),
            htmlspecialchars($data['yearAttended'], ENT_QUOTES)
        );

        $this->mailer->sendHtml($this->recipients, $subject, $html);
    }
}
