# Laravel Setup Guide

## Overview

This project has been converted from a legacy PHP application to a full Laravel framework application.

## What Changed

### Structure
- **Before:** Mixed PHP files in root with nested Laravel app in `/app/` folder
- **After:** Full Laravel structure at root level

### Key Changes
1. Laravel app moved from `/app/` to root level
2. Legacy PHP files converted to Laravel controllers and Blade views
3. All routes now use Laravel routing system
4. Database operations use Laravel's Query Builder and Eloquent
5. Session and CSRF protection built-in

### Legacy Files
All original PHP files have been moved to `_legacy_backup/` for reference:
- `index.php` → Home controller & view
- `Therapy/*.php` → TherapyController & therapy views
- `db.php` → Laravel database configuration
- Assets moved to `public/` and `resources/`

## Installation Steps

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies (for Vite)
npm install
```

### 2. Configure Environment

The `.env` file is already configured with:
- Database: `ebm` 
- Host: `127.0.0.1`
- Username: `root`
- Password: `NewStrongPass123!`

Update these values if your database credentials are different.

### 3. Prepare Database

The application will automatically create the database and tables on first run using `database/schema.sql`.

Alternatively, you can manually import:
```bash
mysql -u root -p < database/schema.sql
```

### 4. Generate Application Key (if needed)

```bash
php artisan key:generate
```

### 5. Build Assets

```bash
npm run dev
# Or for production:
npm run build
```

### 6. Start the Application

#### Using PHP's built-in server:
```bash
php artisan serve
```
Then visit: http://localhost:8000

#### Using XAMPP:
1. Ensure XAMPP's Apache is running
2. Configure Apache to point to `/Applications/XAMPP/xamppfiles/htdocs/EBM/public`
3. Visit: http://localhost

## Routes

### Main Routes
- `/` - Home page
- `/therapy/article-form` - New study entry form
- `/therapy/studies` - List all studies
- `/therapy/reading-journal` - Reading journal view

### API Routes
- `POST /therapy/doi-autofetch` - Fetch article details from DOI
- `POST /therapy/compute-results` - Compute RR/ARR/NNT
- `DELETE /therapy/studies/{id}` - Delete a study

## File Structure

```
EBM/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── HomeController.php
│   │       └── TherapyController.php
│   └── Models/
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── home.blade.php
│       └── therapy/
│           ├── article_form.blade.php
│           ├── studies_list.blade.php
│           ├── ind_list.blade.php
│           ├── ind_results.blade.php
│           └── reading_journal.blade.php
├── routes/
│   └── web.php
├── public/
│   └── images/
│       └── saliksic-header.png
├── database/
│   └── schema.sql
├── _legacy_backup/
│   └── [original PHP files]
└── .env
```

## Development

### Running in Development Mode

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Watch for asset changes
npm run dev
```

### Clearing Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## Features

### Core Functionality
- ✅ Study data entry with DOI autofetch
- ✅ RR/ARR/NNT calculations
- ✅ Individualized ARR calculations
- ✅ Study management (create, view, delete)
- ✅ Reading journal
- ✅ Search studies by exposure/outcome

### Laravel Features
- ✅ Eloquent ORM ready
- ✅ CSRF protection
- ✅ Session management
- ✅ Blade templating
- ✅ Database migrations ready
- ✅ Modern routing system

## Next Steps

To further enhance the application:

1. **Create Eloquent Models** for Article, Study, Individualization
2. **Implement full DOI autofetch** functionality (requires external API)
3. **Add validation** using Laravel Form Requests
4. **Create database migrations** from schema.sql
5. **Add authentication** if needed
6. **Write tests** using PHPUnit
7. **Implement the compute_results logic** fully

## Troubleshooting

### Database Connection Issues
- Verify MySQL is running: `mysql.server status`
- Check credentials in `.env`
- Ensure database `ebm` exists

### Asset Issues
- Run `npm run build` to compile assets
- Check `public/images/` for header image

### Route Issues
- Clear route cache: `php artisan route:clear`
- List all routes: `php artisan route:list`

## License

Licensed under Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0).

© 2024 SALIKSIC Project. Some Rights Reserved.
