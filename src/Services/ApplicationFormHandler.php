<?php

declare(strict_types=1);

namespace TheLukeCenter\Services;

use DateTimeImmutable;
use InvalidArgumentException;

final class ApplicationFormHandler
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
        $this->sheets->appendRow($this->sheetTab . '!A:AA', [
            (new DateTimeImmutable('now'))->format('c'),
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
        ]);

        $this->sendEmail($data);
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, string>
     */
    private function sanitize(array $payload): array
    {
        $allowed = [
            'applicantFirstName','applicantLastName','applicantPreferredName','applicantPronouns','applicantEmail','applicantPhone','applicantPhoneType',
            'addressOne','addressTwo','city','state','zip','vegan','vegetarian','diet','accommodations','org','title','supName','supEmail','supPhone',
            'refferalQuestion','questionOne','questionTwo','questionThree','questionFour','partialScholarship','assistAmount',
        ];

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
        $required = [
            'applicantFirstName','applicantLastName','applicantEmail','applicantPhone','applicantPhoneType','addressOne','city','state','zip',
            'vegan','vegetarian','org','title','supName','supEmail','supPhone','refferalQuestion','questionOne','questionTwo','questionThree','questionFour',
        ];

        foreach ($required as $field) {
            if ($data[$field] === '') {
                throw new InvalidArgumentException('Missing required field: ' . $field);
            }
        }

        if (!filter_var($data['applicantEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid applicant email address.');
        }
        if (!filter_var($data['supEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid sponsor email address.');
        }
    }

    private function ensureHeader(): void
    {
        $this->sheets->ensureSheetExists($this->sheetTab);
        $existing = $this->sheets->getValues($this->sheetTab . '!A1:AA1');
        if (!empty($existing)) {
            return;
        }

        $this->sheets->appendRow($this->sheetTab . '!A:AA', [
            'Timestamp','ApplicantFirst','ApplicantLast','PreferredName','Pronouns','ApplicantEmail','ApplicantPhone','PhoneType',
            'StreetAddressOne','StreetAddressTwo','City','StateProvince','ZipPostalCode','Vegan','Vegetarian','Restrictions','AccessibilityNeeds',
            'OrganizationAgency','TitleRole','SponsorName','SponsorEmail','SponsorPhone','QuestionsQ1','QuestionsQ2','QuestionsQ3','QuestionsQ4',
            'QuestionsQ5','ScholarshipQ1','ScholarshipQ2',
        ]);
    }

    /**
     * @param array<string, string> $data
     */
    private function sendEmail(array $data): void
    {
        $subject = sprintf('New Application Submission: %s %s', $data['applicantFirstName'], $data['applicantLastName']);
        $html = sprintf(
            '<div style="font-family:Arial,sans-serif">'
            . '<h1>New Application</h1>'
            . '<h2>Applicant Information</h2>'
            . '<p><strong>Name:</strong> %s %s<br><strong>Email:</strong> %s<br><strong>Phone:</strong> %s (%s)</p>'
            . '<h2>Mailing Address</h2>'
            . '<p>%s<br>%s<br>%s, %s %s</p>'
            . '<h2>Personal Needs</h2>'
            . '<p><strong>Vegan:</strong> %s<br><strong>Vegetarian:</strong> %s<br><strong>Dietary Restrictions:</strong> %s<br><strong>Accessibility Needs:</strong> %s</p>'
            . '<h2>Organization & Role</h2>'
            . '<p><strong>Organization:</strong> %s<br><strong>Title:</strong> %s</p>'
            . '<h2>Sponsor Information</h2>'
            . '<p><strong>Name:</strong> %s<br><strong>Email:</strong> %s<br><strong>Phone:</strong> %s</p>'
            . '<h2>Questions</h2>'
            . '<p><strong>Referral:</strong> %s</p>'
            . '<p><strong>Leadership Responsibilities:</strong> %s</p>'
            . '<p><strong>Partnership Experience:</strong> %s</p>'
            . '<p><strong>Professional Challenge:</strong> %s</p>'
            . '<p><strong>Program Goals:</strong> %s</p>'
            . '<h2>Scholarship</h2>'
            . '<p><strong>Needs Scholarship:</strong> %s<br><strong>Requested Amount:</strong> %s</p>'
            . '</div>',
            htmlspecialchars($data['applicantFirstName'], ENT_QUOTES),
            htmlspecialchars($data['applicantLastName'], ENT_QUOTES),
            htmlspecialchars($data['applicantEmail'], ENT_QUOTES),
            htmlspecialchars($data['applicantPhone'], ENT_QUOTES),
            htmlspecialchars($data['applicantPhoneType'], ENT_QUOTES),
            htmlspecialchars($data['addressOne'], ENT_QUOTES),
            htmlspecialchars($data['addressTwo'], ENT_QUOTES),
            htmlspecialchars($data['city'], ENT_QUOTES),
            htmlspecialchars($data['state'], ENT_QUOTES),
            htmlspecialchars($data['zip'], ENT_QUOTES),
            htmlspecialchars($data['vegan'], ENT_QUOTES),
            htmlspecialchars($data['vegetarian'], ENT_QUOTES),
            htmlspecialchars($data['diet'], ENT_QUOTES),
            htmlspecialchars($data['accommodations'], ENT_QUOTES),
            htmlspecialchars($data['org'], ENT_QUOTES),
            htmlspecialchars($data['title'], ENT_QUOTES),
            htmlspecialchars($data['supName'], ENT_QUOTES),
            htmlspecialchars($data['supEmail'], ENT_QUOTES),
            htmlspecialchars($data['supPhone'], ENT_QUOTES),
            htmlspecialchars($data['refferalQuestion'], ENT_QUOTES),
            htmlspecialchars($data['questionOne'], ENT_QUOTES),
            htmlspecialchars($data['questionTwo'], ENT_QUOTES),
            htmlspecialchars($data['questionThree'], ENT_QUOTES),
            htmlspecialchars($data['questionFour'], ENT_QUOTES),
            htmlspecialchars($data['partialScholarship'], ENT_QUOTES),
            htmlspecialchars($data['assistAmount'], ENT_QUOTES)
        );

        $this->mailer->sendHtml($this->recipients, $subject, $html);
    }
}
