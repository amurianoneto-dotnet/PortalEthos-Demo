<?php
// Inicia a sessão para guardar o usuário logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('includes/db.php');
include('functions.php'); // Se precisar

// Se o usuário já estiver logado, manda ele de volta pra Home
if (isset($_SESSION['leitor_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

// ==========================================
// PROCESSAMENTO DO FORMULÁRIO (LOGIN E CADASTRO)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ------ CADASTRO DE NOVO LEITOR ------
    if (isset($_POST['acao']) && $_POST['acao'] == 'cadastrar') {
        $nome = mysqli_real_escape_string($conn, $_POST['nome']);
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $senha = $_POST['senha'];
        
        // Verifica se o email já existe no banco
        $verifica = mysqli_query($conn, "SELECT id FROM usuarios WHERE email = '$email'");
        if (mysqli_num_rows($verifica) > 0) {
            $erro = "Este e-mail já está cadastrado. Tente fazer login.";
        } else {
            // Criptografa a senha por segurança antes de salvar no banco
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO usuarios (nome, email, senha) VALUES ('$nome', '$email', '$senha_hash')";
            
            if (mysqli_query($conn, $sql_insert)) {
                $sucesso = "Conta criada com sucesso! Você já pode fazer login.";
            } else {
                $erro = "Erro ao criar conta: " . mysqli_error($conn);
            }
        }
    }
    
    // ------ LOGIN DE LEITOR EXISTENTE ------
    if (isset($_POST['acao']) && $_POST['acao'] == 'entrar') {
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $senha = $_POST['senha'];
        
        $sql_login = "SELECT * FROM usuarios WHERE email = '$email'";
        $res_login = mysqli_query($conn, $sql_login);
        
        if ($usuario = mysqli_fetch_assoc($res_login)) {
            // Verifica se a senha digitada bate com a criptografada no banco
            if (password_verify($senha, $usuario['senha'])) {
                // Deu certo! Cria a sessão do usuário
                $_SESSION['leitor_id'] = $usuario['id'];
                $_SESSION['leitor_nome'] = $usuario['nome'];
                
                // Redireciona pra Home
                header("Location: index.php");
                exit;
            } else {
                $erro = "Senha incorreta. Tente novamente.";
            }
        } else {
            $erro = "E-mail não encontrado. Crie uma conta primeiro.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar ou Cadastrar | Revista Ethos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Roboto', sans-serif; }
        .auth-container { max-width: 450px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
        
        /* Abas no topo */
        .auth-tabs { display: flex; border-bottom: 2px solid #eee; }
        .auth-tab { flex: 1; text-align: center; padding: 18px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 1.1rem; color: #888; cursor: pointer; transition: 0.3s; background: #fdfdfd; }
        .auth-tab.active { color: #8C0303; border-bottom: 3px solid #8C0303; background: #fff; }
        .auth-tab:hover:not(.active) { color: #333; }
        
        .auth-body { padding: 40px 30px; }
        .auth-form { display: none; }
        .auth-form.active { display: block; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; font-family: inherit; box-sizing: border-box; transition: 0.3s; }
        .form-group input:focus { border-color: #8C0303; outline: none; box-shadow: 0 0 0 3px rgba(140, 3, 3, 0.1); }
        
        .btn-submit { width: 100%; padding: 15px; background: #8C0303; color: #fff; border: none; border-radius: 8px; font-size: 1.05rem; font-weight: bold; font-family: 'Poppins', sans-serif; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #610202; }
        
        /* Botões Sociais */
        .social-login { margin-bottom: 25px; }
        .btn-social { width: 100%; padding: 12px; border-radius: 8px; font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; transition: 0.3s; text-decoration: none; margin-bottom: 12px; border: 1px solid #ddd; background: #fff; color: #333; }
        .btn-social:hover { background: #f9f9f9; }
        .btn-social.google img { width: 18px; }
        .btn-social.facebook { background: #1877F2; color: #fff; border: none; }
        .btn-social.facebook:hover { background: #145dbf; }
        
        .divider { display: flex; align-items: center; text-align: center; margin: 25px 0; color: #999; font-size: 0.85rem; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #eee; }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .alert.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    </style>
</head>
<body>

    <?php include('includes/header.php'); ?>

    <div class="auth-container">
        <div class="auth-tabs">
            <div class="auth-tab active" onclick="switchTab('entrar')">Entrar</div>
            <div class="auth-tab" onclick="switchTab('cadastrar')">Criar Conta</div>
        </div>

        <div class="auth-body">
            
            <?php if(!empty($erro)): ?>
                <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $erro ?></div>
            <?php endif; ?>
            
            <?php if(!empty($sucesso)): ?>
                <div class="alert success"><i class="fas fa-check-circle"></i> <?= $sucesso ?></div>
            <?php endif; ?>

            <div class="social-login">
                <button class="btn-social google" onclick="alert('A integração com a API do Google será configurada assim que o site estiver no domínio oficial!');">
                    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google">
                    Entrar com o Google
                </button>
                <button class="btn-social facebook" onclick="alert('A integração com a API do Facebook será configurada assim que o site estiver no domínio oficial!');">
                    <i class="fab fa-facebook-f"></i>
                    Entrar com o Facebook
                </button>
            </div>

            <div class="divider">ou continue com e-mail</div>

            <form id="form-entrar" class="auth-form active" method="POST" action="">
                <input type="hidden" name="acao" value="entrar">
                <div class="form-group">
                    <label>Seu e-mail</label>
                    <input type="email" name="email" placeholder="exemplo@email.com" required>
                </div>
                <div class="form-group">
                    <label>Sua senha</label>
                    <input type="password" name="senha" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-submit">ACESSAR MINHA CONTA</button>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="esqueci_senha.php" style="color: #8C0303; font-size: 0.85rem; text-decoration: none;">Esqueci minha senha</a>
                </div>
            </form>

            <form id="form-cadastrar" class="auth-form" method="POST" action="">
                <input type="hidden" name="acao" value="cadastrar">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" placeholder="Como devemos te chamar?" required>
                </div>
                <div class="form-group">
                    <label>Seu melhor e-mail</label>
                    <input type="email" name="email" placeholder="exemplo@email.com" required>
                </div>
                <div class="form-group">
                    <label>Crie uma senha forte</label>
                    <input type="password" name="senha" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                <button type="submit" class="btn-submit">CRIAR CONTA GRÁTIS</button>
                <p style="text-align: center; font-size: 0.8rem; color: #888; margin-top: 15px;">
                    Ao criar uma conta, você concorda com nossos <a href="politica_privacidade.php" style="color: #8C0303;">Termos de Uso e Privacidade</a>.
                </p>
            </form>

        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.auth-tab').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(el => el.classList.remove('active'));
            
            if(tab === 'entrar') {
                document.querySelectorAll('.auth-tab')[0].classList.add('active');
                document.getElementById('form-entrar').classList.add('active');
            } else {
                document.querySelectorAll('.auth-tab')[1].classList.add('active');
                document.getElementById('form-cadastrar').classList.add('active');
            }
        }
    </script>
</body>
</html>