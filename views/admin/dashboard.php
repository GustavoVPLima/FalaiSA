<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Painel Administrativo</h1>
</div>

<div class="admin-dashboard">
    <div class="admin-stats">
        <div class="stat-card">
            <h3>Usuários</h3>
            <p class="stat-number">--</p>
            <a href="/admin/usuarios" class="btn btn-small">Ver Usuários</a>
        </div>

        <div class="stat-card">
            <h3>Comunidades</h3>
            <p class="stat-number">--</p>
            <a href="/admin/comunidades" class="btn btn-small">Ver Comunidades</a>
        </div>

        <div class="stat-card">
            <h3>Mensagens</h3>
            <p class="stat-number">--</p>
            <a href="/admin/relatorios" class="btn btn-small">Relatórios</a>
        </div>
    </div>

    <div class="admin-menu">
        <h2>Gerenciamento</h2>
        <ul>
            <li><a href="/admin/usuarios">Gerenciar Usuários</a></li>
            <li><a href="/admin/comunidades">Gerenciar Comunidades</a></li>
            <li><a href="/admin/relatorios">Relatórios</a></li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
