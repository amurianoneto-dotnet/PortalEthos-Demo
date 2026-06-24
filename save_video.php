<?php
// 1. Conexão direta (igual ao seu admin.php para não ter erro de caminho)
$conn = mysqli_connect("localhost", "root", "", "portal_ethos");
if (!$conn) { die("Erro de conexão: " . mysqli_connect_error()); }

mysqli_set_charset($conn, "utf8mb4");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo'])) {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $video_url = $_POST['video_url'];

    // Lógica para capturar o ID do YouTube
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match);
    $video_id = $match[1] ?? $video_url;

    $sql = "INSERT INTO ethos_play (titulo, video_id) VALUES ('$titulo', '$video_id')";

    if (mysqli_query($conn, $sql)) {
        header('Location: admin.php?view=radio&status=video_ok');
        exit();
    } else {
        echo "Erro ao salvar no banco: " . mysqli_error($conn);
    }
}

// Lógica para excluir vídeo
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql_del = "DELETE FROM ethos_play WHERE id = $id";
    
    if (mysqli_query($conn, $sql_del)) {
        header('Location: admin.php?view=radio&status=video_del');
        exit();
    } else {
        echo "Erro ao excluir: " . mysqli_error($conn);
    }
}
?>