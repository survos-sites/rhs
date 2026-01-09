# RHS - Museum Collection Management System

A modern Symfony 8.0 PHP application for managing museum and collection data from CollectiveAccess systems. This system provides a powerful backend for cataloging, searching, and managing museum collections with advanced search capabilities and admin interfaces.

## Features

- 🏛️ **Museum Collection Management**: Import and manage museum objects, entities, and collections
- 🔍 **Advanced Search**: Full-text search powered by Meilisearch with filterable and sortable fields
- 🎨 **Modern Admin Interface**: EasyAdmin-based admin panels for data management
- 📱 **Responsive Frontend**: Built with Bootstrap 5 and Stimulus for modern web interactions
- 🔄 **Data Synchronization**: Automated fetching from CollectiveAccess systems
- 🗄️ **Flexible Database**: Support for both PostgreSQL and SQLite
- 🧪 **Comprehensive Testing**: Full PHPUnit test suite with strict type checking

## Key Features

- **CollectiveAccess Integration**: Fetch data from CollectiveAccess via GraphQL API
- **Modern PHP Stack**: Built with PHP 8.4+ and Symfony 8.0
- **Search Integration**: Full-text search with Meilisearch
- **Admin Interface**: EasyAdmin-based admin panels
- **JSONL Support**: Efficient data import/export using JSONL format
- **Entity Generation**: Automatic entity creation from data profiles
- **Testing**: Comprehensive PHPUnit test suite

## Requirements

- **PHP**: 8.4 or higher
- **Composer**: Latest stable version
- **Database**: PostgreSQL (default) or SQLite
- **Meilisearch**: Optional, for search functionality
- **Node.js**: For asset compilation (if needed)

## Requirements

- **PHP 8.4+** with required extensions (ctype, iconv, pdo_sqlite or pdo_pgsql)
- **Composer** 2.0+
- **Node.js** 18+ (for asset management)
- **Meilisearch** (optional, for advanced search)

## Quick Start

### 1. Clone and Install

```bash
git clone <repository-url>
cd rhs
composer install
```

### 2. Database Setup (SQLite - Recommended for Development)

For development, we recommend using SQLite for simplicity:

```bash
# Create the database directory
mkdir -p var/data

# Configure SQLite (edit .env.local)
echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data/app.db" > .env.local

# Create the database schema
bin/console doctrine:database:create
bin/console doctrine:schema:create
```

### 3. Run Database Migrations

```bash
bin/console doctrine:migrations:migrate
```

### 4. Fetch Collection Data from CollectiveAccess

```bash
# Fetch all data with default settings
bin/console ca:fetch

# Or fetch specific bundles with custom profile
bin/console ca:fetch --profile="ca.profile.json" --bundles="ca_objects,ca_entities" --limit=1000
```

### 5. Start Development Server

```bash
# Using Symfony CLI (recommended)
symfony server:start

# Or using PHP's built-in server
php -S localhost:8000 -t public/
```

Visit `http://localhost:8000` to access the application.

## Installation

### Complete Installation Steps

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd rhs
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install  # If you need to modify frontend assets
   ```

3. **Environment Configuration**
   ```bash
   # Copy environment template
   cp .env .env.local
   
   # Edit .env.local with your settings
   nano .env.local
   ```

4. **Database Setup**

   **Option A: SQLite (Development)**
   ```bash
   mkdir -p var/data
   echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data/app.db" > .env.local
   bin/console doctrine:database:create
   bin/console doctrine:schema:create
   ```

   **Option B: PostgreSQL (Production)**
   ```bash
   # Create database
   createdb rhs_app
   
   # Configure in .env.local
   echo "DATABASE_URL=postgresql://user:password@127.0.0.1:5432/rhs_app?serverVersion=16&charset=utf8" > .env.local
   bin/console doctrine:database:create
   bin/console doctrine:schema:create
   ```

5. **Run Migrations**
   ```bash
   bin/console doctrine:migrations:migrate
   ```

6. **Install Assets**
   ```bash
   bin/console assets:install
   bin/console asset-map:compile
   ```

7. **Clear Cache**
   ```bash
   bin/console cache:clear
   ```

## Environment Configuration

### Required Environment Variables

Create or update `.env.local` with these variables:

```bash
# Database (if not using SQLite)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# CollectiveAccess Connection (for ca:fetch command)
CA_BASE_URL=https://your-collectiveaccess-instance.com
CA_USERNAME=your_username
CA_PASSWORD=your_password

# Meilisearch (optional, for search functionality)
MEILI_SERVER=http://127.0.0.1:7700
MEILI_API_KEY=your_api_key
MEILI_SEARCH_KEY=your_search_key

# Symfony Configuration
APP_ENV=dev
APP_SECRET=your_app_secret_here
DEFAULT_URI=http://localhost:8000
```

### Environment Files Priority

Symfony loads environment files in this order (later files override earlier ones):
1. `.env` - Default values
2. `.env.local` - Local overrides (not committed to git)
3. `.env.$APP_ENV` - Environment-specific defaults
4. `.env.$APP_ENV.local` - Environment-specific local overrides

## Development Server

### Starting the Development Server

```bash
# Using Symfony CLI (recommended)
symfony server:start

# Or using PHP built-in server
php -S localhost:8000 -t public/
```

The application will be available at `http://localhost:8000`.

### Alternative: Using Castor Tasks

This project includes Castor tasks for common operations:

```bash
# Install Castor first if you haven't already
composer require castor/castor

# Run build task (sets up testing)
castor build

# Fetch data from CollectiveAccess
castor fetch --limit=100
```

## Configuration

### Environment Variables

Key environment variables in `.env.local`:

```bash
# Database (choose one)
DATABASE_URL=sqlite:///%kernel.project_dir%/var/data/app.db
# DATABASE_URL=postgresql://user:password@127.0.0.1:5432/rhs_app?serverVersion=16

# CollectiveAccess Configuration
COLLECTIVEACCESS_BASE_URL=https://your-ca-instance.org
COLLECTIVEACCESS_API_KEY=your-api-key

# Meilisearch (optional)
MEILISEARCH_URL=http://localhost:7700
MEILISEARCH_API_KEY=your-meilisearch-key

# App Environment
APP_ENV=dev
APP_SECRET=your-app-secret
```

### CollectiveAccess Setup

To use the `ca:fetch` command, you need access to a CollectiveAccess system:

1. **Base URL**: The URL of your CollectiveAccess instance
2. **API Key**: API credentials with access to object, entity, and collection data
3. **Profile**: JSON configuration file defining data mappings (optional)

## The `ca:fetch` Command

The `ca:fetch` command is the primary way to import data from CollectiveAccess systems.

### Basic Usage

```bash
# Fetch all default bundles with no limit
bin/console ca:fetch

# Fetch with specific limit
bin/console ca:fetch --limit=500

# Fetch specific bundles
bin/console ca:fetch --bundles="ca_objects,ca_entities"

# Use custom profile
bin/console ca:fetch --profile="custom.profile.json"

# Run in test mode (no data changes)
bin/console ca:fetch --test

# Verbose output
bin/console ca:fetch --verbose
```

### Command Options

| Option | Description | Default |
|--------|-------------|---------|
| `--profile` | Path to CollectiveAccess profile file | `ca.profile.json` |
| `--bundles` | Comma-separated list of bundles to fetch | `ca_objects,ca_entities,ca_collections,ca_places` |
| `--limit` | Maximum number of records to fetch per bundle | `null` (no limit) |
| `--test` | Run in test mode without making changes | `false` |
| `--verbose` | Enable detailed output | `false` |

### Profile Configuration

A profile file defines how CollectiveAccess data maps to local entities:

```json
{
  "objects": {
    "endpoint": "/service/collections/objects",
    "entity": "Obj",
    "fields": {
      "id": "object_id",
      "name": "preferred_labels.name"
    }
  }
}
```

### Default Bundles

The command fetches these data fields by default:
- Core identification: `preferred_labels.name`, `idno`, `type_id`
- Visibility: `access`, `status`
- Descriptions: `description`, `descriptionSet`, `internal_notes`
- Dates: `object_date`, `primaryDateSet`
- Physical attributes: `dimensions`, `georeference`, `geonames`
- Keywords: `RHS_keywords_list`, `lcsh_terms`
- Rights: `rightsSet`, `sourceSet`
- Related records: entities, places, collections
- Media: representation URLs (small, medium, original)
- Links: `external_link`

## Testing

### Running Tests

```bash
# Run all tests
./bin/phpunit

# Run specific test file
./bin/phpunit tests/YourTest.php

# Run tests with coverage
./bin/phpunit --coverage-text

# Generate a new test
bin/console make:test
```

### Test Configuration

- Tests are located in the `tests/` directory
- PHPUnit configuration: `phpunit.dist.xml`
- Test environment: `APP_ENV=test`
- Test database: Automatically uses separate database with `_test` suffix

## Key Commands

### Development Commands

```bash
# Cache management
bin/console cache:clear
bin/console cache:warmup

# Database operations
bin/console doctrine:migrations:migrate
bin/console doctrine:schema:validate

# Asset management
bin/console asset-map:compile
bin/console assets:install

# Linting and validation
bin/console lint:container
bin/console lint:twig templates/
bin/console lint:yaml config/
bin/console lint:translations
```

### Custom Commands

```bash
# Fetch data from CollectiveAccess
bin/console ca:fetch

# Generate entities from data profiles
bin/console code:entity

# Generate admin controllers
bin/console code:meili:admin
```

## Project Structure

```
src/
├── Command/           # Console commands (ca:fetch, etc.)
├── Controller/        # Web controllers
├── Entity/           # Doctrine entities (Obj, etc.)
├── Repository/       # Doctrine repositories
├── Service/          # Business logic services
└── Twig/             # Twig extensions

templates/             # Twig templates
tests/                 # PHPUnit tests
config/                # Symfony configuration
assets/                # Frontend assets
migrations/            # Database migrations
var/                   # Runtime files (cache, logs, data)
public/                # Web root directory
```

## Database Schema

The main entity is `App\Entity\Obj` which represents museum objects with fields:

- **Core Fields**: `id`, `idno`, `preferredLabelsName`, `typeId`
- **Access Control**: `access`, `status`
- **Content**: `description`, `objectDate`, `dimensions`
- **Relationships**: Links to entities, places, collections
- **Media**: URLs for object representations
- **Metadata**: Keywords, subject headings, rights

The entity is integrated with Meilisearch for full-text search capabilities.

## Code Style Guidelines

This project follows strict PHP 8.4+ standards:

- **Strict Types**: Every PHP file MUST start with `declare(strict_types=1);`
- **Modern Features**: Constructor property promotion, readonly properties, enums, attributes
- **PSR-4 Autoloading**: `App\` namespace maps to `src/`
- **Final Classes**: All entities and services are final where possible
- **Type Declarations**: All methods and properties have strict type declarations

## Dependencies

### Core Dependencies
- **Symfony 8.0**: Main framework
- **Doctrine ORM**: Database abstraction layer
- **Meilisearch**: Full-text search engine
- **EasyAdmin**: Admin interface generation
- **PHPUnit**: Testing framework
- **AssetMapper**: Modern asset management

### Additional Bundles
- **survos/ez-bundle**: Development tools
- **survos/import-bundle**: Data import utilities
- **survos/jsonl-bundle**: JSONL file handling
- **survos/meili-bundle**: Meilisearch integration

## Troubleshooting

### Common Issues

#### Database Connection Errors
```bash
# Check database URL in .env.local
echo $DATABASE_URL

# Validate doctrine configuration
bin/console doctrine:schema:validate
```

#### Cache Issues
```bash
# Clear all cache
bin/console cache:clear --env=dev
bin/console cache:clear --env=prod
```

#### Asset Issues
```bash
# Recompile assets
bin/console asset-map:compile
bin/console assets:install --symlink
```

#### CollectiveAccess Connection Issues
1. Verify the CollectiveAccess instance URL is accessible
2. Check that GraphQL API is enabled on the CollectiveAccess instance
3. Verify username and password credentials
4. Test the connection manually with curl or Postman

### Getting Help

- Check Symfony documentation: https://symfony.com/doc
- Doctrine ORM documentation: https://www.doctrine-project.org
- Meilisearch documentation: https://docs.meilisearch.com
- For project-specific issues, check the `AGENTS.md` file for development guidelines

## Production Deployment

### Environment Setup
```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Clear and warmup production cache
bin/console cache:clear --env=prod
bin/console cache:warmup --env=prod

# Compile assets for production
bin/console asset-map:compile --env=prod

# Set proper permissions
chmod -R 755 var/
```

### Security Considerations
- Never commit `.env.local` to version control
- Use strong `APP_SECRET` values
- Configure proper database credentials
- Set up proper firewall/security rules
- Use HTTPS in production
- Regular security updates via `composer update`

## License

This project is proprietary. All rights reserved.
