<?php
include 'conexao.php';
session_start();
if(isset($_SESSION['logado']) && isset($_GET['id'])){
    $conexao->prepare("DELETE FROM tarefas WHERE id = ?")->execute([$_GET['id']]);
}
header("Location: index.php");
?>