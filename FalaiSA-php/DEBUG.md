# 🐛 Guia de Debug e Troubleshooting - Falaí PHP

## Erros Comuns e Soluções

### 1. Erro 404 - Página não encontrada

**Sintoma**: Toda rota retorna 404

**Causas Possíveis**:
- mod_rewrite não está habilitado
- .htaccess não tem permissão de leitura
- DocumentRoot configurado incorretamente

**Soluções**:
```bash
# Verificar mod_rewrite
sudo a2enmod rewrite

# Dar permissão ao .htaccess
chmod 644 .htaccess

# Reiniciar Apache
sudo systemctl restart apache2

# Verificar VirtualHost
sudo apache2ctl -t  # Syntax OK?
```

### 2. Erro 500 - Internal Server Error

**Sintoma**: Página em branco ou erro 500

**Causas Possíveis**:
- Erro na sintaxe PHP
- Classe/arquivo não encontrado
- Erro de banco de dados

**Soluções**:
```bash
# Verificar logs
tail -f /var/log/apache2/error.log

# Habilitar debug
nano config/app.php
# Alterar APP_DEBUG=true

# Testar sintaxe PHP
php -l index.php
php -l controllers/AuthController.php
```

### 3. Erro de Conexão com Banco

**Sintoma**: "Erro de conexão: ..."

**Causas Possíveis**:
- Credenciais incorretas
- MySQL não está rodando
- Banco não existe

**Soluções**:
```bash
# Verificar MySQL
sudo systemctl status mysql

# Iniciar MySQL
sudo systemctl start mysql

# Testar conexão
mysql -h localhost -u falai_user -p
> USE falai_sa;
> SELECT 1;

# Verificar credenciais
nano config/database.php
```

### 4. Erro "Class not found"

**Sintoma**: "Fatal error: Class 'NomeClasse' not found"

**Causas Possíveis**:
- Arquivo não existe
- Nome da classe incorreto
- Caminho errado no autoloader

**Soluções**:
```bash
# Verificar se arquivo existe
ls -la helpers/Database.php
ls -la models/UsuarioDAO.php

# Verificar nome da classe
grep "class Database" helpers/Database.php

# Verificar se está requerido em index.php
grep "require.*Database" index.php
```

### 5. Upload de arquivo falha

**Sintoma**: "Erro ao salvar arquivo"

**Causas Possíveis**:
- Pasta de upload não existe
- Sem permissão de escrita
- Arquivo muito grande

**Soluções**:
```bash
# Criar pastas
mkdir -p uploads/usuarios
mkdir -p uploads/comunidades
mkdir -p uploads/chat

# Dar permissão
chmod 755 uploads/
chmod 755 uploads/usuarios
chmod 755 uploads/comunidades
chmod 755 uploads/chat

# Mudar proprietário para www-data
sudo chown -R www-data:www-data uploads/

# Verificar limite de upload em php.ini
nano /etc/php/7.4/apache2/php.ini
# upload_max_filesize = 10M
# post_max_size = 10M
```

### 6. Sessão não persiste

**Sintoma**: Usuário é desconectado ao recarregar página

**Causas Possíveis**:
- Session timeout muito curto
- Pasta de sessão sem permissão
- Cookie não está sendo enviado

**Soluções**:
```bash
# Verificar pasta de sessão
php -i | grep "session.save_path"

# Dar permissão
sudo chmod 1777 /var/lib/php/sessions/

# Aumentar timeout em php.ini
nano /etc/php/7.4/apache2/php.ini
# session.gc_maxlifetime = 3600

# Verificar se session_start() está sendo chamado
grep "session_start" index.php
```

### 7. Formulário retorna vazio

**Sintoma**: `Request::post()` retorna null

**Causas Possíveis**:
- POST não foi enviado (GET usado)
- Nome do input incorreto
- Form não tem enctype correto

**Soluções**:
```php
// Verificar método
echo Request::method();  // Deve retornar "POST"

// Verificar nome do campo
var_dump($_POST);  // Ver todos os campos

// Form correto
<form method="POST" enctype="multipart/form-data">
    <input name="campo" ...>
</form>

// Acessar corretamente
$valor = Request::post('campo');
```

### 8. CSS/JS não carrega

**Sintoma**: Página sem estilo ou scripts não funcionam

**Causas Possíveis**:
- Caminho incorreto
- Pasta não existe
- Problemas de permissão

**Soluções**:
```bash
# Verificar se arquivo existe
ls -la static/style.css
ls -la static/js/dados_loader.js

# Verificar permissões
chmod 644 static/style.css
chmod 644 static/js/*.js

# Verificar caminho no HTML
<!-- Correto -->
<link rel="stylesheet" href="/static/style.css">

<!-- Errado -->
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="./static/style.css">
```

### 9. Rota não definida

**Sintoma**: Rota retorna "Página não encontrada"

**Causas Possíveis**:
- Rota não foi adicionada
- Caminho da rota incorreto
- Método HTTP errado

**Soluções**:
```php
// Verificar rotas em index.php
grep "router->get('/rota')" index.php

// Adicionar rota
$router->get('/nova-rota', ['Controller', 'metodo']);

// Testar com curl
curl -X GET http://localhost/nova-rota
curl -X POST http://localhost/nova-rota -d "campo=valor"
```

### 10. Autenticação não funciona

**Sintoma**: Não consegue fazer login

**Causas Possíveis**:
- Credenciais incorretas
- Usuário não existe
- Banco de dados vazio

**Soluções**:
```bash
# Verificar usuários no banco
mysql -u falai_user -p falai_sa
> SELECT * FROM tb_usuario;

# Inserir usuário de teste
> INSERT INTO tb_usuario (nm_login, ds_senha, nm_email) VALUES ('teste', 'senha123', 'teste@email.com');

# Limpar sessão em cookie
# (Limpar cookies do navegador)

# Verificar logs de erro
tail -f /var/log/apache2/error.log
```

## Debug Avançado

### Habilitar Debug Mode

```php
// Em helpers/View.php ou config/app.php
define('DEBUG_MODE', true);

// Então em controllers
if (DEBUG_MODE) {
    echo "Debug: " . print_r($data, true);
}
```

### Usar var_dump() e print_r()

```php
// Nas views
<?php var_dump($variavel); ?>

// Nos controllers
error_log(print_r($data, true));
```

### Ativar erro detalhado do PHP

```php
// No início do index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

### Testar Query SQL Diretamente

```bash
mysql -u falai_user -p falai_sa
> SELECT * FROM tb_usuario WHERE nm_login = 'admin';
> SELECT * FROM tb_comunidade LIMIT 5;
```

### Usar curl para testar APIs

```bash
# GET
curl -X GET http://localhost/chat/1/mensagens

# POST
curl -X POST http://localhost/chat/1/enviar \
  -d "mensagem=teste&tipo=texto"

# Com headers
curl -X GET http://localhost/admin \
  -H "Cookie: PHPSESSID=sua_sessao"
```

## Monitoramento em Produção

### Logs do Apache
```bash
# Acesso
tail -f /var/log/apache2/access.log

# Erros
tail -f /var/log/apache2/error.log

# Site específico (se houver vhost)
tail -f /var/log/apache2/falai_access.log
tail -f /var/log/apache2/falai_error.log
```

### Logs do MySQL
```bash
tail -f /var/log/mysql/error.log
tail -f /var/log/mysql/query.log  # Se habilitado
```

### Monitorar uso de recursos
```bash
# Top (processos)
top

# Disk space
df -h

# MySQL connections
mysql -u root -p -e "SHOW PROCESSLIST;"
```

## Dicas de Performance

### Ativar cache
```php
// Nas queries frequentes
$result = apcu_fetch('key');
if (!$result) {
    $result = Database::query(...);
    apcu_store('key', $result, 3600);  // 1 hora
}
```

### Otimizar índices
```sql
-- Adicionar índices às colunas frequentemente consultadas
ALTER TABLE tb_usuario ADD INDEX idx_login (nm_login);
ALTER TABLE tb_comunidade ADD INDEX idx_criador (criado_por);
ALTER TABLE tb_chat ADD INDEX idx_comunidade (id_chat_comunidade);
```

### Usar prepared statements
```php
// ✅ Bom - prepared statement
Database::query("SELECT * FROM tb_usuario WHERE id = ?", [$id]);

// ❌ Ruim - concatenação
Database::query("SELECT * FROM tb_usuario WHERE id = $id");
```

## Checklist de Deploy

- [ ] Verificar erros: `php -l index.php`
- [ ] Testar rotas principais
- [ ] Verificar permissões de pasta
- [ ] Backup do banco de dados
- [ ] Testar login
- [ ] Testar criação de comunidade
- [ ] Testar upload de arquivo
- [ ] Verificar logs para erros
- [ ] Testar em múltiplos navegadores
- [ ] Verificar HTTPS (se necessário)

---

**Dica**: Mantenha `/var/log/apache2/error.log` aberto em um terminal durante o desenvolvimento!

```bash
tail -f /var/log/apache2/error.log
```

Isso facilitará muito a identificação de problemas.
