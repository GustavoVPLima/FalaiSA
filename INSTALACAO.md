# 📋 Guia de Instalação - Falaí PHP

## Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado
- Conhecimento básico de MySQL

## Passo 1: Preparar o Servidor

### 1.1 Verificar PHP
```bash
php -v
```

### 1.2 Habilitar mod_rewrite (Apache)
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 1.3 Criar banco de dados
```bash
mysql -u root -p
```

Dentro do MySQL:
```sql
CREATE DATABASE falai_sa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'falai_user'@'localhost' IDENTIFIED BY 'sua_senha_segura';
GRANT ALL PRIVILEGES ON falai_sa.* TO 'falai_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Passo 2: Executar Schema do Banco

```bash
mysql -u falai_user -p falai_sa < schema.sql
```

Quando solicitado, digite a senha que você criou.

## Passo 3: Configurar a Aplicação

### 3.1 Editar config/database.php

```php
return [
    'host' => 'localhost',
    'user' => 'falai_user',
    'password' => 'sua_senha_segura',
    'database' => 'falai_sa',
    'charset' => 'utf8mb4'
];
```

### 3.2 Criar diretórios de upload

```bash
mkdir -p uploads/usuarios
mkdir -p uploads/comunidades
mkdir -p uploads/chat
mkdir -p static/uploads/usuarios
mkdir -p static/uploads/comunidades
mkdir -p static/uploads/chat
```

### 3.3 Definir permissões

```bash
chmod 755 uploads/
chmod 755 static/uploads/
chmod 755 uploads/usuarios
chmod 755 uploads/comunidades
chmod 755 uploads/chat
```

## Passo 4: Configurar Virtual Host (Apache)

### 4.1 Criar arquivo de configuração

```bash
sudo nano /etc/apache2/sites-available/falai.conf
```

### 4.2 Adicionar conteúdo

```apache
<VirtualHost *:80>
    ServerName falai.local
    ServerAlias www.falai.local
    
    DocumentRoot /var/www/falai
    
    <Directory /var/www/falai>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/falai_error.log
    CustomLog ${APACHE_LOG_DIR}/falai_access.log combined
</VirtualHost>
```

### 4.3 Habilitar site

```bash
sudo a2ensite falai.conf
sudo systemctl reload apache2
```

### 4.4 Editar /etc/hosts (para teste local)

```bash
sudo nano /etc/hosts
```

Adicionar:
```
127.0.0.1   falai.local
127.0.0.1   www.falai.local
```

## Passo 5: Testar a Instalação

1. Abra seu navegador
2. Acesse: `http://falai.local`
3. Você deve ser redirecionado para `/login`
4. Use as credenciais de teste:
   - Usuário: `admin`
   - Senha: `admin123`

## Passo 6: Criar Primeiro Usuário

1. Na página de login, clique em "Não tem uma conta? Cadastre-se"
2. Preencha o formulário
3. Clique em "Cadastrar"
4. Você será redirecionado para fazer login

## Estrutura de Pastas Esperada

```
/var/www/falai/
├── config/
├── controllers/
├── helpers/
├── models/
├── src/
├── static/
├── uploads/
├── views/
├── .htaccess
├── index.php
├── schema.sql
└── README_PHP.md
```

## Troubleshooting

### Erro 404 ao acessar rotas

- Verifique se mod_rewrite está habilitado
- Verifique se .htaccess tem permissão de leitura
- Reinicie o Apache: `sudo systemctl restart apache2`

### Erro de conexão com banco de dados

- Verifique as credenciais em `config/database.php`
- Verifique se o MySQL está rodando: `sudo systemctl status mysql`
- Verifique se o banco de dados existe

### Erro de permissão em uploads

```bash
sudo chown -R www-data:www-data /var/www/falai/uploads
sudo chown -R www-data:www-data /var/www/falai/static/uploads
sudo chmod -R 755 /var/www/falai/uploads
sudo chmod -R 755 /var/www/falai/static/uploads
```

### Erro "Arquivo não encontrado"

- Verifique se o arquivo existe no caminho especificado
- Verifique as permissões: `ls -la arquivo`
- Verifique se o caminho está correto

## Próximos Passos

1. Altere as credenciais padrão (admin/admin123)
2. Configure HTTPS em produção
3. Configure backups automáticos do banco de dados
4. Configure logs de erros adequadamente
5. Atualize o arquivo de configuração de segurança

## Suporte

Caso tenha dúvidas, verifique:

1. [README_PHP.md](./README_PHP.md) - Documentação geral
2. [config/database.php](./config/database.php) - Configurações do BD
3. Logs do Apache em `/var/log/apache2/`
4. Logs do MySQL em `/var/log/mysql/`

## Contato

Para suporte: suporte@falai.com
