<?php
// Configura o retorno como JSON
header('Content-Type: application/json');

// 1. Caminho físico para o PHP salvar a foto (ajustado para sua estrutura)
$upload_dir = 'assets/img/editor/';

// Cria a pasta se ela não existir
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['upload'])) {
    $file = $_FILES['upload'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $novo_nome = 'img_' . time() . '.' . $ext;
    
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $novo_nome)) {
        // 2. Caminho que o navegador vai usar para EXIBIR a foto
        // Se o seu admin estiver numa pasta separada, pode precisar de ../
        echo json_encode([
            'uploaded' => true,
            'url' => 'assets/img/editor/' . $novo_nome
        ]);
    } else {
        echo json_encode([
            'uploaded' => false,
            'error' => ['message' => 'Não foi possível mover o arquivo para a pasta.']
        ]);
    }
}
?>