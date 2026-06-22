# Mustafa Backend (Laravel API)

Backend API and Administration Panel for the Mustafa Hasb Sido Portfolio & Digital Marketing Platform.

Built with Laravel, Sanctum Authentication, and a bilingual architecture (Arabic / English).

---

## Requirements

* PHP 8.2 or higher
* Composer
* MySQL / MariaDB
* Web Server (Apache or Nginx)
* Symbolic Link Support (`storage:link`)

> Recommended PHP Version: **8.2.31** (development environment)

---

## Installation

### 1. Install Dependencies

```bash
composer install
```

If you encounter dependency conflicts related to Google Analytics packages:

```bash
composer install --ignore-platform-reqs
```

---

### 2. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Update the following values:

```env
APP_NAME="Mustafa Hasb Sido"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.mustafahasbsido.com 
ده الدومين

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mustafa_data
DB_USERNAME=
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=www.mustafahasbsido.com
SESSION_DOMAIN=.www.mustafahasbsido.com 

GOOGLE_ANALYTICS_PROPERTY_ID=
GOOGLE_APPLICATION_CREDENTIALS=storage/app/google-analytics-credentials.json
```

---

## Database Setup

A database backup file is provided separately:

```text
mustafa_backup.sql
```

Import it using phpMyAdmin or MySQL CLI:

```bash
mysql -u username -p database_name < mustafa_backup.sql
```

---

## Storage Link

Required for uploaded files:

```bash
php artisan storage:link
```

Uploaded assets include:

* Blog Images
* Portfolio Images
* Service Images
* Gallery Files

---

## Uploaded Files

A separate archive containing:

```text
storage/app/public
```

must be extracted into:

```text
storage/app/public
```

on the production server.

---

## Google Analytics Credentials

A Google Service Account JSON file is delivered separately.

Place it here:

```text
storage/app/google-analytics-credentials.json
```

---

## Production Optimization

After completing the environment configuration:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Folder Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

---

## Development Notes

### Route Order

Static routes such as:

```text
/categories
/tags
/reorder
/trashed
```

must appear before dynamic routes:

```text
/{id}
```

to prevent route conflicts.

---

### JSON Casts

The following fields require explicit array casting:

```php
gallery
tags
results
list_desc_ar
list_desc_en
```

---

### FormData Updates

When updating records using FormData:

```text
_method = PUT
```

must be included.

Arrays should be sent as:

```text
category_ids[]
```

---

## Deployment Checklist

* Composer dependencies installed
* Environment variables configured
* Database imported
* Storage link created
* Uploaded media copied
* Google Analytics credentials configured
* APP_DEBUG disabled
* Sanctum authentication functioning
* Routes and config cached

---

## Technology Stack

* Laravel
* Sanctum
* MySQL
* Google Analytics Data API
* REST API Architecture
* Bilingual Content Management
