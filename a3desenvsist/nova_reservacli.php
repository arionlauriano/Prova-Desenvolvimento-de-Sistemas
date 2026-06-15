<?php
include 'conexao.php';

if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true || $_SESSION['usuario_id'] == 1) {
    header("Location: index.php");
    exit();
}

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_salvar_reserva'])) {
    $nome = trim($_POST['nome']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $qnt_adultos = (int)$_POST['qnt_adultos'];
    $qnt_criancas = !empty($_POST['qnt_criancas']) ? (int)$_POST['qnt_criancas'] : 0;
    $data_checkin = $_POST['data_checkin'];
    $data_checkout = $_POST['data_checkout'];
    $cod_unid = (int)$_POST['cod_unid'];
    $cod_quart = (int)$_POST['cod_quart'];
    $vlr_reserv = str_replace(',', '.', $_POST['vlr_reserv']);

    $cod_usuario = (int)$_SESSION['usuario_id']; 

    $hoje = date('Y-m-d');

    if (empty($nome) || strlen($cpf) !== 11 || empty($data_checkin) || empty($data_checkout) || empty($cod_unid) || empty($cod_quart) || empty($cod_usuario)) {
        $mensagem = "Por favor, preencha todos os campos obrigatórios corretamente e verifique o CPF.";
        $tipo_mensagem = "danger";
    } elseif ($data_checkin < $hoje) {
        $mensagem = "Erro: A data de Check-in não pode ser uma data que já passou.";
        $tipo_mensagem = "danger";
    } elseif ($data_checkin == $data_checkout) {
        $mensagem = "Erro: As datas de Check-in e Check-out não podem ser iguais.";
        $tipo_mensagem = "danger";
    } elseif ($data_checkout < $data_checkin) {
        $mensagem = "Erro: A data de Check-out não pode ser anterior à data de Check-in.";
        $tipo_mensagem = "danger";
    } else {

        $sql_conflito = "SELECT COUNT(*) AS total FROM reservas 
                         WHERE cod_quart = :cod_quart 
                         AND NOT (data_checkout <= :checkin OR data_checkin >= :checkout)";
        $stmt_conf = $conexao->prepare($sql_conflito);
        $stmt_conf->execute([
            ':cod_quart' => $cod_quart,
            ':checkin'   => $data_checkin,
            ':checkout'  => $data_checkout
        ]);
        $conflito = $stmt_conf->fetch(PDO::FETCH_ASSOC);

        if ($conflito['total'] > 0) {
            $mensagem = "Erro: Este quarto já está ocupado ou reservado no período selecionado.";
            $tipo_mensagem = "danger";
        } else {

            $sql_insert = "INSERT INTO reservas (nome, cpf, qnt_adultos, qnt_criancas, data_checkin, data_checkout, vlr_reserv, cod_unid, cod_quart, cod_usuario) 
                           VALUES (:nome, :cpf, :qnt_adultos, :qnt_criancas, :data_checkin, :data_checkout, :vlr_reserv, :cod_unid, :cod_quart, :cod_usuario)";
            $stmt_in = $conexao->prepare($sql_insert);
            
            $salvou = $stmt_in->execute([
                ':nome'          => $nome,
                ':cpf'           => $cpf,
                ':qnt_adultos'   => $qnt_adultos,
                ':qnt_criancas'  => $qnt_criancas,
                ':data_checkin'  => $data_checkin,
                ':data_checkout' => $data_checkout,
                ':vlr_reserv'    => $vlr_reserv,
                ':cod_unid'      => $cod_unid,
                ':cod_quart'     => $cod_quart,
                ':cod_usuario'   => $cod_usuario 
            ]);

            if ($salvou) {
                header("Location: gerencia_reservas.php?sucesso_cadastro=1");
                exit();
            } else {
                $mensagem = "Erro operacional ao salvar a reserva no sistema.";
                $tipo_mensagem = "danger";
            }
        }
    }
}

$unidades = $conexao->query("SELECT id, nome FROM unidades ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

$sql_tree = "SELECT q.id AS quarto_id, q.num AS quarto_num, al.vlr_diaria, u.id AS unidade_id
             FROM quartos q
             INNER JOIN alas al ON q.cod_ala = al.id
             INNER JOIN unidades u ON al.cod_unid = u.id
             ORDER BY q.num ASC";
$todos_quartos = $conexao->query($sql_tree)->fetchAll(PDO::FETCH_ASSOC);

$indisponibilidades = [];
$reservas_existentes = $conexao->query("SELECT cod_quart, data_checkin, data_checkout FROM reservas")->fetchAll(PDO::FETCH_ASSOC);
foreach ($reservas_existentes as $res) {
    $indisponibilidades[$res['cod_quart']][] = [
        'in'  => $res['data_checkin'],
        'out' => $res['data_checkout']
    ];
}

include 'header.php';
?>

<div class="container mt-5">
    <div class="mb-4">
        <a href="gerencia_reservas.php" class="btn btn-outline-secondary btn-sm mb-2">← Voltar à Relação de Reservas</a>
        <h1 class="h2 text-dark">Lançamento de Reserva (Área do Funcionário)</h1>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show shadow" role="alert">
            <strong>Aviso do Sistema:</strong> <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <div id="js-alerta" class="alert alert-danger d-none shadow" role="alert"></div>

    <div class="card shadow border-0 mb-5">
        <div class="card-header bg-primary text-white p-3">
            <h5 class="mb-0">📋 Dados Cadastrais do Cliente e Estadia</h5>
        </div>
        
        <form method="POST" id="formReserva">
            <input type="hidden" name="acao_salvar_reserva" value="1">
            
            <div class="card-body p-4 bg-light">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-medium">Nome Completo do Titular:</label>
                        <input type="text" name="nome" class="form-control" placeholder="Digite o nome completo..." required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-medium">CPF (Apenas números):</label>
                        <input type="text" name="cpf" maxlength="11" class="form-control" placeholder="Ex: 11122233344" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Quantidade de Adultos:</label>
                        <input type="number" name="qnt_adultos" min="1" value="1" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Quantidade de Crianças:</label>
                        <input type="number" name="qnt_criancas" min="0" value="0" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Escolha a Unidade:</label>
                        <select name="cod_unid" id="selectUnidade" class="form-select" required>
                            <option value="">-- Selecione a Unidade --</option>
                            <?php foreach ($unidades as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Quarto Disponível:</label>
                        <select name="cod_quart" id="selectQuarto" class="form-select" disabled required>
                            <option value="">-- Escolha a Unidade Primeiro --</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Data de Check-in:</label>
                        <input type="date" name="data_checkin" id="dataCheckin" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Data de Check-out:</label>
                        <input type="date" name="data_checkout" id="dataCheckout" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary">Valor Estimado da Reserva (R$):</label>
                        <input type="text" name="vlr_reserv" id="vlrReserv" class="form-control fw-bold bg-white text-success fs-5" readonly value="0,00">
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-end p-3">
                <a href="gerencia_reservas.php" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-success px-4" id="btnSalvar">Confirmar e Registrar Reserva</button>
            </div>
        </form>
    </div>
</div>

<script>
const dadosQuartos = <?php echo json_encode($todos_quartos); ?>;
const agendaOcupada = <?php echo json_encode($indisponibilidades); ?>;

const selectUnidade = document.getElementById('selectUnidade');
const selectQuarto = document.getElementById('selectQuarto');
const dataCheckin = document.getElementById('dataCheckin');
const dataCheckout = document.getElementById('dataCheckout');
const vlrReserv = document.getElementById('vlrReserv');
const divAlerta = document.getElementById('js-alerta');
const formReserva = document.getElementById('formReserva');

selectUnidade.addEventListener('change', function() {
    const unidId = this.value;
    selectQuarto.innerHTML = '';
    
    if(!unidId) {
        selectQuarto.disabled = true;
        selectQuarto.innerHTML = '<option value="">-- Escolha a Unidade Primeiro --</option>';
        resetarCalculo();
        return;
    }

    const quartosFiltrados = dadosQuartos.filter(q => q.unidade_id == unidId);

    if(quartosFiltrados.length === 0) {
        selectQuarto.disabled = true;
        selectQuarto.innerHTML = '<option value="">Nenhum quarto cadastrado nesta unidade</option>';
    } else {
        selectQuarto.disabled = false;
        let options = '<option value="">-- Selecione o Quarto --</option>';
        quartosFiltrados.forEach(q => {
            options += `<option value="${q.quarto_id}" data-diaria="${q.vlr_diaria}">Quarto ${q.quarto_num} (Diária: R$ ${parseFloat(q.vlr_diaria).toFixed(2).replace('.', ',')})</option>`;
        });
        selectQuarto.innerHTML = options;
    }
    resetarCalculo();
});

selectQuarto.addEventListener('change', validarEDesempenharCalculos);
dataCheckin.addEventListener('change', validarEDesempenharCalculos);
dataCheckout.addEventListener('change', validarEDesempenharCalculos);

function resetarCalculo() {
    vlrReserv.value = "0,00";
    divAlerta.classList.add('d-none');
}

function mostrarErroJS(texto) {
    divAlerta.innerText = texto;
    divAlerta.classList.remove('d-none');
    vlrReserv.value = "0,00";
}

function validarEDesempenharCalculos() {
    divAlerta.classList.add('d-none');

    const quartoSelecionado = selectQuarto.value;
    const checkinVal = dataCheckin.value;
    const checkoutVal = dataCheckout.value;

    if (!quartoSelecionado || !checkinVal || !checkoutVal) {
        return;
    }

    const dIn = new Date(checkinVal + "T00:00:00");
    const dOut = new Date(checkoutVal + "T00:00:00");
    const hoje = new Date();
    hoje.setHours(0,0,0,0);

    if (dIn < hoje) {
        mostrarErroJS("Mensagem de Erro: Você não pode registrar uma reserva em uma data que já passou.");
        return;
    }

    if (dIn.getTime() === dOut.getTime()) {
        mostrarErroJS("Mensagem de Erro: A data de Check-in e Check-out não podem coincidir no mesmo dia.");
        return;
    }

    if (dOut < dIn) {
        mostrarErroJS("Mensagem de Erro: A data de Check-out não pode ser configurada antes da data de Check-in.");
        return;
    }

    const ocupacoesQuarto = agendaOcupada[quartoSelecionado] || [];
    const temConflito = ocupacoesQuarto.some(reserva => {
        const resIn = new Date(reserva.in + "T00:00:00");
        const resOut = new Date(reserva.out + "T00:00:00");
        return !(dOut <= resIn || dIn >= resOut);
    });

    if (temConflito) {
        mostrarErroJS("Mensagem de Erro: O quarto selecionado já se encontra ocupado no intervalo de datas escolhido.");
        return;
    }

    const diferencaMilissegundos = dOut.getTime() - dIn.getTime();
    const totalDias = Math.ceil(diferencaMilissegundos / (1000 * 60 * 60 * 24));
    const diariaPreco = parseFloat(selectQuarto.options[selectQuarto.selectedIndex].getAttribute('data-diaria'));
    const resultadoBruto = diariaPreco * totalDias;

    vlrReserv.value = resultadoBruto.toFixed(2).replace('.', ',');
}

formReserva.addEventListener('submit', function(e) {
    if (!divAlerta.classList.contains('d-none') || vlrReserv.value === "0,00") {
        e.preventDefault();
        alert("Por favor, corrija os erros de validação antes de salvar a reserva.");
    }
});
</script>