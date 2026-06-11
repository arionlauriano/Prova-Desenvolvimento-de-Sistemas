<?php
// config.php está sendo ignorado pelo git para esconder a senha do BD
$config = @include('config.php');

$host = $config['host'] ?? 'localhost';
$dbname = $config['dbname'] ?? 'bd_shoreline';
$usuario_bd = $config['usuario_bd'] ?? 'root';
$senha_bd = $config['senha_bd'] ?? '';

try {
    $conexao = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario_bd, $senha_bd);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>