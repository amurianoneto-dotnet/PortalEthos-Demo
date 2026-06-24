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
if ($conn->connect_error) { die("Erro: " . $conn->connect_error); }

// Busca os dados (Atenção: A sua tabela de usuários se chama 'usuarios' no banco que criamos, então mudei de 'leitores' para 'usuarios')
$sql = "SELECT p.*, u.nome as nome_leitor 
        FROM pautas p 
        JOIN usuarios u ON p.leitor_id = u.id 
        ORDER BY p.data_envio DESC";
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciador de Pautas | Redação Ethos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --vinho: #8C0303; --preto: #1a1a1a; --bg: #f8f9fa; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg); margin: 0; color: #333; }
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 160px 20px 60px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 3px solid var(--vinho); padding-bottom: 15px; }
        .admin-header h2 { margin: 0; color: var(--vinho); text-transform: uppercase; }
        .table-container { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--preto); color: white; padding: 18px; text-align: left; font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 18px; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        tr:hover { background-color: #f9f9f9; }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-em_analise { background: #cfe2ff; color: #084298; }
        .status-finalizada { background: #d1e7dd; color: #0f5132; }
        .btn-view { background: var(--vinho); color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 0.85rem; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-view:hover { background: #000; }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h2><i class="fas fa-newspaper"></i> Central de Pautas Recebidas</h2>
            <div style="font-weight: bold; color: #666;">Total: <?= $res->num_rows ?></div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Autor</th>
                        <th>Título</th>
                        <th>Status</th>
                        <th>Anexo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td style="color: #666; font-size: 0.85rem;"><i class="far fa-clock"></i> <?= date('d/m H:i', strtotime($row['data_envio'])) ?></td>
                            <td><strong><?= htmlspecialchars($row['nome_leitor']) ?></strong></td>
                            <td><?= htmlspecialchars($row['titulo']) ?></td>
                            <td><span class="badge status-<?= $row['status'] ?>"><?= str_replace('_', ' ', $row['status']) ?></span></td>
                            <td>
                                <?php if($row['arquivo']): ?>
                                    <a href="uploads/pautas/<?= $row['arquivo'] ?>" target="_blank" style="color: var(--vinho); font-weight:bold; text-decoration:none;"><i class="fas fa-image"></i> Ver</a>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="detalhes_pauta.php?id=<?= $row['id'] ?>" class="btn-view"><i class="fas fa-eye"></i> Detalhes</a></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">Nenhuma pauta enviada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>