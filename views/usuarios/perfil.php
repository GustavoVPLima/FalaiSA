<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Meu Perfil</h1>
</div>

<div class="profile-container">
    <div class="profile-header">
        <img src="/static/uploads/usuarios/<?php echo $user['img_perfil']; ?>" 
             alt="<?php echo $user['nm_login']; ?>" class="profile-avatar">
        <div class="profile-info">
            <h2><?php echo $user['nm_login']; ?></h2>
            <p><?php echo $user['nm_email']; ?></p>
            <p><small><?php echo $user['dt_criacao']; ?></small></p>
        </div>
    </div>

    <div class="profile-actions">
        <a href="/perfil/editar" class="btn btn-primary">Editar Perfil</a>
        <a href="/perfil/alterar-senha" class="btn btn-secondary">Alterar Senha</a>
    </div>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
