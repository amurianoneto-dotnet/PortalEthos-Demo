<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/db.php');
include('functions.php'); 

$album_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coberturas Fotográficas | Revista Ethos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .coberturas-header { background: #8C0303; color: #fff; padding: 40px 20px; text-align: center; margin-bottom: 40px; }
        .coberturas-header h1 { font-family: 'Poppins', sans-serif; font-weight: 900; margin: 0; font-size: 2.5rem; text-transform: uppercase; }
        .coberturas-header p { margin: 10px 0 0; font-size: 1.1rem; opacity: 0.9; }
        
        .albuns-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; padding: 0 20px 60px; max-width: 1200px; margin: 0 auto; }
        .album-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.05); text-decoration: none; color: #333; transition: 0.3s; display: block; border: 1px solid #eee; }
        .album-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(140, 3, 3, 0.15); }
        .album-capa { width: 100%; height: 220px; object-fit: cover; border-bottom: 4px solid #8C0303; }
        .album-info { padding: 20px; }
        .album-info h3 { margin: 0 0 10px; font-family: 'Poppins', sans-serif; font-size: 1.2rem; color: #111; }
        .album-info span { color: #888; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 5px; }

        .fotos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding: 0 20px 60px; max-width: 1200px; margin: 0 auto; }
        .foto-item { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .foto-item:hover { transform: scale(1.03); opacity: 0.9; box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        
        /* CHAMADA ELEGANTE (LÁ NO FINAL) */
        .cta-elegante { background: #111; border-radius: 8px; border-left: 5px solid #8C0303; padding: 50px 40px; margin: 20px auto 80px; max-width: 900px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .cta-elegante h2 { color: #fff; font-family: 'Poppins', sans-serif; margin: 0; font-size: 1.8rem; font-weight: 800; }
        .cta-elegante p { color: #aaa; margin: 0; font-size: 1.05rem; max-width: 650px; line-height: 1.6; }
        .btn-whats-elegante { background: transparent; color: #25D366; border: 2px solid #25D366; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: 700; font-family: 'Poppins', sans-serif; font-size: 1rem; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; margin-top: 10px; text-transform: uppercase; }
        .btn-whats-elegante:hover { background: #25D366; color: #111; box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3); }

        .lightbox { display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .lightbox img { max-width: 90%; max-height: 90vh; border-radius: 8px; box-shadow: 0 0 30px rgba(0,0,0,0.5); border: 2px solid #fff; transition: 0.2s; }
        .lightbox-close { position: absolute; top: 20px; right: 30px; color: #fff; font-size: 2.5rem; cursor: pointer; transition: 0.2s; z-index: 10000; }
        .lightbox-close:hover { color: #8C0303; transform: scale(1.1); }
        
        .lightbox-nav { position: absolute; top: 50%; transform: translateY(-50%); color: white; font-size: 2.5rem; background: rgba(0,0,0,0.5); border: none; padding: 15px 20px; cursor: pointer; border-radius: 5px; transition: 0.3s; z-index: 10000; }
        .lightbox-nav:hover { background: #8C0303; }
        .lightbox-prev { left: 20px; }
        .lightbox-next { right: 20px; }

        .btn-voltar { display: inline-block; background: #111; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-bottom: 20px; transition: 0.3s; }
        .btn-voltar:hover { background: #8C0303; }
        
        @media(max-width: 768px) {
            .cta-elegante { padding: 30px 20px; margin: 20px 15px 60px; }
            .cta-elegante h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

    <?php include('includes/header.php'); ?>

    <?php if ($album_id == 0): ?>
        <header class="coberturas-header">
            <h1><i class="fas fa-camera"></i> Coberturas Eventhos</h1>
            <p>Confira os melhores cliques das festas e eventos de nossa região.</p>
        </header>

        <main class="albuns-grid">
            <?php
            $sql_albuns = "SELECT * FROM coberturas_albuns WHERE status = 'ativo' ORDER BY data_evento DESC";
            $res_albuns = mysqli_query($conn, $sql_albuns);

            if ($res_albuns && mysqli_num_rows($res_albuns) > 0):
                while ($album = mysqli_fetch_assoc($res_albuns)):
                    $id_a = $album['id'];
                    $qtd_fotos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total FROM coberturas_fotos WHERE album_id = $id_a"))['total'];
            ?>
                <a href="coberturas.php?id=<?= $album['id'] ?>" class="album-card">
                    <img src="uploads/coberturas/<?= $album['capa'] ?>" alt="<?= htmlspecialchars($album['titulo']) ?>" class="album-capa">
                    <div class="album-info">
                        <h3><?= htmlspecialchars($album['titulo']) ?></h3>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($album['data_evento'])) ?></span>
                            <span style="color: #8C0303;"><i class="fas fa-images"></i> <?= $qtd_fotos ?> fotos</span>
                        </div>
                    </div>
                </a>
            <?php 
                endwhile;
            else:
            ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: #666;">
                    <i class="fas fa-images fa-3x" style="color: #ddd; margin-bottom: 15px;"></i>
                    <h2>Nenhuma cobertura disponível ainda.</h2>
                    <p>Fique ligado! Em breve teremos muitas fotos por aqui.</p>
                </div>
            <?php endif; ?>
        </main>

        <div class="cta-elegante">
            <h2>Gostou dos nossos cliques?</h2>
            <p>A equipe da Revista Ethos faz a cobertura fotográfica completa da sua festa, show ou evento corporativo com um padrão impecável de qualidade.</p>
            <a href="https://api.whatsapp.com/send?phone=5514996753805&text=Olá! Gostaria de saber mais sobre a cobertura fotográfica da Revista Ethos para o meu evento." target="_blank" class="btn-whats-elegante">
                <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> Fale com nossa equipe
            </a>
        </div>

    <?php else: ?>
        <?php
        $sql_album = "SELECT * FROM coberturas_albuns WHERE id = $album_id";
        $res_album = mysqli_query($conn, $sql_album);
        $album_atual = mysqli_fetch_assoc($res_album);

        if (!$album_atual) {
            echo "<script>window.location.href='coberturas.php';</script>";
            exit;
        }
        ?>
        
        <header class="coberturas-header" style="background: linear-gradient(rgba(140, 3, 3, 0.9), rgba(17, 17, 17, 0.9)), url('uploads/coberturas/<?= $album_atual['capa'] ?>') center/cover;">
            <h1><?= htmlspecialchars($album_atual['titulo']) ?></h1>
            <p><i class="far fa-calendar-alt"></i> Publicado em: <?= date('d/m/Y', strtotime($album_atual['data_evento'])) ?></p>
            <?php if(!empty($album_atual['descricao'])): ?>
                <p style="max-width: 600px; margin: 15px auto 0; font-size: 0.95rem; font-style: italic;">"<?= htmlspecialchars($album_atual['descricao']) ?>"</p>
            <?php endif; ?>
        </header>

        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <a href="coberturas.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar para os álbuns</a>
        </div>

        <main class="fotos-grid">
            <?php
            $sql_fotos = "SELECT * FROM coberturas_fotos WHERE album_id = $album_id ORDER BY id ASC";
            $res_fotos = mysqli_query($conn, $sql_fotos);
            
            $lista_fotos_js = [];
            $index = 0; 

            if ($res_fotos && mysqli_num_rows($res_fotos) > 0):
                while ($foto = mysqli_fetch_assoc($res_fotos)):
                    $caminho_original = 'uploads/coberturas/' . $foto['arquivo'];
                    $lista_fotos_js[] = $caminho_original;
            ?>
                <img src="uploads/coberturas/thumbs/<?= $foto['arquivo'] ?>" 
                     alt="Foto da Cobertura" 
                     class="foto-item" 
                     onclick="abrirLightbox(<?= $index ?>)">
            <?php 
                    $index++;
                endwhile;
            endif; 
            ?>
        </main>

        <div id="meuLightbox" class="lightbox">
            <span class="lightbox-close" onclick="fecharLightbox()" title="Fechar (ESC)">&times;</span>
            <button class="lightbox-nav lightbox-prev" onclick="mudarFoto(-1)" title="Foto Anterior (Seta Esquerda)">&#10094;</button>
            <img id="imgLightbox" src="" alt="Foto Ampliada">
            <button class="lightbox-nav lightbox-next" onclick="mudarFoto(1)" title="Próxima Foto (Seta Direita)">&#10095;</button>
        </div>

        <script>
            let fotosArray = <?php echo json_encode($lista_fotos_js); ?>;
            let fotoAtualIndex = 0; 

            function abrirLightbox(index) {
                fotoAtualIndex = index;
                document.getElementById("imgLightbox").src = fotosArray[fotoAtualIndex];
                document.getElementById("meuLightbox").style.display = "flex";
                document.addEventListener('keydown', controleTeclado);
            }

            function fecharLightbox() {
                document.getElementById("meuLightbox").style.display = "none";
                document.removeEventListener('keydown', controleTeclado);
            }

            function mudarFoto(direcao) {
                fotoAtualIndex += direcao;
                if (fotoAtualIndex >= fotosArray.length) { fotoAtualIndex = 0; }
                if (fotoAtualIndex < 0) { fotoAtualIndex = fotosArray.length - 1; }
                document.getElementById("imgLightbox").src = fotosArray[fotoAtualIndex];
            }

            function controleTeclado(evento) {
                if (evento.key === "ArrowRight") { mudarFoto(1); } 
                else if (evento.key === "ArrowLeft") { mudarFoto(-1); } 
                else if (evento.key === "Escape") { fecharLightbox(); }
            }

            document.getElementById("meuLightbox").addEventListener("click", function(e) {
                if(e.target === this) { fecharLightbox(); }
            });
        </script>

    <?php endif; ?>

    <?php include('includes/footer.php'); ?>

</body>
</html>