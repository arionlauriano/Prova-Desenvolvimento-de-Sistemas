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

$sql_minhas = "SELECT r.*, u.nome AS nome_unidade, q.num AS num_quarto 
               FROM reservas r
               INNER JOIN unidades u ON r.cod_unid = u.id
               INNER JOIN quartos q ON r.cod_quart = q.id
               WHERE r.cod_usuario = :cod_usuario
               ORDER BY r.data_checkin DESC";

$stmt = $conexao->prepare($sql_minhas);
$stmt->bindParam(':cod_usuario', $id_usuario_logado, PDO::PARAM_INT);
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 text-dark">Minhas Reservas</h1>
            <p class="text-muted mb-0">Relação de reservas lançadas por: <strong><?php echo htmlspecialchars($_SESSION["usuario_nome"] ?? $_SESSION["usuario"] ?? 'Funcionário'); ?></strong></p>
        </div>
        <a href="nova_reservacli.php" class="btn btn-primary">Nova Reserva</a>
    </div>

    <hr>

    <?php if (isset($_GET['sucesso_cadastro'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            Reserva registrada com sucesso nos seus lançamentos!
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-3">ID</th>
                            <th scope="col">Hóspede</th>
                            <th scope="col">CPF</th>
                            <th scope="col">Unidade</th>
                            <th scope="col" class="text-center">Quarto</th>
                            <th scope="col" class="text-center">Acomodação</th>
                            <th scope="col">Check-in</th>
                            <th scope="col">Check-out</th>
                            <th scope="col" class="pe-3 text-end">Valor (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reservas) > 0): ?>
                            <?php foreach ($reservas as $res): ?>
                                <tr>
                                    <td class="fw-bold ps-3 text-secondary">#<?php echo $res['id']; ?></td>
                                    <td>
                                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($res['nome']); ?></span>
                                    </td>
                                    <td class="text-muted">
                                        <?php 
                                            if(strlen($res['cpf']) == 11) {
                                                echo substr($res['cpf'], 0, 3) . '.' . substr($res['cpf'], 3, 3) . '.' . substr($res['cpf'], 6, 3) . '-' . substr($res['cpf'], 9);
                                            } else {
                                                echo htmlspecialchars($res['cpf']);
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($res['nome_unidade']); ?></td>
                                    <td class="text-center fw-bold text-primary">Nº <?php echo htmlspecialchars($res['num_quarto']); ?></td>
                                    <td class="text-center small">
                                        <span class="badge bg-light text-dark border">
                                            👥 <?php echo $res['qnt_adultos']; ?> Ad. / 👶 <?php echo $res['qnt_criancas']; ?> Cr.
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            📅 <?php echo date('d/m/Y', strtotime($res['data_checkin'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            📅 <?php echo date('d/m/Y', strtotime($res['data_checkout'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-success fs-6">
                                        R$ <?php echo number_format($res['vlr_reserv'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted bg-white">
                                    <p class="mb-0 fs-5">🔍 Nenhuma reserva encontrada.</p>
                                    <small>Você ainda não realizou o lançamento de nenhuma reserva no sistema.</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>