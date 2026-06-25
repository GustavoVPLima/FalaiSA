# Migração FalaiSA - Flask para PHP Puro

Guia completo para migração do projeto FalaiSA de **Flask (Python)** para **PHP Puro**.

## 📁 Estrutura do Projeto

```
falai-php/
├── config/
│   ├── database.php      # Configurações de BD
│   └── app.php           # Configurações da aplicação
├── src/
│   ├── Controllers/      # Lógica das rotas
│   ├── Models/          # Acesso ao banco de dados
│   ├── Helpers/         # Funções utilitárias
│   └── Router.php       # Sistema de roteamento
├── static/              # CSS, JS, uploads (do projeto original)
├── templates/           # HTML templates (do projeto original)
├── .htaccess           # Rewrite rules Apache
└── index.php           # Entrada principal
```

## 🚀 Como Instalar

### 1. Clonar/Copiar os arquivos
```bash
# Copiar estrutura para seu servidor
cp -r falai-php /var/www/seu-dominio/
```

### 2. Configurar Apache
Criar virtual host:
```apache
<VirtualHost *:80>
    ServerName falai-sa.local
    DocumentRoot /var/www/seu-dominio/falai-php
    
    <Directory /var/www/seu-dominio/falai-php>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Ativar mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 3. Criar pastas de upload
```bash
mkdir -p static/uploads/{usuarios,comunidades,chat}
chmod 755 static/uploads
chmod 755 static/uploads/*
```

### 4. Tester a conexão
Acessar `http://falai-sa.local/login` no navegador

---

## 📚 Mapeamento Flask → PHP

### Rotas

| Flask | PHP |
|-------|-----|
| `@app.route('/login', methods=['POST'])` | `$router->post('/login', [AuthController::class, 'login'])` |
| `request.form.get('usuario')` | `Request::post('usuario')` |
| `redirect(url_for('index'))` | `View::redirect('/')` |
| `render_template('login.html')` | `View::show('login')` |
| `session['usuario']` | `$_SESSION['usuario']` |

### Banco de Dados

| Flask | PHP |
|-------|-----|
| `mysql.connector.connect(...)` | `Database::connect()` |
| `cursor.execute(sql, params)` | `Database::query($sql, $params)` |
| `cursor.fetchone()` | `Database::fetchAssoc($result)` |
| `cursor.fetchall()` | `Database::fetchAll($result)` |

### Upload de Arquivos

```python
# Flask
file = request.files['arquivo']
if file and allowed_file(file.filename):
    filename = secure_filename(file.filename)
    file.save(filepath)
```

```php
// PHP
if (Request::hasFile('arquivo')) {
    $file = Request::file('arquivo');
    if (File::isAllowed($file['name'], 'imagem')) {
        $filename = File::save($file, 'comunidades');
    }
}
```

---

## 🔄 Converter seus Templates Flask → PHP

### Antes (Flask/Jinja2)
```html
<h1>Olá {{ usuario }}!</h1>
{% for comunidade in comunidades %}
    <p>{{ comunidade.nome }}</p>
{% endfor %}
```

### Depois (PHP)
```php
<h1>Olá <?php echo htmlspecialchars($usuario); ?>!</h1>
<?php foreach ($comunidades as $comunidade): ?>
    <p><?php echo htmlspecialchars($comunidade['nm_comunidade']); ?></p>
<?php endforeach; ?>
```

### Renomear templates
- `login.html` → colocar em `templates/login.php`
- Remover extensão `.html` nas chamadas
- Usar `View::show('login')` para renderizar

---

## 📌 Exemplos de Uso

### Login
```php
// AuthController.php
public function login()
{
    $username = Request::post('usuario');
    $password = Request::post('senha');
    
    $user = Usuario::findByLoginAndPassword($username, $password);
    
    if ($user) {
        Auth::login($user);
        View::redirect('/');
    } else {
        $_SESSION['erro'] = 'Inválido';
        View::redirect('/login');
    }
}
```

### Criar Comunidade
```php
// CommunityController.php
public function create()
{
    Auth::check();
    
    if (Request::hasFile('imagem_comunidade')) {
        $imagem = File::save(Request::file('imagem_comunidade'), 'comunidades');
    }
    
    $id = Comunidade::create([
        'nm_comunidade' => Request::post('nome_comunidade'),
        'criado_por' => Auth::userId(),
        'img_perfil' => $imagem
    ]);
    
    View::redirect('/minhas-comunidades');
}
```

### Chat (API JSON)
```php
// ChatController.php
public function sendMessage($id)
{
    Auth::check();
    
    $msg = Chat::create([
        'id_chat_comunidade' => $id,
        'id_chat_usuario' => Auth::userId(),
        'mensagem' => Request::post('mensagem'),
        'tipo' => 'texto'
    ]);
    
    View::json(['success' => true, 'mensagem' => $msg]);
}
```

---

## 🔒 Segurança

- ✅ Prepared statements (proteção contra SQL Injection)
- ✅ Session validation (Auth::check())
- ✅ CSRF protection (adicionar tokens em forms)
- ✅ File upload validation

### Adicionar CSRF Token
```php
// Na renderização
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
View::show('form', ['csrf' => $_SESSION['csrf_token']]);

// No formulário
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Na validação
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF inválido');
}
```

---

## 📝 Checklist de Migração

- [ ] Copiar `static/` para `falai-php/public/`
- [ ] Converter templates `.html` para `.php`
- [ ] Atualizar referências de `{{ variavel }}` para `<?php echo $variavel; ?>`
- [ ] Testar autenticação `/login`
- [ ] Testar criação de comunidades
- [ ] Testar chat e upload de arquivos
- [ ] Configurar `.env` ou `config/database.php` com credenciais reais
- [ ] Testar em Apache/Nginx com `.htaccess` ou nginx.conf

---

## 🆘 Troubleshooting

**404 em rotas**
→ Ativar `mod_rewrite` no Apache

**Erro de conexão BD**
→ Verificar credenciais em `config/database.php`

**Upload não funciona**
→ Criar pastas: `mkdir -p static/uploads/{usuarios,comunidades,chat}`

**Sessão perdida**
→ Adicionar `session_start()` no topo de `index.php`

---

## 📊 Performance vs Flask

| Aspecto | Flask | PHP |
|---------|-------|-----|
| Startup | 100-200ms | 10-50ms |
| Request simples | 50-100ms | 5-20ms |
| Memory per request | 50-100MB | 5-15MB |
| Escalabilidade | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ |

PHP é mais rápido para requisições simples, mas Flask escala melhor em aplicações complexas.

---

**Dúvidas?** Consulte os exemplos em `src/Controllers/`
