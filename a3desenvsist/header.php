<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tarefas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php if (isset($_SESSION["logado"]) && $_SESSION["logado"] === true): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Resort Shoreline</a>
        <div class="navbar-nav ms-auto align-items-center">
            <span class="nav-item nav-link text-light me-3">
                Usuário: <strong><?php echo htmlspecialchars($_SESSION["usuario_nome"] ?? $_SESSION["usuario"] ?? 'Visitante'); ?></strong>
            </span>
            <a class="btn btn-outline-danger btn-sm" href="logout.php">Sair</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container">