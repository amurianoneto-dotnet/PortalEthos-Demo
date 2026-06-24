<?php
include('includes/db.php');

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Busca o nome do arquivo para deletar da pasta física
    $query = mysqli_query($conn, "SELECT arquivo_arte FROM banners WHERE id = $id");
    if($row = mysqli_fetch_assoc($query)) {
        $caminho_arquivo = 'assets/img/ads/' . $row['arquivo_arte'];
        if(file_exists($caminho_arquivo)) {
            unlink($caminho_arquivo); // Apaga a imagem
        }
    }
    
    // Deleta do banco
    mysqli_query($conn, "DELETE FROM banners WHERE id = $id");
}

header("Location: admin.php?view=ads");
exit;
?>