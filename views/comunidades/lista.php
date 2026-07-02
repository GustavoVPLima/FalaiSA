<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Minhas Comunidades</h1>
    <a href="/criarcomunidade" class="btn btn-primary">+ Criar Comunidade</a>
</div>

<?php if (empty($communities)): ?>
    <div class="empty-state">
        <p>Você não é membro de nenhuma comunidade ainda.</p>
        <a href="/criarcomunidade" class="btn btn-primary">Criar Comunidade</a>
    </div>
<?php else: ?>
    <div class="communities-list">
        <?php foreach ($communities as $community): ?>
            <div class="community-item">
                <div class="community-image">
                    <?php
                        $foto = !empty($community['img_perfil'])
                            ? '/static/uploads/comunidades/' . $community['img_perfil']
                            : '/static/uploads/comunidades/comunidade_placeholder.png';
                    ?>
                    <img src="<?php echo $foto; ?>" 
                         alt="<?php echo $community['nm_comunidade']; ?>">
                </div>
                    <div class="community-details">
                        <h3><?php echo $community['nm_comunidade']; ?></h3>
                        <p><?php echo $community['ds_comunidade']; ?></p>
                        <p class="community-membros"><?php echo (int) ($community['total_membros'] ?? 0); ?> membros</p>
                        <div class="community-actions">
                        <?php
                            $isMember = ComunidadeDAO::isMember($_SESSION['id'], $community['id_comunidade']);
                        ?>

                        <?php if ($isMember): ?>
                        <!-- Botões para quem JÁ É membro -->
                            <a href="/chat/<?php echo $community['id_comunidade']; ?>" class="btn btn-small">Conversar</a>
                            <a href="/comunidade/<?php echo $community['id_comunidade']; ?>" class="btn btn-small">Detalhes</a>
                            <a href="/comunidade/<?php echo $community['id_comunidade']; ?>" class="btn btn-primary">Acessar Comunidade</a>
                        <?php else: ?>
                        <!-- Botão para quem NÃO É membro -->
                            <a href="/comunidade/<?php echo $community['id_comunidade']; ?>/entrar" class="btn btn-success">Me Juntar à Comunidade</a>
                        <?php endif; ?>
                    </div>     
                </div>
            </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../rodape.php'; ?>