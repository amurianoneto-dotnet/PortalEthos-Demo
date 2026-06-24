<?php
session_start();
include('includes/db.php');

// Segurança em primeiro lugar
if (!isset($_SESSION['admin_logado'])) {
    die("Acesso negado.");
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // 1. Pega o nome da foto de capa para excluir o arquivo físico
    $res_capa = mysqli_query($conn, "SELECT capa FROM coberturas_albuns WHERE id = $id");
    if ($capa = mysqli_fetch_assoc($res_capa)) {
        $caminho_capa = "uploads/coberturas/" . $capa['capa'];
        if (file_exists($caminho_capa) && !empty($capa['capa'])) {
            unlink($caminho_capa); // O comando unlink() apaga o arquivo do computador/servidor
        }
    }

    // 2. Pega todas as fotos desse álbum para apagar as originais e as miniaturas (thumbs)
    $res_fotos = mysqli_query($conn, "SELECT arquivo FROM coberturas_fotos WHERE album_id = $id");
    if ($res_fotos) {
        while ($foto = mysqli_fetch_assoc($res_fotos)) {
            $caminho_original = "uploads/coberturas/" . $foto['arquivo'];
            $caminho_thumb = "uploads/coberturas/thumbs/" . $foto['arquivo'];
            
            if (file_exists($caminho_original)) unlink($caminho_original);
            if (file_exists($caminho_thumb)) unlink($caminho_thumb);
        }
    }

    // 3. Finalmente, exclui o álbum do banco de dados 
    // (Lembra daquele 'ON DELETE CASCADE' que criamos? Ele vai apagar automaticamente as linhas da tabela de fotos junto com o álbum)
    mysqli_query($conn, "DELETE FROM coberturas_albuns WHERE id = $id");

    echo "<script>alert('Álbum e todas as fotos foram excluídos do servidor com sucesso!'); window.location.href='admin.php?view=coberturas';</script>";
} else {
    header("Location: admin.php?view=coberturas");
}
?>