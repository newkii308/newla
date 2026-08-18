# Modular Packages Ecosystem

NEWLA is organized into focused, modular packages.

## Available Packages

### 1. `@newla/core`
The foundational package providing:
- Dependency Injection Container (`Newla\Core\Container\Container`)
- HTTP Request & Response (`Newla\Core\Http\Request`, `Newla\Core\Http\Response`)
- Fast Route Dispatcher (`Newla\Core\Routing\Router`)
- Database Connection, Query Builder & Migrations (`Newla\Core\Database`)
- Native View Engine with layouts & escaping (`Newla\Core\View\ViewEngine`)
- Environment & Configuration Repositories (`Newla\Core\Environment\Env`, `Newla\Core\Config\Repository`)

### 2. `@newla/security`
Production security suite:
- Cryptographic CSRF tokens & validation middleware
- Argon2id & Bcrypt password hashing
- HTTP Security Headers middleware (`CSP`, `X-Frame-Options`, `X-Content-Type-Options`)
- Sliding-window IP Rate Limiter
- Input sanitization & HTML escaping

### 3. `@newla/validator`
Validation library supporting strings, numbers, emails, URLs, min/max lengths, regex, unique database constraints, and custom rule extensions.

### 4. `@newla/logger`
Multi-channel logging system supporting File, Stderr, Database, and Webhook outputs with JSON or standard line formatters.

### 5. `@newla/storage`
Cloud & local filesystem abstraction supporting Local disk, AWS S3, and Cloudflare R2 without heavy external SDKs.

### 6. `@newla/image`
GD image processing: resizing, aspect-ratio preserved thumbnailing, crop, rotate, and conversion to WebP with malicious upload sanitization.

### 7. `@newla/auth`
Authentication guard supporting session-based authentication, user persistence, password verification, and route security middleware.

### 8. `@newla/api`
Standard JSON REST API responses, pagination formatting, and exception rendering.