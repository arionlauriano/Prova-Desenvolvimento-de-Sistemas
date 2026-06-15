<?php
include 'conexao.php';


if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true || $_SESSION['usuario_id'] != 1) {
    header("Location: index.php");
    exit();
}

$mensagem = "";
$tipo_mensagem = "";


if (isset($_GET['alternar_status']) && isset($_GET['status_atual'])) {
    $id_soli = $_GET['alternar_status'];
    $status_atual = $_GET['status_atual'];
    

    $novo_status = ($status_atual == 'Pendente') ? 'Feito' : 'Pendente';
    
    $sql = "UPDATE soli_serv SET status = :novo_status WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':novo_status', $novo_status);
    $stmt->bindParam(':id', $id_soli);
    
    if ($stmt->execute()) {

        header("Location: gerencia_solicitacoes.php?sucesso=status");
        exit();
    } else {
        $mensagem = "Erro ao atualizar o status da solicitação.";
        $tipo_mensagem = "danger";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_adicionar'])) {
    $cod_reserv = $_POST['cod_reserv'];
    $cod_serv = $_POST['cod_serv'];
    $status = $_POST['status'];

    if (!empty($cod_reserv) && !empty($cod_serv)) {

        $sql_quarto = "SELECT cod_quart FROM reservas WHERE id = :cod_reserv";
        $stmt_q = $conexao->prepare($sql_quarto);
        $stmt_q->bindParam(':cod_reserv', $cod_reserv);
        $stmt_q->execute();
        $reserva_info = $stmt_q->fetch(PDO::FETCH_ASSOC);

        if ($reserva_info) {
            $cod_quart = $reserva_info['cod_quart'];

            $sql = "INSERT INTO soli_serv (cod_quart, cod_reserv, cod_serv, status) VALUES (:cod_quart, :cod_reserv, :cod_serv, :status)";
            $stmt = $conexao->prepare($sql);
            $stmt->bindParam(':cod_quart', $cod_quart);
            $stmt->bindParam(':cod_reserv', $cod_reserv);
            $stmt->bindParam(':cod_serv', $cod_serv);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $mensagem = "Solicitação de serviço registrada com sucesso!";
                $tipo_mensagem = "success";
            } else {
                $mensagem = "Erro ao registrar solicitação.";
                $tipo_mensagem = "danger";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_deletar_confirmado'])) {
    $id_deletar = $_POST['id_deletar'];
    
    $sql = "DELETE FROM soli_serv WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar);
    
    if ($stmt->execute()) {
        $mensagem = "Solicitação de serviço removida com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao remover a solicitação.";
        $tipo_mensagem = "danger";
    }
}


if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'status') {
    $mensagem = "Status do serviço atualizado com sucesso!";
    $tipo_mensagem = "success";
}

$soli_deletando = null;
if (isset($_GET['solicitar_exclusao'])) {
    $id_deletar_aviso = $_GET['solicitar_exclusao'];

    $sql = "SELECT s.id, r.nome AS nome_hospede, ser.nome AS nome_servico 
            FROM soli_serv s 
            INNER JOIN reservas r ON s.cod_reserv = r.id
            INNER JOIN servicos ser ON s.cod_serv = ser.id
            WHERE s.id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar_aviso);
    $stmt->execute();
    $soli_deletando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$lista_unidades = $conexao->query("SELECT id, nome FROM unidades ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$lista_servicos = $conexao->query("SELECT id, nome, vlr_serv FROM servicos ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);


$sql_combo_reservas = "SELECT r.id, r.nome AS nome_hospede, u.nome AS nome_unidade, q.num AS num_quarto 
                       FROM reservas r
                       INNER JOIN unidades u ON r.cod_unid = u.id
                       INNER JOIN quartos q ON r.cod_quart = q.id
                       ORDER BY r.nome ASC";
$lista_reservas = $conexao->query($sql_combo_reservas)->fetchAll(PDO::FETCH_ASSOC);

$unidade_filtrada = isset($_GET['unidade_filter']) ? $_GET['unidade_filter'] : "";
$status_filtrado = isset($_GET['status_filter']) ? $_GET['status_filter'] : "";


$sql_solicitacoes = "SELECT s.id, s.status, r.nome AS nome_reserva, u.nome AS nome_unidade, q.num AS num_quarto, ser.nome AS nome_servico, ser.vlr_serv 
                     FROM soli_serv s
                     INNER JOIN reservas r ON s.cod_reserv = r.id
                     INNER JOIN unidades u ON r.cod_unid = u.id
                     INNER JOIN quartos q ON s.cod_quart = q.id
                     INNER JOIN servicos ser ON s.cod_serv = ser.id";

$condicoes = [];
$parametros = [];

if (!empty($unidade_filtrada)) {
    $condicoes[] = "r.cod_unid = :unidade";
    $parametros[':unidade'] = $unidade_filtrada;
}

if (!empty($status_filtrado)) {
    $condicoes[] = "s.status = :status";
    $parametros[':status'] = $status_filtrado;
}

if (count($condicoes) > 0) {
    $sql_solicitacoes .= " WHERE " . implode(" AND ", $condicoes);
}

$sql_solicitacoes .= " ORDER BY s.id DESC"; 

$stmt_lista = $conexao->prepare($sql_solicitacoes);
$stmt_lista->execute($parametros);
$solicitacoes = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container mt-5">

    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">← Voltar ao Menu</a>
        <h1 class="h2 text-dark">Painel de Solicitações de Serviços</h1>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($soli_deletando): ?>
        <div class="card border-0 shadow mb-5 text-white bg-danger">
            <div class="card-body p-4 text-center">
                <h3 class="mb-3">⚠️ Cancelar Solicitação</h3>
                <p class="fs-5">Deseja remover o pedido de <strong>"<?php echo htmlspecialchars($soli_deletando['nome_servico']); ?>"</strong> feito por <strong>"<?php echo htmlspecialchars($soli_deletando['nome_hospede']); ?>"</strong>?</p>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="id_deletar" value="<?php echo $soli_deletando['id']; ?>">
                    <a href="gerencia_solicitacoes.php" class="btn btn-light text-dark me-2">Manter Pedido</a>
                    <button type="submit" name="acao_deletar_confirmado" class="btn btn-dark">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$soli_deletando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-primary text-white p-3">
                <h5 class="mb-0">➕ Lançar Pedido de Serviço no Quarto</h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_adicionar" value="1">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-medium">Hóspede / Quarto Ocupado:</label>
                            <select name="cod_reserv" class="form-select" required>
                                <option value="">-- Selecione o Hóspede Titular --</option>
                                <?php foreach ($lista_reservas as $res): ?>
                                    <option value="<?php echo $res['id']; ?>">
                                        👤 <?php echo htmlspecialchars($res['nome_hospede']); ?> [Quarto <?php echo htmlspecialchars($res['num_quarto']); ?> - <?php echo htmlspecialchars($res['nome_unidade']); ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Serviço Solicitado:</label>
                            <select name="cod_serv" class="form-select" required>
                                <option value="">-- Escolha o Serviço --</option>
                                <?php foreach ($lista_servicos as $serv): ?>
                                    <option value="<?php echo $serv['id']; ?>">
                                        🛎️ <?php echo htmlspecialchars($serv['nome']); ?> (R$ <?php echo number_format($serv['vlr_serv'], 2, ',', '.'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Situação de Entrada:</label>
                            <select name="status" class="form-select">
                                <option value="Pendente" selected>⏳ Pendente</option>
                                <option value="Feito">✅ Feito</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <button type="submit" class="btn btn-primary">Registrar Solicitação</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-secondary">Filtrar por Unidade:</label>
                    <select name="unidade_filter" class="form-select">
                        <option value="">✨ Todas as Unidades</option>
                        <?php foreach ($lista_unidades as $un): ?>
                            <option value="<?php echo $un['id']; ?>" <?php echo ($unidade_filtrada == $un['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($un['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Filtrar por Status:</label>
                    <select name="status_filter" class="form-select">
                        <option value="">✨ Todos os Status</option>
                        <option value="Pendente" <?php echo ($status_filtrado == 'Pendente') ? 'selected' : ''; ?>>⏳ Apenas Pendentes</option>
                        <option value="Feito" <?php echo ($status_filtrado == 'Feito') ? 'selected' : ''; ?>>✅ Apenas Feitos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100">🔍 Filtrar Chamados</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary text-white">
                    <tr>
                        <th width="25%">Nome na Reserva</th>
                        <th width="20%">Unidade / Resort</th>
                        <th width="12%">Quarto</th>
                        <th width="20%">Serviço Requisitado</th>
                        <th width="13%">Valor Unitário</th>
                        <th width="10%" class="text-center">Ações / Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($solicitacoes) > 0): ?>
                        <?php foreach ($solicitacoes as $s): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($s['nome_reserva']); ?></strong></td>
                                <td><span class="text-muted"><small>🏢 <?php echo htmlspecialchars($s['nome_unidade']); ?></small></span></td>
                                <td><span class="badge bg-secondary px-2 py-1">🔑 N° <?php echo htmlspecialchars($s['num_quarto']); ?></span></td>
                                <td><span class="text-darkfw-medium">🛎️ <?php echo htmlspecialchars($s['nome_servico']); ?></span></td>
                                <td><span class="text-success fw-bold">R$ <?php echo number_format($s['vlr_serv'], 2, ',', '.'); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($s['status'] == 'Pendente'): ?>
                                            <a href="gerencia_solicitacoes.php?alternar_status=<?php echo $s['id']; ?>&status_atual=Pendente&unidade_filter=<?php echo $unidade_filtrada; ?>&status_filter=<?php echo $status_filtrado; ?>" 
                                               class="btn btn-sm btn-warning text-dark fw-bold" 
                                               title="Mudar status para Feito">
                                                ⏳ Pendente
                                            </a>
                                        <?php else: ?>
                                            <a href="gerencia_solicitacoes.php?alternar_status=<?php echo $s['id']; ?>&status_atual=Feito&unidade_filter=<?php echo $unidade_filtrada; ?>&status_filter=<?php echo $status_filtrado; ?>" 
                                               class="btn btn-sm btn-success fw-bold" 
                                               title="Mudar status para Pendente">
                                                ✅ Feito
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="gerencia_solicitacoes.php?solicitar_exclusao=<?php echo $s['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Excluir Registro de Pedido">
                                            ❌
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma solicitação de serviço em andamento ou encontrada para estes filtros.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>