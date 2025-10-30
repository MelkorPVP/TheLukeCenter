<?php

declare(strict_types=1);

namespace TheLukeCenter\Services;

use Google\Client;

final class GoogleClientFactory
{
    /**
     * @param array{credentials_path:string, delegated_user:?string} $config
     * @param array<int, string> $scopes
     */
    public static function make(array $config, array $scopes): Client
    {
        $credentialsPath = $config['credentials_path'] ?? '';
        if (!is_file($credentialsPath)) {
            throw new \RuntimeException('Google credentials file not found.');
        }

        $client = new Client();
        $client->setApplicationName('The Luke Center Website');
        $client->setAuthConfig($credentialsPath);
        $client->setScopes($scopes);
        $client->setAccessType('offline');

        if (!empty($config['delegated_user'])) {
            $client->setSubject($config['delegated_user']);
        }

        return $client;
    }
}
