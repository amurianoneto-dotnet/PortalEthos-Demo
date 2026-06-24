<?php
include('includes/db.php');
include('functions.php');

$slugRecebido = isset($_GET['slug']) ? $_GET['slug'] : 'turismo';

// O TRUQUE DO CALENDÁRIO AQUI:
if ($slugRecebido === 'eventhos') {
    include 'eventhos_calendario.php';
    exit();
}

$categoria = buscarCategoriaPorSlug($conn, $slugRecebido);
$slug = $categoria['slug'] ?? normalizarSlugCategoria($slugRecebido);
$nomeCategoria = $categoria['nome'] ?? strtoupper(obterNomeCategoria($slugRecebido, strtoupper($slugRecebido)));

$slugsRelacionados = obterSlugsRelacionadosCategoria($slugRecebido);
$slugsEscapados = array_map(function ($item) use ($conn) {
    return "'" . mysqli_real_escape_string($conn, $item) . "'";
}, $slugsRelacionados);

// ==========================================
// LÓGICA DE PAGINAÇÃO
// ==========================================
$noticias_por_pagina = 10; // Quantas matérias aparecem por vez
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_atual < 1) $pagina_atual = 1;

$offset = ($pagina_atual - 1) * $noticias_por_pagina;

// Conta o total de notícias nessa categoria para saber quantas páginas teremos
$query_total = "SELECT COUNT(id) as total FROM noticias WHERE categoria_id IN (" . implode(', ', $slugsEscapados) . ")";
$res_total = mysqli_query($conn, $query_total);
$row_total = mysqli_fetch_assoc($res_total);
$total_noticias = $row_total['total'];
$total_paginas = ceil($total_noticias / $noticias_por_pagina);

// Busca as notícias com o LIMIT para a página atual
$query = "SELECT * FROM noticias WHERE categoria_id IN (" . implode(', ', $slugsEscapados) . ") ORDER BY data_publicacao DESC LIMIT $offset, $noticias_por_pagina";
$res = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nomeCategoria, ENT_QUOTES, 'UTF-8'); ?> | ETHOS</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos da Paginação */
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; }
        .page-link { background: #fff; border: 1px solid #ddd; color: #333; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: 600; font-family: 'Poppins', sans-serif; transition: 0.3s; }
        .page-link:hover { background: #f0f0f0; }
        .page-link.active { background: #8C0303; color: white; border-color: #8C0303; pointer-events: none; }
        .page-link.disabled { color: #ccc; pointer-events: none; background: #fafafa; border-color: #eee; }
    </style>
</head>
<body>

    <?php include('includes/header.php'); ?>

    <section class="category-hero">
        <div class="container">
            <span class="badge-categoria">Editoria</span>
            <h1><?php echo htmlspecialchars($nomeCategoria, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Notícias, entrevistas e coberturas da Revista Ethos nessa editoria.</p>
        </div>
    </section>

    <main class="container category-page">
        <section class="category-layout">
            <div class="news-list-container">
                <div class="news-list">
                    <?php if ($res && mysqli_num_rows($res) > 0): ?>
                        <?php while ($noticia = mysqli_fetch_assoc($res)): ?>
                            <article class="cat-item-card">
                                <a class="cat-img" href="noticia.php?id=<?php echo $noticia['id']; ?>">
                                    <img src="<?php echo obterImagemNoticia($noticia['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                                </a>
                                <div class="cat-info">
                                    <span class="tag"><?php echo htmlspecialchars(obterLabelNoticia($noticia), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <h2><a href="noticia.php?id=<?php echo $noticia['id']; ?>"><?php echo htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'); ?></a></h2>
                                    <?php if (!empty($noticia['subtitulo'])): ?>
                                        <p><?php echo htmlspecialchars($noticia['subtitulo'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php else: ?>
                                        <p><?php echo htmlspecialchars(resumirTexto($noticia['conteudo'], 180), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                    <span class="date"><i class="far fa-clock"></i> <?php echo formatarData($noticia['data_publicacao']); ?></span>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="category-empty">
                            <i class="fas fa-folder-open"></i>
                            <p>Nenhuma notícia encontrada nesta categoria.</p>
                            <a href="index.php">Voltar para a Home</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($total_paginas > 1): ?>
                <div class="pagination">
                    <a href="?slug=<?= urlencode($slugRecebido) ?>&pagina=<?= $pagina_atual - 1 ?>" class="page-link <?= ($pagina_atual <= 1) ? 'disabled' : '' ?>"><i class="fas fa-chevron-left"></i></a>
                    
                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?slug=<?= urlencode($slugRecebido) ?>&pagina=<?= $i ?>" class="page-link <?= ($pagina_atual == $i) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <a href="?slug=<?= urlencode($slugRecebido) ?>&pagina=<?= $pagina_atual + 1 ?>" class="page-link <?= ($pagina_atual >= $total_paginas) ? 'disabled' : '' ?>"><i class="fas fa-chevron-right"></i></a>
                </div>
                <?php endif; ?>

            </div>

            <aside class="category-sidebar">
                <div class="sidebar-block">
                    <div class="section-heading">
                        <h2>Publicidade</h2>
                    </div>
                    <?php exibirPublicidade($conn, $slug, 'quadrado'); ?>
                    <?php exibirPublicidade($conn, $slug, 'skyscraper'); ?>
                </div>
            </aside>
        </section>
    </main>

    <?php include('includes/footer.php'); ?>

</body>
</html>