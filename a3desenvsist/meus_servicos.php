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

include 'header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 text-dark">Meus Serviços Solicitados</h1>
            <p class="text-muted mb-0">Pedidos de serviços em reservas de: <strong><?php echo htmlspecialchars(isset($_SESSION["usuario_nome"]) ? $_SESSION["usuario_nome"] : (isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : 'Funcionário')); ?></strong></p>
        </div>
        <a href="nova_solicitacao.php" class="btn btn-primary">Nova Solicitação</a>
    </div>

    <hr>

    <?php if (isset($_GET['sucesso_solicitacao'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            Solicitação de serviço registrada com sucesso!
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