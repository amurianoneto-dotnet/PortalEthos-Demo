<?php
// includes/db.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "portal_ethos";

$conn = mysqli_connect($host, $user, $pass, $db);

// Teste de conexão (opcional)
if (!$conn) {
    die("Falha na conexão: " . mysqli_connect_error());
}

// Ajustar para aceitar acentos do banco
mysqli_set_charset($conn, "utf8");
?>