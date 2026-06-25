<?php include __DIR__ . '/../cabecalho.php'; ?>

<div class="page-header">
    <h1>Gerenciar Usuários</h1>
</div>

<div class="table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Email</th>
                <th>Data Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id_usuario']; ?></td>
                    <td><?php echo $user['nm_login']; ?></td>
                    <td><?php echo $user['nm_email']; ?></td>
                    <td><?php echo $user['dt_criacao']; ?></td>
                    <td>
                        <form method="POST" action="/admin/usuario/<?php echo $user['id_usuario']; ?>/deletar" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Tem certeza?')">Deletar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../rodape.php'; ?>
