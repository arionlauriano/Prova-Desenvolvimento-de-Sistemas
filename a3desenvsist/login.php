<?php
include 'conexao.php';
session_start();

// 1. VERIFICAÇÃO DE REDIRECIONAMENTO (Sempre no topo, antes de qualquer HTML)
if (isset($_SESSION["logado"]) && $_SESSION["logado"] === true) {
    header("Location: index.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = md5($_POST['senha']); 

    $sql = "SELECT * FROM usuario WHERE usuario = :usuario AND senha = :senha";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':senha', $senha);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // CRIA A SESSÃO
        $_SESSION['logado'] = true;
        $_SESSION['usuario_id'] = $dados['id'];
        $_SESSION['usuario'] = $dados['usuario'];

        // 2. REDIRECIONA IMEDIATAMENTE APÓS O LOGIN
        header("Location: index.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}

// SÓ INCLUÍMOS O LAYOUT DAQUI PARA BAIXO
include 'header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="text-center mb-4">Login</h3>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger p-2 small"><?php echo $erro; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Usuário</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>