<?php include __DIR__ . '/cabecalho.php'; ?>

<div class="dashboard-header">
    <h1>Bem-vindo, <?php echo $_SESSION['usuario']; ?>!</h1>
    <p>Suas comunidades</p>
</div>

<?php if (empty($communities)): ?>
    <div class="empty-state">
        <p>Você ainda não é membro de nenhuma comunidade.</p>
        <a href="/criarcomunidade" class="btn btn-primary">Criar Comunidade</a>
        <a href="/comunidades" class="btn btn-secondary">Explorar Comunidades</a>

    </div>
<?php else: ?>
    <div class="communities-grid">
        <?php foreach ($communities as $community): ?>
            <div class="community-card">
                <div class="community-image">
                    <img src="/static/uploads/comunidades/<?php echo $community['img_perfil']; ?>" 
                         alt="">
                </div>
                <div class="community-info">
                    <h3><?php echo $community['nm_comunidade']; ?></h3>
                    <p><?php echo substr($community['ds_comunidade'], 0, 100) . '...'; ?></p>
                    <div class="community-footer">
                        <span class="members"><?php echo $community['total_membros'] ?? 0; ?> membros</span>
                        <a href="/comunidade/<?php echo $community['id_comunidade']; ?>" class="btn btn-small btn-community btn-community-secondary">Detalhes</a>
                        <a href="/chat/<?php echo $community['id_comunidade']; ?>" class="btn btn-small btn-community btn-community-primary">Conversar</a>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/rodape.php'; ?>
