<?php
    
    declare(strict_types=1);
    
    /*
        $pageTitle = $pageTitle ?? 'The Luke Center for Catalytic Leadership';
        $activeNav = $activeNav ?? '';
        $applicationOpen = $applicationOpen ?? false;
        $programName = $programName ?? '';
        $programLocation = $programLocation ?? '';
        $programDates = $programDates ?? '';
    */
    
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>The Luke Center for Catalytic Leadership</title>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="/css/styles.css">
    </head>
    <body>
        <header class="border-bottom bg-white site-header">
            <?php
                // Insert Navbar.
                require __DIR__ . '/navbar.php'; 
            ?>
        </header>
        <main>
                