# The Luke Center deployment layout

This repository keeps shared PHP libraries in `app/` and now separates environment-specific public assets into overlays within `public_html/`:

- `public_html/shared/` contains the default pages, assets, and handlers used by all environments.
- `public_html/test/` contains overrides that should only run in the TEST environment (migrated from the former top-level `test/` directory).
- `public_html/prod/` is reserved for production-only overrides (currently empty aside from a placeholder).
- Thin wrapper scripts at the root of `public_html/` route each request through `public_html/env-loader.php`, which resolves the file from the active environment overlay first and then falls back to `shared/`.

## Environment resolution

Environment selection follows `app/config.php`:

1. Respect `APP_ENV`/`APP_ENVIRONMENT` when set.
2. Detect the TEST host (`test.thelukecenter.org`).
3. Default to PROD.

`app_public_path()` uses this environment to look for a matching file under `public_html/<env>/` and falls back to `public_html/shared/` if no override exists. This means TEST-specific changes can be committed to `public_html/test/` without touching production defaults.

## Static assets

`public_html/css`, `public_html/js`, `public_html/images`, and `public_html/site.webmanifest` are symlinks to the shared versions to keep URLs stable. If a deployment target cannot preserve symlinks, copy the linked files from `public_html/shared/` instead. To test environment-specific static assets, drop overrides into `public_html/test/` and update the symlinks during deployment for that environment.

## Deploying

- Ensure the `public_html/` root (wrappers plus symlinks) is deployed along with the `shared/`, `test/`, and `prod/` overlays.
- Keep `app/` unchanged across environments; only overlay directories should vary.
- Set `APP_ENV=test` (or use the TEST hostname) to exercise the TEST overlays without modifying PROD files.

## Logging

- Server-side logging is controlled by the `ENABLE_APPLICATION_LOGGING` flag surfaced via `.htaccess`/environment variables. When enabled, both web and cron contexts append to the shared `storage/logs/application.log` path defined in `app/config.php`.
- The generated request ID and environment tag are included in each log entry. The same toggle is also surfaced to client-side code so browser console messages can be gated alongside backend logs.
