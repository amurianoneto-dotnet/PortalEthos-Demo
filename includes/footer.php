<style>
    .main-footer {
        background-color: #8C0303;
        color: #ffffff;
        padding: 60px 0 20px 0;
        margin-top: 36px;
        font-family: 'Poppins', sans-serif;
    }
    .footer-logo-center {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 40px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding: 0 20px 40px;
    }
    .footer-logo-center img {
        width: min(100%, 300px);
        height: auto;
        margin: 0 auto;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 50px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .footer-title {
        font-weight: 700;
        font-size: 1.3rem;
        margin-bottom: 25px;
        text-transform: none;
    }
    .footer-text {
        font-size: 0.95rem;
        line-height: 1.7;
        font-family: 'Roboto', sans-serif;
        text-transform: none;
    }

    .footer-post { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; text-decoration: none; color: inherit; }
    .footer-post img { width: 70px; height: 70px; object-fit: cover; border-radius: 4px; }
    .footer-post-info h5 { margin: 0; font-size: 0.9rem; text-transform: uppercase; font-weight: 700; line-height: 1.2; }
    .footer-post-info span { font-size: 0.75rem; opacity: 0.8; }

    .footer-contact-list {
        display: grid;
        gap: 16px;
    }
    .footer-contact-item {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }
    .footer-contact-item i {
        width: 18px;
        margin-top: 5px;
    }
    .footer-contact-item a {
        color: inherit;
        text-decoration: none;
    }
    .footer-contact-item a:hover {
        text-decoration: underline;
    }
    .footer-social-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        color: inherit;
        text-decoration: none;
        font-weight: 700;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        margin-top: 50px;
        border-top: 1px dotted rgba(255,255,255,0.3);
        font-size: 0.85rem;
        letter-spacing: 1px;
    }

    @media (max-width: 900px) {
        .footer-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
$footerContact = [
    [
        'icon' => 'fab fa-whatsapp',
        'label' => 'WhatsApp',
        'text' => '14 99675-3805',
        'href' => 'https://wa.me/5514996753805',
    ],
    [
        'icon' => 'fas fa-map-marker-alt',
        'label' => 'Endereço',
        'text' => 'Rua João Pessoa, 321, Vila Nova',
        'href' => 'https://www.google.com/maps/search/?api=1&query=Rua+Jo%C3%A3o+Pessoa%2C+321%2C+Vila+Nova',
    ],
];
?>

<footer class="main-footer">
    <div class="footer-logo-center">
        <img src="assets/img/logo.png" alt="REVISTA ETHOS">
    </div>

    <div class="footer-grid">
        <div>
            <h4 class="footer-title">Sobre</h4>
            <p class="footer-text">Arte, comportamento, moda, estética, saúde, gastronomia, turismo, arquitetura e meio ambiente, além de perfis, ensaios fotográficos, entrevistas, artigos e crônicas.</p>
            <p class="footer-text" style="margin-top:20px;">Informação e publicidade de alta qualidade, pois acreditamos na cultura, na alegria e no potencial de nossa região.</p>
        </div>

        <div>
            <h4 class="footer-title">Postagens Recentes</h4>
            <?php
            $query_footer = "SELECT id, titulo, imagem_capa, data_publicacao FROM noticias ORDER BY data_publicacao DESC LIMIT 3";
            $res_footer = mysqli_query($conn, $query_footer);

            if ($res_footer):
                while ($f_post = mysqli_fetch_assoc($res_footer)):
            ?>
                <a href="noticia.php?id=<?php echo (int) $f_post['id']; ?>" class="footer-post">
                    <img src="assets/img/<?php echo htmlspecialchars((string) $f_post['imagem_capa'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $f_post['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="footer-post-info">
                        <h5><?php echo htmlspecialchars(resumirTexto((string) $f_post['titulo'], 40), ENT_QUOTES, 'UTF-8'); ?></h5>
                        <span>admin | <?php echo htmlspecialchars(formatarData((string) $f_post['data_publicacao']), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </a>
            <?php
                endwhile;
            endif;
            ?>
        </div>

        <div>
            <h4 class="footer-title">Contato</h4>
            <p class="footer-text" style="margin-bottom:20px;">Fale com a Revista Ethos pelos nossos canais oficiais.</p>

            <div class="footer-contact-list">
                <?php foreach ($footerContact as $item): ?>
                    <div class="footer-contact-item">
                        <i class="<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                        <div>
                            <strong><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <a href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="https://www.instagram.com/revista_ethos/" class="footer-social-link" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-instagram"></i>
                @revista_ethos
            </a>
        </div>
    </div>

    <div class="footer-bottom">
        2026 REVISTA ETHOS. Todos os direitos reservados.
    </div>
</footer>