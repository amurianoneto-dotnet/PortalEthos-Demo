<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/config_menu.php';

// Proteção: Só logados entram!
if (!isset($_SESSION['leitor_id'])) {
    header("Location: login.php");
    exit();
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_id = $_SESSION['leitor_id'];
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $data_evento = $_POST['data_evento'];
    $hora_evento = $_POST['hora_evento'];
    $local_evento = $conn->real_escape_string($_POST['local_evento']);
    $nome_arquivo = null;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $diretorio = "uploads/eventos/";
        if (!is_dir($diretorio)) { mkdir($diretorio, 0777, true); }
        
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid() . "." . $extensao;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorio . $nome_arquivo);
    }

    $sql = "INSERT INTO eventos (usuario_id, titulo, descricao, data_evento, hora_evento, local_evento, imagem) 
            VALUES ('$usuario_id', '$titulo', '$descricao', '$data_evento', '$hora_evento', '$local_evento', '$nome_arquivo')";
    
    if ($conn->query($sql) === TRUE) {
        $mensagem = "Evento cadastrado com sucesso! Ele passará pela nossa curadoria e logo estará na agenda.";
    } else {
        $mensagem = "Erro ao cadastrar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Evento | Revista Ethos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Poppins', sans-serif; margin: 0; }
        .form-container { max-width: 800px; margin: 180px auto 60px; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 5px solid #8C0303; }
        h2 { color: #8C0303; margin-top: 0; display: flex; align-items: center; gap: 10px; }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.95rem; }
        .form-control { width: 100%; padding: 14px; border: 2px solid #eee; border-radius: 8px; font-family: inherit; font-size: 1rem; box-sizing: border-box; background: #fafafa; transition: 0.3s; }
        .form-control:focus { border-color: #8C0303; outline: none; background: #fff; }
        
        .btn-submit { background: #8C0303; color: white; border: none; padding: 18px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; transition: 0.3s; text-transform: uppercase; margin-top: 10px; }
        .btn-submit:hover { background: #000; }
        
        .alert-success { background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #c3e6cb; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="form-container">
        <h2><i class="fas fa-calendar-plus"></i> Cadastrar Novo Evento</h2>
        <p style="color: #666; margin-bottom: 30px;">Preencha os dados abaixo. Após nossa equipe avaliar, ele aparecerá na agenda oficial do portal.</p>

        <?php if ($mensagem): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $mensagem ?></div>
            <a href="categoria.php?slug=eventhos" style="display:inline-block; margin-bottom:20px; color:#8C0303; font-weight:bold; text-decoration:none;"><i class="fas fa-arrow-left"></i> Voltar para a Agenda</a>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nome do Evento</label>
                <input type="text" name="titulo" class="form-control" placeholder="Ex: Festival de Inverno" required>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data_evento" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Horário</label>
                    <input type="time" name="hora_evento" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Local (Bairro / Cidade)</label>
                <input type="text" name="local_evento" class="form-control" placeholder="Ex: Praça Matriz - Barra Bonita" required>
            </div>

            <div class="form-group">
                <label>Breve Descrição</label>
                <textarea name="descricao" class="form-control" rows="4" placeholder="Atrações, valor do ingresso (se houver), classificação..." required></textarea>
            </div>

            <div class="form-group">
                <label>Banner ou Foto Oficial (Obrigatório)</label>
                <input type="file" name="imagem" class="form-control" accept="image/*" required>
            </div>

            <button type="submit" class="btn-submit">Enviar para Avaliação</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>