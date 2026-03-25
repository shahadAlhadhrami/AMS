# AMS System Startup Guide

This guide covers how to set up and run the AMS system using **Laravel Herd** on macOS.

---

## Prerequisites

### 1. Laravel Herd — provides PHP and Composer
Laravel Herd bundles **PHP** and **Composer** — you do not install them separately.

1. Download from [herd.laravel.com](https://herd.laravel.com) and install it
2. Open Herd — its icon will appear in your macOS menu bar
3. That's it. PHP and Composer are now managed by Herd

### 2. Node.js & npm — install separately
Herd does not include Node.js. Install it once:

1. Go to [nodejs.org](https://nodejs.org) and download the **LTS** version
2. Run the installer
3. Verify in a new terminal: `node --version` and `npm --version`

---

## Step 0: Fix "PHP not installed" Error (Herd Users)

If you see `command not found: php` or `command not found: composer`, Herd's binaries are not on your PATH yet.

**Fix — open a brand new terminal window**, then run:

```bash
source ~/.zshrc
```

Then verify it works:

```bash
php --version
composer --version
```

Both should return a version number. If they still fail, check that Herd is running (menu bar icon) and try opening a new terminal window again.

> **Why this happens:** Herd adds its PHP and Composer to your PATH by modifying `~/.zshrc`. Terminals that were already open before Herd was installed, or terminals that don't load `~/.zshrc` (like some VS Code integrated terminals), won't have that PATH update.

---

## First-Time Setup

Run these commands once when setting up AMS on a new machine or after cloning the repo.

### Option A — One Command (Recommended)

From the project root directory:

```bash
composer setup
```

This single command will:
1. Install all PHP dependencies (`composer install`)
2. Create your `.env` file from `.env.example` (if it doesn't exist)
3. Generate the application key (`php artisan key:generate`)
4. Run database migrations (`php artisan migrate`)
5. Install Node.js dependencies (`npm install`)
6. Build frontend assets (`npm run build`)

---

### Option B — Manual Step-by-Step

If you prefer to run each step individually:

#### 1. Install PHP Dependencies
```bash
composer install
```

#### 2. Set Up Environment File
```bash
cp .env.example .env
```

#### 3. Generate Application Key
```bash
php artisan key:generate
```

#### 4. Create the SQLite Database File
```bash
touch database/database.sqlite
```

#### 5. Run Database Migrations
```bash
php artisan migrate
```
*(If prompted to create the database, type `yes`)*

#### 6. Install Node.js Dependencies
```bash
npm install
```

#### 7. Build Frontend Assets
```bash
npm run build
```

---

## Daily Startup

Once setup is complete, this is the only command you need each time you want to run AMS:

```bash
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

To stop everything, press `Ctrl + C` in the terminal.

---

## Troubleshooting

### "php: command not found" or "composer: command not found"
1. Make sure **Laravel Herd is running** (check the menu bar)
2. Open a **new terminal window**
3. Run `source ~/.zshrc` and try again

### "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Database errors / missing tables
```bash
php artisan migrate
```

### SQLite database file missing
```bash
touch database/database.sqlite
php artisan migrate
```

### Frontend assets not loading
```bash
npm install
npm run build
```
