# 🏗️ Arquitetura e Padrões de Código - Falaí

## Visão Geral

Falaí utiliza uma arquitetura **MVC (Model-View-Controller)** simples e direta, sem dependências externas, permitindo total controle sobre o código.

## Padrão MVC

### Model (Camada de Dados)

Os **DAOs** (Data Access Objects) são responsáveis por todas as operações de banco de dados.

**Localização**: `models/`

**Exemplo**: `UsuarioDAO.php`
```php
class UsuarioDAO
{
    public static function findById($id)
    {
        // Query ao banco de dados
    }
    
    public static function create($data)
    {
        // Insere dados no banco
    }
}
```

**Responsabilidades**:
- Consultas SQL
- Validação de dados do banco
- Retorno de arrays associativos
- Manipulação de relações

### View (Camada de Apresentação)

As **Views** são arquivos PHP que renderizam HTML. Recebem dados dos Controllers.

**Localização**: `views/`

**Exemplo**: `views/index.php`
```php
<?php include __DIR__ . '/cabecalho.php'; ?>

<h1><?php echo $titulo; ?></h1>
<p><?php echo $descricao; ?></p>

<?php include __DIR__ . '/rodape.php'; ?>
```

**Responsabilidades**:
- Renderização de HTML
- Iteração sobre dados
- Exibição de formulários
- Inclusão de templates reutilizáveis

### Controller (Camada de Lógica)

Os **Controllers** orquestram a lógica da aplicação, conectando Models e Views.

**Localização**: `controllers/`

**Exemplo**: `AuthController.php`
```php
class AuthController
{
    public function login()
    {
        $username = Request::post('usuario');
        $password = Request::post('senha');
        
        $user = UsuarioDAO::findByLoginAndPassword($username, $password);
        
        if ($user) {
            Auth::login($user);
            View::redirect('/');
        }
    }
}
```

**Responsabilidades**:
- Validação de entrada
- Chamada de DAOs
- Renderização de views
- Redirecionamentos

## Padrões de Código

### 1. Naming Conventions

#### Classes
- PascalCase: `UsuarioDAO`, `AuthController`, `Database`
- Sufixos descritivos:
  - `DAO` para classes de acesso ao banco
  - `Controller` para controllers
  - Helper classes sem sufixo: `Auth`, `Request`

#### Métodos
- camelCase: `findById()`, `markAsRead()`, `addMember()`
- Prefixos descritivos:
  - `get*`: retorna dados
  - `create*`: cria novo registro
  - `update*`: modifica registro
  - `delete*`: remove registro
  - `is*`: retorna boolean

#### Variáveis
- camelCase: `$userId`, `$communityId`, `$userName`
- Prefixos para tipos:
  - `$total*`: números
  - `$is*`: boolean
  - `$arr*`: arrays

#### Constantes
- UPPER_SNAKE_CASE: `MAX_FILE_SIZE`, `UPLOAD_FOLDER`

### 2. Estrutura de Controllers

```php
class NomeController
{
    // Métodos GET
    public function index()
    {
        // Lista recursos
    }
    
    public function show($id)
    {
        // Mostra um recurso
    }
    
    public function create()
    {
        // Mostra formulário de criação
    }
    
    // Métodos POST
    public function store()
    {
        // Salva novo recurso
    }
    
    public function edit($id)
    {
        // Mostra formulário de edição
    }
    
    public function update($id)
    {
        // Atualiza recurso
    }
    
    public function delete($id)
    {
        // Deleta recurso
    }
}
```

### 3. Fluxo de Requisição

```
1. index.php (Router)
   ↓
2. Router::dispatch() encontra a rota
   ↓
3. Controller::metodo() é chamado
   ↓
4. Controller chama DAO para dados
   ↓
5. DAO retorna dados do banco
   ↓
6. Controller renderiza View com dados
   ↓
7. View exibe HTML
```

### 4. Uso de Helpers

#### Auth Helper
```php
Auth::isLoggedIn();           // boolean
Auth::isAdmin();              // boolean
Auth::userId();               // int|null
Auth::username();             // string|null
Auth::check();                // verifica e redireciona se não logado
Auth::login($user);           // registra sessão
Auth::logout();               // destroi sessão
```

#### Request Helper
```php
Request::post('campo');       // $_POST['campo']
Request::get('campo');        // $_GET['campo']
Request::input('campo');      // $_POST ou $_GET
Request::file('campo');       // $_FILES['campo']
Request::hasFile('campo');    // verifica upload
Request::isPost();            // $_SERVER['REQUEST_METHOD'] === 'POST'
Request::isGet();             // $_SERVER['REQUEST_METHOD'] === 'GET'
```

#### View Helper
```php
View::show('template', $data);      // renderiza e exibe
View::render('template', $data);    // retorna como string
View::redirect('/path');             // redireciona
View::json($data);                   // retorna JSON
```

#### File Helper
```php
File::isAllowed($filename, $type);  // valida tipo
File::save($file, $folder);         // salva upload
File::delete($filename, $folder);   // remove arquivo
```

#### Database Helper
```php
Database::query($sql, $params);     // SELECT
Database::execute($sql, $params);   // INSERT/UPDATE/DELETE
Database::fetchAssoc($result);      // um registro
Database::fetchAll($result);        // todos registros
Database::lastInsertId();           // último ID inserido
```

## Segurança

### 1. Prepared Statements

Sempre use prepared statements para prevenir SQL Injection:

```php
// ✅ Correto
$result = Database::query(
    "SELECT * FROM tb_usuario WHERE nm_login = ? AND ds_senha = ?",
    [$login, $password]
);

// ❌ Errado
$result = Database::query(
    "SELECT * FROM tb_usuario WHERE nm_login = '$login' AND ds_senha = '$password'"
);
```

### 2. Validação de Input

Sempre valide entrada do usuário:

```php
// ✅ Correto
if (empty($username) || empty($password)) {
    $_SESSION['erro'] = 'Preencha todos os campos!';
    View::redirect('/login');
}

// ❌ Errado
// Usar dados direto do Request sem validar
```

### 3. Sanitização de Output

Use `htmlspecialchars()` ao exibir dados do usuário:

```php
// ✅ Correto
<p><?php echo htmlspecialchars($message); ?></p>

// ❌ Errado
<p><?php echo $message; ?></p>
```

### 4. Verificação de Autenticação

Sempre verifique autenticação em rotas protegidas:

```php
// ✅ Correto
Auth::check();  // Redireciona se não logado

// ❌ Errado
if (!Auth::isLoggedIn()) { ... }  // Pode continuar executando
```

## Convenções de Rotas

As rotas seguem um padrão RESTful simples:

```
GET    /recurso              → listar
GET    /recurso/{id}         → detalhes
GET    /criar-recurso        → formulário de criação
POST   /criar-recurso        → salvar novo
GET    /recurso/{id}/editar  → formulário de edição
POST   /recurso/{id}/editar  → salvar edição
POST   /recurso/{id}/deletar → deletar
```

## Estrutura de Pasta para Novos Recursos

Ao adicionar um novo recurso (ex: Comentários):

```
1. Criar DAO: models/ComentarioDAO.php
2. Criar Controller: controllers/ComentarioController.php
3. Criar Views: 
   - views/comentarios/lista.php
   - views/comentarios/form.php
4. Adicionar rotas em index.php
```

## Boas Práticas

### 1. DRY (Don't Repeat Yourself)

Evite duplicar código, crie métodos reutilizáveis:

```php
// ❌ Ruim
public function updateProfile() { ... }
public function updateEmail() { ... }
public function updatePassword() { ... }

// ✅ Bom
public function update($data) { ... }
public function updateField($field, $value) { ... }
```

### 2. Single Responsibility

Cada classe deve ter apenas uma responsabilidade:

```php
// ❌ Ruim
class Usuario {
    public function save() { }
    public function delete() { }
    public function sendEmail() { }
    public function uploadPhoto() { }
}

// ✅ Bom
class UsuarioDAO { /* Database */ }
class Email { /* Emails */ }
class File { /* Upload */ }
```

### 3. Nomes Significativos

Use nomes que descrevam a intenção:

```php
// ❌ Ruim
public function get($x) { }
public function processData($d) { }

// ✅ Bom
public function getUserById($userId) { }
public function validateUserInput($data) { }
```

### 4. Keep It Simple

Evite lógica complexa, quebre em métodos menores:

```php
// ❌ Ruim (método muito complexo)
public function processUser() {
    // 50 linhas de lógica
}

// ✅ Bom (métodos pequenos e focados)
public function validateUser() { }
public function createUserRecord() { }
public function addDefaultPermissions() { }
```

## Testando Localmente

Para testar a aplicação localmente:

```bash
# Usando servidor PHP built-in
php -S localhost:8000

# Ou com Apache
# Configure um virtual host conforme INSTALACAO.md
```

Acesse: `http://localhost:8000`

## Próximas Melhorias

- [ ] Sistema de logs estruturado
- [ ] Tratamento de exceções customizado
- [ ] Cache de queries
- [ ] API REST para mobile apps
- [ ] Sistema de permissões granulares
- [ ] Notificações em tempo real com WebSocket

---

**Última atualização**: 2025
