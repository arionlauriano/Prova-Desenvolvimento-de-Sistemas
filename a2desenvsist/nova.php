<?php
include 'conexao.php';
session_start();
if (!isset($_SESSION["logado"])) { header("Location: login.php"); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];

    $sql = "INSERT INTO tarefas (titulo, descricao, status) VALUES (?, ?, 'pendente')";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$titulo, $descricao]);
    header("Location: index.php");
    exit;
}
include 'header.php';
?>

<div class="card mx-auto shadow-sm" style="max-width: 500px;">
    <div class="card-body">
        <h4>Nova Tarefa</h4>
        <form method="POST">
            <div class="mb-3">
                <label>Título</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Descrição</label>
                <textarea name="descricao" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Cadastrar</button>
            <a href="index.php" class="btn btn-light border">Voltar</a>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>