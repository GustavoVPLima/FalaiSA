<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Chat - <?php echo $community['nm_comunidade']; ?></h1>
</div>

<div class="chat-container">
    <div class="chat-sidebar">
        <h3>Membros (<?php echo count($members); ?>)</h3>
        <div class="members-list">
            <?php foreach ($members as $member): ?>
                <div class="member-item">
                    <img src="/static/uploads/usuarios/<?php echo $member['img_perfil']; ?>" 
                         alt="<?php echo $member['nm_login']; ?>">
                    <span><?php echo $member['nm_login']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="chat-main">
        <div class="messages-container" id="messagesContainer">
            <?php foreach ($messages as $message): ?>
                <div class="message <?php echo $message['id_chat_usuario'] == $_SESSION['id'] ? 'own' : ''; ?>">
                    <img src="/static/uploads/usuarios/<?php echo $message['usuario_avatar']; ?>" 
                         alt="<?php echo $message['usuario_nome']; ?>" class="message-avatar">
                    <div class="message-content">
                        <strong><?php echo $message['usuario_nome']; ?></strong>
                        <p><?php echo htmlspecialchars($message['mensagem']); ?></p>
                        <small><?php echo $message['dt_envio']; ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form id="messageForm" class="message-form">
            <input type="hidden" name="community_id" value="<?php echo $community['id_comunidade']; ?>">
            <div class="input-group">
                <input type="text" name="mensagem" id="messageInput" placeholder="Digite uma mensagem..." class="form-control">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Implementar chat em tempo real aqui
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const message = document.getElementById('messageInput').value;
        const communityId = document.querySelector('input[name="community_id"]').value;
        
        // Enviar mensagem via AJAX
        fetch('/chat/' + communityId + '/enviar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'mensagem=' + encodeURIComponent(message)
        }).then(response => response.json()).then(data => {
            document.getElementById('messageInput').value = '';
            // Recarregar mensagens
            location.reload();
        });
    });
</script>

<?php include __DIR__ . '/../rodape.php'; ?>
