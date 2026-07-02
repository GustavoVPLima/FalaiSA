<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Gerenciar Comunidades</h1>
</div>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Comunidade</th>
                <th>Criador</th>
                <th>Data Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($communities as $community): ?>
                <tr>
                    <td><?php echo $community['id_comunidade']; ?></td>
                    <td><?php echo $community['nm_comunidade']; ?></td>
                    <td><?php echo $community['nome_criador']; ?></td>
                    <td><?php echo $community['dt_criacao']; ?></td>
                    <td>
                        <form method="POST" action="/admin/comunidade/<?php echo $community['id_comunidade']; ?>/deletar" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Tem certeza?')">Deletar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
