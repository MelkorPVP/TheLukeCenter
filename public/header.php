<?php
    
    declare(strict_types=1);
    
    $pageTitle = $pageTitle ?? 'The Luke Center for Catalytic Leadership';
    $activeNav = $activeNav ?? '';

    if (isset($config)) {
        $programName = $programName ?? site_content_program_name($config, $logger ?? null);
        $programLocation = $programLocation ?? site_content_program_location($config, $logger ?? null);
        $programDates = $programDates ?? site_content_program_dates($config, $logger ?? null);
        $applicationOpen = $applicationOpen ?? site_content_application_open($config, $logger ?? null);
    } else {
        $applicationOpen = $applicationOpen ?? false;
        $programName = $programName ?? '';
        $programLocation = $programLocation ?? '';
        $programDates = $programDates ?? '';
    }
    
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="/css/styles.css">
        <script>
            window.APP_CONTEXT = <?= json_encode([
                'applicationOpen' => (bool) $applicationOpen,
                'programName' => $programName,
                'programLocation' => $programLocation,
                'programDates' => $programDates,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        </script>
    </head>
    <body>
        <header class="border-bottom bg-white site-header">
            <?php
                // Insert Navbar.
                require __DIR__ . '/navbar.php'; 
            ?>
        </header>
        <main>
                