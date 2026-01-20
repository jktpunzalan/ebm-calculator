# Troubleshooting Guide

## Issues Fixed

### ✅ 1. Permission Denied on Laravel Logs

**Error:**
```
The stream or file "/Applications/XAMPP/xamppfiles/htdocs/EBM/storage/logs/laravel.log" could not be opened in append mode: Permission denied
```

**Solution:**
Changed configuration to use file-based drivers instead of database drivers:

```env
# In .env file:
SESSION_DRIVER=file        # was: database
CACHE_STORE=file           # was: database  
QUEUE_CONNECTION=sync      # was: database
```

This eliminates the need for database tables that would require migrations.

### ✅ 2. Missing Sessions Table

**Error:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'ebm.sessions' doesn't exist
```

**Solution:**
Switched to file-based sessions (see above). Sessions are now stored in `storage/framework/sessions/` instead of the database.

### ✅ 3. Vite Manifest Not Found

**Error:**
```
Vite manifest not found at: /Applications/XAMPP/xamppfiles/htdocs/EBM/public/build/manifest.json
```

**Solution:**
Removed `@vite()` directive from `resources/views/layouts/app.blade.php`. The application now uses inline CSS in Blade templates instead of compiled Vite assets. This simplifies deployment and eliminates the need to run `npm run build`.

## Current Configuration

### Environment Settings (.env)

```env
APP_NAME="SALIKSIC EBM Calculator"
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ebm
DB_USERNAME=root
DB_PASSWORD=NewStrongPass123!

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### File-Based Storage

All temporary data now uses the file system:
- **Sessions:** `storage/framework/sessions/`
- **Cache:** `storage/framework/cache/`
- **Views:** `storage/framework/views/`
- **Logs:** `storage/logs/`

## Verification

### Check if Application is Running

```bash
curl -s http://127.0.0.1:8000 | grep -i SALIKSIC
```

Should return: `<title>SALIKSIC</title>`

### Test All Routes

```bash
# Home page
curl -s http://127.0.0.1:8000 | grep -i "Critical Appraisal"

# Article form
curl -s http://127.0.0.1:8000/therapy/article-form | grep -i "EBM Study Entry"

# Studies list  
curl -s http://127.0.0.1:8000/therapy/studies | grep -i "Studies"

# Reading journal
curl -s http://127.0.0.1:8000/therapy/reading-journal | grep -i "Reading Journal"
```

## Common Issues

### Issue: Storage permissions error

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
```

If permission denied, you may need to use `sudo` or change ownership:
```bash
sudo chown -R $(whoami) storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Config cached with old values

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Issue: Database connection fails

**Symptoms:**
- "SQLSTATE[HY000] [2002] Connection refused"
- "Access denied for user"

**Solution:**
1. Verify MySQL is running:
   ```bash
   mysql.server status
   # or for XAMPP:
   sudo /Applications/XAMPP/xamppfiles/bin/mysql.server status
   ```

2. Check credentials in `.env`:
   ```env
   DB_HOST=127.0.0.1
   DB_DATABASE=ebm
   DB_USERNAME=root
   DB_PASSWORD=YourPassword
   ```

3. Test connection:
   ```bash
   mysql -h 127.0.0.1 -u root -p ebm
   ```

### Issue: "Base table or view not found"

**Symptoms:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'ebm.articles' doesn't exist
```

**Solution:**
The application auto-creates tables on first use. If this fails:
1. Manually import schema:
   ```bash
   mysql -u root -p ebm < database/schema.sql
   ```

2. Verify tables exist:
   ```bash
   mysql -u root -p -e "USE ebm; SHOW TABLES;"
   ```

### Issue: Routes not found

**Symptoms:**
- 404 Not Found errors
- "Target class [Controller] does not exist"

**Solution:**
```bash
php artisan route:clear
php artisan route:list  # Verify routes are registered
```

## Development Workflow

### Starting the Application

```bash
# Clear any cached config
php artisan config:clear

# Start Laravel server
php artisan serve
```

Visit: http://localhost:8000

### Clearing Caches

When you make changes to config files or views:
```bash
php artisan config:clear
php artisan view:clear
```

### Stopping the Server

Press `Ctrl+C` in the terminal where `php artisan serve` is running.

Or kill the process:
```bash
pkill -f "php artisan serve"
```

## Production Considerations

When deploying to production, you may want to:

1. **Use Database Sessions** (requires migration):
   ```bash
   php artisan session:table
   php artisan migrate
   ```
   Then change `.env`: `SESSION_DRIVER=database`

2. **Optimize Performance**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Set Environment**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

4. **Build Assets** (if using Vite):
   ```bash
   npm run build
   ```
   Then restore `@vite()` directive in layout.

## Support

For issues not covered here:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review `SETUP.md` for detailed configuration
3. Check Laravel documentation: https://laravel.com/docs

## Quick Reference

**Start Server:**
```bash
php artisan serve
```

**Clear Caches:**
```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

**List Routes:**
```bash
php artisan route:list
```

**Test Database:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

**View Logs:**
```bash
tail -f storage/logs/laravel.log
```
