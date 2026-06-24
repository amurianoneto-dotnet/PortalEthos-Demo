<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'portal_ethos');

$mensagem = '';
$tipo_mensagem = '';
$link_teste = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string(trim($_POST['email']));
    
    // Verifica se o e-mail existe no banco
    $verifica = $conn->query("SELECT id, nome FROM usuarios WHERE email = '$email'");
    
    if ($verifica->num_rows > 0) {
        $usuario = $verifica->fetch_assoc();
        
        // Gera um token único e aleatório de 64 caracteres
        $token = bin2hex(random_bytes(32));
        // Define a validade para 1 hora a partir de agora
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Salva o token no banco de dados
        $conn->query("UPDATE usuarios SET reset_token = '$token', token_expiracao = '$expiracao' WHERE email = '$email'");
        
        $tipo_mensagem = 'success';
        $mensagem = "Se este e-mail estiver cadastrado, você receberá as instruções em instantes.";
        
        // ========================================================
        // TRUQUE DO LOCALHOST: Mostrar o link na tela para você testar
        // Quando for para um servidor real, você apaga essa linha e usa a função mail() do PHP
        $link_teste = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/redefinir_senha.php?token=" . $token;
        // ========================================================

    } else {
        // Por segurança, mostramos a MESMA mensagem para não revelar a hackers se um email existe ou não
        $tipo_mensagem = 'success';
        $mensagem = "Se este e-mail estiver cadastrado, você receberá as instruções em instantes.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | Revista Ethos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .auth-container { background: #fff; max-width: 400px; width: 100%; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
        .auth-container img { height: 60px; margin-bottom: 20px; }
        .auth-container h2 { color: #8C0303; margin: 0 0 10px; font-size: 1.4rem; text-transform: uppercase; }
        .auth-container p { color: #666; font-size: 0.95rem; margin-bottom: 25px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-control { width: 100%; padding: 14px; border: 2px solid #eee; border-radius: 8px; font-family: inherit; font-size: 1rem; box-sizing: border-box; transition: 0.3s; background: #fafafa; }
        .form-control:focus { border-color: #8C0303; outline: none; background: #fff; }
        .btn-submit { background: #8C0303; color: #fff; border: none; padding: 15px; border-radius: 8px; font-weight: 700; width: 100%; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-submit:hover { background: #000; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 600; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .link-voltar { display: inline-block; margin-top: 20px; color: #666; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: 0.2s; }
        .link-voltar:hover { color: #8C0303; }
        
        /* Área de Teste Localhost */
        .mock-email { margin-top: 30px; background: #fff3cd; border: 2px dashed #ffeeba; padding: 20px; border-radius: 10px; text-align: left; }
        .mock-email h4 { margin: 0 0 10px; color: #856404; font-size: 0.9rem; }
        .mock-email a { display: block; word-break: break-all; color: #084298; font-size: 0.85rem; font-family: monospace; }
    </style>
</head>
<body>

    <div class="auth-container">
        <img src="assets/img/logo.png" alt="ETHOS" style="background: #8C0303; padding: 10px; border-radius: 8px;">
        <h2>Esqueceu a senha?</h2>
        <p>Digite seu e-mail cadastrado e enviaremos um link seguro para você criar uma nova senha.</p>

        <?php if ($mensagem): ?>
            <div class="alert <?= $tipo_mensagem ?>"><i class="fas fa-info-circle"></i> <?= $mensagem ?></div>
            
            <?php if ($link_teste): ?>
                <div class="mock-email">
                    <h4><i class="fas fa-bug"></i> MODO DE TESTE (Localhost):</h4>
                    <span style="font-size: 0.8rem; color:#666; display:block; margin-bottom: 5px;">Como o servidor local não envia e-mail, clique no link abaixo para simular que você abriu a mensagem na sua caixa de entrada:</span>
                    <a href="<?= $link_teste ?>"><?= $link_teste ?></a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Seu endereço de e-mail" required>
                </div>
                <button type="submit" class="btn-submit">Enviar Link de Recuperação</button>
            </form>
        <?php endif; ?>

        <a href="login.php" class="link-voltar"><i class="fas fa-arrow-left"></i> Voltar para o Login</a>
    </div>

</body>
</html>