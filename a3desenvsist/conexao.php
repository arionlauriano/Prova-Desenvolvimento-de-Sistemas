<?php
$host = "localhost";
$dbname = "tarefas"; // Nome conforme seu script
$usuario_db = "root";
$senha_db = "Senha";

try {
    $conexao = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario_db, $senha_db);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>