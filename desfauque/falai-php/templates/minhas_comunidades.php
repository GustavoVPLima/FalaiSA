<?php
// app/templates/minhas_comunidades.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Comunidades - FalaiSA</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>FalaiSA</h1>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/minhas-comunidades">Comunidades</a>
                <a href="/logout" class="btn-logout">Sair</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <h2>Minhas Comunidades</h2>
        
        <a href="/criarcomunidade" class="btn btn-primary">+ Nova Comunidade</a>
        
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['sucesso']); ?>
                <?php unset($_SESSION['sucesso']); ?>
            </div>
        <?php endif; ?>
        
        <div class="comunidades-grid">
            <?php if (empty($comunidades)): ?>
                <p>Você não é membro de nenhuma comunidade ainda.</p>
            <?php else: ?>
                <?php foreach ($comunidades as $comunidade): ?>
                    <div class="card-comunidade">
                        <img src="/static/uploads/comunidades/<?php echo htmlspecialchars($comunidade['img_perfil']); ?>" 
                             alt="<?php echo htmlspecialchars($comunidade['nm_comunidade']); ?>">
                        
                        <h3><?php echo htmlspecialchars($comunidade['nm_comunidade']); ?></h3>
                        <p><?php echo htmlspecialchars($comunidade['ds_comunidade']); ?></p>
                        
                        <div class="card-actions">
                            <a href="/chatcomunidade/<?php echo $comunidade['id_comunidade']; ?>" class="btn btn-sm btn-primary">
                                Entrar
                            </a>
                            
                            <?php if ($comunidade['criado_por'] == $_SESSION['id']): ?>
                                <a href="/editar-comunidade/<?php echo $comunidade['id_comunidade']; ?>" class="btn btn-sm btn-secondary">
                                    Editar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
