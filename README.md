# Backend API Setup

This backend API is built with **Laravel 12**, **Laravel Passport** for authentication, **Spatie Laravel Permission** for role-based access control, and a **hierarchical request approval workflow system**. Follow the instructions below to set up and run the application.

## Assessment Requirements

This project implements a request approval workflow system where:
- Users can submit requests that require approval through a hierarchical approver system
- Each department can have its own approver hierarchy
- Requests flow from the lowest approval level to the highest
- If any approver at a level rejects, the request is rejected
- If all approvers at all levels approve, the request is approved

### Key Features
- **User Authentication**: OAuth2 via Laravel Passport
- **Department Management**: Organize users into departments
- **Role-Based Access Control**: Spatie Permissions for granular access control
- **Request Approval Workflow**: Hierarchical approval system with multiple levels
- **Department-Specific Approvers**: Different approval hierarchies per department

## Prerequisites

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher (for frontend assets)
- **npm**: Latest version
- **MySQL**: 8.4 (if using Docker/Sail) or SQLite (default)
- **Git**: For version control

## Installation Methods

### Option 1: Local Development (Recommended for Windows)

1. **Clone the repository**
```bash
git clone <repository-url>
cd crown-interactive-interview
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node.js dependencies**
```bash
npm install
```

4. **Environment setup**
```bash
copy .env.example .env
php artisan key:generate
```

5. **Configure database**
   - By default, the project uses SQLite (no additional setup needed)
   - For MySQL, update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **Run database migrations**
```bash
php artisan migrate
```

7. **Install Laravel Passport**
```bash
php artisan install:api --passport
php artisan passport:keys
```

8. **Build frontend assets**
```bash
npm run build
```

9. **Start the development server**
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### Option 2: Docker with Docker Compose

1. **Clone the repository**
```bash
git clone <repository-url>
cd crown-interactive-interview
```

2. **Configure environment**
```bash
copy .env.example .env
php artisan key:generate
```

3. **Update .env for Docker**
Update the following variables in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=your_password
```

4. **Start Docker containers**
```bash
docker-compose up -d
```

5. **Run setup inside container**
```bash
docker-compose exec laravel.test composer install
docker-compose exec laravel.test npm install
docker-compose exec laravel.test php artisan migrate
docker-compose exec laravel.test php artisan install:api --passport
docker-compose exec laravel.test php artisan passport:keys
docker-compose exec laravel.test npm run build
```

The application will be available at `http://localhost`

6. **Stop containers**
```bash
docker-compose down
```

7. **View logs**
```bash
docker-compose logs -f
```

## Quick Setup Script

The project includes a composer script for quick setup:

```bash
composer setup
```

This will automatically:
- Install Composer dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run migrations
- Install npm dependencies
- Build frontend assets

## Development

### Running the development server with hot reload

**Local development:**
```bash
composer dev
```

**Docker:**
```bash
docker-compose exec laravel.test php artisan serve
```

This starts:
- Laravel server (http://localhost:8000)
- Queue worker
- Log viewer
- Vite dev server (http://localhost:5173)

### Running tests

**Local development:**
```bash
composer test
```

**Docker:**
```bash
docker-compose exec laravel.test php artisan test
```

### Code formatting

```bash
./vendor/bin/pint
```

## Project Structure

- `app/` - Application core (Controllers, Models, etc.)
- `routes/` - API routes definition
- `database/` - Migrations and seeders
- `resources/` - Frontend assets and views
- `config/` - Configuration files
- `public/` - Publicly accessible files

## Key Features

- **Laravel Passport**: OAuth2 authentication
- **Spatie Permissions**: Role-based access control
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast frontend build tool
- **MySQL/SQLite**: Database support

## Environment Variables

Key environment variables in `.env`:

- `APP_NAME`: Application name
- `APP_ENV`: Environment (local/production)
- `APP_DEBUG`: Debug mode (true/false)
- `APP_URL`: Application URL
- `DB_CONNECTION`: Database connection (sqlite/mysql)
- `DB_*`: Database configuration

## Troubleshooting

### Permission Issues
If you encounter permission issues on Linux/Mac:
```bash
chmod -R 775 storage bootstrap/cache
```

### Passport Keys Missing
If Passport keys are not generated:
```bash
php artisan passport:keys
```

### Cache Issues
**Local development:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

**Docker:**
```bash
docker-compose exec laravel.test php artisan cache:clear
docker-compose exec laravel.test php artisan config:clear
docker-compose exec laravel.test php artisan route:clear
```

## Additional Commands

**Local development:**
- **Queue worker**: `php artisan queue:work`
- **Schedule runner**: `php artisan schedule:run`
- **Log viewer**: `php artisan pail`

**Docker:**
- **Queue worker**: `docker-compose exec laravel.test php artisan queue:work`
- **Schedule runner**: `docker-compose exec laravel.test php artisan schedule:run`
- **Log viewer**: `docker-compose exec laravel.test php artisan pail`
- **Execute bash in container**: `docker-compose exec laravel.test bash`

## Assessment Submission

This project includes all required components for the assessment:

### 1. Entity Relationship Diagram (ERD)
- **Complete ERD**: See [ASSESSMENT_ERD.md](ASSESSMENT_ERD.md)
- Includes request approval system with hierarchical approvers
- Department-specific approval hierarchies
- All relationships and constraints documented

### 2. Department-Specific Approvers
- Each department can have its own approval hierarchy
- Approval levels defined per department
- Approvers assigned to specific levels within departments
- See ERD for complete relationship structure

### 3. RESTful API Endpoints
All required endpoint categories implemented:

**i. User Authentication and Management**
- Register, Login, Logout
- User CRUD operations
- Role/Permission assignment
- Profile management

**ii. Department Management**
- Department CRUD operations
- User assignment to departments
- Department hierarchy support

**iii. Approver Hierarchy Setup**
- Create/Update/Delete approval levels
- Assign approvers to levels
- Department-specific hierarchies
- Priority-based approver ordering

**iv. Request Submission and Tracking**
- Submit requests
- View request status
- Track approval progress
- Request statistics

**v. Approval/Rejection Actions**
- Approve requests
- Reject requests
- View approval history
- Automatic workflow progression

### 4. Docker Setup
- Complete Docker configuration in `compose.yaml`
- Setup instructions for both local and Docker environments
- See installation instructions above

## Assumptions

1. **Database**: Default uses SQLite for simplicity, MySQL available via Docker
2. **Authentication**: OAuth2 access tokens used for API authentication
3. **Approval Workflow**: Requests must go through all defined levels for approval
4. **Department Hierarchy**: Each department operates independently with its own approval structure
5. **Priority System**: Approvers at the same level are processed by priority (lower = first)
6. **Rejection**: Any rejection at any level immediately rejects the entire request
