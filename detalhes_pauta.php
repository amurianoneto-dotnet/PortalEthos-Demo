<?php
session_start();

// Garante que o menu funcione no header
$menuCategorias = [
    ['label' => 'Turismo', 'slug' => 'turismo', 'children' => [['label' => 'Caminhos do Tietê', 'slug' => 'caminhos-do-tiete']]],
    ['label' => 'BioEthos', 'slug' => 'bioethos'],
    ['label' => 'EvenThos', 'slug' => 'eventhos'],
    ['label' => "Joana D'Arc", 'slug' => 'joana-darc'],
    ['label' => 'Poethos', 'slug' => 'poethos', 'children' => [['label' => 'Cultura', 'slug' => 'cultura']]],
    ['label' => 'Selethos', 'slug' => 'selethos'],
    ['label' => 'Arquitethos', 'slug' => 'arquitethos'],
    ['label' => 'Saúde', 'slug' => 'saude'],
    ['label' => 'Moda', 'slug' => 'moda'],
    ['label' => 'Estética', 'slug' => 'estetica'], 
];
$quickMenuLinks = [['label' => 'Home', 'href' => 'index.php']];

$conn = new mysqli('localhost', 'root', '', 'portal_ethos');
if (!isset($_GET['id'])) { header("Location: ver_pautas.php"); exit(); }

$id = $conn->real_escape_string($_GET['id']);

// Lógica de Atualizar Status
if (isset($_POST['novo_status'])) {
    $status_update = $_POST['novo_status'];
    $conn->query("UPDATE pautas SET status = '$status_update' WHERE id = '$id'");
}

$sql = "SELECT p.*, u.nome as nome_leitor, u.email as email_leitor 
        FROM pautas p 
        JOIN usuarios u ON p.leitor_id = u.id 
        WHERE p.id = '$id'";
$res = $conn->query($sql);
$pauta = $res->fetch_assoc();

if (!$pauta) { die("Pauta não encontrada."); }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Pauta | Redação Ethos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --vinho: #8C0303; --preto: #000; --bg: #f4f4f4; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; color: #333; }
        .admin-container { max-width: 900px; margin: 0 auto; padding: 160px 20px 60px; }
        .card-detalhe { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        
        .header-detalhe { background: var(--preto); color: white; padding: 30px; display: flex; justify-content: space-between; align-items: center; }
        .header-detalhe h1 { margin: 0; font-size: 1.4rem; text-transform: uppercase; }
        .btn-voltar { color: white; text-decoration: none; font-size: 0.9rem; border: 1px solid #444; padding: 8px 15px; border-radius: 5px; transition: 0.3s; }
        .btn-voltar:hover { background: #333; }

        .content { padding: 40px; }
        .meta-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 10px; border-left: 5px solid var(--vinho); }
        .meta-info div span { display: block; font-size: 0.8rem; color: #888; text-transform: uppercase; }
        .meta-info div strong { font-size: 1rem; color: #333; }

        .pauta-texto { line-height: 1.8; color: #444; font-size: 1.1rem; white-space: pre-wrap; margin-bottom: 40px; background: #fff; border: 1px solid #eee; padding: 25px; border-radius: 10px; }
        .pauta-imagem { max-width: 100%; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        .acoes { border-top: 1px solid #eee; padding-top: 30px; display: flex; gap: 15px; align-items: center; }
        .btn-status { border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; font-family: inherit; }
        .btn-analise { background: #cfe2ff; color: #084298; }
        .btn-finalizar { background: #d1e7dd; color: #0f5132; }
        .btn-analise:hover, .btn-finalizar:hover { filter: brightness(0.9); transform: translateY(-2px); }
        
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; }
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-em_analise { background: #cfe2ff; color: #084298; }
        .status-finalizada { background: #d1e7dd; color: #0f5132; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="admin-container">
        <div class="card-detalhe">
            <div class="header-detalhe">
                <h1><i class="fas fa-file-alt"></i> Detalhes da Sugestão</h1>
                <a href="ver_pautas.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar pra Lista</a>
            </div>

            <div class="content">
                <div class="meta-info">
                    <div>
                        <span>Enviado por:</span>
                        <strong><?= htmlspecialchars($pauta['nome_leitor']) ?> (<?= htmlspecialchars($pauta['email_leitor']) ?>)</strong>
                    </div>
                    <div>
                        <span>Data e Status:</span>
                        <strong><?= date('d/m/Y H:i', strtotime($pauta['data_envio'])) ?></strong> - 
                        <span class="badge status-<?= $pauta['status'] ?>"><?= str_replace('_', ' ', $pauta['status']) ?></span>
                    </div>
                </div>

                <h2 style="color: var(--vinho); margin-top: 0; font-size: 1.5rem;"><?= htmlspecialchars($pauta['titulo']) ?></h2>
                
                <div class="pauta-texto"><?= htmlspecialchars($pauta['conteudo']) ?></div>

                <?php if ($pauta['arquivo']): ?>
                    <p style="font-weight: bold; color: #666;"><i class="fas fa-paperclip"></i> Anexo enviado:</p>
                    <img src="uploads/pautas/<?= $pauta['arquivo'] ?>" class="pauta-imagem" alt="Imagem da pauta">
                <?php endif; ?>

                <div class="acoes">
                    <span style="font-weight: 600; font-size: 0.95rem; color: #555;">Mudar Status para:</span>
                    <form method="POST" style="display: flex; gap: 10px; margin: 0;">
                        <button type="submit" name="novo_status" value="em_analise" class="btn-status btn-analise"><i class="fas fa-spinner"></i> Em Análise</button>
                        <button type="submit" name="novo_status" value="finalizada" class="btn-status btn-finalizar"><i class="fas fa-check"></i> Finalizada</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>