<?php
// templates/criar_comunidade.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Comunidade - FalaiSA</title>
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
        <h2>Criar Nova Comunidade</h2>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['erro']); ?>
                <?php unset($_SESSION['erro']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="form-criar-comunidade">
            <div class="form-group">
                <label for="nome_comunidade">Nome da Comunidade *</label>
                <input type="text" id="nome_comunidade" name="nome_comunidade" required>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label for="imagem_comunidade">Imagem da Comunidade</label>
                <input type="file" id="imagem_comunidade" name="imagem_comunidade" accept="image/*">
                <small>Formatos aceitos: PNG, JPG, JPEG, GIF, BMP, WEBP (máx 2MB)</small>
            </div>
            
            <div class="form-group">
                <label>Limite de Membros</label>
                
                <div class="radio-group">
                    <label>
                        <input type="radio" name="limite_tipo" value="limitado" checked onchange="toggleMaxUsuario(true)">
                        Limitado
                    </label>
                </div>
                
                <div id="max_usuario_field" class="form-group">
                    <label for="max_usuario">Máximo de Usuários *</label>
                    <input type="number" id="max_usuario" name="max_usuario" min="2" value="10">
                    <small>Mínimo: 2 usuários</small>
                </div>
                
                <div class="radio-group">
                    <label>
                        <input type="radio" name="limite_tipo" value="ilimitado" onchange="toggleMaxUsuario(false)">
                        Sem Limite
                    </label>
                    <input type="hidden" name="sem_limite" value="0" id="sem_limite_hidden">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Criar Comunidade</button>
            <a href="/minhas-comunidades" class="btn btn-secondary">Cancelar</a>
        </form>
    </main>

    <script>
        function toggleMaxUsuario(enable) {
            document.getElementById('max_usuario').disabled = !enable;
            document.getElementById('sem_limite_hidden').value = enable ? '0' : '1';
        }
    </script>
</body>
</html>
