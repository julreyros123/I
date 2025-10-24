# Copilot Instructions for AI Coding Agents

## Project Overview
This is a Laravel-based web application. The codebase follows standard Laravel conventions but includes custom models, controllers, services, and request validation. The main business logic is organized under `app/`, with subfolders for HTTP controllers, models, mail, services, and view components.

## Architecture & Key Components
- **Controllers** (`app/Http/Controllers/`): Handle HTTP requests and orchestrate business logic.
- **Models** (`app/Models/`): Eloquent ORM models for database tables. Examples: `Customer`, `Report`, `BillingRecord`, `Register`, `User`.
- **Services** (`app/Services/`): Custom business logic, e.g., `AccountNumberGenerator`.
- **Mail** (`app/Mail/`): Mailable classes for notifications, e.g., `ReportSubmitted.php`.
- **Requests** (`app/Http/Requests/`): Form request validation.
- **View Components** (`app/View/Components/`): Blade UI components.
- **Migrations** (`database/migrations/`): Schema changes, including custom tables and constraints.

## Developer Workflows
- **Run the application:**
  - Use `php artisan serve` to start the local server.
- **Database migrations:**
  - Run `php artisan migrate` to apply migrations.
- **Testing:**
  - Use `vendor/bin/phpunit` or `php artisan test` for running tests. Pest is also available (`tests/Pest.php`).
- **Dependency management:**
  - Use `composer install` for PHP dependencies.
  - Use `npm install` for frontend assets (see `package.json`).
- **Frontend build:**
  - Use `npm run dev` for development build (Vite + Tailwind).

## Project-Specific Patterns
- **Custom Account Number Logic:**
  - See `app/Services/AccountNumberGenerator.php` and related migrations for unique account number handling.
- **Role Management:**
  - User roles are managed via a custom field in the users table (`2025_09_30_035815_add_role_to_users_table.php`).
- **Report Categories:**
  - Reports have custom category fields (see migrations and `app/Models/Report.php`).
- **Unique Constraints:**
  - Customers have unique account numbers (see `2025_10_07_042213_add_unique_constraint_to_customers_account_no.php`).

## Integration Points
- **Mail:**
  - Custom mailable classes for notifications.
- **Frontend:**
  - Vite and Tailwind for asset compilation.
- **Testing:**
  - Pest and PHPUnit for unit/feature tests.

## Conventions
- Follow PSR-4 autoloading and Laravel directory structure.
- Use Eloquent relationships for model associations.
- Place business logic in services when not directly tied to a model or controller.
- Use form requests for validation.

## Key Files & Directories
- `app/Models/`, `app/Http/Controllers/`, `app/Services/`, `database/migrations/`, `resources/views/`, `tests/`
- `README.md` for general project info

---

**For unclear or incomplete sections, please request clarification or provide examples from the codebase.**
