# ❓ Perguntas Frequentes - Migração Flask → PHP

## 🤔 Geral

### P: Preciso reescrever tudo?
**R:** Não! Os templates HTML podem ser reutilizados (apenas renomeie `.html` → `.php`). Seu JavaScript também funciona igual.

### P: Qual é mais rápido, Flask ou PHP?
**R:** PHP é mais rápido em requisições simples (~5-20ms vs ~50-100ms do Flask). Porém Flask escala melhor para aplicações muito grandes.

### P: Posso usar ambos os projetos ao mesmo tempo?
**R:** Sim! Execute Flask em uma porta (ex: 5000) e PHP em outra (ex: 3000). Ou coloque em domínios diferentes.

### P: Preciso mudar o banco de dados?
**R:** Não! O esquema MySQL é idêntico. Apenas mude as credenciais em `config/database.php`.

---

## 🔧 Técnico

### P: Como adicionar uma nova rota?
**R:**
```php
// 1. No index.php
$router->post('/api/novo-endpoint', [MeuController::class, 'meuMetodo']);

// 2. Criar o controller
namespace App\Controllers;

class MeuController {
    public function meuMetodo() {
        // seu código
    }
}
```

### P: Como fazer uma query customizada?
**R:**
```php
// No seu Model
public static function minhaQuery($params) {
    $sql = "SELECT * FROM tabela WHERE coluna = ?";
    $result = Database::query($sql, [$params]);
    return Database::fetchAll($result);
}
```

### P: Como fazer upload de arquivo?
**R:**
```php
if (Request::hasFile('meu_arquivo')) {
    $file = Request::file('meu_arquivo');
    
    if (File::isAllowed($file['name'], 'imagem')) {
        $filename = File::save($file, 'comunidades');
        // $filename agora é o nome único do arquivo
    }
}
```

### P: Como obter dados da sessão?
**R:**
```php
// Definir
$_SESSION['usuario_id'] = 123;

// Obter
$id = $_SESSION['usuario_id'] ?? null;

// Usar helpers
Auth::userId();        // ID do usuário
Auth::username();      // Nome de usuário
Auth::isLoggedIn();    // Verificar se logado
Auth::isAdmin();       // Verificar se admin
```

### P: Como fazer uma API JSON?
**R:**
```php
public function getChat($id) {
    Auth::check();
    
    $mensagens = Chat::getMessages($id);
    
    View::json([
        'success' => true,
        'mensagens' => $mensagens
    ]);
}
```

### P: Como redirecionar?
**R:**
```php
// Simples
View::redirect('/minhas-comunidades');

// Com mensagem
$_SESSION['sucesso'] = 'Ação concluída!';
View::redirect('/');
```

### P: Como renderizar um template?
**R:**
```php
// Renderizar
View::show('meuTemplate', [
    'usuario' => $user,
    'comunidades' => $comms
]);

// Template recebe as variáveis assim:
// <?php echo $usuario; ?>
```

---

## 🐛 Problemas Comuns

### P: "404 em todas as rotas"
**R:** Ativar mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Verificar se `.htaccess` existe na raiz do projeto.

### P: "Erro: CORS bloqueado"
**R:** Adicionar ao `.htaccess`:
```apache
Header set Access-Control-Allow-Origin "*"
```

Ou especificar domínio:
```apache
Header set Access-Control-Allow-Origin "https://seu-dominio.com"
```

### P: "Session não funciona"
**R:** `session_start()` deve ser a primeira linha do `index.php`:
```php
<?php
session_start(); // ← AQUI!

date_default_timezone_set('America/Sao_Paulo');
// ... resto do código
```

### P: "Upload não encontra arquivo"
**R:** Verificar se pasta existe:
```bash
mkdir -p static/uploads/{usuarios,comunidades,chat}
chmod 777 static/uploads
```

### P: "Erro de conexão BD"
**R:** Verificar credenciais:
```php
// config/database.php
return [
    'host' => 'tini.click',      // ← verificar
    'user' => 'falai_sa',         // ← verificar
    'password' => 'senha_correta', // ← verificar
    'database' => 'falai_sa',      // ← verificar
];

// Testar conexão
$db = Database::connect();
echo "Conectado!";
```

### P: "Arquivos desaparecem após subir"
**R:** Permissões incorretas:
```bash
chmod 755 /var/www/falai-sa
chmod 777 /var/www/falai-sa/static/uploads
```

---

## 📱 Frontend

### P: Meus JS não funcionam?
**R:** Verificar:
1. Caminhos dos arquivos JS:
   ```html
   <!-- ✅ Correto -->
   <script src="/static/js/dados_loader.js"></script>
   
   <!-- ❌ Errado -->
   <script src="static/js/dados_loader.js"></script>
   ```

2. Endpoints das APIs (devem ser iguais ao Flask):
   ```javascript
   fetch('/chatcomunidade/1/mensagens')
   ```

### P: CSS não carrega?
**R:** Mesmo problema - caminhos absolutos:
```html
<!-- ✅ Correto -->
<link rel="stylesheet" href="/static/style.css">

<!-- ❌ Errado -->
<link rel="stylesheet" href="static/style.css">
```

### P: Imagens não aparecem?
**R:** Verificar pasta:
```
/static/uploads/comunidades/imagem.jpg
/static/uploads/usuarios/avatar.jpg
```

---

## 🔐 Segurança

### P: Como proteger rotas?
**R:**
```php
public function minhaRota() {
    Auth::check();  // Redireciona se não logado
    
    // ou para admin apenas
    Auth::check(true); // true = require admin
}
```

### P: Como validar entrada do usuário?
**R:**
```php
$email = trim(Request::post('email'));

if (empty($email)) {
    $_SESSION['erro'] = 'Email obrigatório';
    View::redirect('/form');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro'] = 'Email inválido';
    View::redirect('/form');
}
```

### P: Como evitar SQL Injection?
**R:** Usar prepared statements (já feito nos Helpers):
```php
// ✅ SEGURO
Database::query("SELECT * FROM users WHERE id = ?", [$id]);

// ❌ INSEGURO - NÃO USE
Database::query("SELECT * FROM users WHERE id = $id");
```

### P: Como proteger contra CSRF?
**R:**
```php
// No controller
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
View::show('form', ['csrf' => $_SESSION['csrf_token']]);

// No template
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Validar
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF inválido');
}
```

---

## 📦 Deployment

### P: Como hospedar em produção?
**R:** Opções (em ordem de facilidade):
1. **Shared Hosting** - Basta fazer upload via FTP
2. **VPS** - Configuração manual, mas controle total
3. **Docker** - Para escalabilidade

Ver `DEPLOYMENT.md` para detalhes.

### P: Como configurar HTTPS?
**R:**
```bash
# Em VPS
sudo apt install certbot
sudo certbot --apache -d seu-dominio.com

# Em shared hosting: via cPanel
```

### P: Como fazer backup?
**R:**
```bash
# Banco de dados
mysqldump -u user -p database_name > backup.sql

# Arquivos
tar -czf backup.tar.gz /var/www/falai-sa
```

### P: Como escalar para muitos usuários?
**R:**
1. Cache de banco (Redis)
2. CDN para arquivos estáticos
3. Load balancer (Nginx)
4. Database replication

---

## 🆘 Quando Contactar Suporte

Se encontrar erros tipo:
- "Class not found: App\\Controllers\\MeuController"
- "Fatal error in database.php line X"
- "Permission denied on uploads folder"

Verifique:
1. Estrutura de pastas (`src/Controllers/`, `config/`, etc)
2. Namespaces corretos
3. Permissões de pastas (chmod 755/777)
4. Credenciais do BD

---

## 📚 Recursos Úteis

- [PHP.net - Manual](https://www.php.net/manual/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [MDN - JavaScript](https://developer.mozilla.org/)
- [StackOverflow - PHP](https://stackoverflow.com/questions/tagged/php)

---

**Não encontrou sua dúvida?** Consulte os exemplos em `src/Controllers/` ou `MIGRATION_GUIDE.md`
