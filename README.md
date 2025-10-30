# The Luke Center Website

This repository contains a refactored multi-page PHP application for The Luke Center for Catalytic Leadership. The site uses Bootstrap 5 for responsive layout, Google Sheets for dynamic content, and Google APIs for persisting form submissions and sending notifications.

## Project structure

```
public/                # Web root
  ├── api/             # JSON endpoints for form submissions
  ├── assets/          # Compiled CSS and JavaScript
  ├── *.php            # Individual page templates
config/app.php         # Configuration file (reads from environment variables)
src/                   # PHP services and bootstrap logic
templates/partials/    # Reusable header/footer partials
```

## Requirements

* PHP 8.1+
* Composer
* Google Cloud service account with the Google Sheets API and Gmail API enabled
* Access to the Google Sheets used for site content, application submissions, and contact submissions

## Installation

1. Install dependencies:

   ```bash
   composer install
   ```

2. Provide Google credentials and spreadsheet configuration. The application reads the following environment variables (configure them in your hosting environment or an `.env` loader of your choice):

   | Variable | Description |
   | --- | --- |
   | `GOOGLE_APPLICATION_CREDENTIALS` | Absolute path to the service account JSON credentials file. |
   | `GOOGLE_DELEGATED_USER` | Optional. Email address to impersonate when sending Gmail messages (required for domain-wide delegation). |
   | `GOOGLE_SITE_VALUES_SHEET_ID` | Spreadsheet ID that stores site content values (e.g., program name, dates, directors). |
   | `GOOGLE_SITE_VALUES_RANGE` | Range containing key/value pairs for site content (defaults to `Values!A:B`). |
   | `GOOGLE_CONTACT_SHEET_ID` | Spreadsheet ID where contact form submissions are stored. |
   | `GOOGLE_CONTACT_SHEET_TAB` | Tab name for contact submissions (defaults to `Submissions`). |
   | `GOOGLE_APPLICATION_SHEET_ID` | Spreadsheet ID where application form submissions are stored. |
   | `GOOGLE_APPLICATION_SHEET_TAB` | Tab name for application submissions (defaults to `Submissions`). |
   | `APP_EMAIL_RECIPIENTS` | Comma-separated list of email recipients for notifications. |

   Place the JSON credentials file at the path referenced by `GOOGLE_APPLICATION_CREDENTIALS`. The service account needs read access to the site values spreadsheet and write access to the submission spreadsheets. Grant Gmail API send permissions to the service account or delegated user.

3. (Optional) If you do not use environment variables, you can edit `config/app.php` directly to point to the proper credentials and spreadsheet IDs.

## Running locally

Serve the application from the `public/` directory. For example, using PHP's built-in server:

```bash
php -S localhost:8000 -t public
```

Navigate to `http://localhost:8000` to view the site.

## Deploying

Deploy the contents of the `public/` directory to your web host. Ensure the PHP runtime has access to the Composer `vendor/` directory and the environment variables described above.

## Forms and APIs

* `/api/application.php` – accepts JSON submissions from the application form, appends the data to Google Sheets, and sends notification emails via Gmail.
* `/api/contact.php` – accepts JSON submissions from the contact update form, appends to Google Sheets, and sends email notifications.

Both endpoints return JSON responses with an `ok` flag and optional `error` message.

## Front-end behavior

Client-side JavaScript provides Bootstrap validation feedback and submits forms asynchronously. The submit buttons automatically disable when the application is closed in Google Sheets (based on the `enable_application` flag).
