# TRC Project Structure

## Purpose
Dashboard administrasi sekolah untuk The Rasyidu Center - membantu manajemen, keuangan, dan solusi sistem.

## Tech Stack
- **Backend**: Laravel 11 + Filament 3.2
- **Frontend**: Tailwind CSS, Vite
- **PHP**: 8.4
- **Database**: SQLite (default)

## Code Style
- PSR-4 autoloading
- Filament convention untuk resources/pages/widgets
- Tailwind utility classes untuk styling

## Key Commands

### Development
```bash
npm run dev          # Start Vite dev server
php artisan serve    # Start Laravel dev server
```

### Build
```bash
npm run build        # Production build
```

### Database
```bash
php artisan migrate
php artisan migrate:fresh --seed
```

## Project Structure

### Filament Panels
- **User Panel**: `/` path - Main dashboard untuk users
  - Provider: `app/Providers/Filament/UserPanelProvider.php`
  - Theme: `resources/css/filament/user/theme.css`
  - Resources: `app/Filament/User/Resources/`
  - Pages: `app/Filament/User/Pages/`
  - Widgets: `app/Filament/User/Widgets/`

- **Admin Panel**: `/admin` path - Admin dashboard
  - Provider: `app/Providers/Filament/AdminPanelProvider.php`
  - Theme: `resources/css/filament/admin/theme.css`

### Navigation Groups (User Panel)
- Salesforce
- Service
- Finance
- Rekap Datacenter
- Rekap Akademik
- Rekap Finance
- Data Kuning
- Data Biru

## Important Files
- `app/Providers/Filament/UserPanelProvider.php` - User panel configuration
- `resources/css/filament/user/theme.css` - User panel custom styles
- `resources/css/filament/user/tailwind.config.js` - User panel Tailwind config
- `tailwind.config.js` - Root Tailwind config
- `vite.config.js` - Vite build configuration
- `app/Filament/User/Widgets/SalesForceStatsWidget.php` - Stats widget dengan TRC colors & enhanced data
- `resources/views/filament/user/widgets/sales-force-stats-widget.blade.php` - Enhanced widget UI: pie chart, summary cards, progress bars

## Color Coding System
- Data Kuning: Yellow/Amber badge styling
- Data Biru: Cyan/Sky badge styling