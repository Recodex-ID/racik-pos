# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel Livewire CRUD starter kit built with enterprise-level security and performance in mind. The application uses **Flux UI components** and **Spatie Laravel Permission** for comprehensive role & permission management.

## Development Commands

### Development Environment
```bash
# Start full development environment (server, queue, logs, vite)
composer run dev

# Individual services
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Testing
```bash
# Run all tests with config clear
composer run test

# Run specific test suites
php artisan test
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run single test file
php artisan test tests/Feature/DashboardTest.php
```

### Asset Building
```bash
# Development build with hot reloading
npm run dev

# Production build
npm run build
```

### Code Quality
```bash
# Format code using Laravel Pint
./vendor/bin/pint

# Run Pint with specific configuration
./vendor/bin/pint --config=pint.json
```

### Database & Seeding
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

## Architecture Overview

### Core Stack
- **Laravel 12.x** with **PHP 8.2+**
- **Livewire Volt 1.7** for single-file components
- **Flux UI 2.1** premium component library
- **Spatie Laravel Permission** for RBAC
- **PestPHP** for testing
- **Vite** with **Tailwind CSS 4.x**

### Key Directories
```
app/Livewire/           # Livewire components
├── Actions/            # Action components (Logout)
├── Administrator/      # Admin CRUD components
└── Dashboard.php       # Main dashboard with analytics

resources/views/
├── livewire/          # Component views
├── flux/              # Flux UI component overrides
└── components/        # Blade components

resources/js/          # JavaScript with Chart.js integration
resources/css/         # Tailwind CSS with Flux theming
```

### Database Structure
- **Users table** with roles & permissions (Spatie package)
- **Cache/Jobs/Sessions** using database driver
- **SQLite in-memory** for testing

### Authentication & Authorization
- **Spatie Laravel Permission** integration
- **Role-based middleware**: `role:Super Admin` 
- **Route protection** with `auth`, `verified` middleware
- **Blade directives**: `@role('Super Admin')` for template-level access

### Component Architecture
- **Dashboard Component**: Analytics with computed properties, Chart.js integration, paginated recent users
- **Administrator Components**: Full CRUD for Users, Roles, Permissions with modal forms
- **Form Objects**: Livewire v3 form abstraction for maintainability
- **Computed Properties**: Database queries cached within component lifecycle

## Development Notes
- use the flux(resources/views/flux) component in the created view
- always respond in Indonesian
- before generating code, study docs/LARAVEL-BEST-PRACTICES.md and docs/LIVEWIRE-BEST-PRACTICES.md and apply them to the generated code.
- the generated code must always be consistent with the existing code.

## Git Best Practices
- biasakan konfirmasi dahulu sebelum push ke repo

## JavaScript + Livewire Integration Best Practices

### Event Listeners
Always use both events for Livewire compatibility:
```javascript
document.addEventListener('DOMContentLoaded', initFunction);
document.addEventListener('livewire:navigated', () => setTimeout(initFunction, 300));
```

### Timing & Retry Mechanism
- Use setTimeout with delay (100-300ms) to ensure DOM ready
- Implement retry mechanism with multiple delays:
```javascript
function retryInitialization() {
    [500, 1000, 2000].forEach(delay => {
        setTimeout(() => {
            if (!initialized) initFunction();
        }, delay);
    });
}
```

### Instance Management (Charts, Libraries, etc)
- Always destroy existing instances before creating new ones
- Store instances in window object for global access
- Check if instances already exist before retry:
```javascript
window.instances = { chart1: null, chart2: null };

// Destroy existing
if (window.instances.chart1) {
    window.instances.chart1.destroy();
}
```

### Data Extraction
- Use robust fallback: `dataset.attribute || textContent || defaultValue`
- Wrap in try-catch for error handling
- Parse JSON with fallback default:
```javascript
const data = JSON.parse(element.dataset.data || '{"default": "value"}');
```

### Element Detection
- Check existence of all required elements before initialization
- Return early if elements are not found:
```javascript
const el1 = document.getElementById('required1');
const el2 = document.getElementById('required2');
if (!el1 || !el2) return false;
```

### File Organization
- Separate logic into separate files (chart.js, modal.js, etc)
- Import through main app.js
- Modular approach for maintainability

### Template Pattern
```javascript
// Global instances
window.myInstances = { item1: null, item2: null };

// Init function
function initMyFeature() {
    // Check elements
    if (!requiredElements) return false;
    
    try {
        // Destroy existing
        if (window.myInstances.item1) {
            window.myInstances.item1.destroy();
        }
        
        // Initialize new
        window.myInstances.item1 = new Library(config);
        return true;
    } catch (error) {
        console.error('Init error:', error);
        return false;
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', initMyFeature);
document.addEventListener('livewire:navigated', () => setTimeout(initMyFeature, 300));

// Retry mechanism
function retryInit() {
    [500, 1000, 2000].forEach(delay => {
        setTimeout(() => {
            if (!window.myInstances.item1) initMyFeature();
        }, delay);
    });
}

document.addEventListener('DOMContentLoaded', retryInit);
document.addEventListener('livewire:navigated', retryInit);
```

## Performance Optimization Guidelines

### Livewire Performance
- **Computed Properties**: Use `#[Computed]` for database queries to cache within component lifecycle
- **Event Listeners**: Prefer over polling for real-time updates
- **Primitive Types**: Pass strings/integers instead of objects to components
- **Component Nesting**: Keep at maximum 1 level deep
- **Form Objects**: Always use Livewire v3 form abstraction

### Frontend Performance
- **Vite HMR**: Fast development with hot module replacement
- **Tailwind Purging**: Automatic unused CSS removal
- **Chart.js**: Proper instance management with destroy/recreate pattern
- **Asset Compilation**: Use `npm run build` for production

## Testing Strategy

### Test Structure
- **Feature Tests**: Full component functionality testing
- **Unit Tests**: Individual method/class testing  
- **Database**: In-memory SQLite for fast testing
- **Coverage**: Comprehensive test coverage for core features

### Test Categories
```bash
tests/Feature/
├── Auth/              # Authentication flow tests
├── Administrator/     # Admin CRUD functionality
├── Dashboard/         # Dashboard component tests
└── Settings/          # User settings tests
```

## Key Configuration Files

- **phpunit.xml**: Testing with in-memory SQLite
- **vite.config.js**: Build configuration with Tailwind plugin
- **composer.json**: Development scripts and dependencies
- **package.json**: Frontend build scripts
- **docs/**: Laravel and Livewire best practices documentation