# GUIA PASSO A PASSO: Migração Flask → PHP Puro

## 📋 Checklist de Migração

### Fase 1: Preparação (1-2 horas)
- [ ] Copiar estrutura `falai-php/` para servidor
- [ ] Criar banco de dados MySQL (igual ao Flask)
- [ ] Configurar credenciais em `config/database.php`
- [ ] Ativar `.htaccess` no Apache (mod_rewrite)
- [ ] Testar conexão com BD

### Fase 2: Templates (2-4 horas)
- [ ] Copiar arquivos HTML originais para `templates/`
- [ ] Renomear `.html` → `.php`
- [ ] Converter sintaxe Jinja2 → PHP
- [ ] Testar cada página

### Fase 3: Controllers (4-6 horas)
- [ ] Migrar rotas de autenticação
- [ ] Migrar rotas de comunidades
- [ ] Migrar rotas de chat
- [ ] Testar cada controller

### Fase 4: Upload & Estática (1-2 horas)
- [ ] Copiar pasta `static/`
- [ ] Criar diretórios de upload
- [ ] Testar upload de arquivos

### Fase 5: Testes (2-3 horas)
- [ ] Testar login
- [ ] Testar criação de comunidades
- [ ] Testar chat e mensagens
- [ ] Testar upload de imagens

---

## 🔀 Exemplos de Conversão Linha por Linha

### 1️⃣ Login (Rota)

#### ANTES (Flask)
```python
@app.route('/login', methods=['POST'])
def login_post():
    usuario = request.form.get('usuario')
    senha = request.form.get('senha')

    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    cursor.execute("SELECT *, 'usuario' as tipo FROM tb_usuario WHERE nm_login=%s AND ds_senha=%s", 
                   (usuario, senha))
    user = cursor.fetchone()

    if not user:
        cursor.execute("SELECT*, 'admin' as tipo FROM tb_admin WHERE nm_login=%s AND ds_senha=%s", 
                       (usuario, senha))
        user = cursor.fetchone()
    
    cursor.close()
    conexao.close()

    if user:
        session['logado'] = True
        session['usuario'] = user['nm_login']
        session['id'] = user['id_usuario']
        return redirect(url_for('index'))
    else:
        flash('Usuário ou senha incorretos.', 'erro')
        return redirect(url_for('login'))
```

#### DEPOIS (PHP)
```php
// AuthController.php
public function login()
{
    if (!Request::isPost()) {
        View::redirect('/login');
    }

    $username = Request::post('usuario');
    $password = Request::post('senha');

    // Tentar usuário normal
    $user = Usuario::findByLoginAndPassword($username, $password);

    // Tentar admin
    if (!$user) {
        $user = Usuario::findAdminByLoginAndPassword($username, $password);
    }

    if ($user) {
        Auth::login($user);
        View::redirect('/');
    } else {
        $_SESSION['erro'] = 'Usuário ou senha incorretos.';
        View::redirect('/login');
    }
}
```

---

### 2️⃣ Criar Comunidade (Com Upload)

#### ANTES (Flask)
```python
@app.route('/criarcomunidade', methods=['GET', 'POST'])
def criar_comunidade():
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    if request.method == 'POST':
        nome_comunidade = request.form.get('nome_comunidade')
        max_usuario = request.form.get('max_usuario') 
        sem_limite = request.form.get('sem_limite')
        desc_comunidade = request.form.get('descricao')
        imagem_perfil = 'perfilplaceholder.png'
        id_criador = session.get('id')

        if 'imagem_comunidade' in request.files:
            file = request.files['imagem_comunidade']
            if file and file.filename != '' and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                import time
                timestamp = str(int(time.time()))
                name, ext = os.path.splitext(filename)
                filename = f"comunidade_{timestamp}{ext}"
                file_path = os.path.join(app.config['UPLOAD_FOLDER_COMUNIDADES'], filename)
                file.save(file_path)
                imagem_perfil = filename

        if sem_limite:
            max_usuario = 0

        if nome_comunidade and max_usuario:
            conexao = conectar()
            cursor = conexao.cursor()

            try:
                if int(max_usuario) > 0 and int(max_usuario) < 2:
                    flash('Minimo de usuários é 2', 'erro')
                    return render_template('criar_comunidade.html')
                
                sql = 'INSERT INTO tb_comunidade (...) VALUES (...)'
                cursor.execute(sql, (...))
                conexao.commit()

                comunidade_id = cursor.lastrowid

                cursor.execute(
                    "INSERT INTO tb_usuario_comunidade (id_usuario, id_comunidade) VALUES (%s,%s)",
                    (id_criador, comunidade_id)
                )
                conexao.commit()

                flash('Comunidade criada com sucesso!', 'sucesso')
                return redirect(url_for('minhas_comunidades'))
            except mysql.connector.Error as e:
                conexao.rollback()
                flash('Erro ao criar comunidade!', 'erro')
            finally:
                cursor.close()
                conexao.close()
        else:
            flash('Preencha todos os campos!', 'erro')
    
    return render_template('criar_comunidade.html')
```

#### DEPOIS (PHP)
```php
// CommunityController.php
public function create()
{
    Auth::check(); // Verifica se está logado, redireciona se não

    if (Request::isGet()) {
        View::show('criar_comunidade');
        return;
    }

    // POST
    $nome = Request::post('nome_comunidade');
    $descricao = Request::post('descricao') ?? 'Comunidade sem descrição';
    $maxUsuarios = Request::post('max_usuario');
    $semLimite = Request::post('sem_limite');

    if (empty($nome) || (empty($maxUsuarios) && empty($semLimite))) {
        $_SESSION['erro'] = 'Preencha todos os campos!';
        View::redirect('/criarcomunidade');
    }

    $maxUsuarios = $semLimite ? 0 : intval($maxUsuarios);

    if ($maxUsuarios > 0 && $maxUsuarios < 2) {
        $_SESSION['erro'] = 'Mínimo de usuários é 2';
        View::redirect('/criarcomunidade');
    }

    // Processar imagem
    $imagemPerfil = 'perfilplaceholder.png';

    if (Request::hasFile('imagem_comunidade')) {
        $file = Request::file('imagem_comunidade');

        if (File::isAllowed($file['name'], 'imagem')) {
            try {
                $imagemPerfil = File::save($file, 'comunidades');
            } catch (\Exception $e) {
                $_SESSION['erro'] = $e->getMessage();
                View::redirect('/criarcomunidade');
            }
        } else {
            $_SESSION['erro'] = 'Tipo de arquivo não permitido!';
            View::redirect('/criarcomunidade');
        }
    }

    try {
        $comunidadeId = Comunidade::create([
            'nm_comunidade' => $nome,
            'criado_por' => Auth::userId(),
            'ds_comunidade' => $descricao,
            'max_usuario' => $maxUsuarios,
            'img_perfil' => $imagemPerfil
        ]);

        Comunidade::addMember(Auth::userId(), $comunidadeId);

        $_SESSION['sucesso'] = 'Comunidade criada com sucesso!';
        View::redirect('/minhas-comunidades');
    } catch (\Exception $e) {
        $_SESSION['erro'] = 'Erro ao criar comunidade';
        View::redirect('/criarcomunidade');
    }
}
```

---

### 3️⃣ Chat (API JSON)

#### ANTES (Flask)
```python
@app.route('/chatcomunidade/<int:id_comunidade>/enviar', methods=['POST'])
def enviar_mensagem_chat(id_comunidade):
    """Enviar mensagem no chat (API)"""
    if not session.get('logado'):
        return jsonify({'success': False, 'error': 'Não autenticado'})
    
    id_usuario = session.get('id')
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário é membro
        cursor.execute("""
            SELECT 1 FROM tb_usuario_comunidade 
            WHERE id_usuario = %s AND id_comunidade = %s
        """, (id_usuario, id_comunidade))
        
        if not cursor.fetchone():
            return jsonify({'success': False, 'error': 'Acesso negado'})
        
        mensagem_texto = request.form.get('mensagem', '').strip()
        arquivo = request.files.get('arquivo')
        
        if not mensagem_texto and not arquivo:
            return jsonify({'success': False, 'error': 'Mensagem ou arquivo necessário'})
        
        tipo = 'texto'
        arquivo_url = None
        
        # Processar upload de arquivo
        if arquivo and arquivo.filename:
            filename = secure_filename(arquivo.filename)
            ext = filename.rsplit('.', 1)[1].lower() if '.' in filename else ''
            
            if ext in app.config['ALLOWED_CHAT_EXTENSIONS']['imagem']:
                tipo = 'imagem'
            elif ext in app.config['ALLOWED_CHAT_EXTENSIONS']['audio']:
                tipo = 'audio'
            else:
                tipo = 'arquivo'
            
            unique_filename = f"{uuid.uuid4().hex}_{filename}"
            filepath = os.path.join(app.config['UPLOAD_FOLDER_CHAT'], unique_filename)
            arquivo.save(filepath)
            arquivo_url = unique_filename
        
        # Inserir mensagem no banco
        cursor.execute("""
            INSERT INTO tb_chat (id_chat_comunidade, id_chat_usuario, mensagem, tipo, arquivo_url)
            VALUES (%s, %s, %s, %s, %s)
        """, (id_comunidade, id_usuario, mensagem_texto, tipo, arquivo_url))
        
        conexao.commit()
        
        return jsonify({
            'success': True,
            'mensagem': {
                'id_chat': cursor.lastrowid,
                'mensagem': mensagem_texto,
                'tipo': tipo,
                'arquivo_url': arquivo_url
            }
        })
        
    except mysql.connector.Error as e:
        conexao.rollback()
        return jsonify({'success': False, 'error': str(e)})
    finally:
        cursor.close()
        conexao.close()
```

#### DEPOIS (PHP)
```php
// ChatController.php
public function sendMessage($id)
{
    Auth::check();

    if (!Request::isPost()) {
        View::json(['success' => false, 'error' => 'Método não permitido']);
    }

    if (!Comunidade::isMember(Auth::userId(), $id)) {
        View::json(['success' => false, 'error' => 'Acesso negado']);
    }

    $mensagem = trim(Request::post('mensagem', ''));
    $arquivo = Request::file('arquivo');

    if (empty($mensagem) && !Request::hasFile('arquivo')) {
        View::json(['success' => false, 'error' => 'Mensagem ou arquivo necessário']);
    }

    try {
        $tipo = 'texto';
        $arquivoUrl = null;

        // Processar arquivo
        if (Request::hasFile('arquivo')) {
            $filename = $arquivo['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (File::isAllowed($filename, 'imagem')) {
                $tipo = 'imagem';
            } elseif (File::isAllowed($filename, 'audio')) {
                $tipo = 'audio';
            } else {
                $tipo = 'arquivo';
            }

            $arquivoUrl = File::save($arquivo, 'chat');
        }

        // Criar mensagem
        $msgId = Chat::create([
            'id_chat_comunidade' => $id,
            'id_chat_usuario' => Auth::userId(),
            'mensagem' => $mensagem,
            'tipo' => $tipo,
            'arquivo_url' => $arquivoUrl
        ]);

        // Buscar mensagem criada para retornar
        $novasMensagens = Chat::getNewMessages($id, $msgId - 1);
        $mensagemEnviada = end($novasMensagens);

        View::json([
            'success' => true,
            'mensagem' => $mensagemEnviada
        ]);
    } catch (\Exception $e) {
        View::json(['success' => false, 'error' => $e->getMessage()]);
    }
}
```

---

## 🔌 Conectar ao JavaScript Existente

Seus arquivos JS (`dados_loader.js`, `theme-toggle.js`) precisam ser atualizados apenas nas URLs:

#### ANTES (Flask)
```javascript
// Buscar mensagens
fetch(`/chatcomunidade/${comunidadeId}/mensagens`)
    .then(r => r.json())
    .then(data => {
        // Carregar mensagens
    });

// Enviar mensagem
fetch(`/chatcomunidade/${comunidadeId}/enviar`, {
    method: 'POST',
    body: formData
})
```

#### DEPOIS (PHP)
```javascript
// URLs IGUAIS - não precisa mudar!
// Os endpoints são os mesmos em PHP
fetch(`/chatcomunidade/${comunidadeId}/mensagens`)
    .then(r => r.json())
    .then(data => {
        // Carregar mensagens
    });

// Enviar mensagem
fetch(`/chatcomunidade/${comunidadeId}/enviar`, {
    method: 'POST',
    body: formData
})
```

✅ **Bom!** Seus arquivos JS não precisam ser alterados!

---

## 🚀 Testes Rápidos

### Test 1: Login
```bash
curl -X POST http://localhost/login \
  -d "usuario=admin&senha=senha123"
```

### Test 2: Chat (obter mensagens)
```bash
curl http://localhost/chatcomunidade/1/mensagens
```

### Test 3: Enviar mensagem
```bash
curl -X POST http://localhost/chatcomunidade/1/enviar \
  -d "mensagem=Olá mundo!"
```

---

## 📚 Tabela de Equivalência: Flask vs PHP

| Funcionalidade | Flask | PHP |
|---|---|---|
| **Inicializar app** | `app = Flask(__name__)` | Session no `index.php` |
| **Rota GET** | `@app.route('/path')` | `$router->get('/path', [Controller::class, 'method'])` |
| **Rota POST** | `@app.route('/path', ['POST'])` | `$router->post('/path', [Controller::class, 'method'])` |
| **Query param** | `request.args.get('key')` | `Request::get('key')` |
| **Form data** | `request.form.get('key')` | `Request::post('key')` |
| **Upload file** | `request.files['file']` | `Request::file('file')` |
| **Session set** | `session['key'] = value` | `$_SESSION['key'] = $value` |
| **Session get** | `session.get('key')` | `$_SESSION['key'] ?? null` |
| **Redirect** | `redirect(url_for('path'))` | `View::redirect('/path')` |
| **Render** | `render_template('file.html', data)` | `View::show('file', $data)` |
| **JSON** | `jsonify({...})` | `View::json([...])` |
| **DB Query** | `cursor.execute(sql, params)` | `Database::query($sql, $params)` |
| **Fetch one** | `cursor.fetchone()` | `Database::fetchAssoc($result)` |
| **Fetch all** | `cursor.fetchall()` | `Database::fetchAll($result)` |

---

## 🐛 Troubleshooting

### "404 em todas as rotas"
✅ **Solução:** Ativar mod_rewrite
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### "Erro ao conectar no BD"
✅ **Solução:** Verificar credenciais em `config/database.php`
```php
// Testar conexão
$db = Database::connect();
echo "Conectado!";
```

### "Session não persiste"
✅ **Solução:** `session_start()` deve estar no topo de `index.php`
```php
session_start(); // ← Primeira linha!
```

### "Upload não funciona"
✅ **Solução:** Criar diretórios
```bash
mkdir -p static/uploads/{usuarios,comunidades,chat}
chmod 755 static/uploads
```

---

## 📖 Próximas Etapas

1. **Copiar `static/`** para `falai-php/static/`
2. **Converter todos os templates** de `.html` → `.php`
3. **Testar cada funcionalidade** individualmente
4. **Configurar HTTPS/SSL** em produção
5. **Adicionar CSRF tokens** para formulários

---

**Dúvidas?** Veja os exemplos em `src/Controllers/` e `templates/`
