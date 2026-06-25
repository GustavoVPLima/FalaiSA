<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Relatórios</h1>
</div>

<div class="reports-container">
    <div class="report-card">
        <h3>Total de Usuários</h3>
        <p class="report-number"><?php echo $total_usuarios; ?></p>
    </div>

    <div class="report-card">
        <h3>Total de Comunidades</h3>
        <p class="report-number"><?php echo $total_comunidades; ?></p>
    </div>

    <div class="report-card">
        <h3>Total de Mensagens</h3>
        <p class="report-number"><?php echo $total_mensagens; ?></p>
    </div>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
