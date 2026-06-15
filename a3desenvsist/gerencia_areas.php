<?php
include 'conexao.php';

if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true || $_SESSION['usuario_id'] != 1) {
    header("Location: index.php");
    exit();
}

$mensagem = "";
$tipo_mensagem = "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_adicionar'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $cod_unid = $_POST['cod_unid'];

    if (!empty($nome) && !empty($descricao) && !empty($cod_unid)) {
        $sql = "INSERT INTO areas (nome, descricao, cod_unid) VALUES (:nome, :descricao, :cod_unid)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':cod_unid', $cod_unid);
        
        if ($stmt->execute()) {
            $mensagem = "Área comum adicionada com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao adicionar área comum.";
            $tipo_mensagem = "danger";
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_salvar_edicao'])) {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $cod_unid = $_POST['cod_unid'];

    if (!empty($id) && !empty($nome) && !empty($descricao) && !empty($cod_unid)) {
        $sql = "UPDATE areas SET nome = :nome, descricao = :descricao, cod_unid = :cod_unid WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':cod_unid', $cod_unid);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            header("Location: gerencia_areas.php?sucesso=1");
            exit();
        } else {
            $mensagem = "Erro ao atualizar área comum.";
            $tipo_mensagem = "danger";
        }
    }
}

if (isset($_GET['sucesso'])) {
    $mensagem = "Área comum atualizada com sucesso!";
    $tipo_mensagem = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_deletar_confirmado'])) {
    $id_deletar = $_POST['id_deletar'];
    
    $sql = "DELETE FROM areas WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar);
    
    if ($stmt->execute()) {
        $mensagem = "Área comum removida com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao remover área comum.";
        $tipo_mensagem = "danger";
    }
}

$area_editando = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql = "SELECT * FROM areas WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $area_editando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$area_deletando = null;
if (isset($_GET['solicitar_exclusao'])) {
    $id_deletar_aviso = $_GET['solicitar_exclusao'];
    $sql = "SELECT * FROM areas WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar_aviso);
    $stmt->execute();
    $area_deletando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$sql_unidades = "SELECT id, nome FROM unidades ORDER BY nome ASC";
$stmt_unidades = $conexao->query($sql_unidades);
$lista_unidades = $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);

$unidade_filtrada = isset($_GET['unidade_filtro']) ? $_GET['unidade_filtro'] : "";

if (!empty($unidade_filtrada)) {
    $sql_areas = "SELECT a.*, u.nome AS nome_unidade 
                  FROM areas a 
                  INNER JOIN unidades u ON a.cod_unid = u.id 
                  WHERE a.cod_unid = :cod_unid 
                  ORDER BY a.nome ASC";
    $stmt_lista = $conexao->prepare($sql_areas);
    $stmt_lista->bindParam(':cod_unid', $unidade_filtrada);
} else {
    $sql_areas = "SELECT a.*, u.nome AS nome_unidade 
                  FROM areas a 
                  INNER JOIN unidades u ON a.cod_unid = u.id 
                  ORDER BY u.nome ASC, a.nome ASC";
    $stmt_lista = $conexao->prepare($sql_areas);
}
$stmt_lista->execute();
$areas_comuns = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container mt-5">

    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">← Voltar ao Menu</a>
        <h1 class="h2 text-dark">Gerenciamento de Áreas Comuns</h1>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($area_deletando): ?>
        <div class="card border-0 shadow mb-5 text-white bg-danger">
            <div class="card-body p-4 text-center">
                <h3 class="mb-3">⚠️ Confirmar Exclusão</h3>
                <p class="fs-5">Você tem certeza que deseja remover a área comum <strong>"<?php echo htmlspecialchars($area_deletando['nome']); ?>"</strong>?</p>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="id_deletar" value="<?php echo $area_deletando['id']; ?>">
                    <a href="gerencia_areas.php" class="btn btn-light text-dark me-2">Cancelar</a>
                    <button type="submit" name="acao_deletar_confirmado" class="btn btn-dark">Sim, Excluir</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($area_editando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-dark text-white p-3">
                <h5 class="mb-0">✏️ Editar Área Comum #<?php echo $area_editando['id']; ?></h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_salvar_edicao" value="1">
                    <input type="hidden" name="id" value="<?php echo $area_editando['id']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Nome da Área:</label>
                            <input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($area_editando['nome']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Pertence à Unidade:</label>
                            <select name="cod_unid" class="form-select" required>
                                <?php foreach ($lista_unidades as $un): ?>
                                    <option value="<?php echo $un['id']; ?>" <?php echo ($un['id'] == $area_editando['cod_unid']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($un['nome']); ?>
                                    </option>
                                <?php endforeach; ?> </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Descrição Completa:</label>
                            <input type="text" name="descricao" class="form-control" required value="<?php echo htmlspecialchars($area_editando['descricao']); ?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <a href="gerencia_areas.php" class="btn btn-secondary me-2">Cancelar Edição</a>
                    <button type="submit" class="btn btn-warning text-dark fw-medium">Salvar Alterações</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!$area_editando && !$area_deletando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-primary text-white p-3">
                <h5 class="mb-0">🏊 Cadastrar Nova Área Comum</h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_adicionar" value="1">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Nome da Área:</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Quadra de Beach Tennis" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Vincular ao Resort/Hotel:</label>
                            <select name="cod_unid" class="form-select" required>
                                <option value="">-- Selecione a Unidade --</option>
                                <?php foreach ($lista_unidades as $un): ?>
                                    <option value="<?php echo $un['id']; ?>"><?php echo htmlspecialchars($un['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Descrição Curta:</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Próxima aos blocos de chalés" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <button type="submit" class="btn btn-primary">Cadastrar Área</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-9">
                    <label class="form-label small fw-bold text-secondary">Filtrar por Unidade Operacional:</label>
                    <select name="unidade_filtro" class="form-select">
                        <option value="">✨ Mostrar Todas as Unidades</option>
                        <?php foreach ($lista_unidades as $un): ?>
                            <option value="<?php echo $un['id']; ?>" <?php echo ($unidade_filtrada == $un['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($un['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100">🔍 Filtrar Tabela</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary text-white">
                    <tr>
                        <th width="25%">Área Comum</th>
                        <th width="27%">Unidade / Resort</th>
                        <th width="30%">Descrição</th>
                        <th width="10%" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($areas_comuns) > 0): ?>
                        <?php foreach ($areas_comuns as $area): ?>
                            <tr class="<?php echo ($area_editando && $area_editando['id'] == $area['id']) ? 'table-warning' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($area['nome']); ?></strong></td>
                                <td><span class="badge bg-dark bg-opacity-75 p-2">🏢 <?php echo htmlspecialchars($area['nome_unidade']); ?></span></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($area['descricao']); ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="gerencia_areas.php?editar=<?php echo $area['id']; ?>&unidade_filtro=<?php echo $unidade_filtrada; ?>" 
                                           class="btn btn-sm btn-outline-warning text-dark" 
                                           title="Editar Área">
                                            ✏️
                                        </a>
                                        <a href="gerencia_areas.php?solicitar_exclusao=<?php echo $area['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Excluir Área">
                                            ❌
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma área comum cadastrada para esta unidade até o momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>