<?php include __DIR__ . '/cabecalho.php'; ?>

<div class="dashboard-header">
    <h1>
        Bem-vindo, 
        <span class="text-nowrap-truncate-inline"><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Usuário'); ?></span>
        !
    </h1>
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
                    <?php
                        $fotoComunidade = !empty($community['img_perfil']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/static/uploads/comunidades/' . $community['img_perfil'])
                            ? '/static/uploads/comunidades/' . $community['img_perfil']
                            : '/static/uploads/comunidades/comunidade_placeholder.png';
                    ?>
                    <img src="<?php echo $fotoComunidade; ?>" 
                         alt="<?php echo htmlspecialchars($community['nm_comunidade']); ?>">
                </div>
                <div class="community-info">
                    <h3 class="text-nowrap-truncate"><?php echo htmlspecialchars($community['nm_comunidade']); ?></h3>
                    <p class="text-nowrap-truncate"><?php echo htmlspecialchars(substr($community['ds_comunidade'] ?? '', 0, 100) . '...'); ?></p>
                    <div class="community-footer">
                        <span class="members"><?php echo $community['total_membros'] ?? 0; ?> membros</span>
                        <a href="/chat/<?php echo $community['id_comunidade']; ?>" class="btn btn-small">Conversar</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/rodape.php'; ?>