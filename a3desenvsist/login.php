<?php
include 'conexao.php';

if (isset($_SESSION["logado"]) && $_SESSION["logado"] === true) {
    header("Location: index.php");
    exit();
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $senha = md5($_POST['senha']);
    
    $sql = "SELECT * FROM usuarios WHERE nome = :usuario AND senha = :senha";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':senha', $senha);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['logado'] = true;
        $_SESSION['usuario_id'] = $dados['id'];
        $_SESSION['usuario'] = $dados['nome']; 

        header("Location: index.php");
        exit();
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}

include 'header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="text-center mb-3">Resort Shoreline</h2>
                <hr>
                <h3 class="text-center mb-4">Login</h3>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger p-2 small"><?php echo $erro; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Usuário:</label>
                        <input type="text" name="usuario" class="form-control" required value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha:</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
                
                <br>
                <p>Não possui cadastro? <a href="cadastro.php">Cadastre-se.</a></p>
            </div>
        </div>
    </div>
</div>