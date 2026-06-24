<?php
include('includes/db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id > 0) {
    // Busca a imagem para deletar o arquivo do servidor também (limpeza)
    $busca = mysqli_query($conn, "SELECT imagem_capa FROM noticias WHERE id = $id");
    $img = mysqli_fetch_assoc($busca);
    
    if($img['imagem_capa'] != 'default.jpg') {
        unlink("assets/img/" . $img['imagem_capa']);
    }

    // Deleta a notícia (as fotos da galeria serão deletadas se você usou ON DELETE CASCADE no SQL de ontem)
    $sql = "DELETE FROM noticias WHERE id = $id";
    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('Notícia excluída!'); window.location.href='admin.php';</script>";
    }
}
?>