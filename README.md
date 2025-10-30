# The Luke Center Website

This repository hosts a lightweight multi-page PHP site for The Luke Center for Catalytic Leadership. Each page lives in the `public/` directory and shares a Bootstrap-powered layout while loading dynamic program details from Google Sheets. Form submissions are stored in Google Sheets and forwarded by email through the Gmail API.

## Project structure

```
public/
  ├── api/                 # JSON endpoints for application and contact forms
  ├── assets/              # Static CSS and JavaScript
  ├── config/              # Application configuration and service-account JSON placeholder
  ├── includes/            # Reusable PHP helpers and layout partials
  ├── *.php                # Public pages for the site
```

There is no Composer dependency—every helper is loaded with simple `require` statements from `public/bootstrap.php`.

## Requirements

* PHP 8.1+
* cURL and OpenSSL extensions enabled (used for Google API calls)
* Google Cloud service account with the Google Sheets API and Gmail API enabled
* The service account (or delegated user) must have access to:
  * A spreadsheet containing site content key/value pairs
  * A spreadsheet to store application submissions
  * A spreadsheet to store contact submissions
  * Gmail send permissions for the delegated account (when sending notifications)

## Configuration

Copy your service account JSON file into `public/config/service-account.json` (or place it elsewhere and update the path). Environment variables can override any setting:

| Variable | Description |
| --- | --- |
| `GOOGLE_APPLICATION_CREDENTIALS` | Absolute path to the service account JSON credentials. Defaults to `public/config/service-account.json`. |
| `GOOGLE_DELEGATED_USER` | Optional email address to impersonate when sending Gmail messages. |
| `GOOGLE_SITE_VALUES_SHEET_ID` | Spreadsheet ID that stores site content (program name, dates, directors, etc.). |
| `GOOGLE_SITE_VALUES_RANGE` | Range within the site content sheet. Defaults to `Values!A:B`. |
| `GOOGLE_CONTACT_SHEET_ID` | Spreadsheet ID for storing contact form submissions. |
| `GOOGLE_CONTACT_SHEET_TAB` | Sheet tab for contact submissions. Defaults to `Submissions`. |
| `GOOGLE_APPLICATION_SHEET_ID` | Spreadsheet ID for application form submissions. |
| `GOOGLE_APPLICATION_SHEET_TAB` | Sheet tab for application submissions. Defaults to `Submissions`. |
| `APP_EMAIL_RECIPIENTS` | Comma-separated list of recipients who should receive notification emails. |

You can also edit `public/config/app.php` directly to hard-code these values if preferred.

## Running locally

Serve the `public/` directory with PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

Visit `http://localhost:8000` to load the site.

## Forms and APIs

* `public/api/application.php` – accepts JSON payloads from the application form, appends data to the configured spreadsheet, and emails the recipients list.
* `public/api/contact.php` – accepts JSON payloads from the contact form, appends data to its spreadsheet, and emails the recipients list.

Both endpoints respond with JSON containing an `ok` flag and an optional `error` message.

## Deployment

Deploy everything inside the `public/` directory to your PHP-capable host. Ensure the server can read the service account JSON file and that the necessary environment variables are set. No Composer installation is required.
