# CLI Reference

The `newla` command line interface simplifies every aspect of project creation, development, database management, and maintenance.

## Global Options
- `--version`, `-v`: Display current NEWLA version, PHP version, and OS platform.
- `--help`, `-h`: Show help and command options.

## Commands

### Project Scaffolding
- `newla create <project-name>`: Create a new production-ready NEWLA project with standard directories, `.env`, `public/index.php`, `config/`, and `routes/`.
- `newla init`: Initialize NEWLA inside the current working directory.
- `newla info`: Display runtime information, PHP version, SAPI, OS architecture, and loaded packages.
- `newla doctor`: Comprehensive diagnostic tool checking PHP version, required extensions, PDO drivers, permissions, and directory structure.

### Package Management
- `newla add <package>`: Register and configure a modular package (e.g., `newla add security`).
- `newla remove <package>`: Remove a package from `newla.json`.
- `newla update`: Update dependencies.
- `newla list [packages]`: List all available CLI commands or modular packages.

### Development Server
- `newla dev [--host=127.0.0.1] [--port=8000]`: Launch the built-in PHP development server targeting `public/`.
- `newla serve`: Alias for `newla dev`.
- `newla test [--filter=pattern]`: Run the test suite.

### Code Generators
- `newla make:controller <Name>`: Scaffolds a Controller class in `app/Controllers/`.
- `newla make:model <Name>`: Scaffolds an Active Record model in `app/Models/`.
- `newla make:middleware <Name>`: Scaffolds an HTTP Middleware in `app/Middleware/`.
- `newla make:service <Name>`: Scaffolds a Domain Service in `app/Services/`.
- `newla make:migration <name>`: Creates a timestamped schema migration in `database/migrations/`.
- `newla make:seeder <Name>`: Creates a database seeder in `database/seeders/`.

### Database
- `newla migrate`: Run all pending database migrations.
- `newla migrate:rollback`: Rollback the last executed batch of migrations.
- `newla migrate:fresh`: Drop all tables and re-run migrations from scratch.
- `newla db:seed [--class=SeederClass]`: Execute database seeders.

### Production
- `newla cache:clear`: Clear temporary files in `storage/cache/`.
- `newla build`: Optimize project structure and prepare for deployment.