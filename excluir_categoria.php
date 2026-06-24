<?php
include('includes/db.php');

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Mude 'categorias' se o nome da sua tabela for diferente
    $sql = "DELETE FROM categorias WHERE id = $id";
    mysqli_query($conn, $sql);
}

header("Location: admin.php?view=categorias");
exit;
?>