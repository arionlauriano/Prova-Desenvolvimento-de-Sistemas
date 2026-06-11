<?php
include 'conexao.php';

if (isset($_SESSION["logado"]) && $_SESSION["logado"] === true) {
    header("Location: index.php");
    exit;
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha todos os campos!";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem!";
    } else {
        $sql_busca = "SELECT id FROM usuarios WHERE nome = :usuario";
        $stmt_busca = $conexao->prepare($sql_busca);
        $stmt_busca->bindParam(':usuario', $usuario);
        $stmt_busca->execute();

        if ($stmt_busca->rowCount() > 0) {
            $erro = "Este nome de usuário já está em uso!";
        } else {
            $senha_crypto = md5($senha);
            $sql_insert = "INSERT INTO usuarios (nome, senha) VALUES (:usuario, :senha)";
            $stmt_insert = $conexao->prepare($sql_insert);
            $stmt_insert->bindParam(':usuario', $usuario);
            $stmt_insert->bindParam(':senha', $senha_crypto);

            if ($stmt_insert->execute()) {
                $sucesso = "Cadastro realizado com sucesso! Você já pode fazer login.";
            } else {
                $erro = "Erro ao cadastrar usuário. Tente novamente.";
            }
        }
    }
}

include 'header.php'
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="text-center mb-3">Resort Shoreline</h2>
                <hr>
                <h3 class="text-center mb-4">Cadastro</h3>
                <?php if ($erro): ?>
                    <div class="alert alert-danger p-2 small"><?php echo $erro; ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success p-2 small"><?php echo $sucesso; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Usuário:</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha:</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form label"></label>
                        <input type="password" name="confirmar_senha" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                </form>
                <br>
                <p>Já possui cadastro? <a href="login.php">Faça login.</a></p>
            </div>
        </div>
    </div>
</div>