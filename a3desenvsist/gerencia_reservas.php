<?php
include 'conexao.php';

if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true || $_SESSION['usuario_id'] != 1) {
    header("Location: index.php");
    exit();
}

$meses_pt = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

$mes_anterior_num = (int)date('m', strtotime('-1 month'));
$ano_anterior_num = (int)date('Y', strtotime('-1 month'));
$nome_mes_anterior = $meses_pt[$mes_anterior_num];

if (isset($_GET['gerar_pdf'])) {
    
    if (file_exists('fpdf.php')) {
        require('fpdf.php');
    } else {

        die("Erro: O arquivo 'fpdf.php' não foi encontrado na raiz do projeto. Baixe a biblioteca FPDF para ativar relatórios.");
    }


    $sql_bruto_res = "SELECT id, vlr_reserv FROM reservas WHERE MONTH(data_checkin) = :mes AND YEAR(data_checkin) = :ano";
    $stmt_bruto = $conexao->prepare($sql_bruto_res);
    $stmt_bruto->execute([':mes' => $mes_anterior_num, ':ano' => $ano_anterior_num]);
    $reservas_mes = $stmt_bruto->fetchAll(PDO::FETCH_ASSOC);
    
    $total_arrecadado = 0;
    $ids_reservas = [0]; 
    foreach ($reservas_mes as $r_mes) {
        $total_arrecadado += $r_mes['vlr_reserv'];
        $ids_reservas[] = $r_mes['id'];
    }
    

    $lista_ids_string = implode(',', $ids_reservas);
    $sql_bruto_serv = "SELECT SUM(ser.vlr_serv) AS total_servicos 
                       FROM soli_serv s
                       INNER JOIN servicos ser ON s.cod_serv = ser.id
                       WHERE s.cod_reserv IN ($lista_ids_string)";
    $total_servicos = $conexao->query($sql_bruto_serv)->fetch(PDO::FETCH_ASSOC)['total_servicos'] ?? 0;
    
    $total_arrecadado += $total_servicos;

    $sql_graf_unid = "SELECT u.nome, COUNT(r.id) AS qtd 
                      FROM unidades u 
                      LEFT JOIN reservas r ON r.cod_unid = u.id AND MONTH(r.data_checkin) = :mes AND YEAR(r.data_checkin) = :ano
                      GROUP BY u.id";
    $stmt_g1 = $conexao->prepare($sql_graf_unid);
    $stmt_g1->execute([':mes' => $mes_anterior_num, ':ano' => $ano_anterior_num]);
    $dados_unidades = $stmt_g1->fetchAll(PDO::FETCH_ASSOC);

    $sql_graf_serv = "SELECT ser.nome, COUNT(s.id) AS qtd 
                      FROM servicos ser 
                      LEFT JOIN soli_serv s ON s.cod_serv = ser.id
                      GROUP BY ser.id";
    $dados_servicos = $conexao->query($sql_graf_serv)->fetchAll(PDO::FETCH_ASSOC);

    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 14);
            $this->SetTextColor(44, 62, 80);

            global $nome_mes_anterior;
            $this->Cell(0, 10, utf8_decode("Relatório de " . $nome_mes_anterior . " - Resort Shoreline"), 0, 1, 'C');
            $this->Ln(5);
            $this->Line(10, 25, 200, 25);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(5);

    $pdf->SetFillColor(230, 126, 34);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode("  FATURAMENTO TOTAL DO MÊS ANTERIOR"), 0, 1, 'L', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Ln(2);
    $pdf->Cell(0, 8, utf8_decode("Valor arrecadado no mês de " . $nome_mes_anterior . ": R$ " . number_format($total_arrecadado, 2, ',', '.')), 0, 1);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, utf8_decode("(*Cálculo baseado no fechamento bruto de diárias + taxas de serviços adicionais consumidos)"), 0, 1);
    $pdf->Ln(10);

    $pdf->SetTextColor(44, 62, 80);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, utf8_decode("Gráfico 1: Distribuição de Reservas por Unidade"), 0, 1);
    $pdf->Ln(2);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0,0,0);
    foreach ($dados_unidades as $du) {
        $nome_unidade = utf8_decode($du['nome']);
        $qtd = (int)$du['qtd'];
        $largura_barra = max(($qtd * 15), 3);
        
        $pdf->Cell(60, 6, $nome_unidade . " ($qtd)", 0, 0);

        $pdf->SetFillColor(52, 152, 219);
        $pdf->Cell($largura_barra, 5, "", 0, 1, 'L', true);
        $pdf->Ln(1);
    }
    $pdf->Ln(10);

    $pdf->SetTextColor(44, 62, 80);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, utf8_decode("Gráfico 2: Quantidade de Solicitações por Serviço Geral"), 0, 1);
    $pdf->Ln(2);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0,0,0);
    foreach ($dados_servicos as $ds) {
        $nome_serv = utf8_decode($ds['nome']);
        $qtd_s = (int)$ds['qtd'];
        $largura_barra_s = max(($qtd_s * 15), 3);
        
        $pdf->Cell(60, 6, $nome_serv . " ($qtd_s)", 0, 0);
        $pdf->SetFillColor(46, 204, 113); 
        $pdf->Cell($largura_barra_s, 5, "", 0, 1, 'L', true);
        $pdf->Ln(1);
    }

    $pdf->Output('I', "Relatorio_" . $nome_mes_anterior . "_Shoreline.pdf");
    exit();
}

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_deletar_confirmado'])) {
    $id_deletar = $_POST['id_deletar'];
    $conexao->prepare("DELETE FROM soli_serv WHERE cod_reserv = :id")->execute([':id' => $id_deletar]);
    $sql = "DELETE FROM reservas WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar);
    if ($stmt->execute()) {
        $mensagem = "Reserva cancelada e removida com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao remover a reserva.";
        $tipo_mensagem = "danger";
    }
}

$reserva_deletando = null;
if (isset($_GET['solicitar_exclusao'])) {
    $id_deletar_aviso = $_GET['solicitar_exclusao'];
    $sql = "SELECT id, nome, cpf FROM reservas WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar_aviso);
    $stmt->execute();
    $reserva_deletando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$lista_unidades = $conexao->query("SELECT id, nome FROM unidades ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$lista_alas = $conexao->query("SELECT id, nome FROM alas ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$lista_quartos = $conexao->query("SELECT id, num FROM quartos ORDER BY num ASC")->fetchAll(PDO::FETCH_ASSOC);

$f_nome      = isset($_GET['f_nome']) ? trim($_GET['f_nome']) : "";
$f_cpf       = isset($_GET['f_cpf']) ? trim($_GET['f_cpf']) : "";
$f_unidade   = isset($_GET['f_unidade']) ? $_GET['f_unidade'] : "";
$f_ala       = isset($_GET['f_ala']) ? $_GET['f_ala'] : "";
$f_quarto    = isset($_GET['f_quarto']) ? $_GET['f_quarto'] : "";
$f_checkin   = isset($_GET['f_checkin']) ? $_GET['f_checkin'] : "";
$f_checkout  = isset($_GET['f_checkout']) ? $_GET['f_checkout'] : "";

$sql_reservas = "SELECT r.*, u.nome AS nome_unidade, q.num AS num_quarto
                 FROM reservas r
                 INNER JOIN unidades u ON r.cod_unid = u.id
                 INNER JOIN quartos q ON r.cod_quart = q.id
                 INNER JOIN alas al ON q.cod_ala = al.id";

$condicoes = [];
$parametros = [];

if (!empty($f_nome)) { $condicoes[] = "r.nome LIKE :nome"; $parametros[':nome'] = "%" . $f_nome . "%"; }
if (!empty($f_cpf)) { $condicoes[] = "r.cpf LIKE :cpf"; $parametros[':cpf'] = "%" . $f_cpf . "%"; }
if (!empty($f_unidade)) { $condicoes[] = "r.cod_unid = :unidade"; $parametros[':unidade'] = $f_unidade; }
if (!empty($f_ala)) { $condicoes[] = "q.cod_ala = :ala"; $parametros[':ala'] = $f_ala; }
if (!empty($f_quarto)) { $condicoes[] = "r.cod_quart = :quarto"; $parametros[':quarto'] = $f_quarto; }
if (!empty($f_checkin)) { $condicoes[] = "r.data_checkin = :checkin"; $parametros[':checkin'] = $f_checkin; }
if (!empty($f_checkout)) { $condicoes[] = "r.data_checkout = :checkout"; $parametros[':checkout'] = $f_checkout; }

if (count($condicoes) > 0) { $sql_reservas .= " WHERE " . implode(" AND ", $condicoes); }
$sql_reservas .= " ORDER BY r.data_checkin ASC, r.id DESC";

$stmt_lista = $conexao->prepare($sql_reservas);
$stmt_lista->execute($parametros);
$reservas = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container-fluid px-5 mt-5">

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">← Voltar ao Menu</a>
            <h1 class="h2 text-dark">Relação Global de Reservas</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="gerencia_reservas.php?gerar_pdf=1" target="_blank" class="btn btn-danger shadow-sm">
                📊 Exportar PDF (<?php echo $nome_mes_anterior; ?>)
            </a>
            <a href="nova_reserva.php" class="btn btn-primary shadow-sm">🗓️ Cadastrar Nova Reserva</a>
        </div>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($reserva_deletando): ?>
        <div class="card border-0 shadow mb-5 text-white bg-danger">
            <div class="card-body p-4 text-center">
                <h3 class="mb-3">⚠️ Cancelar e Excluir Reserva</h3>
                <p class="fs-5">Tem certeza que deseja apagar permanentemente a reserva do hóspede <strong>"<?php echo htmlspecialchars($reserva_deletando['nome']); ?>"</strong>?</p>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="id_deletar" value="<?php echo $reserva_deletando['id']; ?>">
                    <a href="gerencia_reservas.php" class="btn btn-light text-dark me-2">Voltar</a>
                    <button type="submit" name="acao_deletar_confirmado" class="btn btn-dark">Sim, Remover Reserva</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-header bg-dark text-white py-2">
            <h6 class="mb-0">🔍 Painel de Filtragem Multifuncional</h6>
        </div>
        <div class="card-body p-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Hóspede:</label>
                    <input type="text" name="f_nome" class="form-control form-control-sm" placeholder="Buscar por nome..." value="<?php echo htmlspecialchars($f_nome); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">CPF:</label>
                    <input type="text" name="f_cpf" class="form-control form-control-sm" placeholder="Apenas números..." value="<?php echo htmlspecialchars($f_cpf); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Unidade/Resort:</label>
                    <select name="f_unidade" class="form-select form-select-sm">
                        <option value="">✨ Todas</option>
                        <?php foreach ($lista_unidades as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($f_unidade == $u['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary">Ala:</label>
                    <select name="f_ala" class="form-select form-select-sm">
                        <option value="">✨ Todas</option>
                        <?php foreach ($lista_alas as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo ($f_ala == $a['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold text-secondary">Quarto:</label>
                    <select name="f_quarto" class="form-select form-select-sm">
                        <option value="">✨ Todos</option>
                        <?php foreach ($lista_quartos as $q): ?>
                            <option value="<?php echo $q['id']; ?>" <?php echo ($f_quarto == $q['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($q['num']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold text-secondary">Check-in:</label>
                    <input type="date" name="f_checkin" class="form-control form-control-sm" value="<?php echo $f_checkin; ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold text-secondary">Check-out:</label>
                    <input type="date" name="f_checkout" class="form-control form-control-sm" value="<?php echo $f_checkout; ?>">
                </div>
                <div class="col-md-4 d-flex gap-2 ms-auto">
                    <a href="gerencia_reservas.php" class="btn btn-outline-secondary btn-sm w-50">Resetar Filtros</a>
                    <button type="submit" class="btn btn-secondary btn-sm w-50">🔍 Filtrar Resultados</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-primary text-white">
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th class="text-center">Adultos</th>
                        <th class="text-center">Crianças</th>
                        <th>Unidade</th>
                        <th>Quarto</th>
                        <th>Data Check-in</th>
                        <th>Data Check-out</th>
                        <th>Valor da Reserva</th>
                        <th class="text-center" width="5%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reservas) > 0): ?>
                        <?php foreach ($reservas as $res): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($res['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($res['cpf']); ?></td>
                                <td class="text-center fw-bold text-secondary"><?php echo $res['qnt_adultos']; ?></td>
                                <td class="text-center text-muted"><?php echo !empty($res['qnt_criancas']) ? $res['qnt_criancas'] : '0'; ?></td>
                                <td><span class="badge bg-dark bg-opacity-75 p-2">🏢 <?php echo htmlspecialchars($res['nome_unidade']); ?></span></td>
                                <td><span class="badge bg-secondary p-2">🔑 Quarto <?php echo htmlspecialchars($res['num_quarto']); ?></span></td>
                                <td><span class="text-primary fw-medium">📅 <?php echo date('d/m/Y', strtotime($res['data_checkin'])); ?></span></td>
                                <td><span class="text-danger fw-medium">📅 <?php echo date('d/m/Y', strtotime($res['data_checkout'])); ?></span></td>
                                <td><span class="text-success fw-bold">R$ <?php echo number_format($res['vlr_reserv'], 2, ',', '.'); ?></span></td>
                                <td class="text-center">
                                    <a href="gerencia_reservas.php?solicitar_exclusao=<?php echo $res['id']; ?>" class="btn btn-xs btn-outline-danger" title="Remover / Cancelar Reserva">❌</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Nenhuma reserva localizada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>