<?php 
session_start();

// O SEGURANÇA DA PORTA: Se não tem a sessão logada, manda pro login!
if (!isset($_SESSION['admin_logado']) || $_SESSION['admin_logado'] !== true) {
    header('Location: login_admin.php');
    exit;
}
include('includes/db.php');
include('functions.php');

$view = $_GET['view'] ?? 'noticias';
$categoriasAdmin = obterCategoriasAdmin($conn);

// ==========================================
// AÇÕES DE REORDENAÇÃO (SETINHAS)
// ==========================================
if (isset($_GET['mover_cat']) && isset($_GET['dir'])) {
    $id = (int)$_GET['mover_cat'];
    $dir = $_GET['dir'] == 'up' ? -1 : 1;
    $conn->query("UPDATE categorias SET ordem = ordem + ($dir) WHERE id = $id");
    header("Location: admin.php?view=categorias");
    exit;
}
if (isset($_GET['mover_video']) && isset($_GET['dir'])) {
    $id = (int)$_GET['mover_video'];
    $dir = $_GET['dir'] == 'up' ? -1 : 1;
    $conn->query("UPDATE ethos_play SET ordem = ordem + ($dir) WHERE id = $id");
    header("Location: admin.php?view=radio");
    exit;
}

// LOGOUT
if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: login_admin.php');
    exit;
}

// ==========================================
// AÇÕES DE AUTORES / COLUNISTAS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'cadastrar_autor') {
    $nome = $conn->real_escape_string($_POST['nome']);
    $cargo = $conn->real_escape_string($_POST['cargo']);
    $instagram = $conn->real_escape_string($_POST['instagram']);
    $biografia = $conn->real_escape_string($_POST['biografia']);
    $nome_foto = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $diretorio = "uploads/autores/";
        if (!is_dir($diretorio)) { mkdir($diretorio, 0777, true); }
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome_foto = "autor_" . uniqid() . "." . $extensao;
        move_uploaded_file($_FILES['foto']['tmp_name'], $diretorio . $nome_foto);
    }

    $conn->query("INSERT INTO autores (nome, cargo, instagram, biografia, foto) VALUES ('$nome', '$cargo', '$instagram', '$biografia', '$nome_foto')");
    header("Location: admin.php?view=autores");
    exit;
}

if (isset($_GET['excluir_autor'])) {
    $id_a = (int)$_GET['excluir_autor'];
    $conn->query("DELETE FROM autores WHERE id = $id_a");
    header("Location: admin.php?view=autores");
    exit;
}

// Se houver uma requisição para mudar status da pauta via admin
if (isset($_GET['mudar_status_pauta']) && isset($_GET['id_pauta'])) {
    $id_p = (int)$_GET['id_pauta'];
    $novo_status = $conn->real_escape_string($_GET['mudar_status_pauta']);
    $conn->query("UPDATE pautas SET status = '$novo_status' WHERE id = $id_p");
    header("Location: admin.php?view=pautas");
    exit;
}

// Se houver requisição para excluir pauta
if (isset($_GET['excluir_pauta'])) {
    $id_p = (int)$_GET['excluir_pauta'];
    $conn->query("DELETE FROM pautas WHERE id = $id_p");
    header("Location: admin.php?view=pautas");
    exit;
}
// ==========================================
// AÇÕES DE EVENTOS
// ==========================================
if (isset($_GET['mudar_status_evento']) && isset($_GET['id_evento'])) {
    $id_e = (int)$_GET['id_evento'];
    $novo_status = $conn->real_escape_string($_GET['mudar_status_evento']);
    $conn->query("UPDATE eventos SET status = '$novo_status' WHERE id = $id_e");
    header("Location: admin.php?view=eventos");
    exit;
}

if (isset($_GET['excluir_evento'])) {
    $id_e = (int)$_GET['excluir_evento'];
    $conn->query("DELETE FROM eventos WHERE id = $id_e");
    header("Location: admin.php?view=eventos");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'cadastrar_evento_admin') {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $data_evento = $_POST['data_evento'];
    $hora_evento = $_POST['hora_evento'];
    $local_evento = $conn->real_escape_string($_POST['local_evento']);
    $nome_arquivo = null;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $diretorio = "uploads/eventos/";
        if (!is_dir($diretorio)) { mkdir($diretorio, 0777, true); }
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid() . "." . $extensao;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorio . $nome_arquivo);
    }

    $sql_insert_evento = "INSERT INTO eventos (usuario_id, titulo, descricao, data_evento, hora_evento, local_evento, imagem, status) 
                          VALUES ('0', '$titulo', '$descricao', '$data_evento', '$hora_evento', '$local_evento', '$nome_arquivo', 'aprovado')";
    $conn->query($sql_insert_evento);
    header("Location: admin.php?view=eventos");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Dashboard | Portal ETHOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>

    <style>
        :root { --vermelho: #ce1212; --preto: #0f0f0f; --fundo: #f4f7f6; --borda: #e0e0e0; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background: var(--fundo); height: 100vh; color: #333; }
        
        /* SIDEBAR */
        .sidebar { width: 280px; background: var(--preto); color: #fff; padding: 30px 20px; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar h2 { color: var(--vermelho); text-align: center; font-weight: 900; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .nav-link { color: #aaa; text-decoration: none; padding: 15px; border-radius: 10px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; font-weight: 600; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: var(--vermelho); color: #fff; }
        
        /* MAIN */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .card { background: #fff; padding: 35px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--borda); margin-bottom: 30px; }
        .form-group { margin-bottom: 25px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        label { display: block; font-weight: 800; margin-bottom: 10px; color: #222; font-size: 0.85rem; text-transform: uppercase; }
        input, select, textarea { width: 100%; padding: 14px; border: 1px solid var(--borda); border-radius: 8px; font-size: 0.95rem; }
        .btn-save { background: var(--vermelho); color: #fff; border: none; padding: 20px; border-radius: 10px; font-weight: 900; cursor: pointer; width: 100%; text-transform: uppercase; transition: 0.3s; }
        .btn-save:hover { background: #000; transform: translateY(-2px); }
        .ck-editor__editable { min-height: 350px; }

        /* TABELA DE GESTÃO */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8f8f8; padding: 15px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #888; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .btn-action { padding: 8px; border-radius: 6px; text-decoration: none; color: #fff; font-size: 12px; margin-right: 5px; cursor: pointer; border: none; }
        .btn-edit { background: #2196F3; }
        .btn-delete { background: #f44336; }

        /* PAUTAS ESPECÍFICOS */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-em_analise { background: #cfe2ff; color: #084298; }
        .status-finalizada { background: #d1e7dd; color: #0f5132; }
        .pauta-texto-preview { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem; color: #666; }

        /* COLUNISTAS */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8f8f8; padding: 15px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #888; border-bottom: 2px solid #eee; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; vertical-align: middle; }
        .btn-action { padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #fff; font-size: 12px; margin-right: 5px; cursor: pointer; border: none; display: inline-block; font-weight: 600;}
        .btn-edit { background: #2196F3; }
        .btn-delete { background: #f44336; }
        .btn-approve { background: #4CAF50; }
        .btn-reject { background: #FF9800; }

        /* BADGES */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-aprovado { background: #d1e7dd; color: #0f5132; }
        .status-rejeitado { background: #f8d7da; color: #721c24; }
        .status-em_analise { background: #cfe2ff; color: #084298; }
        .status-finalizada { background: #d1e7dd; color: #0f5132; }
        .pauta-texto-preview { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem; color: #666; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h2>ETHOS ADM</h2>
        <a href="?view=noticias" class="nav-link <?= ($view == 'noticias') ? 'active' : '' ?>"><i class="fas fa-newspaper"></i> NOVA MATÉRIA</a>
        <a href="?view=gerenciar" class="nav-link <?= ($view == 'gerenciar') ? 'active' : '' ?>"><i class="fas fa-tasks"></i> GERENCIAR POSTS</a>
        
        <a href="?view=categorias" class="nav-link <?= ($view == 'categorias') ? 'active' : '' ?>"><i class="fas fa-tags"></i> EDITORIAS</a>
        <a href="?view=autores" class="nav-link <?= ($view == 'autores') ? 'active' : '' ?>"><i class="fas fa-users"></i> EQUIPE / COLUNISTAS</a>
        <a href="?view=eventos" class="nav-link <?= ($view == 'eventos') ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> AGENDA EVENTHOS</a>
        <a href="?view=coberturas" class="nav-link <?= ($view == 'coberturas') ? 'active' : '' ?>"><i class="fas fa-camera-retro"></i> COBERTURA DE EVENTOS</a>
        <a href="?view=pautas" class="nav-link <?= ($view == 'pautas' || $view == 'ler_pauta') ? 'active' : '' ?>"><i class="fas fa-inbox"></i> CAIXA DE SUGESTÕES</a>
        <a href="?view=ads" class="nav-link <?= ($view == 'ads') ? 'active' : '' ?>"><i class="fas fa-dollar-sign"></i> PUBLICIDADE / ADS</a>
        <a href="?view=radio" class="nav-link <?= ($view == 'radio') ? 'active' : '' ?>"><i class="fas fa-podcast"></i> RÁDIO & PLAYLIST</a>
        <a href="index.php" target="_blank" class="nav-link" style="margin-top:auto; background:#222;"><i class="fas fa-external-link-alt"></i> VER PORTAL</a>
    </aside>

    <main class="main-content">
        <div class="card">
       
            <?php if ($view == 'noticias'): ?>
                <h3 style="font-weight:900; margin-bottom:25px;"> Publicar Nova Matéria</h3>
                <form action="processa_post.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group"><label>Título Principal</label><input type="text" name="titulo" required></div>
                    <div class="form-group"><label>Subtítulo / Gravata</label><input type="text" name="subtitulo"></div>
                    <div class="form-group"><label>Descrição Curta (Lead)</label><textarea name="descricao" rows="2"></textarea></div>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Editoria / Categoria</label>
                            <select name="categoria" required>
                                <?php foreach ($categoriasAdmin as $categoria): ?>
                                    <option value="<?php echo htmlspecialchars($categoria['slug'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Autor / Colunista</label>
                            <select name="autor_id" required>
                                <?php 
                                $res_autores = mysqli_query($conn, "SELECT id, nome FROM autores ORDER BY id ASC");
                                while($aut = mysqli_fetch_assoc($res_autores)): 
                                ?>
                                    <option value="<?= $aut['id'] ?>"><?= htmlspecialchars($aut['nome']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group"><label>Imagem de Capa</label><input type="file" name="imagem" required></div>
                        <div class="form-group">
                            <label>Fotos da Galeria (Opcional)</label>
                            <input type="file" name="galeria[]" multiple accept="image/*">
                            <small style="color:#666;">Segure Ctrl ou Shift para selecionar várias.</small>
                        </div>
                    </div>
                    
                    <div class="form-group"><label>SEO - Palavras-chave (separadas por vírgula)</label><input type="text" name="keywords" placeholder="ex: turismo, tietê, notícias"></div>
                    <div class="form-group"><label>Conteúdo Completo</label><textarea id="editor" name="conteudo"></textarea></div>
                    <button class="btn-save">PUBLICAR AGORA</button>
                </form>

                <?php elseif ($view == 'autores'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-weight:900; margin:0;"><i class="fas fa-users" style="color:var(--vermelho);"></i> Gestão de Equipe e Colunistas</h3>
                </div>

                <div style="background: #fafafa; padding: 25px; border-radius: 10px; border: 1px solid #eee; margin-bottom: 40px;">
                    <h4 style="margin-top:0; color:#333; margin-bottom: 20px;"><i class="fas fa-user-plus"></i> Cadastrar Novo Membro</h4>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="acao" value="cadastrar_autor">
                        <div class="grid-2">
                            <div class="form-group"><label>Nome Completo</label><input type="text" name="nome" placeholder="Ex: Thais Moraes" required></div>
                            <div class="form-group"><label>Cargo / Especialidade</label><input type="text" name="cargo" placeholder="Ex: Jornalista Sênior"></div>
                        </div>
                        <div class="grid-2">
                            <div class="form-group"><label>Instagram (Somente a @)</label><input type="text" name="instagram" placeholder="Ex: @thaismoraes"></div>
                            <div class="form-group"><label>Foto do Perfil (Rosto)</label><input type="file" name="foto" accept="image/*"></div>
                        </div>
                        <div class="form-group"><label>Breve Biografia</label><textarea name="biografia" rows="3" placeholder="Formada em jornalismo, apaixonada por contar histórias..."></textarea></div>
                        <button type="submit" class="btn-save" style="padding: 15px; font-size: 0.9rem;">Salvar Autor</button>
                    </form>
                </div>

                <h3 style="font-weight:900; margin-bottom:15px; border-top: 1px solid #eee; padding-top: 30px;"> Equipe Atual</h3>
                <table>
                    <thead><tr><th>Foto</th><th>Nome e Cargo</th><th>Instagram</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php
                        $res_equipe = $conn->query("SELECT * FROM autores ORDER BY nome ASC");
                        while($eq = $res_equipe->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if($eq['foto']): ?>
                                        <img src="uploads/autores/<?= $eq['foto'] ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #ddd; border-radius: 50%; display:flex; align-items:center; justify-content:center; color:#888;"><i class="fas fa-user"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 800; font-size: 15px; color: #111;"><?= htmlspecialchars($eq['nome']) ?></div>
                                    <div style="color: #666; font-size: 12px;"><?= htmlspecialchars($eq['cargo']) ?></div>
                                </td>
                                <td><span style="font-size:12px; color:var(--vermelho); font-weight: bold;"><?= htmlspecialchars($eq['instagram']) ?></span></td>
                                <td>
                                    <?php if($eq['id'] != 1): // O ID 1 é a 'Redação Ethos', não deixa excluir ?>
                                        <a href="?excluir_autor=<?= $eq['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Excluir este colunista? As matérias dele continuarão, mas sem perfil.')"><i class="fas fa-trash"></i></a>
                                    <?php else: ?>
                                        <span style="font-size:11px; color:#888; font-style:italic;">Padrão</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <?php elseif ($view == 'gerenciar'): ?>
                <h3 style="font-weight:900; margin-bottom:25px;"> Gerenciar Matérias Publicadas</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Categoria</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT id, titulo, categoria_id, data_publicacao FROM noticias ORDER BY id DESC";
                        $res = mysqli_query($conn, $query);
                        while($row = mysqli_fetch_assoc($res)):
                        ?>
                        <tr>
                            <td style="font-weight:600;"><?= mb_substr($row['titulo'], 0, 60) ?>...</td>
                            <td><span style="background:#eee; padding:5px 10px; border-radius:4px; font-size:11px;"><?= htmlspecialchars(strtoupper(obterNomeCategoria($row['categoria_id'], $row['categoria_id'])), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td style="color:#888; font-size:12px;"><?= date('d/m/Y', strtotime($row['data_publicacao'])) ?></td>
                            <td>
                                <a href="editar_noticia.php?id=<?= $row['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="excluir_noticia.php?id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Excluir esta matéria permanentemente?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php elseif ($view == 'categorias'): ?>
<h3 style="font-weight:900; margin-bottom:10px;"> Gestão de Editorias (Categorias)</h3>
<p style="font-size:0.8rem; color:#666; margin-bottom:25px;">Adicione novas categorias para organizar suas matérias.</p>
                
<form action="processa_categoria.php" method="POST" style="margin-bottom: 40px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
        <div class="form-group">
            <label>Nome da Editoria</label>
            <input type="text" name="nome" required placeholder="Ex: Estética">
        </div>
        <div class="form-group">
            <label>Slug (URL Amigável)</label>
            <input type="text" name="slug" required placeholder="Ex: estetica">
        </div>
        <div class="form-group">
            <label>Subcategoria de: (Opcional)</label>
            <select name="parent_id">
                <option value="">Nenhuma (Editoria Principal)</option>
                <?php 
                $res_pais = mysqli_query($conn, "SELECT id, nome FROM categorias WHERE parent_id IS NULL ORDER BY nome ASC");
                if($res_pais) {
                    while($pai = mysqli_fetch_assoc($res_pais)): 
                ?>
                    <option value="<?= $pai['id'] ?>"><?= htmlspecialchars($pai['nome']) ?></option>
                <?php 
                    endwhile; 
                } 
                ?>
            </select>
        </div>
    </div>
    <button type="submit" class="btn-save" style="margin-top: 15px;">CADASTRAR EDITORIA</button>
</form>

<hr style="border: 1px solid #f0f0f0; margin-bottom: 30px;">
<h3 style="font-weight:900; margin-bottom:20px;"> Editorias Cadastradas</h3>
                
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome da Editoria</th>
            <th>Slug (URL)</th>
            <th style="text-align:center;">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query_cat = "SELECT * FROM categorias ORDER BY ordem ASC, nome ASC"; 
        $res_cat = mysqli_query($conn, $query_cat);
                        
        if($res_cat && mysqli_num_rows($res_cat) > 0):
            while($cat = mysqli_fetch_assoc($res_cat)):
        ?>
        <tr>
            <td style="color:#888; font-size:12px;">#<?= $cat['id'] ?></td>
            <td style="font-weight:800; color: var(--vermelho);"><?= htmlspecialchars($cat['nome']) ?></td>
            <td><span style="background:#eee; padding:5px 10px; border-radius:4px; font-size:12px;"><?= htmlspecialchars($cat['slug']) ?></span></td>
            <td style="text-align:center;">
                <a href="?mover_cat=<?= $cat['id'] ?>&dir=up" class="btn-action" style="background:#555;" title="Subir"><i class="fas fa-arrow-up"></i></a>
                <a href="?mover_cat=<?= $cat['id'] ?>&dir=down" class="btn-action" style="background:#555;" title="Descer"><i class="fas fa-arrow-down"></i></a>
                
                <a href="excluir_categoria.php?id=<?= $cat['id'] ?>" class="btn-action btn-delete" onclick="return confirm('CUIDADO: Tem certeza que deseja excluir esta editoria? Matérias ligadas a ela podem ficar sem categoria.')" title="Excluir"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php 
            endwhile;
        else:
        ?>
            <tr><td colspan="4" style="padding:20px; text-align:center; color:#999;">Nenhuma editoria cadastrada ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
                <?php elseif ($view == 'eventos'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-weight:900; margin:0;"><i class="fas fa-calendar-check" style="color:var(--vermelho);"></i> Gestão da Agenda Cultural</h3>
                </div>

                <div style="background: #fafafa; padding: 25px; border-radius: 10px; border: 1px solid #eee; margin-bottom: 40px;">
                    <h4 style="margin-top:0; color:#333; margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> Cadastrar Evento Oficial</h4>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="acao" value="cadastrar_evento_admin">
                        <div class="form-group">
                            <label>Nome do Evento</label>
                            <input type="text" name="titulo" placeholder="Ex: Show na Praça" required>
                        </div>
                        <div class="grid-2">
                            <div class="form-group"><label>Data</label><input type="date" name="data_evento" required></div>
                            <div class="form-group"><label>Horário</label><input type="time" name="hora_evento" required></div>
                        </div>
                        <div class="form-group"><label>Localização (Bairro/Cidade)</label><input type="text" name="local_evento" placeholder="Ex: Concha Acústica" required></div>
                        <div class="form-group"><label>Descrição Curta (Atrações, Ingressos)</label><textarea name="descricao" rows="3" required></textarea></div>
                        <div class="form-group"><label>Banner ou Imagem</label><input type="file" name="imagem" accept="image/*"></div>
                        <button type="submit" class="btn-save" style="padding: 15px; font-size: 0.9rem;">Publicar na Agenda</button>
                    </form>
                </div>

                <h3 style="font-weight:900; margin-bottom:15px; border-top: 1px solid #eee; padding-top: 30px;"> Lista de Eventos Cadastrados</h3>
                <?php
                $sql_eventos = "SELECT e.*, COALESCE(u.nome, 'Equipe ETHOS') as autor FROM eventos e LEFT JOIN usuarios u ON e.usuario_id = u.id ORDER BY e.data_evento DESC";
                $res_eventos = $conn->query($sql_eventos);
                ?>
                <table>
                    <thead>
                        <tr><th>Preview</th><th>Data/Hora</th><th>Evento</th><th>Autor</th><th>Status</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php if($res_eventos && $res_eventos->num_rows > 0): ?>
                            <?php while($ev = $res_eventos->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if($ev['imagem']): ?>
                                        <img src="uploads/eventos/<?= $ev['imagem'] ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #eee; border-radius: 8px; display:flex; align-items:center; justify-content:center; color:#ccc;"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($ev['data_evento'])) ?></strong><br>
                                    <span style="font-size:12px; color:#888;"><i class="far fa-clock"></i> <?= date('H:i', strtotime($ev['hora_evento'])) ?></span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #111;"><?= htmlspecialchars($ev['titulo']) ?></div>
                                    <div class="pauta-texto-preview" style="font-size: 11px;"><i class="fas fa-map-marker-alt" style="color:var(--vermelho);"></i> <?= htmlspecialchars($ev['local_evento']) ?></div>
                                </td>
                                <td style="font-size: 12px; color:#666;"><i class="fas fa-user"></i> <?= htmlspecialchars($ev['autor']) ?></td>
                                <td><span class="badge status-<?= $ev['status'] ?>"><?= $ev['status'] ?></span></td>
                                <td>
                                    <?php if($ev['status'] == 'pendente'): ?>
                                        <a href="?mudar_status_evento=aprovado&id_evento=<?= $ev['id'] ?>" class="btn-action btn-approve" title="Aprovar"><i class="fas fa-check"></i></a>
                                        <a href="?mudar_status_evento=rejeitado&id_evento=<?= $ev['id'] ?>" class="btn-action btn-reject" title="Rejeitar"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                    <a href="?excluir_evento=<?= $ev['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Excluir este evento?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">Nenhum evento na agenda.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php elseif ($view == 'coberturas'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-weight:900; margin:0;"><i class="fas fa-camera-retro" style="color:var(--vermelho);"></i> Cobertura Fotográfica</h3>
                </div>

                <div style="background: #fafafa; padding: 25px; border-radius: 10px; border: 1px solid #eee; margin-bottom: 40px;">
                    <h4 style="margin-top:0; color:#333; margin-bottom: 20px;"><i class="fas fa-images"></i> Criar Novo Álbum</h4>
                    
                    <form action="processa_cobertura.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Nome do Evento (Ex: Festa do Peão 2026)</label>
                            <input type="text" name="titulo" required>
                        </div>
                        <div class="grid-2">
                            <div class="form-group"><label>Data do Evento</label><input type="date" name="data_evento" required></div>
                            <div class="form-group"><label>Foto de Capa do Álbum</label><input type="file" name="capa" accept="image/*" required></div>
                        </div>
                        <div class="form-group">
                            <label>Descrição (Opcional)</label>
                            <textarea name="descricao" rows="2" placeholder="Breve texto sobre como foi o evento..."></textarea>
                        </div>
                        <div class="form-group" style="background: #fff; padding: 20px; border: 2px dashed #ccc; border-radius: 8px; text-align: center;">
                            <label style="font-size: 1.1rem; color: #8C0303;"><i class="fas fa-upload"></i> Selecione todas as fotos da cobertura</label>
                            <input type="file" name="fotos[]" multiple accept="image/*" required style="border: none; margin-top: 10px;">
                            <small style="color:#666; display:block; margin-top:5px;">Segure a tecla CTRL (ou Command) para selecionar várias fotos de uma vez.</small>
                        </div>
                        <button type="submit" class="btn-save" style="padding: 15px; font-size: 1rem;"><i class="fas fa-cloud-upload-alt"></i> SALVAR ÁLBUM E SUBIR FOTOS</button>
                    </form>
                </div>

                <h3 style="font-weight:900; margin-bottom:15px; border-top: 1px solid #eee; padding-top: 30px;"> Álbuns Publicados</h3>
                <table>
                    <thead>
                        <tr><th>Capa</th><th>Evento</th><th>Data</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $res_albuns = mysqli_query($conn, "SELECT * FROM coberturas_albuns ORDER BY data_evento DESC");
                        if(mysqli_num_rows($res_albuns) > 0):
                            while($album = mysqli_fetch_assoc($res_albuns)): 
                        ?>
                        <tr>
                            <td><img src="uploads/coberturas/<?= $album['capa'] ?>" style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($album['titulo']) ?></td>
                            <td><?= date('d/m/Y', strtotime($album['data_evento'])) ?></td>
                            <td>
                            <td>
                                <a href="editar_cobertura.php?id=<?= $album['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Editar</a>
                                <a href="excluir_cobertura.php?id=<?= $album['id'] ?>" class="btn-action btn-delete" onclick="return confirm('ATENÇÃO: Isso vai excluir o álbum e TODAS as fotos dentro dele. Tem certeza?')"><i class="fas fa-trash"></i> Excluir</a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                            echo "<tr><td colspan='4' style='text-align:center; color:#999; padding:20px;'>Nenhuma cobertura publicada ainda.</td></tr>";
                        endif;
                        ?>
                    </tbody>
                </table>

                <?php elseif ($view == 'pautas'): ?>
                <?php
                // Aqui no admin, sua tabela de usuários se chama 'usuarios'
                $sql_pautas = "SELECT p.*, u.nome as nome_leitor 
                               FROM pautas p 
                               JOIN usuarios u ON p.leitor_id = u.id 
                               ORDER BY p.data_envio DESC";
                $res_pautas = $conn->query($sql_pautas);
                ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-weight:900; margin:0;"><i class="fas fa-inbox" style="color:var(--vermelho);"></i> Caixa de Sugestões dos Leitores</h3>
                    <span style="background: #eee; padding: 5px 15px; border-radius: 20px; font-size: 13px; font-weight: bold; color: #555;">Total: <?= $res_pautas->num_rows ?? 0 ?></span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Autor (Leitor)</th>
                            <th>Assunto / Prévia</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($res_pautas && $res_pautas->num_rows > 0): ?>
                            <?php while($row = $res_pautas->fetch_assoc()): ?>
                            <tr>
                                <td style="color: #666; font-size: 0.85rem;"><?= date('d/m/Y H:i', strtotime($row['data_envio'])) ?></td>
                                <td><strong><?= htmlspecialchars($row['nome_leitor']) ?></strong></td>
                                <td>
                                    <div style="font-weight: 600; color: #111;"><?= htmlspecialchars($row['titulo']) ?></div>
                                    <div class="pauta-texto-preview"><?= htmlspecialchars($row['conteudo']) ?></div>
                                </td>
                                <td><span class="badge status-<?= $row['status'] ?>"><?= str_replace('_', ' ', $row['status']) ?></span></td>
                                <td>
                                    <a href="?view=ler_pauta&id=<?= $row['id'] ?>" class="btn-action" style="background: #4CAF50;"><i class="fas fa-eye"></i> Ler</a>
                                    <a href="?excluir_pauta=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Excluir esta sugestão para sempre?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 40px; color: #999;"><i class="fas fa-folder-open fa-2x" style="margin-bottom:10px; display:block; color:#ddd;"></i> Caixa de entrada vazia.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php elseif ($view == 'ler_pauta'): ?>
                <?php
                $id_pauta = (int)$_GET['id'];
                $sql_ler = "SELECT p.*, u.nome as nome_leitor, u.email as email_leitor 
                            FROM pautas p 
                            JOIN usuarios u ON p.leitor_id = u.id 
                            WHERE p.id = $id_pauta";
                $res_ler = $conn->query($sql_ler);
                $pauta = $res_ler->fetch_assoc();
                if(!$pauta){ echo "Pauta não encontrada."; exit; }
                ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-weight:900; margin:0;"><i class="fas fa-file-alt" style="color:var(--vermelho);"></i> Lendo Sugestão</h3>
                    <a href="?view=pautas" style="color: #666; text-decoration: none; font-weight: bold; font-size: 14px;"><i class="fas fa-arrow-left"></i> Voltar pra Lista</a>
                </div>

                <div style="background: #f9f9f9; padding: 20px; border-radius: 10px; border-left: 4px solid var(--vermelho); margin-bottom: 20px; font-size: 0.9rem;">
                    <strong>Autor:</strong> <?= htmlspecialchars($pauta['nome_leitor']) ?> (<?= htmlspecialchars($pauta['email_leitor']) ?>)<br>
                    <strong>Enviado em:</strong> <?= date('d/m/Y H:i', strtotime($pauta['data_envio'])) ?>
                </div>

                <h2 style="margin-top:0;"><?= htmlspecialchars($pauta['titulo']) ?></h2>
                <div style="background: #fff; border: 1px solid #eee; padding: 25px; border-radius: 10px; line-height: 1.8; color: #444; white-space: pre-wrap; font-size: 1rem; margin-bottom: 30px;"><?= htmlspecialchars($pauta['conteudo']) ?></div>

                <?php if ($pauta['arquivo']): ?>
                    <div style="margin-bottom: 30px;">
                        <h4 style="margin-top:0; color: #666;"><i class="fas fa-paperclip"></i> Anexo:</h4>
                        <a href="uploads/pautas/<?= $pauta['arquivo'] ?>" target="_blank" style="display: inline-block; background: var(--preto); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">Ver Arquivo / Foto</a>
                    </div>
                <?php endif; ?>

                <div style="border-top: 2px solid #eee; padding-top: 20px;">
                    <strong style="display:block; margin-bottom: 10px; font-size: 14px; color:#555;">Atualizar Status da Pauta:</strong>
                    <a href="?mudar_status_pauta=em_analise&id_pauta=<?= $pauta['id'] ?>" class="btn-action" style="background: #2196F3; padding: 10px 15px;"><i class="fas fa-spinner"></i> Marcar como Em Análise</a>
                    <a href="?mudar_status_pauta=finalizada&id_pauta=<?= $pauta['id'] ?>" class="btn-action" style="background: #4CAF50; padding: 10px 15px;"><i class="fas fa-check"></i> Marcar como Finalizada</a>
                </div>

           <?php elseif ($view == 'ads'): ?>
                <h3 style="font-weight:900; margin-bottom:10px;"> Gestão de Publicidade</h3>
                <form action="processa_ads.php" method="POST" enctype="multipart/form-data" style="margin-bottom: 50px;">
                    <div class="form-group"><label>Nome do Cliente / Campanha</label><input type="text" name="cliente" required></div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Tamanho do Banner</label>
                            <select name="tamanho" required>
                                <option value="mega">Mega Banner Topo (970x250px)</option>
                                <option value="skyscraper">Lateral Skyscraper (300x600px)</option>
                                <option value="retangulo">Retângulo de Matéria (728x90px)</option>
                                <option value="quadrado">Quadrado Lateral (300x250px)</option>
                                <option value="faixa">Faixa Horizontal (1200x120px)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Onde Exibir?</label>
                            <select name="exibicao" required>
                                <optgroup label="Geral"><option value="home">Somente na Home</option><option value="materias">Todas as matérias</option></optgroup>
                                <optgroup label="Arquivos por Categoria">
                                    <?php foreach ($categoriasAdmin as $categoria): ?>
                                        <option value="<?php echo htmlspecialchars($categoria['slug'], ENT_QUOTES, 'UTF-8'); ?>">Arquivo: <?php echo htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group"><label>Grupo Rotação</label><select name="rotacao"><option value="1">1</option><option value="2">2</option></select></div>
                        <div class="form-group"><label>Link Destino</label><input type="text" name="url"></div>
                    </div>
                    <div class="form-group"><label>Arte do Banner</label><input type="file" name="arte" required></div>
                    <button class="btn-save">ATIVAR CAMPANHA</button>
                </form>
                <table>
                    <thead><tr><th>Cliente</th><th>Tamanho / Posição</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php
                        $res_ads = mysqli_query($conn, "SELECT * FROM banners ORDER BY id DESC");
                        while($ad = mysqli_fetch_assoc($res_ads)): ?>
                        <tr>
                            <td style="font-weight:600;"><?= htmlspecialchars($ad['cliente']) ?></td>
                            <td><span style="background:#eee; padding:5px 10px; border-radius:4px; font-size:11px;"><?= strtoupper($ad['tamanho'] . ' | ' . $ad['exibicao']) ?></span></td>
                            <td><?= $ad['status'] == 1 ? '<span style="color:green;font-weight:bold;">ATIVO</span>' : '<span style="color:red;font-weight:bold;">INATIVO</span>' ?></td>
                            <td>
                                <a href="excluir_ad.php?id=<?= $ad['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Excluir este banner?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
<?php elseif ($view == 'radio'): 
    // 1. Busca o ID do Ao Vivo (aquele que flutua no site)    
    $sql_l = "SELECT aovivo_id FROM configuracoes WHERE id = 1";
    $res_l = mysqli_query($conn, $sql_l);
    $live = mysqli_fetch_assoc($res_l);?>
    <div class="card-admin" style="background:#fff; padding:30px; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <h2 style="color:var(--vermelho); margin-bottom:20px;"><i class="fas fa-play-circle"></i> Gerenciar Vídeos - ETHOS PLAY</h2>
        
        <form action="save_video.php" method="POST" style="display:grid; grid-template-columns: 2fr 2fr 1fr; gap:15px; margin-bottom:40px; background:#f9f9f9; padding:20px; border-radius:10px;">
            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:5px;">TÍTULO DO VÍDEO</label>
                <input type="text" name="titulo" placeholder="Ex: Entrevista com Prefeito" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:5px;" required>
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:5px;">LINK DO YOUTUBE</label>
                <input type="text" name="video_url" placeholder="Cole o link completo do vídeo aqui" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:5px;" required>
            </div>
            <div style="display:flex; align-items:flex-end;">
                <button type="submit" style="width:100%; background:var(--vermelho); color:#fff; border:none; padding:13px; border-radius:5px; font-weight:700; cursor:pointer; text-transform: uppercase;">Adicionar Vídeo</button>
            </div>
        </form>

        <h3 style="font-size: 14px; color: #555; margin-bottom: 15px; text-transform: uppercase;">Vídeos na Playlist:</h3>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:2px solid #eee; color: #777; font-size: 13px;">
                    <th style="padding:10px;">PREVIEW</th>
                    <th>TÍTULO</th>
                    <th style="text-align:center;">AÇÕES</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $videos = mysqli_query($conn, "SELECT * FROM ethos_play ORDER BY ordem ASC, id DESC");
                if(mysqli_num_rows($videos) > 0):
                    while($v = mysqli_fetch_assoc($videos)): 
                ?>
                <tr style="border-bottom:1px solid #f4f4f4;">
                    <td style="padding:10px;">
                        <img src="https://img.youtube.com/vi/<?php echo $v['video_id']; ?>/default.jpg" style="width:80px; border-radius:5px; border: 1px solid #ddd;">
                    </td>
                    <td style="font-weight:600; font-size: 14px;"><?php echo $v['titulo']; ?></td>
                    <td style="text-align:center;">
                        
                        <a href="?mover_video=<?= $v['id'] ?>&dir=up" style="color:#fff; background: #555; padding: 8px 10px; border-radius: 5px; text-decoration:none; margin-right: 5px;" title="Subir"><i class="fas fa-arrow-up"></i></a>
                        <a href="?mover_video=<?= $v['id'] ?>&dir=down" style="color:#fff; background: #555; padding: 8px 10px; border-radius: 5px; text-decoration:none; margin-right: 5px;" title="Descer"><i class="fas fa-arrow-down"></i></a>
                        
                        <a href="save_video.php?delete=<?php echo $v['id']; ?>" style="color:#ff4444; background: #fff1f1; padding: 8px 10px; border-radius: 5px; text-decoration:none;" onclick="return confirm('Apagar este vídeo da ETHOS PLAY?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else:
                    echo "<tr><td colspan='3' style='padding:20px; text-align:center; color:#999;'>Nenhum vídeo cadastrado ainda.</td></tr>";
                endif;
                ?>
            </tbody>
        </table>
    </div>

    <div class="card-admin" style="background:#fff; padding:30px; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
        <h2 style="color:#000; margin-bottom:20px;"><i class="fas fa-broadcast-tower"></i> Configuração Ao Vivo</h2>
        <form action="save_radio.php" method="POST">
            <label style="font-size:12px; font-weight:700; color: #555;">ID DO VÍDEO DO MOMENTO (YouTube)</label>
            <div style="display: flex; gap: 10px; margin-top: 5px;">
                <input type="text" name="aovivo_id" value="<?php echo $live['aovivo_id'] ?? ''; ?>" placeholder="Ex: dQw4w9WgXcQ" style="flex: 1; padding:12px; border:1px solid #ddd; border-radius:5px;">
                <button type="submit" style="background:#000; color:#fff; border:none; padding:10px 25px; border-radius:5px; cursor:pointer; font-weight:700;">SALVAR</button>
            </div>
            <small style="color: #888; display: block; margin-top: 10px;">*Este é o vídeo que aparece no player flutuante no canto da tela.</small>
        </form>
    </div><?php endif; ?>
    
    <script>
// Seu adaptador de upload (não mexa nele)
function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return {
            upload() {
                return loader.file.then(file => new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);
                    fetch('upload_editor.php', { method: 'POST', body: data })
                    .then(response => response.json())
                    .then(result => {
                        if (result.url) { resolve({ default: result.url }); }
                        else { reject('Erro no upload'); }
                    })
                    .catch(() => reject('Erro no servidor'));
                }));
            }
        };
    };
}

// Inicialização do Editor
ClassicEditor
    .create(document.querySelector('#editor'), {
        extraPlugins: [MyCustomUploadAdapterPlugin],
        // Toolbar simplificada para não dar erro
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                'insertTable', 'imageUpload', '|',
                'undo', 'redo'
            ]
        },
        table: {
            contentToolbar: [ 'tableColumn', 'tableRow', 'mergeTableCells' ]
        }
    })
    .catch(error => {
        // Se der erro, ele avisa no console do navegador (F12)
        console.error('Erro ao carregar o editor:', error);
    });
</script>
</body>
</html>