<?php
    
    declare(strict_types=1);
    
    $pageTitle = $pageTitle ?? 'The Luke Center for Catalytic Leadership';
    $activeNav = $activeNav ?? '';

    if (isset($config)) {
        $programName = $programName ?? site_content_program_name($config, $logger ?? null);
        $programLocation = $programLocation ?? site_content_program_location($config, $logger ?? null);
        $programDates = $programDates ?? site_content_program_dates($config, $logger ?? null);
        $applicationOpen = $applicationOpen ?? site_content_application_open($config, $logger ?? null);
        $developerMode = $developerMode ?? app_is_developer_mode();
        $loggingEnabled = (bool) ($config['logging']['enabled'] ?? false);
    } else {
        $applicationOpen = $applicationOpen ?? false;
        $programName = $programName ?? '';
        $programLocation = $programLocation ?? '';
        $programDates = $programDates ?? '';
        $developerMode = $developerMode ?? false;
        $loggingEnabled = app_is_logging_enabled();
    }

    $developerSession = developer_is_authenticated();
    $requestId = ($logger instanceof AppLogger) ? $logger->getRequestId() : '';

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="/css/styles.css">                
        <link rel="icon" href="/images/favicons/favicon.ico">                
        <link rel="icon" href="/images/favicons/androidChrome192x192.png">
        <link rel="icon" href="/images/favicons/androidChrome512x512.png">
        <link rel="apple-touch-icon" href="/images/favicons/appleTouchIcon.png">
        <link rel="icon" href="/images/favicons/favicon16x16.png">
        <link rel="icon" href="/images/favicons/favicon32x32.png">
        <link rel="manifest" href="/site.webmanifest">
        <script>
            window.APP_CONTEXT = <?= json_encode([
                'applicationOpen' => (bool) $applicationOpen,
                'programName' => $programName,
                'programLocation' => $programLocation,
                'programDates' => $programDates,
                'developerMode' => (bool) $developerMode,
                'developerSession' => (bool) $developerSession,
                'loggingEnabled' => (bool) $loggingEnabled,
                'environment' => APP_ENVIRONMENT,
                'requestId' => $requestId,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        </script>
    </head>
    <body data-logging-enabled="<?= $loggingEnabled ? 'true' : 'false' ?>" data-app-environment="<?= htmlspecialchars(APP_ENVIRONMENT, ENT_QUOTES) ?>" data-request-id="<?= htmlspecialchars($requestId, ENT_QUOTES) ?>">
        <header class="border-bottom bg-white site-header">
            <?php
                // Insert Navbar.
                require app_public_path('navbar.php', APP_ENVIRONMENT);
            ?>
        </header>
        <main>
                