# FanHub Plus

FanHub Plus is a Laravel community platform for discovering fandoms, characters, articles, videos, events, upcoming releases, and merchandise. Members can save favourites, rate content, write reviews, and submit their own stories. Administrators manage the platform through a separate dashboard.

## Live website

**[Open FanHub Plus](https://fanhubplus.infinityfree.io/FanHub-Plus/public/)**

Use **Login / Register** on the website to access the demonstration accounts.

## Evaluator login credentials

Use the following accounts to evaluate the administrator and member experiences.

| Account | Email | Password | Role |
| --- | --- | --- | --- |
| Administrator | `admin@example.com` | `password` | Admin |
| Example user | `user@example.com` | `password` | Registered User |
| Additional example user | `alex.rivera@example.com` | `password` | Registered User |

## Suggested evaluation walkthrough

1. **Browse as a guest:** open the homepage, explore fandoms, view characters and content, and try the events, upcoming releases, and merchandise pages.
2. **Sign in as the example user:** inspect the dashboard, favourites, bookmarks, and profile. Saved fandom preferences prioritize matching content while keeping other content available.
3. **Try ratings and reviews:** submit a star rating and a review on a supported detail page. A submitted review is visible to its author while awaiting approval; it becomes public after an administrator approves it.
4. **Check onboarding:** register a new account to see the favourite-fandom selection flow.
5. **Sign in as the administrator:** inspect content, categories, characters, events, merchandise, media, users, and review moderation.
6. **Check both themes and mobile layouts:** use the theme switch and test navigation, filters, cards, and forms at smaller screen widths.

Sign out before switching between the administrator and example user.

## Main features

- Public discovery pages with searching, filtering, and pagination.
- Fandom-based personalization from saved user preferences.
- Character and content detail pages with linked media and related items.
- Event listings, spotlight cards, nearby discovery, and calendar downloads.
- Upcoming releases and merchandise collections.
- Member dashboard, favourites, bookmarks, ratings, reviews, and submissions.
- Admin management, role-based access, media uploads, and moderation.
- Contact form, newsletter subscriptions, and chatbot.
- Responsive light/dark themes and branded error pages.

Some integrations depend on the deployment configuration. Nearby discovery requires location permission; AI responses require a configured Gemini API key; outgoing email requires working mail settings.

## Technology

- Laravel 11 and PHP 8.2+
- MySQL for application data; SQLite for automated tests
- Blade templates, Bootstrap 5, Bootstrap Icons, and Alpine.js
- Vite, Sass, GSAP, Swiper, and Lenis
- Laravel Sanctum for API authentication

## Run locally

### Requirements

- PHP 8.2+ with the extensions required by Laravel and the selected database driver
- Composer 2
- MySQL
- Node.js 20.19+ in the 20.x line, or Node.js 22.12+, with npm

### 1. Install dependencies

Open a terminal in the project directory:

```bash
composer install
npm ci
```

### 2. Configure the environment

Copy `.env.example` to `.env`.

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS / Linux:**

```bash
cp .env.example .env
```

Create an empty MySQL database, then update these values in `.env`:

```dotenv
APP_NAME=FanHubPlus
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fanhubplus
DB_USERNAME=root
DB_PASSWORD=
```

Use your own database username and password.

### 3. Initialize and build

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Open **http://localhost:8000** and use the login credentials above.

Use a separate local database for evaluation.

### 4. Frontend development

To rebuild automatically while editing, run this in a second terminal:

```bash
npm run dev
```

Keep `php artisan serve` running in the first terminal.

### Optional services

For queued work and scheduled tasks, run these in separate terminals as needed:

```bash
php artisan queue:work
php artisan schedule:work
```

Configure mail, Gemini, and remote media storage through the relevant environment settings before evaluating those integrations.

## Tests and maintenance

Run the automated test suite:

```bash
php artisan test
```

Build frontend assets:

```bash
npm run build
```

Clear cached configuration and views after changing environment settings:

```bash
php artisan optimize:clear
```

If older event records have missing slugs, repair their detail links:

```bash
php artisan events:repair-slugs
```

If uploaded images do not appear locally, check the configured storage disk and run `php artisan storage:link` for public-disk uploads.

## Project structure

| Location | Purpose |
| --- | --- |
| `app/Http/Controllers/` | Public, member, and admin request handling |
| `app/Models/` | Database models and relationships |
| `app/Services/` | Shared application logic |
| `resources/views/` | Blade layouts, components, and pages |
| `resources/css/` and `resources/js/` | Frontend styling and behaviour |
| `routes/` | Web, member, admin, and API routes |
| `database/migrations/` | Database schema |
| `database/seeders/` | Demonstration accounts and sample data |
| `tests/` | Automated tests |
| `docs/` | Additional project documentation |
