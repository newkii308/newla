# คู่มือการใช้งานบน Linux

NEWLA รองรับ Linux ทุก Distribution ชั้นนำ

## Ubuntu / Debian

```bash
sudo apt update
sudo apt install -y php-cli php-mbstring php-sqlite3 php-mysql php-curl php-gd php-xml composer
curl -fsSL newla-dev.verin.online | bash
```

## Arch Linux

```bash
sudo pacman -S php composer
curl -fsSL newla-dev.verin.online | bash
```

## Alpine Linux (สำหรับ Docker)

```bash
apk add php php-mbstring php-pdo_sqlite php-pdo_mysql php-curl php-gd php-openssl composer
curl -fsSL newla-dev.verin.online | bash
```