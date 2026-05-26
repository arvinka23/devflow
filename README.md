# DevFlow

A clean, modern project management tool built with Laravel and Tailwind CSS. Manage your projects and tasks with a Kanban board — drag & drop tasks between columns, track progress, and stay organized.

## Features

- **Authentication** — Register, login, logout
- **Projects** — Create, view and delete projects with auto-generated color avatars and progress tracking
- **Kanban Board** — 3-column board (To Do / In Progress / Done) with drag & drop
- **Tasks** — Create tasks with title, description and priority (Low / Medium / High)
- **Dark Mode** — Toggle between light and dark theme, persisted in localStorage
- **Settings** — Update your name and email
- **Responsive** — Works on mobile and desktop

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Blade Templates, Tailwind CSS v4 |
| Database | SQLite |
| Auth | Laravel Breeze |
| Build | Vite 5 |
| Testing | Pest PHP |

## Getting Started

```bash
# Clone the repository
git clone https://github.com/arvinka23/devflow.git
cd devflow

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# Create the database and run migrations
touch database/database.sqlite
php artisan migrate

# Seed a test user (optional)
php artisan db:seed

# Build assets
npm run build
```

## Running Locally

```bash
# Start both servers (two terminals)
php artisan serve   # → http://localhost:8000
npm run dev         # → Vite HMR
```

## Test Account

After running `php artisan db:seed`:

| Field | Value |
|-------|-------|
| Email | test@example.com |
| Password | password |

## Database Schema

```
users       — id, name, email, password
projects    — id, user_id, name, description, color, status
tasks       — id, project_id, title, description, status, priority, order
```

## Roadmap

- [ ] Team sharing
- [ ] Real-time updates (Laravel Echo)
- [ ] Task comments
- [ ] File attachments
- [ ] Labels & tags
- [ ] Activity log

## License

MIT
