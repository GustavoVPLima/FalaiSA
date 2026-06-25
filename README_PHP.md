# Falaí - Plataforma de Comunidades

Uma plataforma PHP para criar e gerenciar comunidades com chat em tempo real.

## 📁 Estrutura do Projeto

```
raiz/
├── config/
│   ├── database.php        # Configurações de banco de dados
│   └── app.php             # Configurações gerais da aplicação
├── models/
│   ├── UsuarioDAO.php      # Queries de usuários
│   ├── ComunidadeDAO.php   # Queries de comunidades
│   └── MensagemDAO.php     # Queries de mensagens
├── controllers/
│   ├── AuthController.php      # Autenticação
│   ├── HomeController.php      # Home e perfil
│   ├── CommunityController.php # Gerenciamento de comunidades
│   ├── ChatController.php      # Chat das comunidades
│   └── AdminController.php     # Painel admin
├── helpers/
│   ├── Database.php        # Conexão com BD
│   ├── Auth.php            # Autenticação e sessão
│   ├── Request.php         # Tratamento de requisições
│   ├── View.php            # Renderização de views
│   └── File.php            # Manipulação de arquivos
├── views/
│   ├── cabecalho.php       # Header comum
│   ├── rodape.php          # Footer comum
│   ├── login.php           # Login
│   ├── index.php           # Home
│   ├── sobre.php           # Sobre
│   ├── comunidades/
│   │   ├── lista.php       # Listagem
│   │   ├── form.php        # Criar comunidade
│   │   ├── chat.php        # Chat
│   │   ├── detalhes.php    # Detalhes
│   │   └── editar.php      # Editar
│   ├── usuarios/
│   │   ├── cadastro.php    # Cadastro
│   │   ├── perfil.php      # Perfil
│   │   └── editar.php      # Editar perfil
│   └── admin/
│       ├── dashboard.php   # Dashboard
│       ├── usuarios.php    # Gerenciar usuários
│       ├── comunidades.php # Gerenciar comunidades
│       └── relatorios.php  # Relatórios
├── src/
│   └── Router.php          # Roteador de URLs
├── static/
│   ├── style.css           # CSS
│   ├── icons/              # Ícones
│   ├── js/                 # JavaScript
│   └── uploads/            # Arquivos enviados
├── uploads/                # Pastas para uploads
├── .htaccess               # Configuração Apache
├── index.php               # Arquivo principal (router)
└── README.md               # Este arquivo
```

## 🚀 Como Instalar

### 1. Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache com mod_rewrite

### 2. Configuração do Banco de Dados

Edite o arquivo `config/database.php` com suas credenciais:

```php
return [
    'host' => 'seu_host',
    'user' => 'seu_usuario',
    'password' => 'sua_senha',
    'database' => 'seu_banco',
    'charset' => 'utf8mb4'
];
```

### 3. Estrutura das Tabelas

Execute as queries SQL para criar as tabelas necessárias:

```sql
-- Tabela de Usuários
CREATE TABLE tb_usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nm_login VARCHAR(100) UNIQUE NOT NULL,
    ds_senha VARCHAR(255) NOT NULL,
    nm_email VARCHAR(100) NOT NULL,
    img_perfil VARCHAR(255),
    dt_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Comunidades
CREATE TABLE tb_comunidade (
    id_comunidade INT PRIMARY KEY AUTO_INCREMENT,
    nm_comunidade VARCHAR(100) NOT NULL,
    ds_comunidade TEXT,
    criado_por INT NOT NULL,
    max_usuario INT,
    img_perfil VARCHAR(255),
    dt_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES tb_usuario(id_usuario)
);

-- Tabela de Relação Usuário-Comunidade
CREATE TABLE tb_usuario_comunidade (
    id_usuario INT NOT NULL,
    id_comunidade INT NOT NULL,
    ultima_visualizacao DATETIME,
    PRIMARY KEY (id_usuario, id_comunidade),
    FOREIGN KEY (id_usuario) REFERENCES tb_usuario(id_usuario),
    FOREIGN KEY (id_comunidade) REFERENCES tb_comunidade(id_comunidade)
);

-- Tabela de Chat/Mensagens
CREATE TABLE tb_chat (
    id_chat INT PRIMARY KEY AUTO_INCREMENT,
    id_chat_comunidade INT NOT NULL,
    id_chat_usuario INT NOT NULL,
    mensagem TEXT,
    tipo VARCHAR(50),
    arquivo_url VARCHAR(255),
    lida BOOLEAN DEFAULT FALSE,
    dt_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_chat_comunidade) REFERENCES tb_comunidade(id_comunidade),
    FOREIGN KEY (id_chat_usuario) REFERENCES tb_usuario(id_usuario)
);

-- Tabela de Admins
CREATE TABLE tb_admin (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    nm_login VARCHAR(100) UNIQUE NOT NULL,
    ds_senha VARCHAR(255) NOT NULL,
    isadmin BOOLEAN DEFAULT TRUE,
    dt_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 4. Permissões de Pastas

```bash
chmod 755 uploads/
chmod 755 uploads/usuarios/
chmod 755 uploads/comunidades/
chmod 755 uploads/chat/
chmod 755 static/uploads/
```

## 📖 Como Usar

### Rotas Principais

#### Autenticação
- `GET /login` - Página de login
- `POST /login` - Fazer login
- `GET /logout` - Fazer logout
- `GET /cadastro` - Página de cadastro
- `POST /cadastro` - Registrar novo usuário

#### Comunidades
- `GET /` - Home com minhas comunidades
- `GET /comunidades` - Listar minhas comunidades
- `GET /criarcomunidade` - Criar comunidade
- `POST /criarcomunidade` - Salvar comunidade
- `GET /comunidade/{id}` - Detalhes da comunidade
- `GET /comunidade/{id}/editar` - Editar comunidade
- `POST /comunidade/{id}/editar` - Salvar edição
- `GET /comunidade/{id}/sair` - Sair da comunidade

#### Chat
- `GET /chat/{id}` - Abrir chat da comunidade
- `POST /chat/{id}/enviar` - Enviar mensagem
- `GET /chat/{id}/novas` - Buscar mensagens novas

#### Perfil
- `GET /perfil` - Ver perfil
- `GET /perfil/editar` - Editar perfil
- `POST /perfil/atualizar` - Salvar alterações

#### Admin
- `GET /admin` - Dashboard
- `GET /admin/usuarios` - Gerenciar usuários
- `GET /admin/comunidades` - Gerenciar comunidades
- `GET /admin/relatorios` - Relatórios

## 🔧 Tecnologias Utilizadas

- **Linguagem**: PHP 7.4+
- **Banco de Dados**: MySQL
- **Servidor Web**: Apache
- **Frontend**: HTML5, CSS3, JavaScript
- **Padrão**: MVC (Model-View-Controller)

## 🎨 Padrão de Código

### Controllers
Os controllers usam a convenção de nomes no formato `ControladorController.php` e contêm métodos que correspondem às ações da aplicação.

### Models/DAOs
Os DAOs contêm os métodos de acesso ao banco de dados, retornando dados estruturados.

### Views
As views são arquivos PHP que renderizam o HTML, recebendo variáveis do controller através da função `View::show()`.

### Helpers
Os helpers são classes utilitárias para autenticação, requisições, views, banco de dados e manipulação de arquivos.

## 🔐 Segurança

- Validação de entrada em todas as requisições
- Proteção contra SQL Injection com prepared statements
- Proteção contra XSS com sanitização de HTML
- Verificação de autenticação em rotas protegidas
- Proteção de uploads com validação de tipos de arquivo

## 📝 Licença

Este projeto é licenciado sob a MIT License.

## 👥 Contribuindo

Para contribuir com melhorias, abra uma pull request com suas alterações.

## 📧 Suporte

Para suporte, entre em contato através de: suporte@falai.com
