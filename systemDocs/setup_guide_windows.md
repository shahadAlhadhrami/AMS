# AMS System Startup Guide — Windows

This guide covers how to set up and run the AMS system on **Windows** using **Laravel Herd**.

---

## Step 1: Install Laravel Herd (PHP + Composer)

Laravel Herd for Windows bundles **PHP** and **Composer** — you do not install them separately.

1. Go to [herd.laravel.com](https://herd.laravel.com) and download the Windows installer
2. Run the installer and follow the prompts
3. After installation, Herd will appear in your **system tray** (bottom-right taskbar)
4. Make sure Herd is running before opening a terminal

---

## Step 2: Install Node.js and npm

1. Go to [nodejs.org](https://nodejs.org) and download the **LTS** Windows installer (`.msi`)
2. Run the installer — keep all default options
3. Restart your computer after installation

---

## Step 3: Verify Your Environment

Open a **new PowerShell window** (search "PowerShell" in the Start menu) and run:

```powershell
php --version
composer --version
node --version
npm --version
```

All four should return a version number. If `php` or `composer` are not found:
- Make sure **Herd is running** (check the system tray)
- **Close and reopen PowerShell** — Herd modifies your PATH and a fresh terminal is needed
- If still not found, restart your computer once after Herd installation

---

## First-Time Setup

Open PowerShell, navigate to the AMS project folder, and run one of the following:

### Option A — One Command (Recommended)

```powershell
composer setup
```

This single command will:
1. Install all PHP dependencies
2. Create your `.env` file from `.env.example`
3. Generate the application key
4. Run database migrations
5. Install Node.js dependencies
6. Build frontend assets

---

### Option B — Manual Step-by-Step

#### 1. Install PHP Dependencies
```powershell
composer install
```

#### 2. Set Up Environment File
```powershell
copy .env.example .env
```

#### 3. Generate Application Key
```powershell
php artisan key:generate
```

#### 4. Create the SQLite Database File
```powershell
New-Item -Path database/database.sqlite -ItemType File -Force
```

#### 5. Run Database Migrations
```powershell
php artisan migrate
```
*(If prompted to create the database, type `yes`)*

#### 6. Install Node.js Dependencies
```powershell
npm install
```

#### 7. Build Frontend Assets
```powershell
npm run build
```

---

## Daily Startup

Once setup is complete, this is the only command you need each time:

```powershell
composer dev
```

This starts four processes simultaneously:

| Process | What it does |
|---|---|
| `php artisan serve` | PHP web server at `http://localhost:8000` |
| `php artisan queue:listen` | Processes background jobs |
| `php artisan pail` | Real-time log viewer |
| `npm run dev` | Vite frontend dev server with hot reload |

Open **[http://localhost:8000](http://localhost:8000)** in your browser.

To stop everything, press `Ctrl + C` in the PowerShell window.

---

## Troubleshooting

### "php is not recognized as a command"
1. Make sure **Laravel Herd is running** (check the system tray)
2. **Close and reopen PowerShell**
3. If still failing, restart your computer — Windows sometimes needs a reboot to apply PATH changes

### "No application encryption key has been specified"
```powershell
php artisan key:generate
```

### Database errors / missing tables
```powershell
php artisan migrate
```

### SQLite database file missing
```powershell
New-Item -Path database/database.sqlite -ItemType File -Force
php artisan migrate
```

### Frontend assets not loading
```powershell
npm install
npm run build
```

### PowerShell script execution is disabled
If you see an error about script execution policy, run this once in PowerShell as Administrator:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```
Then close and reopen PowerShell normally and retry.
