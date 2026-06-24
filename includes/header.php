<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// MÁGICA: MENU COM SUBCATEGORIAS (MÃE E FILHA)
// ==========================================
$menuCategorias = [];
if (isset($conn)) {
    // 1. Busca todas as categorias
    $query_menu = "SELECT id, nome as label, slug, parent_id FROM categorias ORDER BY ordem ASC, nome ASC";
    $res_menu = mysqli_query($conn, $query_menu);
    
    $todas_cats = [];
    if ($res_menu && mysqli_num_rows($res_menu) > 0) {
        while ($row = mysqli_fetch_assoc($res_menu)) {
            $todas_cats[] = $row;
        }
    }

    // 2. Separa quem é Mãe e quem é Filha
    foreach ($todas_cats as $cat) {
        if (empty($cat['parent_id'])) {
            $cat['children'] = [];
            foreach ($todas_cats as $sub) {
                if ($sub['parent_id'] == $cat['id']) {
                    $cat['children'][] = ['label' => $sub['label'], 'slug' => $sub['slug']];
                }
            }
            if (empty($cat['children'])) {
                unset($cat['children']);
            }
            $menuCategorias[] = $cat;
        }
    }
}

// Links Rápidos
$quickMenuLinks = [
    ['label' => 'Home', 'href' => 'index.php'],
    ['label' => 'Sobre nós', 'href' => 'sobre.php'],
    ['label' => 'Bio.Ethos', 'href' => '#'],
    ['label' => 'Caminhos do Tietê', 'href' => 'https://caminhosdotiete.com.br/', 'target' => '_blank'],
];
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    :root {
        --vermelho-vinho: #8C0303;
        --preto-hover: #000000;
        --fonte-menu: 'Poppins', sans-serif;
    }

    .side-panel-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        z-index: 998;
    }

    .side-panel {
        position: fixed;
        top: 0;
        left: 0;
        width: min(380px, 92vw);
        height: 100vh;
        background: #ffffff;
        color: #1a1a1a;
        box-shadow: 18px 0 40px rgba(15, 23, 42, 0.18);
        transform: translateX(-100%);
        transition: transform 0.28s ease;
        z-index: 999;
        display: flex;
        flex-direction: column;
    }

    .side-panel.active {
        transform: translateX(0);
    }

    .side-panel-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .side-panel__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 24px 24px 20px;
        border-bottom: 1px solid rgba(140, 3, 3, 0.12);
    }

    .side-panel__title {
        margin: 0;
        color: var(--vermelho-vinho);
        font-family: var(--fonte-menu);
        font-size: 1.3rem;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .side-panel__close {
        border: none;
        background: transparent;
        color: #1a1a1a;
        font-size: 2rem;
        line-height: 1;
        cursor: pointer;
    }

    .side-panel__content {
        padding: 22px 24px 28px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 28px;
    }

    .side-panel__intro {
        margin: 0;
        color: #5f6368;
        font-family: 'Roboto', sans-serif;
        font-size: 0.95rem;
        line-height: 1.7;
        text-transform: none;
    }

    .side-panel__section-title {
        margin: 0 0 14px;
        color: #1a1a1a;
        font-family: var(--fonte-menu);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.12em;
    }

    .side-panel__nav,
    .side-panel__utility {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }

    .side-panel__group {
        width: 100%;
    }

    .side-panel__item {
        display: block;
        width: 100%;
        border: 1px solid rgba(140, 3, 3, 0.12);
        border-radius: 16px;
        padding: 14px 16px;
        text-decoration: none;
        color: #1a1a1a;
        font-family: var(--fonte-menu);
        font-size: 0.95rem;
        font-weight: 600;
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        text-transform: uppercase;
        background: #fff;
    }

    .side-panel__item:hover {
        border-color: #000000;
        background: #000000;
        color: #FFFFFF;
        transform: translateX(4px);
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.12);
    }

    .side-panel__children {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
        margin-bottom: 2px;
    }

    .side-panel__child {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(140, 3, 3, 0.06);
        color: var(--vermelho-vinho);
        text-decoration: none;
        font-family: var(--fonte-menu);
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .side-panel__child:hover {
        background: #000000;
        color: #FFFFFF;
        transform: translateX(4px);
    }

    .side-panel__utility a {
        display: block;
        width: 100%;
        padding: 12px 14px;
        border-radius: 14px;
        color: #5f6368;
        text-decoration: none;
        font-family: 'Roboto', sans-serif;
        font-size: 0.95rem;
        text-transform: none;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .side-panel__utility a:hover {
        background: #000000;
        color: #FFFFFF;
        transform: translateX(4px);
    }

    body.menu-open {
        overflow: hidden;
    }

    .header-utility-bar {
        background: #6f0202;
        color: #fff;
        font-family: var(--fonte-menu);
        font-size: 0.78rem;
        letter-spacing: 0.04em;
    }

    .header-utility-inner {
        max-width: 1300px;
        margin: 0 auto;
        padding: 10px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 22px;
        flex-wrap: wrap;
    }

    .header-utility-inner a {
        color: inherit;
        text-decoration: none;
        opacity: 0.9;
    }

    .full-header {
        background: var(--vermelho-vinho);
        color: #FFFFFF;
        width: 100%;
        text-transform: uppercase;
        margin-bottom: 18px;
    }

    .header-top-wrapper {
        padding: 28px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .menu-trigger {
        background: none;
        border: none;
        color: #FFFFFF;
        font-family: var(--fonte-menu);
        font-weight: 400 !important;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logo-container img {
        height: 85px;
        display: block;
    }

    .user-actions {
        display: flex;
        gap: 24px;
        align-items: center;
        font-family: var(--fonte-menu);
        font-size: 18px;
    }

    .btn-conta {
        background: rgba(0,0,0,0.2);
        color: #FFFFFF;
        padding: 12px 22px;
        border-radius: 8px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s;
        font-weight: 400 !important;
    }
    .btn-conta:hover { background: var(--preto-hover); }

    .btn-anuncie {
        background: transparent;
        color: #FFFFFF;
        border: 2px solid #FFFFFF;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
    }
    .btn-anuncie:hover {
        background: #FFFFFF;
        color: var(--vermelho-vinho);
    }

    .nav-main {
        border-top: 1px solid rgba(255,255,255,0.1);
        display: flex;
        justify-content: center;
    }

    .nav-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-wrap: nowrap;
        justify-content: center;
        gap: 4px;
    }

    .nav-item { position: relative; }

    .nav-link {
        display: block;
        padding: 20px 16px;
        color: #FFFFFF;
        text-decoration: none;
        font-family: var(--fonte-menu);
        font-weight: 500 !important;
        font-size: 1rem;
        letter-spacing: 0.04em;
        transition: 0.3s;
        white-space: nowrap;
    }

    .nav-link:hover { background: var(--preto-hover); }

    .dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #FFFFFF;
        min-width: 260px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        z-index: 999;
    }

    .nav-item:hover .dropdown { display: block; }

    .dropdown-link {
        display: block;
        padding: 15px 20px;
        color: var(--vermelho-vinho);
        text-decoration: none;
        font-family: var(--fonte-menu);
        font-size: 15px;
        font-weight: 400 !important;
        border-bottom: 1px solid #f2f2f2;
        transition: 0.2s;
        text-transform: uppercase;
    }

    .dropdown-link:hover {
        background: var(--vermelho-vinho);
        color: #FFFFFF;
    }

    .container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 20px;
    }

    @media (max-width: 1200px) {
        .nav-list { flex-wrap: wrap; }
        .nav-link { padding: 16px 14px; font-size: 0.92rem; }
    }

    @media (max-width: 900px) {
        .side-panel__content { padding-inline: 20px; }
        .header-top-wrapper { flex-wrap: wrap; justify-content: center; text-align: center; }
        .header-utility-inner { justify-content: center; gap: 14px; }
        .user-actions { gap: 18px; font-size: 15px; flex-wrap: wrap; justify-content: center; }
        .nav-list { flex-wrap: wrap; }
        .nav-link { padding: 14px 12px; font-size: 0.88rem; }
    }
</style>

<div id="side-panel-overlay" class="side-panel-overlay" onclick="closeMenu()"></div>

<aside id="side-panel" class="side-panel" aria-hidden="true">
    <div class="side-panel__header">
        <h3 class="side-panel__title">Menu</h3>
        <button type="button" class="side-panel__close" onclick="closeMenu()" aria-label="Fechar menu">&times;</button>
    </div>

    <div class="side-panel__content">
        <section>
            <p class="side-panel__section-title">Editorias</p>
            <nav class="side-panel__nav" aria-label="Menu do ecossistema">
                <?php foreach ($menuCategorias as $item): ?>
                    <div class="side-panel__group">
                        <a href="categoria.php?slug=<?php echo urlencode($item['slug']); ?>" class="side-panel__item"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                        
                        <?php if (!empty($item['children'])): ?>
                            <div class="side-panel__children">
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php 
                                    // RADAR DE DESVIO CORRIGIDO (MENU MOBILE) - Olha pro SLUG agora!
                                    $slug_atual = strtolower(trim($child['slug']));
                                    if (strpos($slug_atual, 'caminhos-do-tiete') !== false || strpos($slug_atual, 'caminhos-do-tiet') !== false) {
                                        $link_final = 'caminhos-do-tiete.php';
                                        $icone = '<i class="fas fa-map-marked-alt"></i>';
                                    } else {
                                        $link_final = 'categoria.php?slug=' . urlencode($child['slug']);
                                        $icone = '<i class="fas fa-arrow-right"></i>';
                                    }
                                    ?>
                                    <a href="<?= $link_final ?>" class="side-panel__child">
                                        <?= $icone ?>
                                        <?php echo htmlspecialchars($child['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="side-panel__group">
                    <a href="coberturas.php" class="side-panel__item" style="color: #8C0303; font-weight: 800;"><i class="fas fa-camera"></i> COBERTURAS</a>
                </div>
            </nav>
        </section>

        <section>
            <p class="side-panel__section-title">Links rápidos</p>
            <div class="side-panel__utility">
                <?php foreach ($quickMenuLinks as $link): ?>
                    <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo !empty($link['target']) ? " target=\"_blank\" rel=\"noopener noreferrer\"" : ""; ?>><?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</aside>

<div class="header-utility-bar">
    <div class="header-utility-inner">
        <a href="sobre.php">Sobre Nós</a>
        <a href="politica_privacidade.php">Política de Privacidade</a>
        <a href="politica_cookies.php">Política de Cookies</a>
        <a href="contato.php">Contato</a>
    </div>
</div>

<div class="full-header">
    <header class="container">
        <div class="header-top-wrapper">
            <button type="button" class="menu-trigger" onclick="toggleMenu()" aria-controls="side-panel" aria-expanded="false">
                <i class="fas fa-bars" style="font-size: 28px;"></i> MENU
            </button>

            <a href="index.php" class="logo-container">
                <img src="assets/img/logo.png" alt="ETHOS">
            </a>

            <div class="user-actions">
                <form action="busca.php" method="GET" style="margin:0; display:flex; align-items:center; background:rgba(0,0,0,0.2); border-radius:8px; padding:6px 12px; transition:0.3s;">
                    <input type="text" name="q" placeholder="Buscar matéria..." style="background:transparent; border:none; color:#fff; font-family:var(--fonte-menu); font-size:14px; outline:none; width:130px;" required>
                    <button type="submit" style="background:none; border:none; color:#fff; cursor:pointer;"><i class="fas fa-search"></i></button>
                </form>

                <a href="contato.php" class="btn-anuncie"><i class="fas fa-bullhorn"></i> Anuncie</a>

                <?php if (isset($_SESSION['leitor_id'])): ?>
                    <div class="nav-item" style="position: relative; padding-bottom: 15px; margin-bottom: -15px;">
                        <a href="#" class="btn-conta" style="background: #000;">
                            <i class="fas fa-user-circle"></i> Olá, <?= htmlspecialchars(explode(' ', trim($_SESSION['leitor_nome']))[0]) ?> 
                            <i class="fas fa-chevron-down" style="font-size:12px; margin-left:5px;"></i>
                        </a>
                        
                        <div class="dropdown" style="right: 0; left: auto; min-width: 200px; border-radius: 8px; overflow: hidden; top: 100%;">
                            <a href="painel_leitor.php" class="dropdown-link" style="text-transform: none; font-weight: 600;"><i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Enviar Pauta</a>
                            <a href="logout.php" class="dropdown-link" style="text-transform: none; font-weight: 600; color: #ce1212;"><i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Sair da Conta</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-conta">ENTRAR NA CONTA <i class="fas fa-user-circle"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <nav class="nav-main">
        <div class="container">
            <ul class="nav-list">
                <?php foreach ($menuCategorias as $item): ?>
                    <li class="nav-item">
                        <a href="categoria.php?slug=<?php echo urlencode($item['slug']); ?>" class="nav-link">
                            <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if (!empty($item['children'])): ?>
                                <i class="fas fa-chevron-down" style="font-size:12px; margin-left:5px;"></i>
                            <?php endif; ?>
                        </a>

                        <?php if (!empty($item['children'])): ?>
                            <div class="dropdown">
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php 
                                    // RADAR DE DESVIO CORRIGIDO (MENU COMPUTADOR) - Olha pro SLUG!
                                    $slug_atual = strtolower(trim($child['slug']));
                                    if (strpos($slug_atual, 'caminhos-do-tiete') !== false || strpos($slug_atual, 'caminhos-do-tiet') !== false) {
                                        $link_final = 'caminhos-do-tiete.php';
                                        $estilo = 'color: #8C0303; font-weight: 700;';
                                    } else {
                                        $link_final = 'categoria.php?slug=' . urlencode($child['slug']);
                                        $estilo = '';
                                    }
                                    ?>
                                    <a href="<?= $link_final ?>" class="dropdown-link" style="<?= $estilo ?>">
                                        <?php echo htmlspecialchars($child['label'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                
                <li class="nav-item">
                    <a href="coberturas.php" class="nav-link" style="color: #fff; font-weight: 800 !important; background: rgba(0,0,0,0.2); border-radius: 8px;">
                        <i class="fas fa-camera"></i> COBERTURAS
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>

<script>
    (function () {
        const panel = document.getElementById('side-panel');
        const overlay = document.getElementById('side-panel-overlay');

        window.toggleMenu = function toggleMenu() {
            if (!panel || !overlay) {
                return;
            }

            const isOpen = panel.classList.toggle('active');
            overlay.classList.toggle('active', isOpen);
            document.body.classList.toggle('menu-open', isOpen);
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        };

        window.closeMenu = function closeMenu() {
            if (!panel || !overlay) {
                return;
            }

            panel.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('menu-open');
            panel.setAttribute('aria-hidden', 'true');
        };

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                window.closeMenu();
            }
        });
    })();
</script>