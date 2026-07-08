<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="container mt-4">

    <h2>Alterar Senha</h2>

    <?php if (isset($_SESSION['erro'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['erro']; ?>
        </div>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['sucesso'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['sucesso']; ?>
        </div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <form action="/perfil/alterar-senha" method="POST">

        <div class="mb-3">
            <label class="form-label">
                Senha Atual
            </label>

            <input
                type="password"
                name="senha_atual"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">
                Nova Senha
            </label>

            <input
                type="password"
                name="nova_senha"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">
                Confirmar Nova Senha
            </label>

            <input
                type="password"
                name="confirmar_senha"
                class="form-control"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Alterar Senha
        </button>

        <a href="/perfil" class="btn btn-secondary">
            Cancelar
        </a>

    </form>

</div>

<?php include __DIR__ . '/../rodape.php'; ?>