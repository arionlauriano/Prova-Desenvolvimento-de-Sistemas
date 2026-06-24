<?php
include 'conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true) {
    header("Location: login.php");
    exit();
}

$id_usuario_logado = (int)$_SESSION['usuario_id'];
$mensagem_sucesso = "";
$mensagem_erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn_cadastrar_servico'])) {
    $cod_reserv = (int)$_POST['cod_reserv'];
    $cod_serv = (int)$_POST['cod_serv'];

    $sql_valida = "SELECT cod_quart FROM reservas WHERE id = :cod_reserv AND cod_usuario = :cod_usuario";
    $stmt_valida = $conexao->prepare($sql_valida);
    $stmt_valida->bindParam(':cod_reserv', $cod_reserv, PDO::PARAM_INT);
    $stmt_valida->bindParam(':cod_usuario', $id_usuario_logado, PDO::PARAM_INT);
    $stmt_valida->execute();
    $reserva_valida = $stmt_valida->fetch(PDO::FETCH_ASSOC);

    if ($reserva_valida) {
        $cod_quart = $reserva_valida['cod_quart'];
        
        $sql_insere = "INSERT INTO soli_serv (cod_quart, cod_reserv, cod_serv, status) VALUES (:cod_quart, :cod_reserv, :cod_serv, 'Pendente')";
        $stmt_insere = $conexao->prepare($sql_insere);
        $stmt_insere->bindParam(':cod_quart', $cod_quart, PDO::PARAM_INT);
        $stmt_insere->bindParam(':cod_reserv', $cod_reserv, PDO::PARAM_INT);
        $stmt_insere->bindParam(':cod_serv', $cod_serv, PDO::PARAM_INT);
        
        if ($stmt_insere->execute()) {
            $mensagem_sucesso = "Solicitação de serviço registrada com sucesso!";
        } else {
            $mensagem_erro = "Erro ao registrar o serviço. Tente novamente.";
        }
    } else {
        $mensagem_erro = "Reserva inválida ou não pertence ao seu usuário.";
    }
}

$sql_solicitacoes = "SELECT s.id, s.status, r.nome AS nome_hospede, r.id AS ref_reserva, q.num AS num_quarto, ser.nome AS nome_servico, ser.vlr_serv
                     FROM soli_serv s
                     INNER JOIN reservas r ON s.cod_reserv = r.id
                     INNER JOIN quartos q ON s.cod_quart = q.id
                     INNER JOIN servicos ser ON s.cod_serv = ser.id
                     WHERE r.cod_usuario = :cod_usuario
                     ORDER BY s.id DESC";

$stmt = $conexao->prepare($sql_solicitacoes);
$stmt->bindParam(':cod_usuario', $id_usuario_logado, PDO::PARAM_INT);
$stmt->execute();
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_minhas_reservas = "SELECT id, nome FROM reservas WHERE cod_usuario = :cod_usuario ORDER BY id DESC";
$stmt_res = $conexao->prepare($sql_minhas_reservas);
$stmt_res->bindParam(':cod_usuario', $id_usuario_logado, PDO::PARAM_INT);
$stmt_res->execute();
$minhas_reservas = $stmt_res->fetchAll(PDO::FETCH_ASSOC);

$sql_todos_servicos = "SELECT id, nome, vlr_serv FROM servicos ORDER BY nome ASC";
$stmt_ser = $conexao->query($sql_todos_servicos);
$todos_servicos = $stmt_ser->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 text-dark">Meus Serviços Solicitados</h1>
            <p class="text-muted mb-0">Pedidos de serviços em reservas de: <strong><?php echo htmlspecialchars(isset($_SESSION["usuario_nome"]) ? $_SESSION["usuario_nome"] : (isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : 'Funcionário')); ?></strong></p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaSolicitacao">
            Nova Solicitação
        </button>
    </div>

    <hr>

    <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem_sucesso; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensagem_erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem_erro; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-3">ID Pedido</th>
                            <th scope="col">Hóspede / Reserva</th>
                            <th scope="col" class="text-center">Quarto</th>
                            <th scope="col">Serviço Solicitado</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="pe-3 text-end">Valor Serviço</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($solicitacoes) > 0): ?>
                            <?php foreach ($solicitacoes as $sol): ?>
                                <tr>
                                    <td class="fw-bold ps-3 text-secondary">#<?php echo $sol['id']; ?></td>
                                    <td>
                                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($sol['nome_hospede']); ?></span>
                                        <br>
                                        <small class="text-muted">Ref. Reserva #<?php echo $sol['ref_reserva']; ?></small>
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        Nº <?php echo htmlspecialchars($sol['num_quarto']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($sol['nome_servico']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($sol['status'] === 'Feito'): ?>
                                            <span class="badge bg-success">Feito</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-success fs-6">
                                        R$ <?php echo number_format($sol['vlr_serv'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted bg-white">
                                    <p class="mb-0 fs-5">🔍 Nenhuma solicitação encontrada.</p>
                                    <small>Não há pedidos de serviços cadastrados para as suas reservas.</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovaSolicitacao" tabindex="-1" aria-labelledby="modalNovaSolicitacaoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalNovaSolicitacaoLabel">Nova Solicitação de Serviço</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cod_reserv" class="form-label">Selecione a Reserva / Hóspede:</label>
                        <select class="form-select" name="cod_reserv" id="cod_reserv" required>
                            <option value="" disabled selected>Escolha uma das suas reservas...</option>
                            <?php foreach ($minhas_reservas as $reserva): ?>
                                <option value="<?php echo $reserva['id']; ?>">
                                    Reserva #<?php echo $reserva['id']; ?> - <?php echo htmlspecialchars($reserva['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cod_serv" class="form-label">Selecione o Serviço:</label>
                        <select class="form-select" name="cod_serv" id="cod_serv" required>
                            <option value="" disabled selected>Escolha o serviço desejado...</option>
                            <?php foreach ($todos_servicos as $servico): ?>
                                <option value="<?php echo $servico['id']; ?>">
                                    <?php echo htmlspecialchars($servico['nome']); ?> (R$ <?php echo number_format($servico['vlr_serv'], 2, ',', '.'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_cadastrar_servico" class="btn btn-primary">Adicionar Serviço</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>