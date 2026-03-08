# IT-14 Water Billing & Management System

A robust, modern web application built with Laravel 12 for managing water utility services, customer billing, and staff operations.

## 🚀 Features

- **Dashboard & Monitoring**: Real-time stats on billing, collection rates, and customer growth.
- **Customer Management**: Detailed profiles, connection classifications (Residential, Commercial, Industrial), and status tracking.
- **Automated Billing**: Accurate water consumption tracking with automated bill generation and history.
- **Payment Processing**: Multi-method payment recordings with quick search and receipt generation.
- **Meter Lifecycle Management**: Tracking installations, transfers, audits, and maintenance schedules.
- **Staff Portal**: Specialized interface for field staff to manage service tickets, inspections, and daily progress.
- **Reporting & Analytics**: Comprehensive revenue reports, usage trends, and operational metrics.
- **Activity Logging**: Full audit trail of administrative and staff actions.

## 🛠 Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade Templates, Vanilla CSS, Vite
- **Database**: MySQL / MariaDB
- **Tools**: Nixpacks (for Railway deployment), Composer, NPM

## 🏠 Local Installation

1. **Clone the repository**:
   ```bash
   git clone [repository-url]
   cd IT-14-new
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**:
   - Copy `.env.example` to `.env`.
   - Update `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` with your local database credentials.
   - Set up your `APP_URL`.

4. **Generate App Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

6. **Build Assets & Start Server**:
   ```bash
   npm run build
   php artisan serve
   ```

## ☁️ Deployment (Railway)

This project is optimized for deployment on [Railway](https://railway.app/).

1. **Push your code to GitHub**.
2. **Connect your repository** to a new Railway project.
3. **Environment Variables**: Add the following in the Railway Variables tab:
   - `APP_KEY` (copy from your local `.env`)
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - Database variables (Railway automatically links these if you add a MySQL service): `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
4. **Build & Start**: Railway uses the included `railway.json` and `Procfile` to handle migrations and start the server automatically.

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
