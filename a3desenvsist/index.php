<?php
// Framework: Bootstrap 5
// Fonte: https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css
include 'conexao.php';
session_start();

if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true) {
    header("Location: login.php");
    exit;
}

$stmt = $conexao->query("SELECT * FROM tarefas");
$tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tarefas</h2>
    <a href="nova.php" class="btn btn-success">Nova Tarefa</a>
</div>

<div class="table-responsive bg-white p-3 shadow-sm rounded">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Título</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tarefas as $t): ?>
            <tr>
                <td><?php echo $t['titulo']; ?></td>
                <td>
                    <?php if ($t['status'] == 'concluida'): ?>
                        <span class="badge bg-success">Concluída</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pendente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="editar.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                    <a href="concluir.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-success">Concluir</a>
                    <a href="excluir.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>