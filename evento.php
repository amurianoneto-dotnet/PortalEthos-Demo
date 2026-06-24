<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once 'includes/db.php';
include_once 'includes/config_menu.php';

// Verifica se passou um ID válido na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: categoria.php?slug=eventhos");
    exit();
}

$id = (int)$_GET['id'];

// Busca o evento no banco
$sql = "SELECT e.*, COALESCE(u.nome, 'Equipe ETHOS') as autor FROM eventos e LEFT JOIN usuarios u ON e.usuario_id = u.id WHERE e.id = $id AND e.status = 'aprovado'";
$res = $conn->query($sql);

if ($res->num_rows === 0) {
    echo "<script>alert('Evento não encontrado ou aguardando aprovação.'); window.location.href='categoria.php?slug=eventhos';</script>";
    exit();
}

$evento = $res->fetch_assoc();

// ==========================================
// MÁGICA 1: GERADOR DE LINK PRO GOOGLE CALENDAR
// ==========================================
$data_hora_inicio = $evento['data_evento'] . ' ' . $evento['hora_evento'];
$timestamp_inicio = strtotime($data_hora_inicio);
$data_gcal_inicio = date('Ymd\THis', $timestamp_inicio);
$data_gcal_fim = date('Ymd\THis', $timestamp_inicio + (2 * 3600)); // Adiciona 2 horas de duração por padrão

$link_calendario = "https://calendar.google.com/calendar/render?action=TEMPLATE" .
                   "&text=" . urlencode($evento['titulo']) .
                   "&dates=" . $data_gcal_inicio . "/" . $data_gcal_fim .
                   "&details=" . urlencode($evento['descricao']) .
                   "&location=" . urlencode($evento['local_evento']);

// ==========================================
// MÁGICA 2: LINKS DE COMPARTILHAMENTO
// ==========================================
// Pega a URL completa da página atual onde o usuário está
$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$url_atual = $protocolo . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$texto_zap = urlencode("Confira este evento: " . $evento['titulo'] . " na Revista ETHOS! ");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($evento['titulo']) ?> | Agenda ETHOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --vinho: #8C0303; --fundo: #f8f9fa; --preto: #1a1a1a; }
        body { background: var(--fundo); font-family: 'Poppins', sans-serif; margin: 0; color: #333; }
        
        .evento-page { max-width: 1200px; margin: 0 auto; padding: 140px 20px 60px; }
        
        .btn-voltar { display: inline-flex; align-items: center; gap: 8px; color: #666; text-decoration: none; font-weight: 600; margin-bottom: 20px; transition: 0.2s; }
        .btn-voltar:hover { color: var(--vinho); }

        .layout-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }
        
        /* Lado Esquerdo (Conteúdo) */
        .conteudo-principal { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); height: fit-content; }
        .imagem-destaque { width: 100%; max-height: 500px; object-fit: cover; display: block; }
        
        .texto-evento { padding: 40px; }
        .texto-evento h1 { margin: 0 0 15px; font-size: 2.2rem; color: var(--vinho); line-height: 1.2; }
        .autor-info { font-size: 0.85rem; color: #888; margin-bottom: 30px; display: flex; align-items: center; gap: 8px; }
        .texto-evento p { font-size: 1.05rem; line-height: 1.8; color: #444; white-space: pre-wrap; }

        /* Lado Direito (Sidebar de Informações) */
        .sidebar-info { display: flex; flex-direction: column; gap: 25px; }
        
        .info-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-top: 5px solid var(--vinho); }
        .info-item { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 20px; }
        .info-item:last-child { margin-bottom: 0; }
        .info-item i { color: var(--vinho); font-size: 1.5rem; margin-top: 5px; }
        .info-item div span { display: block; font-size: 0.8rem; color: #888; text-transform: uppercase; font-weight: bold; }
        .info-item div strong { font-size: 1.1rem; color: var(--preto); }

        /* Botão Calendário */
        .btn-agenda { background: var(--preto); color: white; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; border-radius: 10px; text-decoration: none; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; width: 100%; box-sizing: border-box; margin-top: 10px; }
        .btn-agenda:hover { background: var(--vinho); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(140,3,3,0.2); }

        /* COMPARTILHAMENTO */
        .share-box { margin-top: 25px; padding-top: 25px; border-top: 1px solid #eee; }
        .share-box h4 { font-size: 0.85rem; color: #888; text-transform: uppercase; margin: 0 0 15px; text-align: center; letter-spacing: 1px; }
        .share-buttons { display: flex; gap: 10px; }
        .btn-share { flex: 1; display: flex; justify-content: center; align-items: center; padding: 12px; border-radius: 8px; color: white; text-decoration: none; font-size: 1.2rem; transition: 0.3s; }
        .btn-share.whatsapp { background: #25D366; }
        .btn-share.facebook { background: #1877F2; }
        /* CORREÇÃO DO AZUL AQUI KKKK */
        .btn-share.twitter { background: #1DA1F2; } 
        .btn-share:hover { filter: brightness(0.9); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        /* Mapa Embed */
        .mapa-container { border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: white; }
        .mapa-header { padding: 15px 20px; background: var(--preto); color: white; font-weight: bold; font-size: 0.9rem; text-transform: uppercase; display: flex; align-items: center; gap: 10px;}

        @media (max-width: 900px) {
            .layout-grid { grid-template-columns: 1fr; }
            .evento-page { padding-top: 180px; }
            .texto-evento h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="evento-page">
        <a href="categoria.php?slug=eventhos" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar para a Agenda</a>

        <div class="layout-grid">
            <div class="conteudo-principal">
                <?php if($evento['imagem']): ?>
                    <img src="uploads/eventos/<?= $evento['imagem'] ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" class="imagem-destaque">
                <?php endif; ?>
                
                <div class="texto-evento">
                    <h1><?= htmlspecialchars($evento['titulo']) ?></h1>
                    <div class="autor-info">
                        <i class="fas fa-user-circle"></i> Divulgado por: <?= htmlspecialchars($evento['autor']) ?>
                    </div>
                    <p><?= htmlspecialchars($evento['descricao']) ?></p>
                </div>
            </div>

            <div class="sidebar-info">
                
                <div class="info-card">
                    <div class="info-item">
                        <i class="far fa-calendar-alt"></i>
                        <div>
                            <span>Data do Evento</span>
                            <strong><?= date('d/m/Y', strtotime($evento['data_evento'])) ?></strong>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="far fa-clock"></i>
                        <div>
                            <span>Horário de Início</span>
                            <strong><?= date('H:i', strtotime($evento['hora_evento'])) ?></strong>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <span>Localização</span>
                            <strong><?= htmlspecialchars($evento['local_evento']) ?></strong>
                        </div>
                    </div>

                    <a href="<?= $link_calendario ?>" target="_blank" class="btn-agenda">
                        <i class="fab fa-google"></i> Salvar na Agenda
                    </a>

                    <div class="share-box">
                        <h4><i class="fas fa-share-nodes"></i> Compartilhar Evento</h4>
                        <div class="share-buttons">
                            <a href="https://api.whatsapp.com/send?text=<?= $texto_zap ?>%20<?= urlencode($url_atual) ?>" target="_blank" class="btn-share whatsapp" title="Compartilhar no WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($url_atual) ?>" target="_blank" class="btn-share facebook" title="Compartilhar no Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="mapa-container">
                    <div class="mapa-header">
                        <i class="fas fa-map"></i> Como chegar
                    </div>
                    <iframe 
                        width="100%" 
                        height="300" 
                        style="border:0; display:block;" 
                        loading="lazy" 
                        allowfullscreen 
                        src="https://maps.google.com/maps?q=<?= urlencode($evento['local_evento']) ?>&output=embed">
                    </iframe>
                </div>

            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>