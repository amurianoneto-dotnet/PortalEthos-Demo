<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once 'includes/db.php';
// Define as variáveis do menu se não existirem
include_once 'includes/config_menu.php';

// Busca os eventos aprovados
$sql = "SELECT * FROM eventos WHERE status = 'aprovado' AND data_evento >= CURDATE() ORDER BY data_evento ASC";
$res_eventos = $conn->query($sql);
$meses = ['', 'JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agenda Cultural - EVENTHOS | Revista ETHOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --vinho: #8C0303; --fundo: #f4f7f6; --preto: #1a1a1a; }
        body { background: var(--fundo); font-family: 'Poppins', sans-serif; margin: 0; }
        
        .agenda-hero { background: linear-gradient(to right, #4a0101, var(--vinho)); color: white; padding: 160px 20px 60px; text-align: center; }
        .agenda-hero h1 { font-weight: 900; font-size: 3rem; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 2px; }
        .agenda-hero p { font-size: 1.1rem; opacity: 0.9; margin-bottom: 30px; }
        
        .btn-cadastrar { background: white; color: var(--vinho); padding: 15px 30px; border-radius: 30px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .btn-cadastrar:hover { transform: translateY(-3px); background: var(--preto); color: white; }

        .container-agenda { max-width: 1000px; margin: -40px auto 60px; padding: 0 20px; position: relative; z-index: 10; }
        
        .evento-card { background: white; border-radius: 15px; display: flex; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 30px; transition: 0.3s; }
        .evento-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
        
        .evento-data { background: var(--preto); color: white; min-width: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; border-right: 5px solid var(--vinho); }
        .evento-data .dia { font-size: 2.5rem; font-weight: 900; line-height: 1; }
        .evento-data .mes { font-size: 1.2rem; font-weight: 600; color: var(--vinho); }
        
        .evento-img { width: 250px; background: #eee; }
        .evento-img img { width: 100%; height: 100%; object-fit: cover; }
        
        .evento-info { padding: 30px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .evento-info h3 { margin: 0 0 10px; font-size: 1.4rem; color: var(--vinho); }
        .evento-info p { color: #555; font-size: 0.95rem; margin-bottom: 15px; line-height: 1.6; }
        
        .evento-meta { display: flex; justify-content: space-between; align-items: center; margin-top: auto; border-top: 1px solid #eee; padding-top: 15px; }
        .meta-detalhes { display: flex; gap: 20px; font-size: 0.85rem; color: #888; font-weight: 600; }
        .meta-detalhes i { color: var(--vinho); margin-right: 5px; }
        
        .btn-leia-mais { background: #f4f4f4; color: var(--preto); padding: 8px 20px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 700; transition: 0.2s; text-transform: uppercase; }
        .btn-leia-mais:hover { background: var(--vinho); color: white; }

        @media (max-width: 768px) {
            .evento-card { flex-direction: column; }
            .evento-data { min-width: auto; padding: 15px; flex-direction: row; gap: 10px; border-right: none; border-bottom: 5px solid var(--vinho); }
            .evento-data .dia { font-size: 2rem; }
            .evento-img { width: 100%; height: 200px; }
            .evento-meta { flex-direction: column; align-items: flex-start; gap: 15px; }
            .btn-leia-mais { width: 100%; text-align: center; box-sizing: border-box; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="agenda-hero">
        <h1>EVENTHOS</h1>
        <p>A agenda cultural oficial da nossa região.</p>
        <a href="enviar_evento.php" class="btn-cadastrar"><i class="fas fa-calendar-plus"></i> Divulgar Meu Evento</a>
    </section>

    <div class="container-agenda">
        <?php if($res_eventos && $res_eventos->num_rows > 0): ?>
            <?php while($evento = $res_eventos->fetch_assoc()): 
                $data_partes = explode('-', $evento['data_evento']);
                $dia = $data_partes[2];
                $mes_num = (int)$data_partes[1];
                // Limita a descrição a 120 caracteres para a prévia
                $descricao_curta = mb_substr($evento['descricao'], 0, 120) . (mb_strlen($evento['descricao']) > 120 ? '...' : '');
            ?>
                <div class="evento-card">
                    <div class="evento-data">
                        <span class="dia"><?= $dia ?></span>
                        <span class="mes"><?= $meses[$mes_num] ?></span>
                    </div>
                    
                    <?php if($evento['imagem']): ?>
                    <div class="evento-img">
                        <a href="evento.php?id=<?= $evento['id'] ?>"><img src="uploads/eventos/<?= $evento['imagem'] ?>" alt="Capa do Evento"></a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="evento-info">
                        <h3><a href="evento.php?id=<?= $evento['id'] ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($evento['titulo']) ?></a></h3>
                        <p><?= htmlspecialchars($descricao_curta) ?></p>
                        
                        <div class="evento-meta">
                            <div class="meta-detalhes">
                                <span><i class="far fa-clock"></i> <?= date('H:i', strtotime($evento['hora_evento'])) ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($evento['local_evento']) ?></span>
                            </div>
                            <a href="evento.php?id=<?= $evento['id'] ?>" class="btn-leia-mais">Ver Detalhes</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="background: white; padding: 50px; text-align: center; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                <i class="far fa-calendar-times" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                <h3 style="color: var(--vinho); margin-bottom: 10px;">Nenhum evento agendado</h3>
                <p style="color: #666;">A agenda está livre por enquanto.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>