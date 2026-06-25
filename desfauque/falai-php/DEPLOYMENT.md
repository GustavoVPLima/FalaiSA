# Deploy & Produção - PHP Puro FalaiSA

## 🌐 Opções de Hosting

### Opção 1: Compartilhado (Mais Barato)
- Hostinger, Bluehost, GoDaddy
- PHP 7.4+ suportado
- MySQL incluído
- Apache com mod_rewrite
- **Custo:** R$ 15-40/mês

**Setup:**
1. Fazer upload via FTP
2. Criar banco via cPanel
3. Ativar mod_rewrite
4. Acessar domínio

### Opção 2: VPS (Recomendado)
- DigitalOcean, Linode, Vultr
- Controle total do servidor
- PHP 8.0+
- SSL grátis (Let's Encrypt)
- **Custo:** R$ 20-100/mês

**Setup:**
```bash
# SSH no servidor
ssh root@seu_ip

# Instalar Apache, PHP, MySQL
sudo apt update
sudo apt install apache2 php mysql-server

# Ativar mod_rewrite
sudo a2enmod rewrite

# Clonar/copiar projeto
git clone seu-repo /var/www/falai-sa

# Configurar permissões
chmod -R 755 /var/www/falai-sa
chmod -R 777 /var/www/falai-sa/static/uploads

# Criar virtual host
sudo nano /etc/apache2/sites-available/falai-sa.conf
```

**Exemplo de Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName falai-sa.com.br
    DocumentRoot /var/www/falai-sa

    <Directory /var/www/falai-sa>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/falai-sa-error.log
    CustomLog ${APACHE_LOG_DIR}/falai-sa-access.log combined
</VirtualHost>
```

### Opção 3: Containerizado (Docker)
**Dockerfile:**
```dockerfile
FROM php:8.1-apache

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite

COPY . /var/www/html/

RUN chmod -R 755 /var/www/html/
RUN chmod -R 777 /var/www/html/static/uploads/

EXPOSE 80
```

**Docker Compose:**
```yaml
version: '3'
services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html
    
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: falai_sa
    ports:
      - "3306:3306"
```

---

## 🔒 Segurança em Produção

### 1. HTTPS/SSL
```bash
# Usar Let's Encrypt com Certbot
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d falai-sa.com.br
```

### 2. Headers de Segurança
**Adicionar ao `.htaccess`:**
```apache
# Segurança
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"

# CORS
Header set Access-Control-Allow-Origin "https://falai-sa.com.br"
```

### 3. PHP Hardening
**`config/security.php`:**
```php
// Desabilitar exibição de erros em produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Session segura
ini_set('session.secure', 1);
ini_set('session.httponly', 1);
ini_set('session.samesite', 'Strict');

// Limpar uploads antigos
ini_set('upload_tmp_dir', '/tmp');
```

### 4. CSRF Protection
```php
// Gerar token na sessão
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validar em formulários
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF inválido');
}
```

### 5. SQL Injection - Já Protegido!
```php
// ✅ Seguro - prepared statements
Database::query("SELECT * FROM users WHERE id = ?", [$id]);

// ❌ Inseguro - EVITE!
Database::query("SELECT * FROM users WHERE id = $id");
```

---

## 📊 Performance

### Otimizações Recomendadas

1. **Cache de BD**
```php
// Implementar cache simples
class Cache
{
    public static function get($key, $callback, $ttl = 3600)
    {
        $cache_file = "/tmp/cache_{$key}.json";
        
        if (file_exists($cache_file) && time() - filemtime($cache_file) < $ttl) {
            return json_decode(file_get_contents($cache_file), true);
        }
        
        $data = $callback();
        file_put_contents($cache_file, json_encode($data));
        return $data;
    }
}

// Uso
$comunidades = Cache::get('user_communities', function() {
    return Comunidade::getByUser(Auth::userId());
}, 3600);
```

2. **Compressão Gzip**
```apache
# Adicionar ao .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>
```

3. **Browser Caching**
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## 📈 Monitoramento

### Logs
```php
// Log de erros
error_log("Erro ao criar comunidade: " . $e->getMessage());

// Log de acesso
file_put_contents(__DIR__ . '/../logs/access.log', 
    date('Y-m-d H:i:s') . " - " . $_SERVER['REQUEST_URI'] . "\n", 
    FILE_APPEND);
```

### Backups Automáticos
```bash
# Script de backup
#!/bin/bash
BACKUP_DIR="/backups"
DB_NAME="falai_sa"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup BD
mysqldump -u root -p $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Backup arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/falai-sa

# Remover backups antigos
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

---

## 🚀 Checklist de Deploy

- [ ] Testar localmente
- [ ] Criar banco de dados em produção
- [ ] Configurar credenciais em `config/database.php`
- [ ] Criar diretórios de upload com permissões
- [ ] Ativar HTTPS/SSL
- [ ] Configurar headers de segurança
- [ ] Desabilitar exibição de erros
- [ ] Ativar logs
- [ ] Configurar backups
- [ ] Testar todas as funcionalidades
- [ ] Monitorar performance
- [ ] Usar CDN para assets estáticos

---

**Suporte:** Consulte a documentação do seu provedor de hosting
