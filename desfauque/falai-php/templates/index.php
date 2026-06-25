<?php
// templates/index.php - Página inicial
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início - FalaiSA</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>FalaiSA</h1>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/minhas-comunidades">Comunidades</a>
                <a href="/sobre-nos">Sobre</a>
                <a href="/logout" class="btn-logout">Sair</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <section class="hero">
            <h1>Bem-vindo ao FalaiSA!</h1>
            <p>Crie e gerencie comunidades, converse com membros e compartilhe conhecimento</p>
            <a href="/minhas-comunidades" class="btn btn-primary btn-lg">Explore Comunidades</a>
        </section>
    </main>
</body>
</html>
