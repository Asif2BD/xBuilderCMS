# XBuilder Deployment Guide

Complete deployment instructions for Apache, Nginx, and OpenLiteSpeed servers.

---

## 📋 Prerequisites

- **PHP 8.0 or higher**
- **Required PHP Extensions:**
  - curl
  - json
  - openssl
  - zip (for DOCX support)
  - mbstring
- **Web Server:** Apache, Nginx, or OpenLiteSpeed
- **AI API Key** from Claude, Gemini, or OpenAI

---

## 🔧 Apache Deployment

### Shared Hosting (cPanel, Plesk, etc.)

1. **Upload Files**
   - Upload all files to `public_html` (or your domain's root directory)
   - Ensure `.htaccess` file is uploaded (enable "show hidden files")

2. **Set Permissions**
   ```bash
   chmod 755 xbuilder
   chmod 755 site
   chmod 700 xbuilder/storage
   ```

3. **Visit Your Site**
   - Navigate to `yourdomain.com`
   - You'll be redirected to `/xbuilder/setup`
   - Complete the setup wizard

### VPS/Dedicated Server

1. **Install Apache & PHP**
   ```bash
   sudo apt update
   sudo apt install apache2 php8.0 php8.0-fpm php8.0-curl php8.0-json php8.0-mbstring php8.0-zip
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **Clone Repository**
   ```bash
   cd /var/www
   git clone https://github.com/Asif2BD/xBuilderCMS.git
   cd xBuilderCMS
   ```

3. **Set Permissions**
   ```bash
   sudo chown -R www-data:www-data .
   sudo chmod 755 xbuilder site
   sudo chmod 700 xbuilder/storage
   ```

4. **Configure Virtual Host**
   ```bash
   sudo nano /etc/apache2/sites-available/xbuilder.conf
   ```

   Add:
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/xBuilderCMS

       <Directory /var/www/xBuilderCMS>
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/xbuilder-error.log
       CustomLog ${APACHE_LOG_DIR}/xbuilder-access.log combined
   </VirtualHost>
   ```

5. **Enable Site**
   ```bash
   sudo a2ensite xbuilder
   sudo systemctl reload apache2
   ```

---

## 🚀 Nginx Deployment

### 1. Install Nginx & PHP-FPM

```bash
sudo apt update
sudo apt install nginx php8.0-fpm php8.0-curl php8.0-json php8.0-mbstring php8.0-zip
```

### 2. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/Asif2BD/xBuilderCMS.git
cd xBuilderCMS
```

### 3. Set Permissions

```bash
sudo chown -R www-data:www-data .
sudo chmod 755 xbuilder site
sudo chmod 700 xbuilder/storage
```

### 4. Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/xbuilder
```

Copy the entire contents from `nginx.conf` file in the repository, then update:
- `root /var/www/xBuilderCMS;`
- `server_name yourdomain.com;`
- `fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;` (check your PHP version)

### 5. Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/xbuilder /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Fix PHP-FPM Permissions (if needed)

```bash
sudo nano /etc/php/8.0/fpm/pool.d/www.conf
```

Ensure:
```ini
user = www-data
group = www-data
listen.owner = www-data
listen.group = www-data
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.0-fpm
```

---

## ⚡ OpenLiteSpeed Deployment

### 1. Install OpenLiteSpeed

```bash
wget -O - http://rpms.litespeedtech.com/debian/enable_lst_debian_repo.sh | sudo bash
sudo apt update
sudo apt install openlitespeed lsphp80 lsphp80-common lsphp80-curl lsphp80-json
```

### 2. Clone Repository

```bash
cd /usr/local/lsws/Example/html
sudo git clone https://github.com/Asif2BD/xBuilderCMS.git
cd xBuilderCMS
```

### 3. Use LiteSpeed Configuration

```bash
# Remove default .htaccess
sudo rm -f .htaccess

# Use the LiteSpeed-optimized version
sudo cp .htaccess.litespeed .htaccess
```

### 4. Set Permissions

```bash
sudo chown -R nobody:nogroup .
sudo chmod 755 xbuilder site
sudo chmod 700 xbuilder/storage
```

### 5. Configure Virtual Host

1. Access OpenLiteSpeed WebAdmin: `https://your-ip:7080`
2. Login (default: admin/123456)
3. Go to **Virtual Hosts** → **Add**
4. Set:
   - Virtual Host Name: `xbuilder`
   - Virtual Host Root: `/usr/local/lsws/Example/html/xBuilderCMS/`
   - Config File: `$SERVER_ROOT/conf/vhosts/xbuilder/vhconf.conf`
5. Add a listener on port 80
6. Map the domain to the virtual host

### 6. Enable LiteSpeed Cache (Optional)

The `.htaccess.litespeed` file already includes LiteSpeed Cache directives for optimal performance!

---

## 🔒 SSL/HTTPS Setup

### Using Let's Encrypt (Recommended - Free)

#### Apache
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

#### Nginx
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

#### OpenLiteSpeed
```bash
sudo apt install certbot
sudo certbot certonly --webroot -w /usr/local/lsws/Example/html/xBuilderCMS -d yourdomain.com
```

Then configure SSL in OpenLiteSpeed WebAdmin:
- Virtual Host → SSL → SSL Private Key: `/etc/letsencrypt/live/yourdomain.com/privkey.pem`
- SSL Certificate: `/etc/letsencrypt/live/yourdomain.com/fullchain.pem`

---

## 🐳 Docker Deployment (Coming Soon)

Docker support is planned for future releases. Star the repository to stay updated!

---

## 🔍 Troubleshooting

### 403 Forbidden Error

**Apache:**
```bash
sudo chmod 755 /var/www/xBuilderCMS
sudo chown -R www-data:www-data /var/www/xBuilderCMS
```

**Nginx:**
Check PHP-FPM is running:
```bash
sudo systemctl status php8.0-fpm
```

### 500 Internal Server Error

Check error logs:

**Apache:**
```bash
sudo tail -f /var/log/apache2/error.log
```

**Nginx:**
```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.0-fpm.log
```

### API Key Validation Fails

- Ensure cURL is enabled: `php -m | grep curl`
- Check outbound HTTPS connections are allowed (firewall)
- Verify the API key is correct and has credits

### Storage Directory Not Writable

```bash
sudo chmod 700 xbuilder/storage
sudo chown -R www-data:www-data xbuilder/storage  # or nobody:nogroup for OpenLiteSpeed
```

---

## 📊 Performance Optimization

### PHP Configuration

Edit `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 120
memory_limit = 256M
```

### Enable OPcache

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### Nginx Caching

Add to Nginx config:
```nginx
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=xbuilder:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
```

---

## 🔄 Updates

To update XBuilder:

```bash
cd /var/www/xBuilderCMS
git pull origin main
sudo chown -R www-data:www-data .
```

**Note:** Your configuration and generated sites are preserved in `xbuilder/storage/` and `site/` directories.

---

## 🆘 Need Help?

- **Issues**: [GitHub Issues](https://github.com/Asif2BD/xBuilderCMS/issues)
- **Documentation**: [Wiki](https://github.com/Asif2BD/xBuilderCMS/wiki)
- **Community**: Join our discussions

---

**Made with ❤️ by Asif Rahman**

*Powered by xCloud.host*
