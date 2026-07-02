<?php
// Verificar se usuário já está logado
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    $redirect = Auth::isAdmin() ? '/admin' : '/';
    header("Location: $redirect");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Falaí</title>
    <link rel="stylesheet" href="/static/style.css">
    <link rel="icon" href="/static/icons/iconefalai.png" type="image/x-icon">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <img src="/static/icons/iconefalai.png" alt="Falaí Logo" class="auth-logo">
                <h2>Bem-vindo de volta ao Falaí</h2>
            </div>

            <?php if (isset($_SESSION['erro'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <div class="form-group">
                    <label for="usuario" class="form-label">Usuário:</label>
                    <input type="text" name="usuario" id="usuario" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">Senha:</label>
                    <input type="password" name="senha" id="senha" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                
                <div class="mt-3 text-center">
                    <a href="/cadastro" class="btn btn-secondary">
                        Não tem uma conta? Cadastre-se
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
