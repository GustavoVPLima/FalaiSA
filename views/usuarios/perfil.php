<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Meu Perfil</h1>
</div>

<div class="profile-container">
    <div class="profile-header">
        <?php 
        // Define a imagem de perfil (com fallback para placeholder)
        $imgPerfil = !empty($user['img_perfil']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/static/uploads/usuarios/' . $user['img_perfil']) 
            ? '/static/uploads/usuarios/' . $user['img_perfil'] 
            : '/static/images/default-avatar.png';
        ?>
        
        <img src="<?php echo $imgPerfil; ?>" 
             alt="Avatar de <?php echo htmlspecialchars($user['nm_login']); ?>" 
             class="profile-avatar">
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['nm_login']); ?></h2>
            <p><?php echo htmlspecialchars($user['nm_email']); ?></p>
            <p>
                <small>
                    <?php 
                    // Exibe a data de cadastro com fallback seguro
                    if (isset($user['dt_cadastro']) && !empty($user['dt_cadastro'])) {
                        $data = new DateTime($user['dt_cadastro']);
                        echo 'Membro desde: ' . $data->format('d/m/Y');
                    } else {
                        echo 'Data não disponível';
                    }
                    ?>
                </small>
            </p>
        </div>
    </div>

    <div class="profile-actions">
        <a href="/perfil/editar" class="btn btn-primary">Editar Perfil</a>
        <a href="/perfil/alterar-senha" class="btn btn-secondary">Alterar Senha</a>
    </div>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>