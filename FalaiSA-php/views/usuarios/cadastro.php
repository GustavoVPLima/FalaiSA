<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Falaí</title>
    <link rel="stylesheet" href="/static/style.css">
    
</head>
<body>
  
<div class="auth-container">
    <div class="auth-box">
        <form method="POST" action="/cadastro" class="form">
            <div class="form-group">
                <label for="usuario" class="form-label">Usuário:</label>
                <input type="text" name="usuario" id="usuario" class="form-control" maxlength="40" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" maxlength="45" required>
            </div>
            
            <div class="form-group">
                <label for="numero" class="form-label">Telefone:</label>
                <input type="text" name="numero" id="numero" class="form-control" maxlength="15" required>
            </div>

            <div class="form-group">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" name="senha" id="senha" class="form-control" maxlength="45" required>
            </div>

            <div class="form-group">
                <label for="senha_confirma" class="form-label">Confirmar Senha:</label>
                <input type="password" name="senha_confirma" id="senha_confirma" class="form-control" maxlength="45" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
            </div>
            <div class="form-actions text-center mt-3">
                <a href="/login" class="btn btn-secondary">Já tem uma conta? Faça login</a>
            </div>            
        </form>
    </div>
</div>
</html>
