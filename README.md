# The Luke Center deployment layout

This repository keeps shared PHP libraries in `app/` and separates environment-specific public assets by document root to match the hosting layout:

- `public_html/` contains the production site that maps to `www.thelukecenter.org`.
- `test.thelukecenter.org/` contains the TEST site that maps to `test.thelukecenter.org`.

## Environment resolution

Environment selection follows `app/config.php`:

1. Respect `APP_ENV`/`APP_ENVIRONMENT` when set.
2. Detect the TEST host (`test.thelukecenter.org`).
3. Default to PROD.

`app_public_path()` uses this environment to look inside the appropriate document root so shared includes are resolved relative to the active site.

## Static assets

Static assets live alongside the PHP entry points inside each document root. Keep directory layouts in `public_html/` and `test.thelukecenter.org/` consistent so relative paths stay stable between environments.

## Deploying

- Deploy `public_html/` to the production web root and `test.thelukecenter.org/` to the TEST web root.
- Keep `app/` unchanged across environments.
- Set `APP_ENV=test` (or use the TEST hostname) to exercise the TEST assets without modifying production files.

## Logging

- Server-side logging is controlled by the `ENABLE_APPLICATION_LOGGING` flag surfaced via `.htaccess`/environment variables. When enabled, both web and cron contexts append to the shared `storage/logs/application.log` path defined in `app/config.php`.
- The generated request ID and environment tag are included in each log entry. The same toggle is also surfaced to client-side code so browser console messages can be gated alongside backend logs.
