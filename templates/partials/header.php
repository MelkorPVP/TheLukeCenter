<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'The Luke Center for Catalytic Leadership';
$activeNav = $activeNav ?? '';
$applicationOpen = $applicationOpen ?? false;
$programName = $programName ?? '';
$programLocation = $programLocation ?? '';
$programDates = $programDates ?? '';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/styles.css">
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
      <nav class="navbar navbar-expand-lg container py-2 flex-column" aria-label="Primary navigation">
        <a class="navbar-brand d-flex align-items-center gap-2 mx-auto" href="/index.php">
          <img class="img-fluid" height="256" width="256"
            src="https://lh3.googleusercontent.com/d/1Uc2Me5kJQuKruXV_qCD8COLzUS19kerv=w800"
            srcset="https://lh3.googleusercontent.com/d/1Uc2Me5kJQuKruXV_qCD8COLzUS19kerv=w400 400w, https://lh3.googleusercontent.com/d/1Uc2Me5kJQuKruXV_qCD8COLzUS19kerv=w800 800w, https://lh3.googleusercontent.com/d/1Uc2Me5kJQuKruXV_qCD8COLzUS19kerv=w1200 1200w"
            sizes="(max-width: 256px) 100vw, 800px"
            alt="The Luke Center for Catalytic Leadership"
            loading="lazy">
        </a>
        <button class="navbar-toggler mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNav" aria-controls="primaryNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse mt-3 mt-lg-0" id="primaryNav">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
            <?php
              $items = [
                'home' => ['href' => '/index.php', 'label' => 'Home'],
                'pacific' => ['href' => '/pacific-program.php', 'label' => 'Pacific Program'],
                'catalytic' => ['href' => '/catalytic-leadership.php', 'label' => 'Catalytic Leadership'],
                'alumni' => ['href' => '/alumni.php', 'label' => 'Alumni'],
                'board' => ['href' => '/board-of-directors.php', 'label' => 'Board of Directors'],
                'apply' => ['href' => '/apply.php', 'label' => 'Apply'],
                'contact' => ['href' => '/contact.php', 'label' => 'Contact'],
              ];
              foreach ($items as $key => $item):
                $active = $activeNav === $key ? 'active' : '';
            ?>
              <li class="nav-item">
                <a class="nav-link <?= $active ?>" href="<?= $item['href'] ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </nav>
    </header>
    <main>
