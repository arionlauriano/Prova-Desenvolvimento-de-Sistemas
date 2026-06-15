<?php
// Inicia a sessão para ter acesso aos dados atuais
session_start();

// 1. Limpa todas as variáveis de sessão salvas na memória
$_SESSION = array();

// 2. Se o sistema utiliza cookies de sessão (padrão do PHP), destrói o cookie no navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destrói a sessão ativa no servidor definitivamente
session_destroy();

// 4. Redireciona o usuário para a página de login/index
header("Location: index.php");
exit();
?>