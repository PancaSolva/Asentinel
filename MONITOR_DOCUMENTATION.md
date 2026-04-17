# Asentinel Monitoring System - Production Setup Guide

This guide explains how to set up the automated monitoring system (ping system) on a production server.

## **Prerequisites**

- PHP 8.2+
- Composer
- Database (SQLite/MySQL/PostgreSQL)
- **Supervisor** (recommended for process management)

---

## **1. Environment Configuration**

Ensure your `.env` file is configured to use the database queue:

```env
QUEUE_CONNECTION=database
```

Run the migrations to ensure the necessary tables exist:

```bash
php artisan migrate
```

---

## **2. Scheduling (Cron Job)**

The **Scheduler** is responsible for triggering the monitoring checks every minute as defined in `routes/console.php`.

Add the following Cron entry to your server (usually via `crontab -e`):

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

*Replace `/path-to-your-project` with the absolute path to your Asentinel installation.*

---

## **3. Queue Worker (Supervisor)**

To ensure the monitoring jobs (the actual HTTP pings) are processed continuously, you should use **Supervisor** to keep the `php artisan queue:work` command running.

### **Supervisor Configuration File**

Create a configuration file at `/etc/supervisor/conf.d/asentinel-worker.conf`:

```ini
[program:asentinel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-your-project/storage/logs/worker.log
stopwaitsecs=3600
```

### **Activate Supervisor**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start asentinel-worker:*
```

---

## **4. Monitoring Operations**

| Task | Command |
| :--- | :--- |
| **Manual Trigger** | `php artisan services:check` |
| **Check Queue Status** | `php artisan queue:monitor default` |
| **Restart Workers** | `php artisan queue:restart` (after code updates) |

---

## **5. Troubleshooting**

- **Timeout Issues**: If pings are slow, increase the timeout in `app/Jobs/CheckServiceJob.php`.
- **Logs**: Check `storage/logs/laravel.log` or the Supervisor log file defined above for any execution errors.
- **Queue Backlog**: If jobs are piling up, increase the `numprocs` in the Supervisor configuration.
