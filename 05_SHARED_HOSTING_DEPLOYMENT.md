# Shared Hosting Deployment

## Required
- PHP compatible with Laravel 13
- MySQL
- writable storage/bootstrap cache
- HTTPS
- SMTP or hosting mail service
- Composer support or locally-built vendor
- Node only needed for local asset build unless hosting supports it

## Local Production Build
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

Configure database, mail, filesystem and session.

Do NOT require Redis.

## Queue
Queue is NOT required for core functionality.

Do not make deployment depend on:
```bash
php artisan queue:work
```

If hosting later supports cron/queue, optional background tasks can be enabled.

## Storage
If supported:
```bash
php artisan storage:link
```

## Checklist
- [ ] APP_DEBUG=false
- [ ] HTTPS
- [ ] DB configured
- [ ] storage writable
- [ ] uploads work
- [ ] SMTP works
- [ ] auth works
- [ ] authorization works
- [ ] no queue dependency
- [ ] no Redis dependency
