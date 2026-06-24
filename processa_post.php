<?php
include('includes/db.php');
include('functions.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Captura e limpa os dados
    // O mysqli_real_escape_string é essencial para o CKEditor não quebrar o banco com as aspas do HTML
    $titulo       = mysqli_real_escape_string($conn, $_POST['titulo']);
    $subtitulo    = mysqli_real_escape_string($conn, $_POST['subtitulo']);
    $descricao    = mysqli_real_escape_string($conn, $_POST['descricao']);
    $categoria    = mysqli_real_escape_string($conn, normalizarSlugCategoria($_POST['categoria'] ?? ''));
    $keywords     = mysqli_real_escape_string($conn, $_POST['keywords']);
    $conteudo     = mysqli_real_escape_string($conn, $_POST['conteudo']);
    
    // NOVO: Captura o ID do autor escolhido no painel
    $autor_id     = isset($_POST['autor_id']) ? (int)$_POST['autor_id'] : 1;
    
    // Define a pasta de destino das imagens
    $target_dir = "assets/img/";
    if(!is_dir($target_dir)){ 
        mkdir($target_dir, 0777, true); 
    }

    // 2. Lógica de Upload da Imagem de Capa (Destaque)
    $nome_imagem = "default.jpg";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $nome_imagem = "capa_" . time() . "." . $extensao;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $target_dir . $nome_imagem);
    }

    // 3. Insere a notícia no banco INCLUINDO O autor_id
    $sql = "INSERT INTO noticias (titulo, subtitulo, descricao, conteudo, imagem_capa, categoria_id, keywords, autor_id, data_publicacao) 
            VALUES ('$titulo', '$subtitulo', '$descricao', '$conteudo', '$nome_imagem', '$categoria', '$keywords', $autor_id, NOW())";

    if (mysqli_query($conn, $sql)) {
        // Pega o ID da notícia que acabou de ser criada para a galeria
        $noticia_id = mysqli_insert_id($conn);

        // 4. Lógica de Upload da Galeria (Se houver múltiplas fotos separadas)
        if (!empty($_FILES['galeria']['name'][0])) {
            $galeria_dir = $target_dir . "galeria/";
            if(!is_dir($galeria_dir)){ 
                mkdir($galeria_dir, 0777, true); 
            }

            foreach ($_FILES['galeria']['name'] as $key => $name) {
                if ($_FILES['galeria']['error'][$key] == 0) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $novo_nome_galeria = "gal_" . time() . "_" . rand(100, 999) . "." . $ext;
                    
                    if (move_uploaded_file($_FILES['galeria']['tmp_name'][$key], $galeria_dir . $novo_nome_galeria)) {
                        $sql_galeria = "INSERT INTO noticias_galeria (noticia_id, arquivo) VALUES ($noticia_id, '$novo_nome_galeria')";
                        mysqli_query($conn, $sql_galeria);
                    }
                }
            }
        }

        echo "<script>alert('Matéria publicada com sucesso!'); window.location.href='admin.php?view=noticias';</script>";
    } else {
        echo "Erro ao publicar no banco de dados: " . mysqli_error($conn);
    }
}
?>