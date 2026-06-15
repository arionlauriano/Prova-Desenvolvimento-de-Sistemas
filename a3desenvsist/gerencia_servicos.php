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

    $vlr_serv = str_replace(',', '.', trim($_POST['vlr_serv']));

    if (!empty($nome) && !empty($descricao) && is_numeric($vlr_serv)) {
        $sql = "INSERT INTO servicos (nome, descricao, vlr_serv) VALUES (:nome, :descricao, :vlr_serv)";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':vlr_serv', $vlr_serv);
        
        if ($stmt->execute()) {
            $mensagem = "Serviço adicionado com sucesso!";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Erro ao adicionar serviço.";
            $tipo_mensagem = "danger";
        }
    } else {
        $mensagem = "Preencha todos os campos e insira um valor numérico válido para o serviço.";
        $tipo_mensagem = "danger";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_salvar_edicao'])) {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $vlr_serv = str_replace(',', '.', trim($_POST['vlr_serv']));

    if (!empty($id) && !empty($nome) && !empty($descricao) && is_numeric($vlr_serv)) {
        $sql = "UPDATE servicos SET nome = :nome, descricao = :descricao, vlr_serv = :vlr_serv WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':vlr_serv', $vlr_serv);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            header("Location: gerencia_servicos.php?sucesso=1");
            exit();
        } else {
            $mensagem = "Erro ao atualizar serviço.";
            $tipo_mensagem = "danger";
        }
    } else {
        $mensagem = "Dados inválidos. Não foi possível atualizar.";
        $tipo_mensagem = "danger";
    }
}

if (isset($_GET['sucesso'])) {
    $mensagem = "Serviço atualizado com sucesso!";
    $tipo_mensagem = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_deletar_confirmado'])) {
    $id_deletar = $_POST['id_deletar'];
    
    $sql = "DELETE FROM servicos WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar);
    
    if ($stmt->execute()) {
        $mensagem = "Serviço removido com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao remover serviço.";
        $tipo_mensagem = "danger";
    }
}

$servico_editando = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql = "SELECT * FROM servicos WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $servico_editando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$servico_deletando = null;
if (isset($_GET['solicitar_exclusao'])) {
    $id_deletar_aviso = $_GET['solicitar_exclusao'];
    $sql = "SELECT * FROM servicos WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id_deletar_aviso);
    $stmt->execute();
    $servico_deletando = $stmt->fetch(PDO::FETCH_ASSOC);
}

$filtro = isset($_GET['busca']) ? trim($_GET['busca']) : "";

if (!empty($filtro)) {
    $sql_busca = "SELECT * FROM servicos WHERE nome LIKE :filtro ORDER BY nome ASC";
    $stmt_lista = $conexao->prepare($sql_busca);
    $param_filtro = "%$filtro%";
    $stmt_lista->bindParam(':filtro', $param_filtro);
} else {
    $sql_busca = "SELECT * FROM servicos ORDER BY nome ASC";
    $stmt_lista = $conexao->prepare($sql_busca);
}
$stmt_lista->execute();
$servicos = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container mt-5">

    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">← Voltar ao Menu</a>
        <h1 class="h2 text-dark">Gerenciamento de Serviços</h1>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($servico_deletando): ?>
        <div class="card border-0 shadow mb-5 text-white bg-danger">
            <div class="card-body p-4 text-center">
                <h3 class="mb-3">⚠️ Confirmar Exclusão</h3>
                <p class="fs-5">Você tem certeza que deseja remover o serviço <strong>"<?php echo htmlspecialchars($servico_deletando['nome']); ?>"</strong>?</p>
                <p class="small bg-dark bg-opacity-25 p-2 rounded d-inline-block">Atenção: Se este serviço possuir solicitações ativas vinculadas a quartos (`soli_serv`), a exclusão poderá falhar ou remover os históricos dependendo da integridade do banco.</p>
                
                <form method="POST" class="mt-3">
                    <input type="hidden" name="id_deletar" value="<?php echo $servico_deletando['id']; ?>">
                    <a href="gerencia_servicos.php" class="btn btn-light text-dark me-2">Cancelar</a>
                    <button type="submit" name="acao_deletar_confirmado" class="btn btn-dark">Sim, Excluir</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($servico_editando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-dark text-white p-3">
                <h5 class="mb-0">✏️ Editar Serviço #<?php echo $servico_editando['id']; ?></h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_salvar_edicao" value="1">
                    <input type="hidden" name="id" value="<?php echo $servico_editando['id']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Nome do Serviço:</label>
                            <input type="text" name="nome" class="form-control" required value="<?php echo htmlspecialchars($servico_editando['nome']); ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-medium">Descrição Detalhada:</label>
                            <input type="text" name="descricao" class="form-control" required value="<?php echo htmlspecialchars($servico_editando['descricao']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Valor do Serviço (R$):</label>
                            <input type="text" name="vlr_serv" class="form-control" placeholder="0.00" required value="<?php echo number_format($servico_editando['vlr_serv'], 2, ',', ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <a href="gerencia_servicos.php" class="btn btn-secondary me-2">Cancelar Edição</a>
                    <button type="submit" class="btn btn-warning text-dark fw-medium">Salvar Alterações</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (!$servico_editando && !$servico_deletando): ?>
        <div class="card shadow border-0 mb-5">
            <div class="card-header bg-primary text-white p-3">
                <h5 class="mb-0">🛎️ Cadastrar Novo Serviço</h5>
            </div>
            <form method="POST">
                <div class="card-body p-4 bg-light">
                    <input type="hidden" name="acao_adicionar" value="1">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Nome do Serviço:</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Lavanderia Expressa" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-medium">Descrição Operacional:</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Lavagem e passonaria entregue em até 12 horas no quarto" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Preço / Taxa (R$):</label>
                            <input type="text" name="vlr_serv" class="form-control" placeholder="Ex: 45,00" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end p-3">
                    <button type="submit" class="btn btn-primary">Cadastrar Serviço</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-10">
                    <label class="form-label small fw-bold text-secondary">Buscar serviço por nome:</label>
                    <input type="text" name="busca" class="form-control" placeholder="Digite o nome do serviço para pesquisar..." value="<?php echo htmlspecialchars($filtro); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">🔍 Pesquisar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary text-white">
                    <tr>
                        <th width="25%">Nome do Serviço</th>
                        <th width="40%">Descrição</th>
                        <th width="15%">Custo / Valor</th>
                        <th width="10%" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($servicos) > 0): ?>
                        <?php foreach ($servicos as $serv): ?>
                            <tr class="<?php echo ($servico_editando && $servico_editando['id'] == $serv['id']) ? 'table-warning' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($serv['nome']); ?></strong></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($serv['descricao']); ?></small></td>
                                <td><span class="text-success fw-bold">R$ <?php echo number_format($serv['vlr_serv'], 2, ',', '.'); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="gerencia_servicos.php?editar=<?php echo $serv['id']; ?>&busca=<?php echo urlencode($filtro); ?>" 
                                           class="btn btn-sm btn-outline-warning text-dark" 
                                           title="Editar Serviço">
                                            ✏️
                                        </a>
                                        <a href="gerencia_servicos.php?solicitar_exclusao=<?php echo $serv['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Excluir Serviço">
                                            ❌
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum serviço disponível ou encontrado com esse filtro.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>