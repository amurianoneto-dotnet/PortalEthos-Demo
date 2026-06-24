<?php
session_start();
include('includes/db.php');

// Verifica se é admin
if (!isset($_SESSION['admin_logado'])) {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $data_evento = $_POST['data_evento'];
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    
    // Configuração de Pastas
    $dir_original = "uploads/coberturas/";
    $dir_thumbs = "uploads/coberturas/thumbs/";
    
    // 1. Processa a Capa do Álbum
    $nome_capa = "";
    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === 0) {
        $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
        $nome_capa = "capa_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['capa']['tmp_name'], $dir_original . $nome_capa);
    }

    // 2. Cria o Álbum no Banco de Dados
    $sql_album = "INSERT INTO coberturas_albuns (titulo, data_evento, descricao, capa) 
                  VALUES ('$titulo', '$data_evento', '$descricao', '$nome_capa')";
    
    if (mysqli_query($conn, $sql_album)) {
        $album_id = mysqli_insert_id($conn); // Pega o ID do álbum que acabou de criar
        
        // 3. Processa as Múltiplas Fotos
        if (isset($_FILES['fotos'])) {
            $total_fotos = count($_FILES['fotos']['name']);
            
            for ($i = 0; $i < $total_fotos; $i++) {
                if ($_FILES['fotos']['error'][$i] === 0) {
                    
                    $nome_temp = $_FILES['fotos']['tmp_name'][$i];
                    $ext_foto = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));
                    $nome_arquivo = $album_id . "_" . time() . "_" . uniqid() . "." . $ext_foto;
                    
                    $caminho_final = $dir_original . $nome_arquivo;
                    $caminho_thumb = $dir_thumbs . $nome_arquivo;
                    
                    // Move a foto original
                    if (move_uploaded_file($nome_temp, $caminho_final)) {
                        
                        // ---- MÁGICA DE ENCOLHER (CRIAR MINIATURA THUMBNAIL) ----
                        // Largura fixa da miniatura: 400px (pra carregar super rápido)
                        $thumb_width = 400;
                        
                        list($width, $height) = getimagesize($caminho_final);
                        $thumb_height = floor($height * ($thumb_width / $width));
                        
                        $tmp_img = imagecreatetruecolor($thumb_width, $thumb_height);
                        
                        // Suporte para JPG, PNG e WebP
                        if ($ext_foto == 'jpg' || $ext_foto == 'jpeg') {
                            $src_img = imagecreatefromjpeg($caminho_final);
                            imagecopyresampled($tmp_img, $src_img, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
                            imagejpeg($tmp_img, $caminho_thumb, 80); // 80 de qualidade pra ficar leve
                        } elseif ($ext_foto == 'png') {
                            $src_img = imagecreatefrompng($caminho_final);
                            imagealphablending($tmp_img, false);
                            imagesavealpha($tmp_img, true);
                            imagecopyresampled($tmp_img, $src_img, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
                            imagepng($tmp_img, $caminho_thumb, 8);
                        } elseif ($ext_foto == 'webp') {
                            $src_img = imagecreatefromwebp($caminho_final);
                            imagecopyresampled($tmp_img, $src_img, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
                            imagewebp($tmp_img, $caminho_thumb, 80);
                        }
                        
                        imagedestroy($tmp_img);
                        if (isset($src_img)) { imagedestroy($src_img); }
                        // ---------------------------------------------------------
                        
                        // Salva no banco de dados que essa foto pertence a este álbum
                        mysqli_query($conn, "INSERT INTO coberturas_fotos (album_id, arquivo) VALUES ($album_id, '$nome_arquivo')");
                    }
                }
            }
        }
        
        echo "<script>alert('Álbum e fotos publicados com sucesso!'); window.location.href='admin.php?view=coberturas';</script>";
    } else {
        echo "Erro ao salvar álbum: " . mysqli_error($conn);
    }
} else {
    header("Location: admin.php");
}
?>