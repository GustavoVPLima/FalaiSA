<?php
// templates/chat_comunidade.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($comunidade['nm_comunidade']); ?> - FalaiSA</title>
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

    <main class="container chat-container">
        <div class="chat-sidebar">
            <div class="comunidade-info">
                <img src="/static/uploads/comunidades/<?php echo htmlspecialchars($comunidade['img_perfil']); ?>" 
                     alt="<?php echo htmlspecialchars($comunidade['nm_comunidade']); ?>">
                <h3><?php echo htmlspecialchars($comunidade['nm_comunidade']); ?></h3>
                <p><?php echo htmlspecialchars($comunidade['ds_comunidade']); ?></p>
            </div>
            
            <div class="membros">
                <h4>Membros (<?php echo $total_membros; ?>)</h4>
                <ul>
                    <?php foreach ($membros as $membro): ?>
                        <li>
                            <img src="/static/uploads/usuarios/<?php echo htmlspecialchars($membro['img_perfil']); ?>" 
                                 alt="<?php echo htmlspecialchars($membro['nm_login']); ?>" class="avatar">
                            <span><?php echo htmlspecialchars($membro['nm_login']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="chat-main">
            <div id="mensagens" class="mensagens-container">
                <!-- Mensagens serão carregadas via JavaScript -->
            </div>
            
            <form id="form-enviar" class="form-enviar" enctype="multipart/form-data">
                <div class="input-group">
                    <input type="text" id="mensagem" name="mensagem" placeholder="Digite sua mensagem..." autocomplete="off">
                    
                    <input type="file" id="arquivo" name="arquivo" style="display: none;">
                    <button type="button" class="btn-anexar" onclick="document.getElementById('arquivo').click();">
                        📎 Anexar
                    </button>
                    
                    <button type="submit" class="btn-enviar">Enviar</button>
                </div>
            </form>
        </div>
    </main>

    <script src="/static/js/dados_loader.js"></script>
    <script src="/static/js/theme-toggle.js"></script>
    <script>
        const comunidadeId = <?php echo $comunidade['id_comunidade']; ?>;
        const usuarioAtual = "<?php echo htmlspecialchars($usuario); ?>";
        
        // Script para carregar e enviar mensagens (usar seu JS original)
    </script>
</body>
</html>
