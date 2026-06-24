<?php
include('includes/db.php');

if(isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = intval($_GET['status']);
    
    mysqli_query($conn, "UPDATE banners SET status = $status WHERE id = $id");
}

header("Location: admin.php?view=ads");
exit;
?>