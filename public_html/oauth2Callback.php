<?php

        declare(strict_types=1);

        $container = require_once __DIR__ . '/app/bootstrap.php';
        $googleConfig = $container['config']['google'] ?? [];
        $logger = $container['logger'] ?? null;

        if (!isset($_GET['code']))
        {
                http_response_code(400);
                echo 'Missing "code" parameter from Google.';
                exit;
        }

        $code = (string) $_GET['code'];

        try
        {
                $tokens = google_exchange_code_for_tokens($googleConfig, $code);
        }
        catch (Throwable $e)
        {
                http_response_code(500);
                if ($logger instanceof AppLogger) {
                        $logger->error('Failed to exchange OAuth code', ['error' => $e->getMessage()]);
                }
                echo 'Failed to exchange authorization code for tokens: ' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                exit;
        }
?>
<!doctype html>
<html lang="en">
        <head>
                <meta charset="utf-8">
                <title>Google OAuth Successful</title>
        </head>
        <body>
                <h1>Google OAuth Successful</h1>
                <p>Tokens have been stored. You can close this window.</p>
        </body>
</html>
