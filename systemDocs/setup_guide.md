# AMS System Setup Guide

This guide provides step-by-step instructions on how to set up and run the AMS (Laravel) system on a new machine or environment.

## Prerequisites

Before starting, ensure your new environment has the following installed:
- **PHP** (version 8.2 or higher)
- **Composer** (PHP dependency manager)
- **Node.js & npm** (Javascript runtime and package manager)

---

## Setup Instructions

Since you have already configured the `.env` file, follow these steps to install the necessary dependencies, set up the database, and start the application.

### Method 1: Automated Setup (Recommended)

The project has a built-in Composer script that automatically installs dependencies, generates the application key, runs migrations, and builds the frontend assets.

You can run this single command in your terminal from the root directory of the project:

```bash
composer setup
```

*This command will sequentially run `composer install`, set up your `.env`, generate your app key, run database migrations, install npm packages, and build your assets.*

---

### Method 2: Manual Setup

If you prefer to run the setup steps manually, execute the following commands in order:

#### 1. Install PHP Dependencies
Install all the required Laravel packages using Composer:
```bash
composer install
```

#### 2. Generate Application Key
Generate a secure application key (this will update your `.env` file):
```bash
php artisan key:generate
```

#### 3. Run Database Migrations
Create the necessary database tables:
```bash
php artisan migrate
```
*(If prompted to create the database, type `yes`)*

#### 4. Install Node Dependencies
Install frontend packages (like Tailwind CSS and Vite dependencies) using npm:
```bash
npm install
```

#### 5. Build Frontend Assets
Compile the frontend assets for the application:
```bash
npm run build
```

---

## Running the Application

Once the setup is complete, you can start the development servers to view the application in your browser.

The simplest way is to use the built-in development command which concurrently runs the PHP server, Vite, and other necessary processes:

```bash
composer dev
```

Alternatively, you can run them manually in separate terminal windows:

**Terminal 1 (PHP Server):**
```bash
php artisan serve
```

**Terminal 2 (Frontend Assets):**
```bash
npm run dev
```

The application should now be accessible at `http://localhost:8000` (or whichever URL is provided in the terminal output).
