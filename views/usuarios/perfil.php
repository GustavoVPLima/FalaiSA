<?php include __DIR__ . '/../cabecalho.php'; ?>

<?php
$foto = $user['img_perfil'] ?? 'perfilplaceholder.png';

$caminhoFisico = __DIR__ . '/../../static/uploads/usuarios/' . $foto;

if (!file_exists($caminhoFisico)) {
    $foto = 'perfilplaceholder.png';
}
?>

<div class="page-header">
    <h1>Meu Perfil</h1>
</div>

<div class="profile-container">

    <div class="profile-header">

        <img
            src="/static/uploads/usuarios/<?php echo $foto; ?>"
            alt="<?php echo htmlspecialchars($user['nm_login']); ?>"
            class="profile-avatar"
        >

        <div class="profile-info">

            <h2><?php echo htmlspecialchars($user['nm_login']); ?></h2>

            <p><?php echo htmlspecialchars($user['nm_email']); ?></p>

            <?php if (!empty($user['dt_criacao'])): ?>
                <p>
                    <small>
                        Cadastrado em:
                        <?php echo htmlspecialchars($user['dt_criacao']); ?>
                    </small>
                </p>
            <?php endif; ?>

        </div>

    </div>

    <div class="profile-actions">

        <a href="/perfil/editar" class="btn btn-primary">
            Editar Perfil
        </a>

        <a href="/perfil/alterar-senha" class="btn btn-secondary">
            Alterar Senha
        </a>

    </div>

</div>

<?php include __DIR__ . '/../rodape.php'; ?>