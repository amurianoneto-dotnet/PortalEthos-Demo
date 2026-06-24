<?php
include('includes/db.php');

// Garante que a comunicação com o banco seja em UTF-8
mysqli_set_charset($conn, "utf8mb4");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Captura e limpa os IDs
    $ethos_play_id = mysqli_real_escape_string($conn, $_POST['ethos_play_id']);
    $aovivo_id     = mysqli_real_escape_string($conn, $_POST['aovivo_id']);

    // 2. Executa a atualização ou inserção (Sempre no ID 1)
    $sql = "INSERT INTO configuracoes (id, ethos_play_id, aovivo_id) 
            VALUES (1, '$ethos_play_id', '$aovivo_id') 
            ON DUPLICATE KEY UPDATE 
            ethos_play_id = '$ethos_play_id', 
            aovivo_id = '$aovivo_id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Mídias atualizadas com sucesso!'); 
                window.location.href='admin.php?view=radio';
              </script>";
    } else {
        // Se der erro aqui, é quase certeza que as colunas não existem no banco
        echo "<h2>Erro ao atualizar mídias:</h2> " . mysqli_error($conn);
        echo "<br><br><strong>DICA:</strong> Execute este comando no seu SQL do phpMyAdmin:<br>";
        echo "<code>ALTER TABLE configuracoes ADD COLUMN ethos_play_id VARCHAR(255), ADD COLUMN aovivo_id VARCHAR(255);</code>";
    }
}
?>