<?php
session_start();
include 'config.php';

$user = $_POST['usuario'];
$pass = $_POST['senha'];

$sql = "SELECT * FROM usuarios WHERE usuario = :user LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user' => $user]);

$usuario = $stmt->fetch();

if($usuario && password_verify($pass, $usuario['senha'])){
    $_SESSION['logado'] = true;
    $_SESSION['usuario'] = $usuario['usuario'];

    header("Location: admin.php");
    exit;
} else {
    echo "Usuário ou senha inválidos";
}