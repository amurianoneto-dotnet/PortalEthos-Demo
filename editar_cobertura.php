<?php
session_start();
include('includes/db.php');

// Segurança
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header('Location: login_admin.php');
    exit;
}

$id_album = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ========================================================
// 1. EXCLUIR UMA FOTO ESPECÍFICA DE DENTRO DO ÁLBUM
// ========================================================
if (isset($_GET['del_foto'])) {
    $id_foto = (int)$_GET['del_foto'];
    
    // Pega o nome do arquivo pra poder apagar da pasta
    $res_f = mysqli_query($conn, "SELECT arquivo FROM coberturas_fotos WHERE id = $id_foto");
    if ($f = mysqli_fetch_assoc($res_f)) {
        $arq_orig = "uploads/coberturas/" . $f['arquivo'];
        $arq_thumb = "uploads/coberturas/thumbs/" . $f['arquivo'];
        
        if (file_exists($arq_orig)) unlink($arq_orig);
        if (file_exists($arq_thumb)) unlink($arq_thumb);
        
        // Apaga do banco
        mysqli_query($conn, "DELETE FROM coberturas_fotos WHERE id = $id_foto");
    }
    header("Location: editar_cobertura.php?id=$id_album");
    exit;
}

// ========================================================
// 2. ATUALIZAR DADOS, CAPA E SUBIR NOVAS FOTOS
// ========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $data_evento = $_POST['data_evento'];
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    
    // Atualiza os textos primeiro
    mysqli_query($conn, "UPDATE coberturas_albuns SET titulo='$titulo', data_evento='$data_evento', descricao='$descricao' WHERE id=$id_album");

    // Trocar a Capa?
    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === 0) {
        // Primeiro apaga a capa velha
        $res_capa_velha = mysqli_query($conn, "SELECT capa FROM coberturas_albuns WHERE id=$id_album");
        $capa_velha = mysqli_fetch_assoc($res_capa_velha)['capa'];
        if (file_exists("uploads/coberturas/".$capa_velha)) { unlink("uploads/coberturas/".$capa_velha); }
        
        // Sobe a nova
        $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
        $nome_capa = "capa_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['capa']['tmp_name'], "uploads/coberturas/" . $nome_capa);
        
        mysqli_query($conn, "UPDATE coberturas_albuns SET capa='$nome_capa' WHERE id=$id_album");
    }

    // Adicionar NOVAS fotos ao álbum?
    if (isset($_FILES['fotos']) && $_FILES['fotos']['error'][0] === 0) {
        $total_fotos = count($_FILES['fotos']['name']);
        for ($i = 0; $i < $total_fotos; $i++) {
            if ($_FILES['fotos']['error'][$i] === 0) {
                $nome_temp = $_FILES['fotos']['tmp_name'][$i];
                $ext_foto = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));
                $nome_arquivo = $id_album . "_" . time() . "_" . uniqid() . "." . $ext_foto;
                
                $caminho_final = "uploads/coberturas/" . $nome_arquivo;
                $caminho_thumb = "uploads/coberturas/thumbs/" . $nome_arquivo;
                
                if (move_uploaded_file($nome_temp, $caminho_final)) {
                    // Mágica da Miniatura (Mesmo motor que usamos antes)
                    $thumb_width = 400;
                    list($width, $height) = getimagesize($caminho_final);
                    $thumb_height = floor($height * ($thumb_width / $width));
                    $tmp_img = imagecreatetruecolor($thumb_width, $thumb_height);
                    
                    if ($ext_foto == 'jpg' || $ext_foto == 'jpeg') {
                        $src_img = imagecreatefromjpeg($caminho_final);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
                        imagejpeg($tmp_img, $caminho_thumb, 80);
                    } elseif ($ext_foto == 'png') {
                        $src_img = imagecreatefrompng($caminho_final);
                        imagealphablending($tmp_img, false); imagesavealpha($tmp_img, true);
                        imagecopyresampled($tmp_img, $src_img, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
                        imagepng($tmp_img, $caminho_thumb, 8);
                    }
                    imagedestroy($tmp_img);
                    if (isset($src_img)) { imagedestroy($src_img); }
                    
                    mysqli_query($conn, "INSERT INTO coberturas_fotos (album_id, arquivo) VALUES ($id_album, '$nome_arquivo')");
                }
            }
        }
    }
    echo "<script>alert('Álbum atualizado com sucesso!'); window.location.href='editar_cobertura.php?id=$id_album';</script>";
}

// Busca os dados do álbum pra preencher a tela
$res_album = mysqli_query($conn, "SELECT * FROM coberturas_albuns WHERE id = $id_album");
$album = mysqli_fetch_assoc($res_album);
if (!$album) { die("Álbum não encontrado!"); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cobertura | ETHOS ADM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mesmos estilos globais do seu admin.php pra ficar igualzinho */
        :root { --vermelho: #ce1212; --preto: #0f0f0f; --fundo: #f4f7f6; --borda: #e0e0e0; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background: var(--fundo); height: 100vh; color: #333; }
        .sidebar { width: 280px; background: var(--preto); color: #fff; padding: 30px 20px; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar h2 { color: var(--vermelho); text-align: center; font-weight: 900; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .nav-link { color: #aaa; text-decoration: none; padding: 15px; border-radius: 10px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; font-weight: 600; transition: 0.3s; }
        .nav-link.active, .nav-link:hover { background: var(--vermelho); color: #fff; }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .card { background: #fff; padding: 35px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--borda); margin-bottom: 30px; }
        .form-group { margin-bottom: 25px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        label { display: block; font-weight: 800; margin-bottom: 10px; color: #222; font-size: 0.85rem; text-transform: uppercase; }
        input, select, textarea { width: 100%; padding: 14px; border: 1px solid var(--borda); border-radius: 8px; font-size: 0.95rem; }
        .btn-save { background: var(--vermelho); color: #fff; border: none; padding: 20px; border-radius: 10px; font-weight: 900; cursor: pointer; width: 100%; text-transform: uppercase; transition: 0.3s; }
        .btn-save:hover { background: #000; }
        
        /* Estilo da grade de fotos pra apagar */
        .grid-fotos-edit { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
        .foto-edit-box { position: relative; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .foto-edit-box img { width: 100%; height: 120px; object-fit: cover; display: block; }
        .btn-del-foto { position: absolute; top: 5px; right: 5px; background: rgba(255,0,0,0.8); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: bold; transition: 0.2s; }
        .btn-del-foto:hover { background: red; transform: scale(1.1); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h2>ETHOS ADM</h2>
        <a href="admin.php?view=coberturas" class="nav-link"><i class="fas fa-arrow-left"></i> VOLTAR AOS ÁLBUNS</a>
    </aside>

    <main class="main-content">
        <div class="card">
            <h3 style="font-weight:900; margin-bottom:25px; color: var(--vermelho);"><i class="fas fa-edit"></i> Editando: <?= htmlspecialchars($album['titulo']) ?></h3>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nome do Evento</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($album['titulo']) ?>" required>
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Data do Evento</label>
                        <input type="date" name="data_evento" value="<?= $album['data_evento'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Substituir Capa (Deixe vazio para manter a atual)</label>
                        <input type="file" name="capa" accept="image/*">
                        <div style="margin-top: 10px;">
                            <img src="uploads/coberturas/<?= $album['capa'] ?>" style="width: 100px; border-radius: 5px;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" rows="2"><?= htmlspecialchars($album['descricao']) ?></textarea>
                </div>

                <div class="form-group" style="background: #f9f9f9; padding: 20px; border: 2px dashed #bbb; border-radius: 8px;">
                    <label style="color: #2196F3;"><i class="fas fa-plus-circle"></i> Adicionar Mais Fotos ao Álbum</label>
                    <input type="file" name="fotos[]" multiple accept="image/*" style="border: none;">
                </div>

                <button type="submit" class="btn-save">SALVAR ALTERAÇÕES</button>
            </form>

            <h3 style="font-weight:900; margin-top:50px; border-top: 1px solid #eee; padding-top: 30px;">Gerenciar Fotos Deste Álbum</h3>
            <p style="color: #666; font-size: 0.9rem;">Clique no X vermelho para apagar uma foto permanentemente.</p>
            
            <div class="grid-fotos-edit">
                <?php
                $res_fotos = mysqli_query($conn, "SELECT * FROM coberturas_fotos WHERE album_id = $id_album ORDER BY id DESC");
                while ($f = mysqli_fetch_assoc($res_fotos)):
                ?>
                    <div class="foto-edit-box">
                        <img src="uploads/coberturas/thumbs/<?= $f['arquivo'] ?>" alt="foto">
                        <a href="?id=<?= $id_album ?>&del_foto=<?= $f['id'] ?>" class="btn-del-foto" onclick="return confirm('Apagar esta foto para sempre?')" title="Apagar Foto">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </main>
</body>
</html>