<?php
include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    
    // Pega o slug digitado, converte pra minúsculo e tira espaços sobrando
    $slug = mysqli_real_escape_string($conn, strtolower(trim($_POST['slug']))); 
    
    // Troque espaço por hífen por segurança no slug
    $slug = str_replace(' ', '-', $slug); 

    // NOVO: Captura a subcategoria (parent_id)
    // Se vier vazio, manda gravar como 'NULL' (Categoria Mãe/Principal)
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';

    // O comando de salvar agora inclui a coluna parent_id
    $sql = "INSERT INTO categorias (nome, slug, parent_id) VALUES ('$nome', '$slug', $parent_id)";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('Editoria cadastrada com sucesso!'); 
                window.location.href='admin.php?view=categorias';
              </script>";
    } else {
        echo "Erro ao cadastrar: " . mysqli_error($conn);
    }
}
?>