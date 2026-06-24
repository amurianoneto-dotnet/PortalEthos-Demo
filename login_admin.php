<?php
session_start();
include('includes/db.php');

// Se já estiver logado, manda direto pro painel
if (isset($_SESSION['admin_logado'])) {
    header('Location: admin.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha'];

    $res = mysqli_query($conn, "SELECT * FROM admin_usuarios WHERE email = '$email'");
    if ($res && mysqli_num_rows($res) > 0) {
        $admin = mysqli_fetch_assoc($res);
        
        // Verifica se a senha bate com a criptografia
        if (password_verify($senha, $admin['senha'])) {
            $_SESSION['admin_logado'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            header('Location: admin.php');
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "E-mail não encontrado!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal ETHOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #000; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; color: #333; }
        .login-box { background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 400px; text-align: center; }
        .login-box img { height: 60px; margin-bottom: 20px; filter: invert(1); }
        .login-box h2 { color: #8C0303; margin-top: 0; font-weight: 900; text-transform: uppercase; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 0.85rem; color: #555; text-transform: uppercase; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1rem; }
        .btn-login { background: #8C0303; color: #fff; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 1rem; transition: 0.3s; }
        .btn-login:hover { background: #000; }
        .erro { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: bold; }
    </style>
</head>
<body>

    <div class="login-box">
        <img src="assets/img/logo.png" alt="ETHOS">
        <h2>Painel Restrito</h2>
        
        <?php if($erro): ?>
            <div class="erro"><i class="fas fa-exclamation-triangle"></i> <?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>E-mail Administrativo</label>
                <input type="email" name="email" placeholder="admin@revistaethos.com.br" required>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">ENTRAR NO PORTAL <i class="fas fa-sign-in-alt"></i></button>
        </form>
    </div>

</body>
</html>