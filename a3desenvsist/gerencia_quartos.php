<?php
include 'conexao.php';

// Trava de segurança: apenas o admin (ID 1) pode acessar
if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true || $_SESSION['usuario_id'] != 1) {
    header("Location: index.php");
    exit();
}

$mensagem = "";
$tipo_mensagem = "";

// -------------------------------------------------------------------------
// 1. AÇÃO: ADICIONAR QUARTO
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_adicionar'])) {
    $num = trim($_POST['num']);
    $cod_ala = $_POST['cod_ala'];

    if (!empty($num) && !empty($cod_ala)) {
        $sql = "INSERT INTO quartos (num, cod_ala) VALUES (:num, :cod_ala)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':num', $num);
        $stmt->bindParam(':cod_ala', $cod_ala);
        
        if ($stmt->execute()) {
            $mensagem = "Quarto adicionado com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao adicionar quarto.";
            $tipo_mensagem = "danger";
        }
    }
}

// -------------------------------------------------------------------------
// 2. AÇÃO: SALVAR ALTERAÇÕES DA EDIÇÃO
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_salvar_edicao'])) {
    $id = $_POST['id'];
    $num = trim($_POST['num']);
    $cod_ala = $_POST['cod_ala'];

    if (!empty($id) && !empty($num) && !empty($cod_ala)) {
        $sql = "UPDATE quartos SET num = :num, cod_ala = :cod_ala WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':num', $num);
        $stmt->bindParam(':cod_ala', $cod_ala);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            header("Location: gerencia_quartos.php?sucesso=1");
            exit();
        } else {
            $mensagem = "Erro ao atualizar quarto.";
            $tipo_mensagem = "danger";
        }
    }
}

if (isset($_GET['sucesso'])) {
    $mensagem = "Quarto atualizado com sucesso!";
    $tipo_mensagem = "success";
}

// -------------------------------------------------------------------------
// 3. AÇÃO: CONFIRMAR E DELETAR DEFINITIVAMENTE
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_deletar_confirmado'])) {
    $id_deletar = $_POST['id_deletar'];
    
    $sql = "DELETE FROM quartos WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar);
    
    if ($stmt->execute()) {
        $mensagem = "Quarto removido com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao remover quarto.";
        $tipo_mensagem = "danger";
    }
}

// -------------------------------------------------------------------------
// 4. CARREGAR DADOS DE EDIÇÃO OU DELEÇÃO (Se houver ID na URL)
// -------------------------------------------------------------------------
$quarto_editando = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql = "SELECT * FROM quartos WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $quarto_editando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$quarto_deletando = null;
if (isset($_GET['solicitar_exclusao'])) {
    $id_deletar_aviso = $_GET['solicitar_exclusao'];
    $sql = "SELECT * FROM quartos WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar_aviso);
    $stmt->execute();
    $quarto_deletando = $stmt->fetch(PDO::FETCH_ASSOC);
}

// -------------------------------------------------------------------------
// 5. CARREGAR LISTAS COMPLEMENTARES (Para os Selects de Filtro e Cadastro)
// -------------------------------------------------------------------------
// Unidades
$lista_unidades = $conexao->query("SELECT id, nome FROM unidades ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Alas (Traz o nome do resort junto para organizar o select de cadastro)
$sql_todas_alas = "SELECT al.id, al.nome, al.vlr_diaria, u.nome AS nome_unidade 
                   FROM alas al 
                   INNER JOIN unidades u ON al.cod_unid = u.id 
                   ORDER BY u.nome ASC, al.nome ASC";
$lista_alas = $conexao->query($sql_todas_alas)->fetchAll(PDO::FETCH_ASSOC);


// -------------------------------------------------------------------------
// 6. BUSCA E FILTROS COMBINADOS (Unidade + Ala)
// -------------------------------------------------------------------------
$unidade_filtrada = isset($_GET['unidade_filtro']) ? $_GET['unidade_filtro'] : "";
$ala_filtrada = isset($_GET['ala_filtro']) ? $_GET['ala_filtro'] : "";

// Base da query com os JOINS necessários para exibir tudo na tabela
$sql_quartos = "SELECT q.id, q.num, al.nome AS nome_ala, al.vlr_diaria, u.nome AS nome_unidade 
                FROM quartos q
                INNER JOIN alas al ON q.cod_ala = al.id
                INNER JOIN unidades u ON al.cod_unid = u.id";

$condicoes = [];
$parametros = [];

if (!empty($unidade_filtrada)) {
    $condicoes[] = "al.cod_unid = :unidade";
    $parametros[':unidade'] = $unidade_filtrada;
}

if (!empty($ala_filtrada)) {
    $condicoes[] = "q.cod_ala = :ala";
    $parametros[':ala'] = $ala_filtrada;
}

// Se houver filtros, anexa na query
if (count($condicoes) > 0) {
    $sql_quartos .= " WHERE " . implode(" AND ", $condicoes);
}

$sql_quartos .= " ORDER BY u.nome ASC, al.nome ASC, q.num ASC";

$stmt_lista = $conexao->prepare($sql_quartos);
$stmt_lista->execute($parametros);
$quartos = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container mt-5">

    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">← Voltar ao Menu</a>
        <h1 class="h2 text-dark">Gerenciamento de Quartos</h1>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($quarto_deletando): ?>
        <div class="card border-0 shadow mb-5 text-white bg-danger">
            <div class="card-body p-4 text-center">
                <h3 class="mb-3">⚠️ Confirmar Exclusão</h3>
                <p class="fs-5">Você tem certeza que deseja remover o quarto número <strong>"<?php echo htmlspecialchars($quarto_deletando['num']); ?>"</strong>?</p>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="id_deletar" value="<?php echo $quarto_deletando['id']; ?>">
                    <a href="gerencia_quartos.php" class="btn btn-light text-dark me-2">Cancelar</a>
                    <button type="submit" name="acao_deletar_confirmado" class="btn btn-dark">Sim, Excluir</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($quarto_editando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-dark text-white p-3">
                <h5 class="mb-0">✏️ Editar Quarto #<?php echo $quarto_editando['id']; ?></h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_salvar_edicao" value="1">
                    <input type="hidden" name="id" value="<?php echo $quarto_editando['id']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Número do Quarto:</label>
                            <input type="text" name="num" class="form-control" required value="<?php echo htmlspecialchars($quarto_editando['num']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Selecione a Nova Ala/Resort:</label>
                            <select name="cod_ala" class="form-select" required>
                                <?php foreach ($lista_alas as $ala): ?>
                                    <option value="<?php echo $ala['id']; ?>" <?php echo ($ala['id'] == $quarto_editando['cod_ala']) ? 'selected' : ''; ?>>
                                        🏢 <?php echo htmlspecialchars($ala['nome_unidade']); ?> — 🌿 Ala: <?php echo htmlspecialchars($ala['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <a href="gerencia_quartos.php" class="btn btn-secondary me-2">Cancelar Edição</a>
                    <button type="submit" class="btn btn-warning text-dark fw-medium">Salvar Alterações</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!$quarto_editando && !$quarto_deletando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-primary text-white p-3">
                <h5 class="mb-0">🔑 Cadastrar Novo Quarto</h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_adicionar" value="1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Identificação / Número do Quarto:</label>
                            <input type="text" name="num" class="form-control" placeholder="Ex: 101, B2, Luxo-05" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Vincular à Ala Ocupacional:</label>
                            <select name="cod_ala" class="form-select" required>
                                <option value="">-- Selecione a Ala correspondente --</option>
                                <?php foreach ($lista_alas as $ala): ?>
                                    <option value="<?php echo $ala['id']; ?>">
                                        🏢 <?php echo htmlspecialchars($ala['nome_unidade']); ?> — 🌿 Ala: <?php echo htmlspecialchars($ala['nome']); ?> (R$ <?php echo number_format($ala['vlr_diaria'], 2, ',', '.'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <button type="submit" class="btn btn-primary">Cadastrar Quarto</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Filtrar por Unidade:</label>
                    <select name="unidade_filtro" class="form-select">
                        <option value="">✨ Todas as Unidades</option>
                        <?php foreach ($lista_unidades as $un): ?>
                            <option value="<?php echo $un['id']; ?>" <?php echo ($unidade_filtrada == $un['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($un['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-secondary">Filtrar por Ala específica:</label>
                    <select name="ala_filtro" class="form-select">
                        <option value="">✨ Todas as Alas</option>
                        <?php foreach ($lista_alas as $ala): ?>
                            <option value="<?php echo $ala['id']; ?>" <?php echo ($ala_filtrada == $ala['id']) ? 'selected' : ''; ?>>
                                [<?php echo htmlspecialchars($ala['nome_unidade']); ?>] — <?php echo htmlspecialchars($ala['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100">🔍 Filtrar Combinado</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary text-white">
                    <tr>
                        <th width="20%">Quarto (Nº)</th>
                        <th width="25%">Ala Cadastrada</th>
                        <th width="25%">Unidade / Resort</th>
                        <th width="12%">Valor Diária</th>
                        <th width="8%" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($quartos) > 0): ?>
                        <?php foreach ($quartos as $q): ?>
                            <tr class="<?php echo ($quarto_editando && $quarto_editando['id'] == $q['id']) ? 'table-warning' : ''; ?>">
                                <td><span class="badge bg-primary p-2 fs-6">🔑 <?php echo htmlspecialchars($q['num']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($q['nome_ala']); ?></strong></td>
                                <td><span class="text-muted">🏢 <?php echo htmlspecialchars($q['nome_unidade']); ?></span></td>
                                <td><span class="text-success fw-bold">R$ <?php echo number_format($q['vlr_diaria'], 2, ',', '.'); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="gerencia_quartos.php?editar=<?php echo $q['id']; ?>&unidade_filtro=<?php echo $unidade_filtrada; ?>&ala_filtro=<?php echo $ala_filtrada; ?>" 
                                           class="btn btn-sm btn-outline-warning text-dark" 
                                           title="Editar Quarto">
                                            ✏️
                                        </a>
                                        <a href="gerencia_quartos.php?solicitar_exclusao=<?php echo $q['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Excluir Quarto">
                                            ❌
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum quarto encontrado para a combinação de filtros selecionada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>