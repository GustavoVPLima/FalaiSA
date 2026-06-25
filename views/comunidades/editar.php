<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Editar Comunidade</h1>
</div>

<div class="form-container">
    <form method="POST" action="/comunidade/<?php echo $community['id_comunidade']; ?>/editar" enctype="multipart/form-data" class="form">
        <div class="form-group">
            <label for="nome" class="form-label">Nome da Comunidade:</label>
            <input type="text" name="nome" id="nome" class="form-control" value="<?php echo $community['nm_comunidade']; ?>" required>
        </div>

        <div class="form-group">
            <label for="descricao" class="form-label">Descrição:</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="5" required><?php echo $community['ds_comunidade']; ?></textarea>
        </div>

        <div class="form-group">
            <label for="max_usuarios" class="form-label">Máximo de Usuários:</label>
            <input type="number" name="max_usuarios" id="max_usuarios" class="form-control" value="<?php echo $community['max_usuario']; ?>">
        </div>

        <div class="form-group">
            <label for="imagem" class="form-label">Imagem da Comunidade:</label>
            <input type="file" name="imagem" id="imagem" class="form-control" accept="image/*">
            <small>Imagem atual: <?php echo $community['img_perfil']; ?></small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="/comunidade/<?php echo $community['id_comunidade']; ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
