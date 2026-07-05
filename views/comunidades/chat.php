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
                <div class="message-row <?php echo $message['id_chat_usuario'] == $_SESSION['id'] ? 'own-message' : 'other-message'; ?>">
                    <?php if ($message['id_chat_usuario'] != $_SESSION['id']): ?>
                        <img src="/static/uploads/usuarios/<?php echo $message['usuario_avatar']; ?>"
                             alt="<?php echo $message['usuario_nome']; ?>" class="message-avatar">
                    <?php endif; ?>

                    <div class="message-bubble">
                        <div class="message-header">
                            <strong><?php echo $message['id_chat_usuario'] == $_SESSION['id'] ? 'Você' : $message['usuario_nome']; ?></strong>
                            <span class="message-time"><?php echo $message['dt_envio']; ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($message['mensagem']); ?></p>
                    </div>

                    <?php if ($message['id_chat_usuario'] == $_SESSION['id']): ?>
                        <img src="/static/uploads/usuarios/<?php echo $message['usuario_avatar']; ?>"
                             alt="Seu avatar" class="message-avatar">
                    <?php endif; ?>
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
    const form = document.getElementById('messageForm');
    const input = document.getElementById('messageInput');
    const messagesContainer = document.getElementById('messagesContainer');
    const submitButton = form.querySelector('button[type="submit"]');

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    window.addEventListener('load', () => {
        setTimeout(scrollToBottom, 50);
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const message = input.value.trim();
        const communityId = document.querySelector('input[name="community_id"]').value;

        if (!message) {
            return;
        }

        submitButton.disabled = true;

        try {
            const response = await fetch('/chat/' + communityId + '/enviar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'mensagem=' + encodeURIComponent(message)
            });

            const data = await response.json();

            if (data.sucesso) {
                const ownMessage = document.createElement('div');
                ownMessage.className = 'message-row own-message';
                ownMessage.innerHTML = `
                    <div class="message-bubble">
                        <div class="message-header">
                            <strong>Você</strong>
                            <span class="message-time">agora</span>
                        </div>
                        <p>${message}</p>
                    </div>
                    <img src="/static/uploads/usuarios/<?php echo $_SESSION['img_perfil'] ?? 'default.png'; ?>" alt="Seu avatar" class="message-avatar">
                `;

                messagesContainer.appendChild(ownMessage);
                input.value = '';
                requestAnimationFrame(scrollToBottom);
            }
        } catch (error) {
            console.error(error);
        } finally {
            submitButton.disabled = false;
        }
    });
</script>

<?php include __DIR__ . '/../rodape.php'; ?>
