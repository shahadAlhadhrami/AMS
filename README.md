# Academic Management System (AMS)

This is a Laravel-based application for academic management, project evaluations, and rubrics.

## Setup Guide (Running on a New Machine)

Because this repository follows best practices, certain files and folders (like `.env`, `vendor/`, and `node_modules/`) are intentionally ignored by Git. When cloning this project to a new computer, you must run a few steps to install dependencies and set up the environment.

### Prerequisites
- PHP (matching the version in `composer.json`, likely 8.2+)
- Composer
- Node.js & npm
- MySQL / MariaDB

### Step-by-Step Installation

1. **Clone the repository and enter the directory**
   ```bash
   git clone <repository-url>
   cd AMS
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   ```

4. **Set up the Environment File**
   Copy the example environment file to create your local `.env`:
   ```bash
   cp .env.example .env
   ```
   *Note: Open `.env` and update your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Run Database Migrations and Seeders**
   This will create all the tables and populate them with initial data.
   ```bash
   php artisan migrate --seed
   ```

7. **Compile Frontend Assets**
   ```bash
   npm run build
   # or for active development: npm run dev
   ```

8. **Serve the Application**
   ```bash
   php artisan serve
   ```
   You can now access the application at `http://localhost:8000`.

## Common Commands After Making Changes

Quick reference for commands to run after different types of changes.

### After changing a Blade / UI file
```bash
php artisan view:clear
```
> Clears cached compiled views so Laravel re-renders from the updated source file.

### After changing any PHP file (config, routes, providers, etc.)
```bash
php artisan optimize:clear
```
> Clears all Laravel caches at once: config, routes, views, events, compiled files, and Filament cache.

### After adding or changing Tailwind CSS classes in a Blade file
```bash
npm run build
```
> Recompiles CSS/JS assets. Required whenever you add new Tailwind utility classes that weren't previously in the compiled stylesheet.

### After creating or modifying a migration file
```bash
php artisan migrate
```
> Runs any pending migrations against the database.

### After modifying a seeder
```bash
php artisan db:seed
# or to re-seed a specific seeder:
php artisan db:seed --class=ExampleSeeder
```

### Nuclear option — something looks wrong and nothing else works
```bash
php artisan optimize:clear && npm run build
```
> Clears all server-side caches and rebuilds all frontend assets. Also do a hard browser refresh (`Cmd+Shift+R` / `Ctrl+Shift+R`) after this.

---

## Database Schema vs Migrations

The database definition located in `systemDocs/Database_Stageing/database_schema.sql` serves as the base architectural design. However, the actual database migrations (`database/migrations/`) contain the absolute source of truth for the application.

Recent additions not reflected in the staging schema include:
- **Activity Logging** tables (`activity_log`)
- **Role and Permissions** tables via Spatie (`roles`, `permissions`, etc.)
- **Allowed Email Domains** table
- **Two-Factor Authentication** columns on the `users` table
- A `fill_order` column on the `evaluations` table