<?php
// Inicia a sessão no topo para o sistema de login funcionar depois
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('includes/db.php');
include('functions.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// NOVA QUERY: Busca a notícia e já junta com a tabela de autores!
$query = "SELECT n.*, a.nome AS autor_nome, a.cargo AS autor_cargo, a.biografia AS autor_bio, a.foto AS autor_foto, a.instagram AS autor_instagram 
          FROM noticias n 
          LEFT JOIN autores a ON n.autor_id = a.id 
          WHERE n.id = $id LIMIT 1";
$res = mysqli_query($conn, $query);
$noticia = $res ? mysqli_fetch_assoc($res) : null;

if (!$noticia) {
    header('Location: index.php');
    exit;
}

// ==========================================
// LÓGICA DO AUTOR (CORRIGIDA PARA CONVIDADOS)
// ==========================================
$autor_id_banco = isset($noticia['autor_id']) ? (int)$noticia['autor_id'] : 1;

if ($autor_id_banco == 1 || empty($noticia['autor_nome'])) {
    // É a equipe padrão da Revista
    $autor_nome = 'Redação Ethos';
    $autor_cargo = 'Equipe Editorial';
    $autor_bio = 'Equipe oficial de jornalismo da Revista ETHOS.';
    $autor_foto = '';
    $autor_insta = ''; 
} else {
    // É um colunista ou convidado específico
    $autor_nome = $noticia['autor_nome'];
    $autor_cargo = $noticia['autor_cargo'];
    $autor_bio = $noticia['autor_bio']; // Se tiver vazio, vai ficar vazio!
    $autor_foto = !empty($noticia['autor_foto']) ? 'uploads/autores/' . $noticia['autor_foto'] : '';
    $autor_insta = $noticia['autor_instagram'];
}
// ==========================================

$query_galeria = "SELECT arquivo FROM noticias_galeria WHERE noticia_id = $id ORDER BY id ASC";
$res_galeria = mysqli_query($conn, $query_galeria);
$galeria = [];
if ($res_galeria) {
    while ($foto = mysqli_fetch_assoc($res_galeria)) {
        $galeria[] = $foto;
    }
}

$categoria = buscarCategoriaPorSlug($conn, $noticia['categoria_id'] ?? '');
$categoriaSlug = $categoria['slug'] ?? normalizarSlugCategoria($noticia['categoria_id'] ?? '');
$categoriaNome = $categoria['nome'] ?? obterNomeCategoria($noticia['categoria_id'] ?? '', 'Revista Ethos');
$label = obterLabelNoticia($noticia);
$resumo = $noticia['descricao'] ?: $noticia['subtitulo'] ?: resumirTexto($noticia['conteudo'], 180);

$categoriaBusca = mysqli_real_escape_string($conn, $noticia['categoria_id'] ?? '');
$query_rel = "SELECT id, titulo, subtitulo, imagem_capa, categoria_id, subcategoria, data_publicacao
              FROM noticias
              WHERE id != $id
              ORDER BY (categoria_id = '$categoriaBusca') DESC, data_publicacao DESC
              LIMIT 4";
$res_rel = mysqli_query($conn, $query_rel);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?> | ETHOS</title>
    <meta name="description" content="<?php echo htmlspecialchars($resumo, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilo da Caixinha do Autor */
        .author-box {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f9f9f9;
            padding: 30px;
            border-radius: 12px;
            border-left: 5px solid #8C0303;
            margin: 40px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
        .author-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .author-avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #888;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
        }
        .author-info h4 {
            margin: 0 0 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            color: #111;
        }
        .author-info span {
            display: block;
            font-size: 0.85rem;
            color: #8C0303;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .author-info p {
            margin: 0;
            font-size: 0.95rem;
            color: #555;
            line-height: 1.6;
        }
        .author-social {
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #888;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: 0.2s;
        }
        .author-social:hover {
            color: #E1306C; /* Cor do Insta */
        }
        
        @media(max-width: 600px){
            .author-box { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <?php include('includes/header.php'); ?>

    <main class="container article-page">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <?php if (!empty($categoriaSlug)): ?>
                <span>/</span>
                <a href="categoria.php?slug=<?php echo urlencode($categoriaSlug); ?>"><?php echo htmlspecialchars($categoriaNome, ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endif; ?>
            <span>/</span>
            <span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
        </nav>

        <section class="article-hero">
            <div class="article-hero__content">
                <span class="badge-categoria"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                <h1 class="article-title"><?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?></h1>

                <?php if (!empty($noticia['subtitulo'])): ?>
                    <p class="article-subtitle"><?php echo htmlspecialchars($noticia['subtitulo'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <div class="article-meta">
                    <span><i class="far fa-calendar"></i> <?php echo formatarDataCompleta($noticia['data_publicacao']); ?></span>
                    
                    <span><i class="fas fa-pen-nib"></i> Por <?= htmlspecialchars($autor_nome) ?></span>
                    
                    <span><i class="far fa-folder-open"></i> <?php echo htmlspecialchars($categoriaNome, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <?php 
                $link_materia = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
                $titulo_materia = urlencode($noticia['titulo']);
                ?>
                <div style="margin-top: 15px; display: flex; gap: 12px; align-items: center;">
                    <span style="font-weight: 700; color: #888; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Compartilhar:</span>
                    <a href="https://api.whatsapp.com/send?text=<?= $titulo_materia ?> - <?= $link_materia ?>" target="_blank" style="color: #25D366; font-size: 1.2rem; transition: 0.2s;" title="WhatsApp" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $link_materia ?>" target="_blank" style="color: #1877F2; font-size: 1.2rem; transition: 0.2s;" title="Facebook" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= $link_materia ?>&text=<?= $titulo_materia ?>" target="_blank" style="color: #000; font-size: 1.2rem; transition: 0.2s;" title="X (Twitter)" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"><i class="fab fa-x-twitter"></i></a>
                </div>
            </div>

            <div class="article-hero__image">
                <img src="<?php echo obterImagemNoticia($noticia['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </section>

        <section class="article-layout">
            <article class="article-main">
                <?php if (!empty($resumo)): ?>
                    <div class="article-summary">
                        <strong>Resumo:</strong> <?php echo htmlspecialchars($resumo, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="article-content">
                    <?php echo $noticia['conteudo']; ?>
                </div>

                <div class="author-box">
                    <?php if(!empty($autor_foto) && file_exists($autor_foto)): ?>
                        <img src="<?= $autor_foto ?>" alt="Foto do Autor" class="author-avatar">
                    <?php else: ?>
                        <div class="author-avatar-placeholder"><i class="fas fa-user-edit"></i></div>
                    <?php endif; ?>
                    
                    <div class="author-info">
                        <h4><?= htmlspecialchars($autor_nome) ?></h4>
                        
                        <?php if(!empty($autor_cargo)): ?>
                            <span><?= htmlspecialchars($autor_cargo) ?></span>
                        <?php endif; ?>

                        <?php if(!empty($autor_bio)): ?>
                            <p><?= htmlspecialchars($autor_bio) ?></p>
                        <?php endif; ?>
                        
                        <?php if(!empty($autor_insta)): ?>
                            <a href="https://instagram.com/<?= str_replace('@', '', $autor_insta) ?>" target="_blank" class="author-social">
                                <i class="fab fa-instagram" style="font-size: 1.2rem;"></i> <?= htmlspecialchars($autor_insta) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin: 40px 0; text-align: center; width: 100%; overflow: hidden;">
                    <?php exibirPublicidade($conn, 'materias', 'retangulo'); ?>
                </div>

                <?php if (!empty($galeria)): ?>
                    <section class="article-gallery">
                        <div class="section-heading">
                            <h2>Galeria de fotos</h2>
                            <span><?php echo count($galeria); ?> imagens</span>
                        </div>
                        <div class="gallery-grid">
                            <?php foreach ($galeria as $foto): ?>
                                <figure class="gallery-card">
                                    <img src="assets/img/galeria/<?php echo htmlspecialchars($foto['arquivo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Galeria da matéria <?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section style="margin-top: 50px; border-top: 2px solid #f0f0f0; padding-top: 30px;">
                    <h3 style="font-family: 'Poppins', sans-serif; color: #8C0303; font-size: 1.3rem; margin-bottom: 20px;">
                        <i class="far fa-comments"></i> Comentários
                    </h3>

                    <?php 
                    // Verifica se o usuário está logado na sessão
                    $usuario_logado = isset($_SESSION['leitor_id']); 
                    ?>

                    <?php if($usuario_logado): ?>
                        <form action="processa_comentario.php" method="POST" style="margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 10px;">
                            <input type="hidden" name="noticia_id" value="<?= $id ?>">
                            <div style="display: flex; gap: 15px; align-items: flex-start;">
                                <div style="width: 40px; height: 40px; background: #8C0303; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div style="flex: 1;">
                                    <textarea name="comentario" rows="3" placeholder="O que você achou desta matéria?" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; margin-bottom: 10px; resize: vertical; box-sizing: border-box;" required></textarea>
                                    <button type="submit" style="background: #8C0303; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 0.85rem;">Publicar Comentário</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div style="background: #fdfdfd; padding: 30px 20px; border-radius: 10px; text-align: center; margin-bottom: 30px; border: 1px dashed #ccc;">
                            <i class="fas fa-lock" style="font-size: 2rem; color: #ccc; margin-bottom: 10px;"></i>
                            <h4 style="margin: 0 0 10px; font-family: 'Poppins', sans-serif; color: #333;">Quer participar da discussão?</h4>
                            <p style="color: #666; margin-bottom: 20px; font-size: 0.95rem;">Faça login ou crie uma conta gratuita na Revista Ethos para deixar o seu comentário.</p>
                            <a href="login.php" style="background: #8C0303; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: bold; display: inline-block; font-family: 'Poppins', sans-serif;">ENTRAR OU CADASTRAR</a>
                        </div>
                    <?php endif; ?>

                    <div id="comentarios-lista" style="margin-top: 30px;">
                        <?php
                        // Vai no banco e busca os comentários aprovados desta matéria, puxando o nome do leitor
                        $sql_comentarios = "SELECT c.*, u.nome FROM comentarios c 
                                            JOIN usuarios u ON c.usuario_id = u.id 
                                            WHERE c.post_id = $id AND c.status = 'aprovado' 
                                            ORDER BY c.data_registro DESC";
                        $res_comentarios = mysqli_query($conn, $sql_comentarios);

                        if ($res_comentarios && mysqli_num_rows($res_comentarios) > 0):
                            // Se tiver comentários, faz um loop para exibir todos
                            while ($coment = mysqli_fetch_assoc($res_comentarios)):
                        ?>
                                <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 20px; background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                                    <div style="width: 45px; height: 45px; background: #eee; color: #8C0303; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-family: 'Poppins', sans-serif; flex-shrink: 0; font-size: 1.2rem;">
                                        <?= strtoupper(substr($coment['nome'], 0, 1)) ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                                            <h5 style="margin: 0; font-size: 1rem; color: #111; font-family: 'Poppins', sans-serif;"><?= htmlspecialchars($coment['nome']) ?></h5>
                                            <small style="color: #999; font-size: 0.8rem;"><i class="far fa-clock"></i> <?= date('d/m/Y \à\s H:i', strtotime($coment['data_registro'])) ?></small>
                                        </div>
                                        <p style="margin: 0; color: #444; line-height: 1.6; font-size: 0.95rem;"><?= nl2br(htmlspecialchars($coment['comentario'])) ?></p>
                                    </div>
                                </div>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <div style="background: #fdfdfd; padding: 30px; border-radius: 10px; text-align: center; border: 1px dashed #ccc;">
                                <i class="far fa-comments" style="font-size: 2rem; color: #ddd; margin-bottom: 10px;"></i>
                                <p style="color: #888; margin: 0; font-size: 0.95rem;">Nenhum comentário ainda. Seja o primeiro a participar da discussão!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </article>

            <aside class="article-sidebar">
                <div class="sidebar-block">
                    <div class="section-heading">
                        <h2>Veja também</h2>
                    </div>

                    <div class="related-list">
                        <?php if ($res_rel && mysqli_num_rows($res_rel) > 0): ?>
                            <?php while ($rel = mysqli_fetch_assoc($res_rel)): ?>
                                <a class="related-card" href="noticia.php?id=<?php echo $rel['id']; ?>">
                                    <img src="<?php echo obterImagemNoticia($rel['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($rel['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div>
                                        <span><?php echo htmlspecialchars(obterLabelNoticia($rel), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <h3><?php echo htmlspecialchars($rel['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <small><?php echo formatarData($rel['data_publicacao']); ?></small>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="margin:0; color:#666;">Assim que novas matérias forem publicadas, elas aparecerão aqui.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sidebar-block" style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="section-heading">
                        <h2>Publicidade</h2>
                    </div>
                    <?php exibirPublicidade($conn, 'materias', 'quadrado'); ?>
                    <?php exibirPublicidade($conn, 'materias', 'skyscraper'); ?>
                </div>
            </aside>
        </section>
    </main>

    <?php include('includes/footer.php'); ?>

</body>
</html>