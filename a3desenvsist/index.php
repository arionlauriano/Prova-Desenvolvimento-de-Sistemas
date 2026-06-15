<?php
include 'conexao.php';

if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true) {
    header("Location: login.php");
    exit();
}

include 'header.php';

$isAdmin = ($_SESSION['usuario_id'] == 1);
?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-normal text-dark">Resort Shoreline</h1>
        <p class="text-muted fs-5">Painel de Controle Principal</p>
        <hr class="w-25 mx-auto" style="border-top: 2px solid #0d6efd; opacity: 0.8;">
    </div>

    <div class="row justify-content-center g-4">

        <?php if ($isAdmin): ?>
            <div class="col-md-4">
                <div class="card shadow border-0 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <h3 class="text-center text-primary mb-4 fs-4 fw-medium">Infraestrutura</h3>
                        <div class="list-group list-group-flush my-auto">
                            <a href="gerencia_unid.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                🏢 Gerenciar Unidades
                            </a>
                            <a href="gerencia_areas.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                🏊 Áreas Comuns
                            </a>
                            <a href="gerencia_alas.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                🌿 Gerenciar Alas
                            </a>
                            <a href="gerencia_quartos.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                🛏️ Gerenciar Quartos
                            </a> </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h3 class="text-center text-primary mb-4 fs-4 fw-medium">Serviços</h3>
                    <div class="list-group list-group-flush my-auto">
                        <?php if ($isAdmin): ?>
                            <a href="gerencia_servicos.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                🛎️ Gerenciar Serviços
                            </a>
                            <a href="gerencia_soliserv.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                📋 Solicitações dos Serviços
                            </a>  
                        <?php else: ?>                           
                            <a href="meus_servicos.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                🛎️ Meus Serviços
                            </a>
                        <?php endif; ?>         
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h3 class="text-center text-primary mb-4 fs-4 fw-medium">Reservas</h3>
                    <div class="list-group list-group-flush my-auto">
                        <?php if($isAdmin): ?>
                            <a href="gerencia_reservas.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                📅 Gerenciar Reservas
                            </a>
                        <?php else: ?>
                            <a href="minhas_reservas.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                📅 Meus Agendamentos
                            </a>
                            <a href="nova_reservacli.php" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light text-secondary">
                                ➕ Nova Reserva
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>