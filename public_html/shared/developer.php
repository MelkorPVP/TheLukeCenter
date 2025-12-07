<?php
    $container = require dirname(__DIR__, 2) . '/app/bootstrap.php';
    $config = $container['config'];
    $logger = $container['logger'];

    if (!developer_is_authenticated())
    {
        http_response_code(403);
        echo '<!doctype html><html><body><p>Developer authentication required. Use the Administration link in the navigation bar to sign in.</p></body></html>';
        exit;
    }

    $pageTitle = 'Developer Controls – The Luke Center';
    $activeNav = '';

    $logPath = (string) ($config['logging']['file'] ?? (APP_ROOT . '/storage/logs/application.log'));
    $developerMode = app_is_developer_mode();
    $loggingEnabled = app_is_logging_enabled();
    $flags = developer_current_env_flags();
    $statusMessage = '';
    $statusType = 'info';
    $actionDetails = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $action = (string) ($_POST['action'] ?? '');

        try {
            switch ($action) {
                case 'set_dev_mode':
                    $value = strtolower((string) ($_POST['value'] ?? '')) === 'true';
                    developer_set_env_flags(['DEVELOPER_MODE' => $value ? 'true' : 'false']);
                    putenv('DEVELOPER_MODE=' . ($value ? 'true' : 'false'));
                    $_ENV['DEVELOPER_MODE'] = $value ? 'true' : 'false';
                    $_SERVER['DEVELOPER_MODE'] = $value ? 'true' : 'false';
                    $statusMessage = 'Developer mode flag updated.';
                    $statusType = 'success';
                    break;

                case 'set_logging':
                    $value = strtolower((string) ($_POST['value'] ?? '')) === 'true';
                    developer_set_env_flags(['ENABLE_APPLICATION_LOGGING' => $value ? 'true' : 'false']);
                    putenv('ENABLE_APPLICATION_LOGGING=' . ($value ? 'true' : 'false'));
                    $_ENV['ENABLE_APPLICATION_LOGGING'] = $value ? 'true' : 'false';
                    $_SERVER['ENABLE_APPLICATION_LOGGING'] = $value ? 'true' : 'false';
                    $statusMessage = 'Application logging flag updated.';
                    $statusType = 'success';
                    break;

                case 'promote_test_to_prod':
                    $result = developer_copy_overlay(APP_ENV_TEST, APP_ENV_PROD, $logger);
                    $statusMessage = sprintf('Promoted %d file(s) from TEST to PROD.', $result['copied']);
                    $statusType = 'success';
                    $actionDetails = $result['files'];
                    break;

                case 'clone_prod_to_test':
                    $result = developer_copy_overlay(APP_ENV_PROD, APP_ENV_TEST, $logger);
                    $statusMessage = sprintf('Cloned %d file(s) from PROD to TEST.', $result['copied']);
                    $statusType = 'success';
                    $actionDetails = $result['files'];
                    break;

                case 'purge_logs':
                    $result = developer_purge_logs($logPath, 30, $logger);
                    $statusMessage = sprintf('Purged %d old entries. %d entries remain.', $result['removed'], $result['remaining']);
                    $statusType = 'success';
                    break;

                default:
                    throw new InvalidArgumentException('Unknown action requested.');
            }
        } catch (Throwable $e) {
            $statusMessage = $e->getMessage();
            $statusType = 'danger';
        }

        $flags = developer_current_env_flags();
        $developerMode = app_is_developer_mode();
        $loggingEnabled = app_is_logging_enabled();
    }

    require app_public_path('header.php', APP_ENVIRONMENT);
?>

<section class="hero text-center text-hero border-bottom">
    <div class="container py-4">
        <h1 class="display-5 fw-bold text-uppercase letter-wide text-brand">Developer Controls</h1>
        <p class="fs-5 mb-0">Manage feature flags and environment file overlays.</p>
    </div>
</section>

<section class="py-4 py-md-5">
    <div class="container">
        <?php if ($statusMessage !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($statusType, ENT_QUOTES) ?>" role="alert">
                <?= htmlspecialchars($statusMessage, ENT_QUOTES) ?>
                <?php if (!empty($actionDetails)): ?>
                    <ul class="mt-2 mb-0 small">
                        <?php foreach ($actionDetails as $file): ?>
                            <li><?= htmlspecialchars($file, ENT_QUOTES) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h4">Feature Flags</h2>
                        <p class="mb-2">Developer mode is currently <strong><?= $developerMode ? 'ON' : 'OFF' ?></strong>.</p>
                        <form method="post" class="d-grid gap-2 gap-sm-3 d-sm-flex align-items-center">
                            <input type="hidden" name="action" value="set_dev_mode">
                            <input type="hidden" name="value" value="<?= $developerMode ? 'false' : 'true' ?>">
                            <button class="btn <?= $developerMode ? 'btn-outline-danger' : 'btn-success' ?>" type="submit">
                                <?= $developerMode ? 'Disable Developer Mode' : 'Enable Developer Mode' ?>
                            </button>
                            <div class="text-muted small">Stored in <?= htmlspecialchars(app_htaccess_path(), ENT_QUOTES) ?></div>
                        </form>

                        <hr>

                        <p class="mb-2">Application logging is <strong><?= $loggingEnabled ? 'ENABLED' : 'DISABLED' ?></strong>.</p>
                        <form method="post" class="d-grid gap-2 gap-sm-3 d-sm-flex align-items-center">
                            <input type="hidden" name="action" value="set_logging">
                            <input type="hidden" name="value" value="<?= $loggingEnabled ? 'false' : 'true' ?>">
                            <button class="btn <?= $loggingEnabled ? 'btn-outline-danger' : 'btn-success' ?>" type="submit">
                                <?= $loggingEnabled ? 'Disable Logging' : 'Enable Logging' ?>
                            </button>
                            <div class="text-muted small">Env key: ENABLE_APPLICATION_LOGGING</div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h4">Environment File Sync</h2>
                        <p class="mb-3">Sync overlay files between TEST (<?= htmlspecialchars(app_public_overlay_dir(APP_ENV_TEST), ENT_QUOTES) ?>) and PROD (<?= htmlspecialchars(app_public_overlay_dir(APP_ENV_PROD), ENT_QUOTES) ?>).</p>

                        <form method="post" class="d-grid d-sm-flex gap-2 mb-3">
                            <input type="hidden" name="action" value="promote_test_to_prod">
                            <button class="btn btn-brand" type="submit">Promote TEST → PROD</button>
                        </form>

                        <form method="post" class="d-grid d-sm-flex gap-2">
                            <input type="hidden" name="action" value="clone_prod_to_test">
                            <button class="btn btn-outline-brand" type="submit">Clone PROD → TEST</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h4">Application Log Maintenance</h2>
                        <p class="mb-2">Log path: <code><?= htmlspecialchars($logPath, ENT_QUOTES) ?></code></p>
                        <p class="mb-3">Purge entries older than 30 days from the unified log.</p>
                        <form method="post" class="d-grid d-sm-flex gap-2">
                            <input type="hidden" name="action" value="purge_logs">
                            <button class="btn btn-warning" type="submit">Purge Old Entries</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
    require app_public_path('footer.php', APP_ENVIRONMENT);
?>
