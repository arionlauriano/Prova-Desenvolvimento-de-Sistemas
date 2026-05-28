<?php
include 'conexao.php';
session_start();
if(isset($_SESSION['logado']) && isset($_GET['id'])){
    $conexao->prepare("UPDATE tarefas SET status = 'concluida' WHERE id = ?")->execute([$_GET['id']]);
}
header("Location: index.php");
?>