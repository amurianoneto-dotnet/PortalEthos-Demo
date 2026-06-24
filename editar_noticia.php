<?php
include_once('includes/db.php');
include_once('functions.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$categoriasAdmin = obterCategoriasAdmin($conn);

if ($id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM noticias WHERE id = $id");
    $dados = mysqli_fetch_assoc($res);
} else {
    header("Location: admin.php");
    exit;
}

// 2. Lógica de Salvamento (Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $subtitulo = mysqli_real_escape_string($conn, $_POST['subtitulo']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $categoria = mysqli_real_escape_string($conn, normalizarSlugCategoria($_POST['categoria'] ?? ''));
    $conteudo = mysqli_real_escape_string($conn, $_POST['conteudo']);
    
    // NOVO: Captura o autor_id atualizado
    $autor_id = isset($_POST['autor_id']) ? (int)$_POST['autor_id'] : 1;

    // Verifica se enviou uma nova imagem de capa    
    if (!empty($_FILES['imagem']['name'])) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = md5(time()) . "." . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], "assets/img/" . $novo_nome);
        $sql_img = ", imagem_capa = '$novo_nome'";
    } else {
        $sql_img = "";
    }

    // UPDATE modificado para incluir o autor_id
    $update = "UPDATE noticias SET 
                titulo = '$titulo', 
                subtitulo = '$subtitulo', 
                descricao = '$descricao', 
                categoria_id = '$categoria', 
                autor_id = $autor_id,
                conteudo = '$conteudo' 
                $sql_img 
               WHERE id = $id";

    if (mysqli_query($conn, $update)) {
        echo "<script>alert('Matéria atualizada com sucesso!'); window.location.href='admin.php?view=gerenciar';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Matéria | ETHOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 40px; margin: 0; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 800; text-transform: uppercase; font-size: 12px; margin-bottom: 8px; color: #333; }
        input, select, textarea { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit; font-size: 1rem; }
        input:focus, select:focus, textarea:focus { border-color: #ce1212; outline: none; }
        .btn-save { background: #ce1212; color: #fff; border: none; padding: 18px 30px; border-radius: 8px; font-weight: 900; cursor: pointer; width: 100%; font-size: 1rem; transition: 0.3s; margin-top: 20px; }
        .btn-save:hover { background: #000; }
        .ck-editor__editable { min-height: 400px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin.php?view=gerenciar" style="text-decoration: none; color: #666; font-size: 13px; font-weight: bold;"><i class="fas fa-arrow-left"></i> Voltar ao Gerenciamento</a>
    <h2 style="font-weight: 900; margin-top: 20px; color: #ce1212; font-size: 1.8rem; line-height: 1.2;">EDITAR: <?php echo htmlspecialchars($dados['titulo']); ?></h2>
    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 30px;">

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Título Principal</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($dados['titulo']); ?>" required>
        </div>

        <div class="form-group">
            <label>Subtítulo / Gravata</label>
            <input type="text" name="subtitulo" value="<?php echo htmlspecialchars($dados['subtitulo']); ?>">
        </div>

        <div class="form-group">
            <label>Descrição Curta (Lead)</label>
            <textarea name="descricao" rows="2"><?php echo htmlspecialchars($dados['descricao']); ?></textarea>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Categoria</label>
                <select name="categoria">
                    <?php $categoriaAtual = normalizarSlugCategoria($dados['categoria_id'] ?? ''); ?>
                    <?php foreach ($categoriasAdmin as $categoria): ?>
                        <option value="<?php echo htmlspecialchars($categoria['slug'], ENT_QUOTES, 'UTF-8'); ?>" <?php if($categoriaAtual === $categoria['slug']) echo 'selected'; ?>><?php echo htmlspecialchars($categoria['nome'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Autor / Colunista</label>
                <select name="autor_id" required>
                    <?php 
                    $autorAtual = isset($dados['autor_id']) ? (int)$dados['autor_id'] : 1;
                    $res_autores = mysqli_query($conn, "SELECT id, nome FROM autores ORDER BY id ASC");
                    while($aut = mysqli_fetch_assoc($res_autores)): 
                    ?>
                        <option value="<?= $aut['id'] ?>" <?= ($autorAtual == $aut['id']) ? 'selected' : '' ?>><?= htmlspecialchars($aut['nome']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Alterar Capa (Deixe vazio para manter a atual)</label>
            <input type="file" name="imagem" accept="image/*">
            <small style="color: #888; margin-top: 5px; display: block;">Imagem atual: <?= htmlspecialchars($dados['imagem_capa']) ?></small>
        </div>

        <div class="form-group">
            <label>Conteúdo da Matéria</label>
            <textarea id="editor" name="conteudo"><?php echo $dados['conteudo']; ?></textarea>
        </div>

        <button type="submit" class="btn-save">SALVAR ALTERAÇÕES DA MATÉRIA</button>
    </form>
</div>

<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: { items: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'insertTable', '|', 'undo', 'redo' ] }
        })
        .catch(error => { console.error(error); });
</script>

</body>
</html>