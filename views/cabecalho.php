<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - Falaí' : 'Falaí'; ?></title>
    <link rel="stylesheet" href="/static/style.css">
    <link rel="icon" href="/static/icons/iconefalai.png" type="image/x-icon">
</head>
<body>
    <div class="app-container">
        <!-- Navbar do Usuário -->
        <nav class="navbar navbar-user">
            <div class="navbar-header">
                <div class="logo">
                    <img src="/static/icons/iconefalai.png" alt="Falaí Logo">
                </div>
                <span>Falaí</span>
            </div>

            <div class="user-profile">
                <a href="/perfil">
                    <?php
                    $fotoPerfil = $_SESSION['foto_perfil'] ?? 'perfilplaceholder.png';
                    if ($fotoPerfil !== 'perfilplaceholder.png') {
                        echo '<img src="/static/uploads/usuarios/' . $fotoPerfil . '" alt="Perfil" class="user-avatar">';
                    } else {
                        echo '<img src="/static/icons/perfilplaceholder.png" alt="Perfil" class="user-avatar">';
                    }
                    ?>
                    <div class="user-name"><?php echo $_SESSION['usuario'] ?? 'Usuário'; ?></div>
                </a>
            </div>

            <div class="nav-menu">
                <ul>
                    <li>
                        <a href="/comunidades">
                            <span class="icon"><img src="/static/icons/comunidadesicon.png"></span>
                            <span>Comunidades</span>
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            <span class="icon"><img src="/static/icons/usuarioicon.png"></span>
                            <span>Minhas comunidades</span>
                        </a>
                    </li>

                    <li>
                        <a href="/criarcomunidade">
                            <span class="icon"><img src="/static/icons/maisicon.png"></span>
                            <span>Criar comunidade</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-footer">
                <a href="/perfil">
                    <span class="icon"><img src="/static/icons/configicon.png"></span>
                    <span>Configurações</span>
                </a>
                <a href="/logout">
                    <span class="icon"><img src="/static/icons/sairicon.png"></span>
                    <span>Sair</span>
                </a>
            </div>
        </nav>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['erro'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?>
                </div>
            <?php endif; ?>
