<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1><?php echo $community['nm_comunidade']; ?></h1>
</div>

<div class="community-details-container">
    <div class="community-header">
        <img src="/static/uploads/comunidades/<?php echo $community['img_perfil']; ?>" 
             alt="" class="community-banner">
    </div>

    <div class="community-info-section">
        <h2>Sobre</h2>
        <p><?php echo $community['ds_comunidade']; ?></p>

        <h3>Criador</h3>
        <p><?php echo $community['nome_criador']; ?></p>

        <h3>Membros (<?php echo $community['total_membros']; ?>)</h3>
        <div class="members-list">
            <?php foreach ($members as $member): ?>
                <div class="member-item">
                    <img src="/static/uploads/usuarios/<?php echo $member['img_perfil']; ?>" 
                         alt="<?php echo $member['nm_login']; ?>">
                    <span><?php echo $member['nm_login']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="community-actions">
            <a href="/chat/<?php echo $community['id_comunidade']; ?>" class="btn btn-primary">Conversar</a>
            <?php if ($community['criado_por'] == $_SESSION['id']): ?>
                <a href="/comunidade/<?php echo $community['id_comunidade']; ?>/editar" class="btn btn-secondary">Editar</a>
                <form method="POST" action="/comunidade/<?php echo $community['id_comunidade']; ?>/deletar" style="display:inline;">
                    <input type="hidden" name="_method" value="POST">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja deletar?')">Deletar</button>
                </form>

            <?php else: ?>
                <a href="/comunidade/<?php echo $community['id_comunidade']; ?>/sair" class="btn btn-secondary">Sair</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
