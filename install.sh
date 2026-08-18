#!/usr/bin/env sh
set -e

echo "\033[1;36m=======================================\033[0m"
echo "\033[1;36m       Installing NEWLA CLI...         \033[0m"
echo "\033[1;36m=======================================\033[0m"

# 1. Check PHP
if ! command -v php >/dev/null 2>&1; then
    echo "\033[31mError: PHP 8.2+ is required but not installed.\033[0m"
    echo "On Ubuntu/Debian: sudo apt install -y php-cli php-mbstring php-sqlite3 php-curl"
    echo "On Termux: pkg install -y php"
    echo "On macOS: brew install php"
    exit 1
fi

PHP_VER=$(php -r "echo PHP_VERSION;")
echo "\033[32m✓ Found PHP $PHP_VER\033[0m"

# 2. Determine paths
INSTALL_ROOT="$HOME/.newla"
INSTALL_BIN="$HOME/.local/bin"

if [ -w "/usr/local/bin" ] && [ -z "$TERMUX_VERSION" ]; then
    INSTALL_BIN="/usr/local/bin"
fi

mkdir -p "$INSTALL_ROOT"
mkdir -p "$INSTALL_BIN"

# 3. Download latest release archive from GitHub
echo "Downloading NEWLA from GitHub..."
curl -fsSL https://github.com/newkii308/newla/archive/refs/heads/main.tar.gz | tar -xz -C "$INSTALL_ROOT" --strip-components=1

chmod +x "$INSTALL_ROOT/cli/bin/newla"

# 4. Link executable
ln -sf "$INSTALL_ROOT/cli/bin/newla" "$INSTALL_BIN/newla"

# 5. Check PATH for Termux / user installs
case ":$PATH:" in
    *":$INSTALL_BIN:"*) ;;
    *)
        echo "\033[33mAdding $INSTALL_BIN to your PATH in ~/.bashrc...\033[0m"
        echo "export PATH=\"$INSTALL_BIN:\$PATH\"" >> "$HOME/.bashrc"
        export PATH="$INSTALL_BIN:$PATH"
        ;;
esac

echo "\033[1;32m✓ NEWLA CLI installed successfully!\033[0m"
echo "Run 'newla --version' or 'newla doctor' to get started."