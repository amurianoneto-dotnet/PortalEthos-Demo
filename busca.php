<?php
include('includes/db.php');
include('functions.php'); // Certifique-se de que suas funções estão aqui
include('includes/header.php');

$termo_busca = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
?>

<div style="background: #f4f7f6; min-height: 60vh; padding: 50px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        
        <h2 style="font-family: 'Poppins', sans-serif; color: #8C0303; margin-bottom: 30px;">
            Resultados para: <span style="color: #333;">"<?= htmlspecialchars($termo_busca) ?>"</span>
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            <?php
            if ($termo_busca !== '') {
                // Altere 'noticias' e 'conteudo' para os nomes reais da sua tabela, se forem diferentes
                $sql = "SELECT * FROM noticias WHERE titulo LIKE '%$termo_busca%' OR conteudo LIKE '%$termo_busca%' ORDER BY id DESC";
                $res = mysqli_query($conn, $sql);

                if (mysqli_num_rows($res) > 0) {
                    while ($materia = mysqli_fetch_assoc($res)) {
                        ?>
                        <div style="background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                           <img src="<?= obterImagemNoticia($materia['imagem_capa']) ?>" style="width: 100%; height: 200px; object-fit: cover; background: #eee;" alt="Capa da matéria">
                            <div style="padding: 20px;">
                                <h3 style="font-family: 'Poppins', sans-serif; font-size: 18px; margin: 0 0 10px;">
                                    <a href="noticia.php?id=<?= $materia['id'] ?>" style="color: #000; text-decoration: none;">
                                        <?= htmlspecialchars($materia['titulo']) ?>
                                    </a>
                                </h3>
                                <p style="color: #666; font-family: 'Roboto', sans-serif; font-size: 14px; margin-bottom: 15px;">
                                    <?= mb_substr(strip_tags($materia['conteudo']), 0, 100) ?>...
                                </p>
                                <a href="noticia.php?id=<?= $materia['id'] ?>" style="color: #8C0303; font-weight: bold; text-decoration: none; font-size: 14px;">Ler matéria completa ➔</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='color: #666;'>Nenhuma matéria encontrada com esse termo. Tente usar outras palavras.</p>";
                }
            } else {
                echo "<p style='color: #666;'>Por favor, digite algo na barra de pesquisa.</p>";
            }
            ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>