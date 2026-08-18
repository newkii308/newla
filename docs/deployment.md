# คู่มือการ Deploy ขึ้นเซิร์ฟเวอร์จริง (Production Deployment)

## 1. การ Deploy บน VPS (Nginx + PHP-FPM บน Ubuntu/Debian)

### โครงสร้างการทำงาน:
```text
ผู้ใช้งาน ➔ Nginx (Port 80/443) ➔ public/index.php ➔ PHP-FPM
```

### การตั้งค่า Virtual Host ของ Nginx:
```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/my-project/public;

    index index.php;
    charset utf-8;

    # ความปลอดภัย: ป้องกันการเข้าถึงไฟล์ซ่อนและไฟล์คอนฟิก
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 2. การ Deploy บน Shared Hosting (cPanel / DirectAdmin)

1. อัปโหลดโค้ดโปรเจกต์ทั้งหมดขึ้นเซิร์ฟเวอร์
2. ตั้งค่า **Document Root** ของโดเมนให้ชี้ไปที่โฟลเดอร์ `/public`:
   ```text
   /home/username/domains/example.com/public_html/public
   ```
3. ไฟล์ `public/.htaccess` ที่ NEWLA สร้างไว้ จะทำการ Rewrite URL และป้องกันการเข้าถึง `.env`, `app/`, `config/`, `storage/` โดยอัตโนมัติ