# คู่มือการใช้งานบน Android / Termux

NEWLA สามารถทำงานบน Android ผ่านแอปพลิเคชัน Termux ได้อย่างสมบูรณ์แบบโดยไม่ต้องรูทเครื่อง

## 1. ติดตั้งแพ็กเกจที่จำเป็นใน Termux

```bash
pkg update && pkg upgrade
pkg install -y php composer git
```

## 2. ติดตั้ง NEWLA CLI

```bash
curl -fsSL https://raw.githubusercontent.com/newla-php/newla/main/install.sh | sh
```

เพิ่ม PATH ใน `~/.bashrc`:
```bash
echo 'export PATH="$HOME/.local/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
```

## 3. สร้างและรันโปรเจกต์

```bash
newla create mobile-shop
cd mobile-shop
newla dev
```

เปิดบราวเซอร์ Chrome บนมือถือไปที่ `http://127.0.0.1:8000` ได้ทันที!