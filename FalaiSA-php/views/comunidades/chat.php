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
                <div class="message <?php echo $message['id_chat_usuario'] == $_SESSION['id'] ? 'own' : ''; ?>" data-id="<?php echo $message['id_chat']; ?>">
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
                <input type="text" name="mensagem" id="messageInput" placeholder="Digite uma mensagem..." class="form-control" autocomplete="off">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const communityId = document.querySelector('input[name="community_id"]').value;
    const currentUserId = <?php echo (int) $_SESSION['id']; ?>;
    const messagesContainer = document.getElementById('messagesContainer');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');

    const POLL_INTERVAL = 2500; // ms - ajuste como quiser (2-3s é um bom equilíbrio)
    let pollTimer = null;
    let isTabVisible = true;

    function getLastMessageId() {
        const items = messagesContainer.querySelectorAll('.message[data-id]');
        if (items.length === 0) return 0;
        const last = items[items.length - 1];
        return parseInt(last.getAttribute('data-id'), 10) || 0;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function isScrolledToBottom() {
        return messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < 80;
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function renderMessage(message) {
        const wrapper = document.createElement('div');
        const isOwn = parseInt(message.id_chat_usuario, 10) === currentUserId;
        wrapper.className = 'message' + (isOwn ? ' own' : '');
        wrapper.setAttribute('data-id', message.id_chat);

        wrapper.innerHTML = `
            <img src="/static/uploads/usuarios/${escapeHtml(message.usuario_avatar)}"
                 alt="${escapeHtml(message.usuario_nome)}" class="message-avatar">
            <div class="message-content">
                <strong>${escapeHtml(message.usuario_nome)}</strong>
                <p>${escapeHtml(message.mensagem)}</p>
                <small>${escapeHtml(message.dt_envio)}</small>
            </div>
        `;
        return wrapper;
    }

    function appendMessages(messages) {
        if (!messages || messages.length === 0) return;

        const shouldAutoScroll = isScrolledToBottom();

        messages.forEach(msg => {
            if (messagesContainer.querySelector(`.message[data-id="${msg.id_chat}"]`)) return;
            messagesContainer.appendChild(renderMessage(msg));
        });

        if (shouldAutoScroll) {
            scrollToBottom();
        }
    }

    function fetchNewMessages() {
        const lastId = getLastMessageId();

        fetch('/chat/' + communityId + '/novas?last_id=' + lastId)
            .then(response => response.json())
            .then(data => {
                appendMessages(data.messages);
            })
            .catch(err => console.error('Erro ao buscar novas mensagens:', err));
    }

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(() => {
            if (isTabVisible) fetchNewMessages();
        }, POLL_INTERVAL);
    }

    document.addEventListener('visibilitychange', function () {
        isTabVisible = !document.hidden;
        if (isTabVisible) fetchNewMessages();
    });

    messageForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;

        messageInput.value = '';

        fetch('/chat/' + communityId + '/enviar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'mensagem=' + encodeURIComponent(message)
        })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    fetchNewMessages();
                } else if (data.erro) {
                    alert(data.erro);
                }
            })
            .catch(err => console.error('Erro ao enviar mensagem:', err));
    });

    scrollToBottom();
    startPolling();
})();
</script>

<?php include __DIR__ . '/../rodape.php'; ?>