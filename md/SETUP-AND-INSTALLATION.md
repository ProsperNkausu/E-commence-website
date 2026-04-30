# Setup and Installation Guide

## Prerequisites

Before installing TemaTech-Innovation, ensure your system has:

| Component | Requirement | Recommended |
|-----------|-------------|-------------|
| **PHP** | 8.1+ | 8.2 or 8.3 |
| **MySQL** | 10.4+ | 10.4 / MariaDB 8.0+ |
| **Apache** | 2.4+ | XAMPP with built-in |
| **Composer** | 2.0+ | Latest stable |
| **RAM** | 2GB | 4GB+ |
| **Storage** | 500MB | 1GB+ |
| **OS** | Windows, macOS, Linux | Windows 10+ / macOS 12+ / Ubuntu 20+ |

## Installation Methods

### Method 1: Local Development (XAMPP)

**Recommended for development and testing**

#### Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Choose version with PHP 8.1+
3. Run installer and select:
   - Apache ✓
   - MySQL ✓
   - PHP ✓
   - PhpMyAdmin ✓
4. Choose installation directory (default: `C:\xampp` on Windows)
5. Complete installation

#### Step 2: Start Services

1. Open XAMPP Control Panel
2. Start **Apache** module (click Start)
3. Start **MySQL** module (click Start)
4. Verify both show "Running" status

#### Step 3: Clone/Copy Project

1. Navigate to XAMPP htdocs folder:
   ```
   Windows: C:\xampp\htdocs
   macOS: /Applications/XAMPP/htdocs
   Linux: /opt/lampp/htdocs
   ```

2. Copy `TemaTech-innovation` folder here

#### Step 4: Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Log in (default: root with no password)
3. Click "New" to create database
4. Database name: `tematech_innovation`
5. Collation: `utf8mb4_general_ci`
6. Click "Create"

#### Step 5: Import Database Schema

1. Open `tematech_innovation` database
2. Click "Import" tab
3. Click "Choose File"
4. Select: `TemaTech-innovation/database/tematech_innovation (clean db).sql`
5. Click "Import"
6. Wait for success message

#### Step 6: Configure Database Connection

1. Open `TemaTech-innovation/config/db.php`
2. Verify settings:
   ```php
   'host' => 'localhost',
   'database' => 'tematech_innovation', 
   'username' => 'root',
   'password' => '',  // Leave empty for XAMPP default
   'charset' => 'utf8mb4'
   ```

#### Step 7: Install Composer Dependencies

1. Open Command Prompt/Terminal
2. Navigate to project:
   ```bash
   cd C:\xampp\htdocs\TemaTech-innovation
   ```
   
3. Install dependencies:
   ```bash
   composer install
   ```

4. Wait for installation to complete (~2-5 minutes)
5. Verify `vendor/` folder is created

#### Step 8: Configure Email (Optional)

1. Open `config/env.php`
2. For Gmail SMTP:
   ```php
   MAIL_HOST='smtp.gmail.com'
   MAIL_PORT=587
   MAIL_USERNAME='your-email@gmail.com'
   MAIL_PASSWORD='app-specific-password'
   MAIL_ENCRYPTION='tls'
   ```
3. **Important:** Use Gmail app password, not your main password
   - Go to myaccount.google.com → Security
   - Enable 2-factor authentication
   - Generate "App passwords"
   - Use this password in configuration

#### Step 9: Configure Payment Gateway

1. Register at https://lenco.co
2. Get API keys from Lenco dashboard
3. Create `.env` file in project root:
   ```
   LENCO_SECRET_KEY=your_secret_key_here
   LENCO_PUBLIC_KEY=your_public_key_here
   ```

#### Step 10: Start Application

1. Open browser
2. Navigate to: `http://localhost/TemaTech-innovation`
3. You should see homepage

#### Step 11: Login as Admin

1. Go to: `http://localhost/TemaTech-innovation/admin`
2. Login credentials:
   ```
   Email: admin@TemaTech.com
   Password: (check your setup or reset via database)
   ```

---

### Method 2: Production Setup (Apache with cPanel)

#### Step 1: Prerequisites

- Domain name or subdomain
- Hosting with cPanel access
- SSH access (optional but recommended)
- PHP 8.1+ with extensions: PDO, MySQL, GD, cURL
- MySQL 10.4+ database access

#### Step 2: Upload Project

1. Via FTP/SFTP or File Manager:
   - Upload all files to `public_html` or subdirectory
   - Ensure `.htaccess` is uploaded (hidden file)

2. Via Git (if available):
   ```bash
   git clone https://github.com/your-repo/TemaTech-innovation.git
   cd TemaTech-innovation
   ```

#### Step 3: Create Database

Via cPanel **MySQL Databases**:
1. Create database: `yourusername_tematech`
2. Create MySQL user: `yourusername_admin`
3. Assign user to database with ALL privileges
4. Note the full database name with prefix

#### Step 4: Import Schema

Via cPanel **phpMyAdmin**:
1. Select your database
2. Import: `database/tematech_innovation (clean db).sql`
3. All tables created

#### Step 5: Configure Database

1. Edit `config/db.php`:
   ```php
   'host' => 'localhost',           // Usually localhost
   'database' => 'full_db_name_with_prefix',
   'username' => 'full_username',   // With cPanel prefix
   'password' => 'your_password',
   'charset' => 'utf8mb4'
   ```

#### Step 6: Install Dependencies

Via SSH or cPanel terminal:
```bash
cd /home/yourusername/public_html/TemaTech-innovation
composer install --optimize-autoloader
```

Or via cPanel File Manager:
1. Upload `vendor/` folder if local install completed

#### Step 7: Set File Permissions

```bash
chmod 755 .
chmod -R 755 public/
chmod -R 755 database/
chmod -R 755 includes/
chmod 644 .htaccess
chmod 644 config/db.php
```

#### Step 8: Configure .htaccess for Production

Main `.htaccess` file should contain:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect HTTP to HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Custom routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?page=$1 [QSA,L]
</IfModule>
```

#### Step 9: Environment Configuration

1. Create `.env` file in root:
   ```
   APP_ENV=production
   APP_DEBUG=false
   LENCO_SECRET_KEY=your_production_key
   LENCO_PUBLIC_KEY=your_production_key
   ```

2. Restrict access to `.env`:
   ```apache
   <Files .env>
       Deny from all
   </Files>
   ```

#### Step 10: SSL Certificate

1. Via cPanel **AutoSSL** or Let's Encrypt
2. Generate free SSL certificate
3. Update domain to use HTTPS
4. Update `config/db.php` if needed for HTTPS URLs

#### Step 11: Test Application

1. Visit your domain: `https://yourdomain.com`
2. Admin panel: `https://yourdomain.com/admin`
3. Test login and basic functionality

---

## Troubleshooting Installation

### Issue: Database Connection Failed

**Error:** `SQLSTATE[HY000]: General error: 1030`

**Solution:**
1. Check MySQL is running
2. Verify database name in `config/db.php`
3. Check username/password correct
4. Ensure database exists and user has permissions
5. Test credentials in phpMyAdmin

### Issue: Composer Dependencies Missing

**Error:** `Uncaught Error: Class not found 'PHPMailer\PHPMailer\PHPMailer'`

**Solution:**
```bash
cd TemaTech-innovation
composer install
composer dump-autoload -o
```

### Issue: 404 Pages Not Found

**Error:** All pages return 404 except index.php

**Solution:**
1. Check `.htaccess` is in root folder
2. Enable Apache mod_rewrite:
   ```bash
   a2enmod rewrite
   ```
3. Restart Apache
4. Check configuration in `routes/web.php`

### Issue: File Upload Permissions

**Error:** "Cannot upload product images"

**Solution:**
```bash
chmod -R 755 public/images/
chmod -R 755 public/category/
chmod -R 755 storage/    # If exists
```

### Issue: Email Not Sending

**Error:** Invoice emails not received

**Solutions:**
1. Check Gmail SMTP credentials in `config/env.php`
2. Verify app password (not main Gmail password)
3. Check "Less secure apps" is enabled (if not using app password)
4. Test with simple PHPMailer script:
   ```php
   require 'vendor/autoload.php';
   $mail = new PHPMailer\PHPMailer\PHPMailer();
   $mail->isSMTP();
   $mail->Host = 'smtp.gmail.com';
   $mail->SMTPAuth = true;
   $mail->Username = 'your-email@gmail.com';
   $mail->Password = 'app-password';
   $mail->Port = 587;
   $mail->setFrom('noreply@tematech.com', 'TemaTech');
   $mail->addAddress('test@example.com');
   $mail->Subject = 'Test';
   $mail->Body = 'Test email';
   $mail->send();
   ```

### Issue: Payment Gateway Not Working

**Error:** Lenco payment verification fails

**Solutions:**
1. Verify `LENCO_SECRET_KEY` and `LENCO_PUBLIC_KEY` are correct
2. Check .env file loaded via `config/env.php`
3. Test API connection:
   ```bash
   curl -X GET https://api.lenco.co/api/v1/verify \
        -H "Authorization: Bearer YOUR_SECRET_KEY"
   ```
4. Verify HTTPS for production

### Issue: White Screen of Death

**Error:** Blank page, no error message

**Solutions:**
1. Enable PHP error display in development:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
2. Check PHP error log: `php_error.log`
3. Check Apache error log
4. verify all files uploaded correctly

---

## Post-Installation Checklist

- [x] Apache and MySQL running
- [x] Database imported successfully
- [x] Composer dependencies installed
- [x] Database connection configured
- [x] Able to login as admin
- [x] Can browse products
- [x] Can add items to cart
- [x] Email configuration complete
- [x] Payment gateway configured
- [x] File permissions set correctly
- [x] HTTPS enabled (production)
- [x] SSL certificate installed (production)
- [x] Backups configured (production)
- [x] Environment variables secured

---

## Default Credentials

### Admin Account
```
Email: admin@TemaTech.com
Password: (Set during database import or reset)
```

### Database
```
Hostname: localhost
Database: tematech_innovation
Username: root (local) or provided by host (production)
Password: (blank for XAMPP, or hosting provided)
```

---

## Performance Optimization

### Enable Caching (Production)
```php
// In config files
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
```

### Database Optimization
```sql
-- Add indexes for frequently queried fields
CREATE INDEX idx_customer_email ON customers(email);
CREATE INDEX idx_order_status ON orders(status);
CREATE INDEX idx_product_name ON products(name);
```

### Compression
```apache
# .htaccess
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## Security Hardening

1. **Restrict Admin Access:**
   ```apache
   <Directory "/path/to/admin">
       Require ip 192.168.1.0/24    # Your IP range
   </Directory>
   ```

2. **Hide Sensitive Files:**
   ```apache
   <FilesMatch "\.env|composer\.json|composer\.lock|\.git">
       Order allow,deny
       Deny from all
   </FilesMatch>
   ```

3. **Update Dependencies:**
   ```bash
   composer update
   ```

4. **Regular Backups:**
   ```bash
   mysqldump -u root -p tematech_innovation > backup_$(date +%Y%m%d).sql
   tar -czf TemaTech-backup-$(date +%Y%m%d).tar.gz TemaTech-innovation/
   ```

---

## Next Steps

1. Review [DEVELOPMENT-GUIDE.md](DEVELOPMENT-GUIDE.md) for coding standards
2. Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for common issues
3. Study [ARCHITECTURE.md](ARCHITECTURE.md) to understand the system
4. Configure admin panel settings
5. Add your products and categories

---

**Installation Complete!** You're now ready to develop or deploy TemaTech-Innovation.
