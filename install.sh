#!/usr/bin/env sh
set -e

echo "\033[1;36mInstalling NEWLA CLI...\033[0m"

# Check PHP
if ! command -v php >/dev/null 2>&1; then
    echo "\033[31mError: PHP 8.2+ is required but not installed.\033[0m"
    echo "On Ubuntu/Debian: sudo apt install php-cli php-mbstring php-sqlite3 php-curl"
    echo "On Termux: pkg install php"
    echo "On macOS: brew install php"
    exit 1
fi

PHP_VER=$(php -r "echo PHP_VERSION;")
echo "\033[32m✓ Found PHP $PHP_VER\033[0m"

# Install target
INSTALL_DIR="/usr/local/bin"
if [ ! -w "$INSTALL_DIR" ] || [ -n "$TERMUX_VERSION" ]; then
    INSTALL_DIR="$HOME/.local/bin"
    mkdir -p "$INSTALL_DIR"
fi

# Download or link binary
echo "Installing newla binary to $INSTALL_DIR/newla..."
curl -fsSL https://raw.githubusercontent.com/newla-php/newla/main/cli/bin/newla -o "$INSTALL_DIR/newla"
chmod +x "$INSTALL_DIR/newla"

echo "\033[1;32m✓ NEWLA CLI installed successfully!\033[0m"
echo "Run 'newla --version' to get started."