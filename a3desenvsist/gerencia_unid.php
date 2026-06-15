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
    $loc = trim($_POST['loc']);

    if (!empty($nome) && !empty($loc)) {
        $sql = "INSERT INTO unidades (nome, loc) VALUES (:nome, :loc)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':loc', $loc);
        
        if ($stmt->execute()) {
            $mensagem = "Unidade adicionada com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao adicionar unidade.";
            $tipo_mensagem = "danger";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_salvar_edicao'])) {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $loc = trim($_POST['loc']);

    if (!empty($id) && !empty($nome) && !empty($loc)) {
        $sql = "UPDATE unidades SET nome = :nome, loc = :loc WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':loc', $loc);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $mensagem = "Unidade atualizada com sucesso!";
            $tipo_mensagem = "success";

            header("Location: gerencia_unid.php?sucesso=1");
            exit();
        } else {
            $mensagem = "Erro ao atualizar unidade.";
            $tipo_mensagem = "danger";
        }
    }
}

if (isset($_GET['sucesso'])) {
    $mensagem = "Unidade atualizada com sucesso!";
    $tipo_mensagem = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_deletar_confirmado'])) {
    $id_deletar = $_POST['id_deletar'];
    
    $sql = "DELETE FROM unidades WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar);
    
    if ($stmt->execute()) {
        $mensagem = "Unidade removida com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao remover unidade.";
        $tipo_mensagem = "danger";
    }
}

$unidade_editando = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql = "SELECT * FROM unidades WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $unidade_editando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$unidade_deletando = null;
if (isset($_GET['solicitar_exclusao'])) {
    $id_deletar_aviso = $_GET['solicitar_exclusao'];
    $sql = "SELECT * FROM unidades WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar_aviso);
    $stmt->execute();
    $unidade_deletando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$filtro = isset($_GET['busca']) ? trim($_GET['busca']) : "";
if (!empty($filtro)) {
    $sql_busca = "SELECT * FROM unidades WHERE nome LIKE :filtro ORDER BY nome ASC";
    $stmt_lista = $conexao->prepare($sql_busca);
    $param_filtro = "%$filtro%";
    $stmt_lista->bindParam(':filtro', $param_filtro);
} else {
    $sql_busca = "SELECT * FROM unidades ORDER BY nome ASC";
    $stmt_lista = $conexao->prepare($sql_busca);
}
$stmt_lista->execute();
$unidades = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container mt-5">

    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">← Voltar ao Menu</a>
        <h1 class="h2 text-dark">Gerenciamento de Unidades</h1>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($unidade_deletando): ?>
        <div class="card border-0 shadow mb-5 text-white bg-danger">
            <div class="card-body p-4 text-center">
                <h3 class="mb-3">⚠️ Confirmar Exclusão</h3>
                <p class="fs-5">Você tem certeza absoluta de que deseja remover a unidade <strong>"<?php echo htmlspecialchars($unidade_deletando['nome']); ?>"</strong>?</p>
                <p class="small bg-dark bg-opacity-25 p-2 rounded d-inline-block">Esta ação é irreversível e apagará todas as ALAS, QUARTOS e ÁREAS vinculadas a ela (Regra Cascade).</p>
                
                <form method="POST" class="mt-3">
                    <input type="hidden" name="id_deletar" value="<?php echo $unidade_deletando['id']; ?>">
                    <a href="gerencia_unid.php" class="btn btn-light text-dark me-2">Cancelar</a>
                    <button type="submit" name="acao_deletar_confirmado" class="btn btn-dark">Sim, Excluir Tudo</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($unidade_editando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-dark text-white p-3">
                <h5 class="mb-0">✏️ Editar Informações da Unidade #<?php echo $unidade_editando['id']; ?></h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_salvar_edicao" value="1">
                    <input type="hidden" name="id" value="<?php echo $unidade_editando['id']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nome da Unidade:</label>
                            <input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($unidade_editando['nome']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Localização / Endereço:</label>
                            <input type="text" name="loc" class="form-control" required value="<?php echo htmlspecialchars($unidade_editando['loc']); ?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <a href="gerencia_unid.php" class="btn btn-secondary me-2">Cancelar Edição</a>
                    <button type="submit" class="btn btn-warning text-dark fw-medium">Salvar Alterações</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!$unidade_editando && !$unidade_deletando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-primary text-white p-3">
                <h5 class="mb-0">🏢 Cadastrar Nova Unidade</h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_adicionar" value="1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nome do Resort/Hotel:</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Resort Tropical Sol" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Localização:</label>
                            <input type="text" name="loc" class="form-control" placeholder="Ex: Porto de Galinhas, PE" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <button type="submit" class="btn btn-primary">Cadastrar Unidade</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="busca" class="form-control" placeholder="Digite o nome da unidade para filtrar..." value="<?php echo htmlspecialchars($filtro); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">🔍 Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary text-white">
                    <tr>
                        <th width="40%">Nome da Unidade</th>
                        <th width="35%">Localização</th>
                        <th width="15%" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($unidades) > 0): ?>
                        <?php foreach ($unidades as $un): ?>
                            <tr class="<?php echo ($unidade_editando && $unidade_editando['id'] == $un['id']) ? 'table-warning' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($un['nome']); ?></strong></td>
                                <td><span class="text-muted">📍 <?php echo htmlspecialchars($un['loc']); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="gerencia_unid.php?editar=<?php echo $un['id']; ?>&busca=<?php echo urlencode($filtro); ?>" 
                                           class="btn btn-sm btn-outline-warning text-dark" 
                                           title="Editar Unidade">
                                            ✏️
                                        </a>
                                        <a href="gerencia_unid.php?solicitar_exclusao=<?php echo $un['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Excluir Unidade">
                                            ❌
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nenhuma unidade cadastrada ou encontrada com esse filtro.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>