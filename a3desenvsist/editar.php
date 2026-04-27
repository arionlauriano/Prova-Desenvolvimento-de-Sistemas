<?php
include 'conexao.php';
session_start();

// Trava de segurança
if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true) {
    header("Location: login.php");
    exit;
}

// Busca os dados da tarefa pelo ID enviado via GET
$id = $_GET['id'];
$stmt = $conexao->prepare("SELECT * FROM tarefas WHERE id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a tarefa não existir, volta para a lista
if (!$t) {
    header("Location: index.php");
    exit;
}

// Processa a atualização via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];

    $sql = "UPDATE tarefas SET titulo = ?, descricao = ?, status = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$titulo, $descricao, $status, $id]);

    header("Location: index.php");
    exit;
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Editar Tarefa</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" 
                               value="<?php echo htmlspecialchars($t['titulo']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="4" 
                                  required><?php echo htmlspecialchars($t['descricao']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status da Tarefa</label>
                        <select name="status" class="form-select">
                            <option value="pendente" <?php echo ($t['status'] == 'pendente') ? 'selected' : ''; ?>>
                                🟡 Pendente
                            </option>
                            <option value="concluida" <?php echo ($t['status'] == 'concluida') ? 'selected' : ''; ?>>
                                🟢 Concluída
                            </option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>