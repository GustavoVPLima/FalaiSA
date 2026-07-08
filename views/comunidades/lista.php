<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Comunidades</h1>

    <a href="/criarcomunidade" class="btn btn-primary">+ Criar Comunidade</a>
</div>

<?php if (empty($communities)): ?>
    <div class="empty-state">
        <p>Nenhuma comunidade encontrada.</p>

        <a href="/criarcomunidade" class="btn btn-primary">Criar Comunidade</a>
    </div>
<?php else: ?>
    <div class="communities-list">
        <?php foreach ($communities as $community): ?>
            <?php $communityImage = !empty($community['img_perfil']) ? $community['img_perfil'] : 'comunidade_placeholder.png'; ?>
            <div class="community-item">
                <div class="community-image">
                    <img src="/static/uploads/comunidades/<?php echo $communityImage; ?>" 
                         alt="">
                </div>
                <div class="community-details">
                    <h3><?php echo $community['nm_comunidade']; ?></h3>
                    <p><?php echo substr($community['ds_comunidade'], 0, 100) . '...'; ?></p>
                    <div class="community-actions">
                        <?php
                            $isMember = ComunidadeDAO::isMember($_SESSION['id'], $community['id_comunidade']);
                        ?>

                        <?php if ($isMember): ?>
                        <!-- Botões para quem JÁ É membro -->
                            <a href="/chat/<?php echo $community['id_comunidade']; ?>" class="btn btn-small btn-community btn-community-primary">Conversar</a>
                            <a href="/comunidade/<?php echo $community['id_comunidade']; ?>" class="btn btn-small btn-community btn-community-secondary">Detalhes</a>

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
