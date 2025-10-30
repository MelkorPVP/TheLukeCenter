<?php

declare(strict_types=1);

namespace TheLukeCenter\Services;

use Google\Service\Gmail;
use Google\Service\Gmail\Message;

final class GmailMailer
{
    private Gmail $gmail;
    private string $sender;

    public function __construct(Gmail $gmail, ?string $sender)
    {
        $this->gmail = $gmail;
        $this->sender = $sender ?: 'me';
    }

    /**
     * @param array<int, string> $recipients
     */
    public function sendHtml(array $recipients, string $subject, string $htmlBody): void
    {
        foreach ($recipients as $recipient) {
            $this->sendMessage($recipient, $subject, $htmlBody);
        }
    }

    private function sendMessage(string $to, string $subject, string $htmlBody): void
    {
        $rawMessageString = sprintf(
            "To: %s\r\nSubject: %s\r\nContent-Type: text/html; charset=utf-8\r\nMIME-Version: 1.0\r\n\r\n%s",
            $to,
            $subject,
            $htmlBody
        );

        $encodedMessage = rtrim(strtr(base64_encode($rawMessageString), '+/', '-_'), '=');

        $message = new Message();
        $message->setRaw($encodedMessage);

        $this->gmail->users_messages->send($this->sender, $message);
    }
}
