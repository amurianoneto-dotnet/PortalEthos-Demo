<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'portal_ethos');

$mensagem = '';
$tipo_mensagem = '';
$token_valido = false;

// Pega o token da URL
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    // Escapa o token para segurança
    $token_seguro = $conn->real_escape_string($token);
    
    // Verifica se o token existe e se a data de expiração ainda é maior que o momento atual
    $sql = "SELECT id FROM usuarios WHERE reset_token = '$token_seguro' AND token_expiracao > NOW()";
    $verifica = $conn->query($sql);
    
    if ($verifica->num_rows > 0) {
        $token_valido = true;
        
        // Se o formulário de nova senha for enviado
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nova_senha = $_POST['nova_senha'];
            $confirma_senha = $_POST['confirma_senha'];
            
            if ($nova_senha === $confirma_senha) {
                // Senhas iguais: Criptografa e atualiza
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                
                // Atualiza a senha e "queima" o token (transforma em NULL para não ser usado de novo)
                $conn->query("UPDATE usuarios SET senha = '$senha_hash', reset_token = NULL, token_expiracao = NULL WHERE reset_token = '$token_seguro'");
                
                $tipo_mensagem = 'success';
                $mensagem = "Senha alterada com sucesso! Você já pode fazer login.";
                $token_valido = false; // Esconde o formulário
            } else {
                $tipo_mensagem = 'error';
                $mensagem = "As senhas não coincidem. Tente novamente.";
            }
        }
    } else {
        $tipo_mensagem = 'error';
        $mensagem = "Este link é inválido ou já expirou. Solicite uma nova recuperação.";
    }
} else {
    $tipo_mensagem = 'error';
    $mensagem = "Link de recuperação ausente.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Senha | Revista Ethos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .auth-container { background: #fff; max-width: 400px; width: 100%; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
        .auth-container h2 { color: #8C0303; margin: 0 0 10px; font-size: 1.4rem; text-transform: uppercase; }
        .auth-container p { color: #666; font-size: 0.95rem; margin-bottom: 25px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #444; }
        .form-control { width: 100%; padding: 14px; border: 2px solid #eee; border-radius: 8px; font-family: inherit; font-size: 1rem; box-sizing: border-box; transition: 0.3s; background: #fafafa; }
        .form-control:focus { border-color: #8C0303; outline: none; background: #fff; }
        .btn-submit { background: #8C0303; color: #fff; border: none; padding: 15px; border-radius: 8px; font-weight: 700; width: 100%; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-submit:hover { background: #000; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 600; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .link-voltar { display: inline-block; margin-top: 20px; color: #fff; background: #000; text-decoration: none; font-size: 0.9rem; font-weight: 600; padding: 10px 20px; border-radius: 8px; transition: 0.2s; }
        .link-voltar:hover { background: #8C0303; }
    </style>
</head>
<body>

    <div class="auth-container">
        <h2><i class="fas fa-lock" style="color: #ccc; display: block; margin-bottom: 10px; font-size: 2rem;"></i> Nova Senha</h2>
        
        <?php if ($mensagem): ?>
            <div class="alert <?= $tipo_mensagem ?>"><?= $mensagem ?></div>
            <?php if ($tipo_mensagem == 'success'): ?>
                <a href="login.php" class="link-voltar">Fazer Login Agora</a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($token_valido): ?>
            <p>Crie uma nova senha forte e segura.</p>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Sua Nova Senha</label>
                    <input type="password" name="nova_senha" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirme a Nova Senha</label>
                    <input type="password" name="confirma_senha" class="form-control" placeholder="Repita a senha" required minlength="6">
                </div>
                <button type="submit" class="btn-submit">Salvar Nova Senha</button>
            </form>
        <?php endif; ?>

        <?php if (!$token_valido && $tipo_mensagem != 'success'): ?>
            <a href="esqueci_senha.php" style="color: #8C0303; font-weight: bold; text-decoration: none; display: block; margin-top: 20px;">Tentar novamente</a>
        <?php endif; ?>
    </div>

</body>
</html>