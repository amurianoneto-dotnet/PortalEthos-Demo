<?php
session_start();
include('includes/db.php');

// 1. O SEGURANÇA: Só deixa passar quem fez login
if (!isset($_SESSION['leitor_id'])) {
    die("<script>alert('Você precisa estar logado para comentar!'); window.history.back();</script>");
}

// 2. RECEBENDO A ENCOMENDA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $usuario_id = (int)$_SESSION['leitor_id'];
    
    // AQUI ESTÁ A MÁGICA: Puxando o 'noticia_id' igualzinho está no seu noticia.php
    $post_id = isset($_POST['noticia_id']) ? (int)$_POST['noticia_id'] : 0;
    
    $comentario = mysqli_real_escape_string($conn, trim($_POST['comentario']));

    // 3. VERIFICAÇÃO: Não deixa mandar comentário vazio
    if ($post_id > 0 && !empty($comentario)) {
        
        // 4. SALVANDO NO BANCO DE DADOS
        $sql = "INSERT INTO comentarios (post_id, usuario_id, comentario, status) 
                VALUES ($post_id, $usuario_id, '$comentario', 'aprovado')";
        
        if (mysqli_query($conn, $sql)) {
            // Sucesso! Volta pra matéria. (Já coloquei o nome correto do seu arquivo: noticia.php)
            header("Location: noticia.php?id=$post_id#comentarios");
            exit;
        } else {
            echo "Erro do banco de dados: " . mysqli_error($conn);
        }
    } else {
        // Se tiver vazio, só manda voltar calado
        header("Location: noticia.php?id=$post_id");
        exit;
    }
} else {
    // Se alguém tentar acessar esse arquivo direto pela URL, chuta pra Home
    header("Location: index.php");
    exit;
}
?>