<?php 
// CORREÇÃO DO ERRO DO MENU: Iniciar a sessão na LINHA 1, antes de qualquer HTML!
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include('includes/db.php'); 
include('functions.php'); 

// 1. Notícia MAIOR (Destaque Principal)
$query_principal = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 0, 1";
$res_principal = mysqli_query($conn, $query_principal);
$destaque = mysqli_fetch_assoc($res_principal);

// 2. Notícias da Lateral Direita (Posições 2 e 3)
$query_side = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 1, 2";
$res_side = mysqli_query($conn, $query_side);

// 3. Bloco 1 (3 Notícias com botão Leia Mais)
$query_bloco1 = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 3, 3";
$res_bloco1 = mysqli_query($conn, $query_bloco1);

// 4. Bloco 2 (Lista Simples de Notícias - 4 Itens)
$query_bloco2 = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 6, 4";
$res_bloco2 = mysqli_query($conn, $query_bloco2);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETHOS | O Portal da Nossa Região</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --vermelho-ethos: #8C0303;
            --preto: #000000;
            --fundo: #f4f6f9;
        }
        body { font-family: 'Roboto', sans-serif; background: var(--fundo); margin:0; padding:0; }
        h1, h2, h3, h4, h5 { font-family: 'Poppins', sans-serif; }
        
        /* Ticker Financeiro */
        .financial-ticker { background: #000; color: #fff; padding: 8px 0; overflow: hidden; white-space: nowrap; font-size: 0.8rem; font-weight: 600; font-family: 'Poppins', sans-serif; letter-spacing: 1px;}
        .ticker-content { display: inline-block; animation: ticker 25s linear infinite; }
        .ticker-item { margin-right: 40px; display: inline-flex; align-items: center; gap: 6px; }
        .ticker-up { color: #4CAF50; }
        .ticker-down { color: #f44336; }
        @keyframes ticker { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }

        .ad-mega-top { text-align: center; max-width: 100%; overflow: hidden; margin-top: 30px; margin-bottom: 20px; }
        .ad-faixa { text-align: center; max-width: 100%; overflow: hidden; margin: 50px 0; }

        /* ==================================================
           NOVO CSS DA SEÇÃO DE DESTAQUE (ALINHAMENTO PERFEITO)
           ================================================== */
        .news-section {
            display: grid;
            grid-template-columns: 2fr 1.1fr; /* Proporção melhor */
            gap: 30px;
            align-items: stretch; /* ISSO FAZ OS DOIS LADOS TEREM A MESMA ALTURA! */
            min-height: 500px;
        }
        .main-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }
        .main-card img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }
        .card-overlay {
            position: relative;
            z-index: 2;
            background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.3) 60%, transparent 100%);
            padding: 40px;
            color: white;
            margin-top: auto; /* Empurra os textos para baixo */
        }
        .news-section aside {
            display: flex;
            flex-direction: column;
            gap: 15px; /* Espaço entre notícias e banners */
        }
        .side-item {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            cursor: pointer;
            transition: 0.3s;
            border-left: 4px solid var(--vermelho-ethos);
        }
        .side-item:hover { transform: translateX(5px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        
        /* A MÁGICA PARA ALINHAR O BANNER NO RODAPÉ */
        .ad-sidebar-box {
            margin-top: auto; /* Empurra o banner para o rodapé exato da coluna! */
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center; /* Centraliza o banner dentro da coluna */
        }

        /* Estilos do Bloco 1 (3 Cards) */
        .grid-3-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin: 50px 0; }
        .card-noticia { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.04); transition: 0.3s; display: flex; flex-direction: column; }
        .card-noticia:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        .card-noticia img { width: 100%; height: 220px; object-fit: cover; }
        .card-conteudo { padding: 25px; flex: 1; display: flex; flex-direction: column; }
        .card-categoria { color: var(--vermelho-ethos); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 10px; display: block; }
        .card-conteudo h3 { font-size: 1.3rem; margin: 0 0 15px; color: #111; line-height: 1.3; }
        .card-conteudo p { color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px; }
        .btn-leia-mais { margin-top: auto; display: inline-block; background: #f0f0f0; color: #111; text-align: center; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; transition: 0.2s; }
        .btn-leia-mais:hover { background: var(--vermelho-ethos); color: #fff; }

        /* Estilo ETHOS PLAY */
        .ethos-play-section { background: var(--preto); padding: 60px 0; border-radius: 20px; margin: 50px 0; color: #fff; }
        .play-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding: 0 40px; }
        .play-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; padding: 0 40px; }
        .video-card { display: flex; gap: 15px; background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; cursor: pointer; transition: 0.3s; align-items: center; }
        .video-card:hover { background: rgba(255,255,255,0.1); transform: translateX(5px); }
        .video-thumb { width: 140px; height: 80px; position: relative; border-radius: 8px; overflow: hidden; flex-shrink: 0; }
        .video-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .video-thumb::after { content: '\f144'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; inset: 0; background: rgba(0,0,0,0.4); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; opacity: 0; transition: 0.3s; }
        .video-card:hover .video-thumb::after { opacity: 1; }

        /* Estilos do Bloco 2 (Lista Simples) e Sobre Nós */
        .grid-2-col { display: grid; grid-template-columns: 2fr 1.2fr; gap: 40px; margin: 50px 0; }
        .lista-simples { display: flex; flex-direction: column; gap: 20px; }
        .item-simples { background: #fff; padding: 20px 25px; border-radius: 10px; border-left: 4px solid var(--vermelho-ethos); box-shadow: 0 5px 15px rgba(0,0,0,0.03); text-decoration: none; transition: 0.2s; display: block; }
        .item-simples:hover { transform: translateX(5px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-left-color: #000; }
        .item-simples .cat { font-size: 0.75rem; color: #888; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; display: block;}
        .item-simples h4 { margin: 0; color: #111; font-size: 1.1rem; line-height: 1.4; }

        /* Caixinha Sobre Nós na Home */
        .box-sobre { background: #fff; padding: 40px; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05); height: fit-content; }
        .box-sobre h3 { color: var(--vermelho-ethos); font-size: 1.8rem; margin-top: 0; margin-bottom: 20px; text-transform: uppercase; }
        .box-sobre p { color: #666; font-size: 0.95rem; line-height: 1.7; margin-bottom: 30px; }
        .box-sobre .btn-sobre { background: var(--preto); color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; transition: 0.3s; display: inline-block; }
        .box-sobre .btn-sobre:hover { background: var(--vermelho-ethos); }

        @media (max-width: 900px) {
            .news-section, .grid-3-cards, .play-grid, .grid-2-col { grid-template-columns: 1fr; }
            .play-header { flex-direction: column; gap: 15px; text-align: center; padding: 0 20px; }
            .play-grid { padding: 0 20px; }
            .ticker-item { margin-right: 20px; }
        }
    </style>
</head>
<body>

    <div class="financial-ticker">
        <div class="ticker-content" id="ticker-content">
            <span style="color: #ccc;">Atualizando cotações de mercado...</span>
        </div>
    </div>

    <?php include('includes/header.php'); ?>

    <div class="ad-mega-top">
        <div class="container">
            <?php exibirPublicidade($conn, 'home', 'mega'); ?>
        </div>
    </div>

    <main class="container" style="margin-top: 20px;">
        
        <section class="news-section">
            <?php if($destaque): ?>
            <div class="main-card" onclick="window.location.href='noticia.php?id=<?php echo $destaque['id']; ?>'">
                <img src="assets/img/<?php echo $destaque['imagem_capa']; ?>" alt="Destaque">
                <div class="card-overlay">
                    <span style="background:var(--vermelho-ethos); padding:6px 15px; font-weight:700; font-size:12px; border-radius:4px; text-transform:uppercase; color:#fff;">
                        <?php echo !empty($destaque['subcategoria']) ? $destaque['subcategoria'] : 'Destaque'; ?>
                    </span>
                    <h2 style="font-size: 3rem; margin-top:15px; line-height:1.1; font-weight:800;"><?php echo $destaque['titulo']; ?></h2>
                    <?php if(!empty($destaque['subtitulo'])): ?>
                        <p style="margin-top:10px; opacity:0.9; font-weight:400; font-size:1.1rem;"><?php echo $destaque['subtitulo']; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <aside>
                <?php if($res_side && mysqli_num_rows($res_side) > 0): ?>
                    <?php while($side = mysqli_fetch_assoc($res_side)): ?>
                    <div class="side-item" onclick="window.location.href='noticia.php?id=<?php echo $side['id']; ?>'">
                        <small style="color:var(--vermelho-ethos); font-weight:700; text-transform:uppercase; font-family:'Poppins';">
                            <?php echo !empty($side['subcategoria']) ? $side['subcategoria'] : 'Notícia'; ?>
                        </small>
                        <h3 style="font-size:1.2rem; font-weight:700; margin-top:5px; line-height:1.3;"><?php echo $side['titulo']; ?></h3>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <div class="ad-sidebar-box">
                    <?php exibirPublicidade($conn, 'home', 'quadrado'); ?>
                    <?php exibirPublicidade($conn, 'home', 'skyscraper'); ?>
                </div>
            </aside>
        </section>

        <?php if($res_bloco1 && mysqli_num_rows($res_bloco1) > 0): ?>
        <div class="grid-3-cards">
            <?php while($b1 = mysqli_fetch_assoc($res_bloco1)): ?>
                <div class="card-noticia">
                    <img src="assets/img/<?php echo $b1['imagem_capa']; ?>" alt="Capa">
                    <div class="card-conteudo">
                        <span class="card-categoria"><?php echo !empty($b1['subcategoria']) ? $b1['subcategoria'] : 'Geral'; ?></span>
                        <h3><?php echo $b1['titulo']; ?></h3>
                        <p><?php echo mb_substr(strip_tags($b1['conteudo']), 0, 90); ?>...</p>
                        <a href="noticia.php?id=<?php echo $b1['id']; ?>" class="btn-leia-mais">Leia Mais</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <div class="ad-faixa">
            <?php exibirPublicidade($conn, 'home', 'retangulo'); ?>
        </div>

        <style>
            /* Mágica da barra de rolagem vermelha e preta */
            .playlist-scroll {
                max-height: 380px; /* Trava a altura para caber exatamente 3 vídeos */
                overflow-y: auto; /* Cria o scroll se passar de 3 vídeos */
                padding-right: 15px; /* Espacinho pra barra não colar no texto */
            }
            .playlist-scroll::-webkit-scrollbar { width: 6px; }
            .playlist-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
            .playlist-scroll::-webkit-scrollbar-thumb { background: var(--vermelho-ethos); border-radius: 10px; }
        </style>

        <section class="ethos-play-section">
            <div class="play-header">
                <h2 style="font-size: 2.2rem; font-weight:900; margin:0;">
                    <i class="fas fa-play-circle" style="color:var(--vermelho-ethos);"></i> ETHOS PLAY
                </h2>
                <a href="https://www.youtube.com/@revistaethos" target="_blank" style="background: #ff0000; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: 800; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                    <i class="fab fa-youtube"></i> ACESSE NOSSO CANAL
                </a>
            </div>

            <?php 
            // AUMENTAMOS O LIMITE PARA 10! Assim ele tem vídeos para rolar no scroll
            $res_play = mysqli_query($conn, "SELECT * FROM ethos_play ORDER BY ordem ASC, id DESC LIMIT 10");
            $videos_lista = [];
            while($v = mysqli_fetch_assoc($res_play)) { $videos_lista[] = $v; }
            if (count($videos_lista) > 0):
                $destaque_v = $videos_lista[0];
            ?>
            <div class="play-grid">
                <div>
                    <div style="position:relative; padding-top:56.25%;">
                        <iframe id="mainPlayer" style="position:absolute; top:0; left:0; width:100%; height:100%; border-radius:15px; border: 2px solid rgba(255,255,255,0.1);" src="https://www.youtube.com/embed/<?php echo $destaque_v['video_id']; ?>?rel=0" frameborder="0" allowfullscreen></iframe>
                    </div>
                    <h3 id="mainTitle" style="color:#fff; margin-top:20px; font-size:1.5rem;"><?php echo $destaque_v['titulo']; ?></h3>
                </div>

                <div class="playlist-scroll" style="display: flex; flex-direction: column; gap: 15px;">
                    <p style="color:#aaa; font-size: 11px; text-transform: uppercase; font-weight:700; letter-spacing:1px; margin:0;">Próximos Vídeos</p>
                    
                    <?php foreach($videos_lista as $video): ?>
                    <div class="video-card" onclick="document.getElementById('mainPlayer').src='https://www.youtube.com/embed/<?php echo $video['video_id']; ?>?autoplay=1&rel=0'; document.getElementById('mainTitle').innerText='<?php echo addslashes($video['titulo']); ?>';">
                        <div class="video-thumb">
                            <img src="https://img.youtube.com/vi/<?php echo $video['video_id']; ?>/mqdefault.jpg" alt="Thumb">
                        </div>
                        <div>
                            <h5 style="color:#fff; margin:0 0 5px; font-size: 0.95rem; line-height: 1.3; font-weight: 600;"><?php echo $video['titulo']; ?></h5>
                            <small style="color:var(--vermelho-ethos); font-weight: bold; font-size: 0.75rem;">ASSISTIR AGORA</small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                </div>
            </div>
            <?php endif; ?>
        </section>

        <div class="grid-2-col">
            <div class="lista-simples">
                <h2 style="font-weight:900; font-size:1.8rem; text-transform:uppercase; margin-top:0; border-bottom: 3px solid var(--vermelho-ethos); padding-bottom: 10px; display: inline-block; width: fit-content;">Mais Lidas</h2>
                
                <?php if($res_bloco2 && mysqli_num_rows($res_bloco2) > 0): ?>
                    <?php while($b2 = mysqli_fetch_assoc($res_bloco2)): ?>
                        <a href="noticia.php?id=<?php echo $b2['id']; ?>" class="item-simples">
                            <span class="cat"><?php echo !empty($b2['subcategoria']) ? $b2['subcategoria'] : 'Geral'; ?> &bull; <?= date('d/m/Y', strtotime($b2['data_publicacao'])) ?></span>
                            <h4><?php echo $b2['titulo']; ?></h4>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666;">Novas atualizações em breve.</p>
                <?php endif; ?>
            </div>

            <div class="box-sobre">
                <h3>A Revista</h3>
                <img src="assets/img/logo.png" alt="Ethos" style="height: 50px; margin-bottom: 15px; filter: brightness(0) invert(0); opacity: 0.8;">
                <p>Muito mais que informação. Um espaço que escuta, interpreta e compartilha os sinais do nosso tempo. Valorizando a cultura, a ética e o potencial da nossa região (Barra Bonita - SP).</p>
                <a href="sobre.php" class="btn-sobre">Conhecer Nossa História</a>
            </div>
        </div>

        <div class="ad-faixa">
            <?php exibirPublicidade($conn, 'home', 'faixa'); ?>
        </div>

    </main>

    <div class="live-player" id="livePlayer" style="box-shadow: 0 -10px 30px rgba(0,0,0,0.3); position:fixed; bottom:20px; right:20px; width:350px; z-index:9999; background:#000; border-radius:10px; overflow:hidden;">
        <div style="background:var(--vermelho-ethos); color:#fff; padding:15px 20px; font-weight:700; font-size:13px; display:flex; justify-content:space-between; align-items:center;">
            <span><i class="fas fa-broadcast-tower"></i> AO VIVO | ETHOS TV</span>
            <button onclick="document.getElementById('livePlayer').style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer; font-size:25px; line-height:0;">&times;</button>
        </div>
        <div style="position:relative; padding-top:56.25%;">
            <iframe style="position:absolute; top:0; left:0; width:100%; height:100%;" src="https://www.youtube.com/embed/<?php 
                $sql_live = "SELECT aovivo_id FROM configuracoes WHERE id = 1";
                $res_live = mysqli_query($conn, $sql_live);
                $live = mysqli_fetch_assoc($res_live);
                echo $live['aovivo_id'] ?? ''; 
            ?>?autoplay=0" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script>
        // Puxa cotação real e grátis da API AwesomeAPI
        fetch('https://economia.awesomeapi.com.br/last/USD-BRL,EUR-BRL,BTC-BRL')
            .then(response => response.json())
            .then(data => {
                const usd = data.USDBRL;
                const eur = data.EURBRL;
                const btc = data.BTCBRL;

                function formatCoin(coin, symbol) {
                    let isUp = parseFloat(coin.pctChange) > 0;
                    let colorClass = isUp ? 'ticker-up' : 'ticker-down';
                    let arrow = isUp ? '▲' : '▼';
                    return `<span class="ticker-item">
                                <i class="fas fa-coins" style="color:#888;"></i> 
                                ${symbol}: R$ ${parseFloat(coin.bid).toFixed(2).replace('.', ',')} 
                                <span class="${colorClass}">${arrow} ${coin.pctChange}%</span>
                            </span>`;
                }

                let htmlTicker = formatCoin(usd, "DÓLAR") + formatCoin(eur, "EURO") + formatCoin(btc, "BITCOIN") + 
                                 '<span class="ticker-item"><i class="fas fa-chart-line" style="color:#8C0303;"></i> IBOVESPA: FECHAMENTO EM ALTA</span>' +
                                 formatCoin(usd, "DÓLAR") + formatCoin(eur, "EURO");

                document.getElementById('ticker-content').innerHTML = htmlTicker;
            })
            .catch(error => {
                document.getElementById('ticker-content').innerHTML = "<span>Bem-vindo à Revista ETHOS - O portal de notícias da sua região.</span>";
            });
    </script>
</body>
</html>