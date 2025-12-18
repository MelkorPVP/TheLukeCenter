<?php
    declare(strict_types=1);
    
    $container = require_once __DIR__ . '/app/bootstrap.php';
    $googleConfig = $container['config']['google'] ?? [];
    $logger = $container['logger'] ?? null;

    try {
        // Load full application configuration.
        
        if (empty($googleConfig)) {
            throw new RuntimeException('Google configuration missing from app/bootstrap.php');
        }
        
        // If Google redirected back here with a ?code=..., finish the exchange.
        if (isset($_GET['code'])) {
            $code = (string) $_GET['code'];
            
            $tokens = google_exchange_code_for_tokens($googleConfig, $code);
            $path   = google_get_token_path($googleConfig);
            
            echo '<h2>Authorization successful.</h2>';
            echo '<p>Token saved to: ' .
            htmlspecialchars($path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
            '</p>';
            echo '<h3>Token payload</h3><pre>' .
            htmlspecialchars(print_r($tokens, true), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
            '</pre>';
            exit;
        }
        
        // Initial step: redirect user to Google consent screen.
        $oauth = google_normalize_oauth_config($googleConfig);
        
        $base = 'https://accounts.google.com/o/oauth2/v2/auth';
        $params = [
        'client_id'     => $oauth['client_id'],
        'redirect_uri'  => $oauth['redirect_uri'],
        'response_type' => 'code',
        'scope'         => implode(' ', $oauth['scopes']),
        'access_type'   => 'offline',
        'prompt'        => 'consent',
        ];
        
        header('Location: ' . $base . '?' . http_build_query($params));
        exit;
        
        } catch (Throwable $e) {
        http_response_code(500);
        if ($logger instanceof AppLogger) {
            $logger->error('Authorization exchange failed', ['error' => $e->getMessage()]);
        }
        echo '<h2>Authorization Error</h2><pre>' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
        '</pre>';
        exit;
    }
