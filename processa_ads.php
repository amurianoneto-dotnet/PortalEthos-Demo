<?php
include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Captura os dados do formulário de Publicidade    
    $cliente   = mysqli_real_escape_string($conn, $_POST['cliente']);
    $tamanho   = mysqli_real_escape_string($conn, $_POST['tamanho']);
    $exibicao  = mysqli_real_escape_string($conn, $_POST['exibicao']);
    $rotacao   = intval($_POST['rotacao']); // Garante que seja um número de 1 a 5    
    $url_link  = mysqli_real_escape_string($conn, $_POST['url']);

    // 2. Lógica de Upload da Arte do Banner    
    $nome_banner = "banner_default.jpg";
    if (isset($_FILES['arte']) && $_FILES['arte']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['arte']['name'], PATHINFO_EXTENSION));
        
        // Criamos um nome único para o arquivo não sobrescrever outro banner do mesmo cliente        
        $nome_banner = "ad_" . time() . "_" . rand(10, 99) . "." . $extensao;
        
        // Verifica se a pasta de banners existe, se não, cria        
        if(!is_dir("assets/img/ads/")){
            mkdir("assets/img/ads/", 0777, true);
        }
        
        move_uploaded_file($_FILES['arte']['tmp_name'], "assets/img/ads/" . $nome_banner);
    }

    // 3. Insere no banco de dados na tabela 'banners'    
    $sql = "INSERT INTO banners (cliente, tamanho, exibicao, rotacao, url_link, arquivo_arte, status) 
            VALUES ('$cliente', '$tamanho', '$exibicao', $rotacao, '$url_link', '$nome_banner', 1)";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Campanha ativada com sucesso no grupo de rotação $rotacao!'); 
                window.location.href='admin.php?view=ads';
              </script>";
    } else {
        echo "Erro ao ativar campanha: " . mysqli_error($conn);
        echo "<br><br>Certifique-se de que a tabela 'banners' existe com as colunas: cliente, tamanho, exibicao, rotacao, url_link e arquivo_arte.";
    }
}
?>