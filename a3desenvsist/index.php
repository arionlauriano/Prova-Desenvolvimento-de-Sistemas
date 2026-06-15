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
        <p class="w-25 mx-auto">Painel de Controle Principal</p>
        <hr class="w-25 mx-auto">
    </div>

    <div class="row justify-content-center g-4">

        <?php if ($isAdmin): ?>
            <div class="col-md-4">
                <div class="card shadow border-0 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <h3 class="text-center text-primary mb-4 fs-4 fw-medium">Infraestrutura</h3>
                        <div class="list-group list-group-flush my-auto">
                            <a href="gerencia_unid.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🏢 Gerenciar Unidades
                            </a>
                            <a href="gerencia_areas.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🏊 Áreas Comuns
                            </a>
                            <a href="gerencia_alas.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🌿 Gerenciar Alas
                            </a>
                            <a href="gerencia_quartos.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🛏️ Gerenciar Quartos
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow border-0 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <h3 class="text-center text-primary mb-4 fs-4 fw-medium">Reservas</h3>
                        <div class="list-group list-group-flush my-auto">
                            <a href="unid.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🏢 Gerenciar Unidades
                            </a>
                            <a href="areas.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🏊 Áreas Comuns
                            </a>
                            <a href="alas.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🌿 Gerenciar Alas
                            </a>
                            <a href="quartos.php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                🛏️ Gerenciar Quartos
                        </div>
                    </div>
                </div>
            </div>

                        <div class="col-md-4">
                <div class="card shadow border-0 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <h3 class="text-center text-primary mb-4 fs-4 fw-medium">Infraestrutura</h3>
                        <div class="list-group list-group-flush my-auto">
                            <a href=".php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                
                            </a>
                            <a href=".php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                
                            </a>
                            <a href=".php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                
                            </a>
                            <a href=".php" class="list-group-item list-group-item-action border-0 py-3 rouded mb-2 bg-lignt text-secondary">
                                
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>