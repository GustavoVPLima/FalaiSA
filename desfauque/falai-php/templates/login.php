<?php
// Verificar se está logado
if (!isset($_SESSION['logado'])) {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FalaiSA</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>
    <div class="container-login">
        <h1>FalaiSA</h1>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['erro']); ?>
                <?php unset($_SESSION['erro']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>
</body>
</html>
