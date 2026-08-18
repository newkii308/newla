# Contributing to NEWLA

Thank you for your interest in contributing to NEWLA!

## Code Guidelines
- Adhere strictly to **PSR-12** code styling.
- Enable `declare(strict_types=1);` in every PHP file.
- Use native PHP 8.2+ features (enums, readonly properties, typed parameters, match expressions).
- Write automated tests in `tests/` for all new features.
- Avoid introducing heavy third-party dependencies into `@newla/core`.

## Submitting Pull Requests
1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/my-feature`.
3. Run the test suite: `newla test`.
4. Commit your changes: `git commit -m "Add feature X"`.
5. Push to branch: `git push origin feature/my-feature`.
6. Open a Pull Request.