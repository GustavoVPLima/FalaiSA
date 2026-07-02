<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Editar Perfil</h1>
</div>

<div class="form-container">
    <form method="POST" action="/perfil/atualizar" enctype="multipart/form-data" class="form">
        <div class="form-group">
            <label for="email" class="form-label">Email:</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo $user['nm_email']; ?>" required>
        </div>

        <div class="form-group">
            <label for="foto_perfil" class="form-label">Foto de Perfil:</label>
            <input type="file" name="foto_perfil" id="foto_perfil" class="form-control" accept="image/*">
            <small>Foto atual: <?php echo $user['img_perfil']; ?></small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="/perfil" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
