<?php 
include('includes/db.php');
include('functions.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ==========================================
// 1. PROCESSAMENTO DA ATUALIZAÇÃO (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente   = mysqli_real_escape_string($conn, $_POST['cliente']);
    $tamanho   = mysqli_real_escape_string($conn, $_POST['tamanho']);
    $exibicao  = mysqli_real_escape_string($conn, $_POST['exibicao']);
    $rotacao   = intval($_POST['rotacao']);
    $url_link  = mysqli_real_escape_string($conn, $_POST['url']);

    // Começa a montar a query de update
    $sql_update = "UPDATE banners SET 
                   cliente = '$cliente', 
                   tamanho = '$tamanho', 
                   exibicao = '$exibicao', 
                   rotacao = $rotacao, 
                   url_link = '$url_link'";

    // Verifica se o usuário enviou uma NOVA imagem
    if (isset($_FILES['arte']) && $_FILES['arte']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['arte']['name'], PATHINFO_EXTENSION));
        $nome_banner = "ad_" . time() . "_" . rand(10, 99) . "." . $extensao;
        
        if(!is_dir("assets/img/ads/")){ mkdir("assets/img/ads/", 0777, true); }
        
        if(move_uploaded_file($_FILES['arte']['tmp_name'], "assets/img/ads/" . $nome_banner)) {
            // Se subiu a nova com sucesso, vamos apagar a antiga da pasta
            $q_old = mysqli_query($conn, "SELECT arquivo_arte FROM banners WHERE id = $id");
            if($old_banner = mysqli_fetch_assoc($q_old)) {
                $caminho_antigo = "assets/img/ads/" . $old_banner['arquivo_arte'];
                if(file_exists($caminho_antigo) && !empty($old_banner['arquivo_arte'])) {
                    unlink($caminho_antigo);
                }
            }
            // Adiciona a nova imagem na query de update
            $sql_update .= ", arquivo_arte = '$nome_banner'";
        }
    }

    $sql_update .= " WHERE id = $id";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>
                alert('Campanha atualizada com sucesso!'); 
                window.location.href='admin.php?view=ads';
              </script>";
        exit;
    } else {
        $erro = "Erro ao atualizar: " . mysqli_error($conn);
    }
}

// ==========================================
// 2. BUSCA OS DADOS ATUAIS PARA PREENCHER O FORM
// ==========================================
$query = "SELECT * FROM banners WHERE id = $id";
$result = mysqli_query($conn, $query);
$ad = mysqli_fetch_assoc($result);

if (!$ad) {
    header("Location: admin.php?view=ads");
    exit;
}

$categoriasAdmin = obterCategoriasAdmin($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Campanha | ETHOS ADM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --vermelho: #ce1212; --preto: #0f0f0f; --fundo: #f4f7f6; --borda: #e0e0e0; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background: var(--fundo); height: 100vh; color: #333; }
        
        /* SIDEBAR (Igual ao Admin) */
        .sidebar { width: 280px; background: var(--preto); color: #fff; padding: 30px 20px; display: flex; flex-direction: column; }
        .sidebar h2 { color: var(--vermelho); text-align: center; font-weight: 900; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .nav-link { color: #aaa; text-decoration: none; padding: 15px; border-radius: 10px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; font-weight: 600; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: var(--vermelho); color: #fff; }
        
        /* MAIN */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .card { background: #fff; padding: 35px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--borda); }
        .form-group { margin-bottom: 25px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        label { display: block; font-weight: 800; margin-bottom: 10px; color: #222; font-size: 0.85rem; text-transform: uppercase; }
        input, select { width: 100%; padding: 14px; border: 1px solid var(--borda); border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;}
        .btn-save { background: var(--vermelho); color: #fff; border: none; padding: 20px; border-radius: 10px; font-weight: 900; cursor: pointer; width: 100%; text-transform: uppercase; transition: 0.3s; margin-top: 20px; }
        .btn-save:hover { background: #000; transform: translateY(-2px); }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #666; text-decoration: none; font-weight: 600; }
        .btn-back:hover { color: var(--vermelho); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h2>ETHOS ADM</h2>
        <a href="admin.php?view=noticias" class="nav-link"><i class="fas fa-newspaper"></i> NOVA MATÉRIA</a>
        <a href="admin.php?view=gerenciar" class="nav-link"><i class="fas fa-tasks"></i> GERENCIAR POSTS</a>
        <a href="admin.php?view=ads" class="nav-link active"><i class="fas fa-dollar-sign"></i> PUBLICIDADE / ADS</a>
        <a href="admin.php?view=radio" class="nav-link"><i class="fas fa-podcast"></i> RÁDIO & PLAYLIST</a>
        <a href="index.php" target="_blank" class="nav-link" style="margin-top:auto; background:#222;"><i class="fas fa-external-link-alt"></i> VER PORTAL</a>
    </aside>

    <main class="main-content">
        <a href="admin.php?view=ads" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar para Publicidade</a>
        
        <div class="card">
            <h3 style="font-weight:900; margin-bottom:10px;"> Editar Campanha</h3>
            <p style="font-size:0.8rem; color:#666; margin-bottom:25px;">Altere as informações ou faça upload de uma nova arte.</p>
            
            <?php if(isset($erro)) echo "<div style='color:red; margin-bottom:20px;'>$erro</div>"; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nome do Cliente / Campanha</label>
                    <input type="text" name="cliente" required value="<?= htmlspecialchars($ad['cliente']) ?>">
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Tamanho do Banner</label>
                        <select name="tamanho" required>
                            <option value="mega" <?= $ad['tamanho'] == 'mega' ? 'selected' : '' ?>>Mega Banner Topo (970x250px)</option>
                            <option value="skyscraper" <?= $ad['tamanho'] == 'skyscraper' ? 'selected' : '' ?>>Lateral Skyscraper (300x600px)</option>
                            <option value="retangulo" <?= $ad['tamanho'] == 'retangulo' ? 'selected' : '' ?>>Retângulo de Matéria (728x90px)</option>
                            <option value="quadrado" <?= $ad['tamanho'] == 'quadrado' ? 'selected' : '' ?>>Quadrado Lateral (300x250px)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Onde Exibir? (Página de Arquivo/Categoria)</label>
                        <select name="exibicao" required>
                            <optgroup label="Geral">
                                <option value="home" <?= $ad['exibicao'] == 'home' ? 'selected' : '' ?>>Somente na Home</option>
                                <option value="materias" <?= $ad['exibicao'] == 'materias' ? 'selected' : '' ?>>Em todas as matérias (Internas)</option>
                                <option value="portal" <?= $ad['exibicao'] == 'portal' ? 'selected' : '' ?>>Em todo o portal (Global)</option>
                            </optgroup>
                            <optgroup label="Arquivos por Categoria">
                                <?php foreach ($categoriasAdmin as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria['slug']) ?>" <?= $ad['exibicao'] == $categoria['slug'] ? 'selected' : '' ?>>
                                        Arquivo: <?= htmlspecialchars($categoria['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Grupo de Rotação (1 a 5)</label>
                        <select name="rotacao">
                            <option value="1" <?= $ad['rotacao'] == 1 ? 'selected' : '' ?>>Banner Principal (Fixo ou 1º da Fila)</option>
                            <option value="2" <?= $ad['rotacao'] == 2 ? 'selected' : '' ?>>2º na Rotação</option>
                            <option value="3" <?= $ad['rotacao'] == 3 ? 'selected' : '' ?>>3º na Rotação</option>
                            <option value="4" <?= $ad['rotacao'] == 4 ? 'selected' : '' ?>>4º na Rotação</option>
                            <option value="5" <?= $ad['rotacao'] == 5 ? 'selected' : '' ?>>5º na Rotação</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Link de Destino</label>
                        <input type="text" name="url" value="<?= htmlspecialchars($ad['url_link']) ?>">
                    </div>
                </div>

                <div class="form-group" style="background: #f9f9f9; padding: 20px; border-radius: 10px; border: 1px dashed #ccc;">
                    <label>Arte Atual</label>
                    <div style="margin-bottom: 15px;">
                        <img src="assets/img/ads/<?= $ad['arquivo_arte'] ?>" style="max-height: 100px; border-radius: 5px; border: 1px solid #ddd; background: #fff;">
                    </div>
                    
                    <label>Substituir Arte (Deixe em branco para manter a atual)</label>
                    <input type="file" name="arte" accept="image/*" style="background: #fff;">
                </div>

                <button type="submit" class="btn-save">SALVAR ALTERAÇÕES</button>
            </form>
        </div>
    </main>

</body>
</html>