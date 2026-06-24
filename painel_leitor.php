<?php
session_start();

// Garante que o menu funcione no header
$menuCategorias = [
    ['label' => 'Turismo', 'slug' => 'turismo', 'children' => [['label' => 'Caminhos do Tietê', 'slug' => 'caminhos-do-tiete']]],
    ['label' => 'BioEthos', 'slug' => 'bioethos'],
    ['label' => 'EvenThos', 'slug' => 'eventhos'],
    ['label' => "Joana D'Arc", 'slug' => 'joana-darc'],
    ['label' => 'Poethos', 'slug' => 'poethos', 'children' => [['label' => 'Cultura', 'slug' => 'cultura']]],
    ['label' => 'Selethos', 'slug' => 'selethos'],
    ['label' => 'Arquitethos', 'slug' => 'arquitethos'],
    ['label' => 'Saúde', 'slug' => 'saude'],
    ['label' => 'Moda', 'slug' => 'moda'],
    ['label' => 'Estética', 'slug' => 'estetica'], 
];
$quickMenuLinks = [
    ['label' => 'Home', 'href' => 'index.php'],
    ['label' => 'Sobre nós', 'href' => 'sobre.php'],
    ['label' => 'Site: Bio.Ethos', 'href' => '#'],
    ['label' => 'Caminhos do Tietê', 'href' => 'https://caminhosdotiete.com.br/', 'target' => '_blank'],
];

// Verifica se está logado
if (!isset($_SESSION['leitor_id'])) {
    header("Location: login.php");
    exit();
}

// Conexão com o Banco
$conn = new mysqli('localhost', 'root', '', 'portal_ethos');
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$mensagem = "";

// Lógica de Salvar no Banco
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leitor_id = $_SESSION['leitor_id'];
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $conteudo = $conn->real_escape_string($_POST['conteudo']);
    $nome_arquivo = null;

    // Lógica da Foto / Documento
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $diretorio = "uploads/pautas/";
        if (!is_dir($diretorio)) { mkdir($diretorio, 0777, true); }
        
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid() . "." . $extensao;
        move_uploaded_file($_FILES['foto']['tmp_name'], $diretorio . $nome_arquivo);
    }

    $sql = "INSERT INTO pautas (leitor_id, titulo, conteudo, arquivo) VALUES ('$leitor_id', '$titulo', '$conteudo', '$nome_arquivo')";
    if ($conn->query($sql) === TRUE) {
        $mensagem = "Material recebido com sucesso! Nossa equipe editorial entrará em contato em breve.";
    } else {
        $mensagem = "Erro ao enviar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espaço do Autor | Revista Ethos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --vinho: #8C0303; --cinza-fundo: #f8f9fa; --preto-total: #000000; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--cinza-fundo); margin: 0; color: #333; }
        .container-painel { max-width: 1100px; margin: 0 auto; padding: 160px 20px 60px; }
        
        .welcome-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; border-left: 6px solid var(--vinho); }
        .welcome-text h1 { margin: 0; font-size: 1.5rem; color: var(--vinho); text-transform: uppercase; }
        .welcome-text p { margin: 5px 0 0; color: #666; font-size: 1.1rem; }

        .grid-pauta { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 30px; }
        .card-form { background: white; padding: 35px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-form h2 { font-size: 1.3rem; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem; }
        .form-control { width: 100%; padding: 14px; border: 2px solid #eee; border-radius: 8px; font-family: inherit; box-sizing: border-box; transition: 0.3s; background: #fafafa; }
        .form-control:focus { border-color: var(--vinho); outline: none; background: #fff; }

        .btn-enviar { background: var(--vinho); color: white; border: none; padding: 18px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-enviar:hover { background: #000; transform: translateY(-2px); }

        .sidebar-box { background: var(--preto-total); color: white; padding: 35px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .sidebar-box h3 { color: #ffffff; margin-top: 0; font-size: 1.2rem; border-left: 4px solid var(--vinho); padding-left: 15px; margin-bottom: 25px; }
        .sidebar-box ul { padding-left: 20px; }
        .sidebar-box li { margin-bottom: 20px; font-size: 0.95rem; line-height: 1.6; color: #ddd; }
        .sidebar-box li strong { color: var(--vinho); }

        .alert-success { background: var(--vinho); color: #ffffff; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: none; display: flex; align-items: center; gap: 15px; font-weight: 600; box-shadow: 0 4px 15px rgba(140, 3, 3, 0.2); }

        @media (max-width: 900px) { .grid-pauta { grid-template-columns: 1fr; } .container-painel { padding-top: 200px; } }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="container-painel">
        <div class="welcome-card">
            <div class="welcome-text">
                <h1>Espaço do Autor & Pautas</h1>
                <p>Olá, <strong><?= htmlspecialchars(explode(' ', trim($_SESSION['leitor_nome']))[0]) ?></strong>! Este é o seu canal direto com a redação da Revista Ethos.</p>
            </div>
            <i class="fas fa-pen-fancy" style="color: #eee; font-size: 60px;"></i>
        </div>

        <?php if ($mensagem): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <div class="grid-pauta">
            <div class="card-form">
                <h2><i class="fas fa-paper-plane" style="color: var(--vinho);"></i> Enviar Conteúdo</h2>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Assunto / Título do Artigo</label>
                        <input type="text" name="titulo" class="form-control" placeholder="Ex: Sugestão de entrevista com o artista X..." required>
                    </div>
                    <div class="form-group">
                        <label>Conteúdo da Sugestão ou Artigo Completo</label>
                        <textarea name="conteudo" class="form-control" rows="8" placeholder="Descreva os detalhes da sua pauta, o porquê deveríamos cobrir este evento, ou cole aqui o seu artigo na íntegra..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Anexos (Press Release, Fotos ou Artigo em PDF)</label>
                        <input type="file" name="foto" class="form-control" accept="image/*,.pdf,.doc,.docx">
                    </div>
                    <button type="submit" class="btn-enviar">Protocolar Material</button>
                </form>
            </div>

            <div class="sidebar-box">
                <h3><i class="fas fa-lightbulb"></i> Diretrizes Editoriais</h3>
                <ul>
                    <li><strong>Artigos de Opinião:</strong> Aceitamos textos originais e inéditos. Capriche na argumentação e na clareza.</li>
                    <li><strong>Sugestão de Entrevista:</strong> Conhece alguém com uma história incrível? Nos conte o porquê o público da Ethos adoraria lê-la.</li>
                    <li><strong>Cobertura de Eventos:</strong> Envie o Release (material de divulgação) com datas, locais e fotos em alta resolução.</li>
                    <li><strong>Revisão:</strong> Todo material enviado passará pela curadoria da nossa equipe antes de uma possível publicação.</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>