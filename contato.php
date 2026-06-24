<?php
include('includes/db.php');
include('functions.php');

$contatoInfo = [
    [
        'icone' => 'fab fa-whatsapp',
        'titulo' => 'WhatsApp',
        'texto' => '14 99675-3805',
        'link' => 'https://wa.me/5514996753805',
        'cta' => 'Chamar no WhatsApp',
    ],
    [
        'icone' => 'fas fa-map-marker-alt',
        'titulo' => 'Endereço',
        'texto' => 'Rua João Pessoa, 321, Vila Nova',
        'link' => 'https://www.google.com/maps/search/?api=1&query=Rua+Jo%C3%A3o+Pessoa%2C+321%2C+Vila+Nova',
        'cta' => 'Abrir no mapa',
    ],
    [
        'icone' => 'fab fa-instagram',
        'titulo' => 'Instagram',
        'texto' => '@revista_ethos',
        'link' => 'https://www.instagram.com/revista_ethos/',
        'cta' => 'Acessar perfil',
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato | ETHOS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <main class="container contact-page">
        <section class="contact-hero">
            <div class="contact-hero__content">
                <span class="badge-categoria">Contato</span>
                <h1>Fale com a Revista Ethos</h1>
                <p>
                    Anuncie, envie sugestões de pauta, marque entrevistas ou fale com nossa equipe pelos canais abaixo.
                    Deixamos tudo reunido nesta página para facilitar o contato com o ecossistema Ethos.
                </p>

                <div class="contact-actions">
                    <a href="https://wa.me/5514996753805" class="contact-action contact-action--primary" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-whatsapp"></i>
                        Conversar no WhatsApp
                    </a>
                    <a href="https://www.instagram.com/revista_ethos/" class="contact-action" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-instagram"></i>
                        Visitar Instagram
                    </a>
                </div>
            </div>

            <div class="contact-map-card">
                <div class="contact-map-card__header">
                    <h2>Onde estamos</h2>
                    <p>Rua João Pessoa, 321, Vila Nova.</p>
                </div>
                <iframe
                    src="https://www.google.com/maps?q=Rua%20Jo%C3%A3o%20Pessoa%2C%20321%2C%20Vila%20Nova&output=embed"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mapa da Revista Ethos">
                </iframe>
            </div>
        </section>

        <section class="contact-grid">
            <div class="contact-info-panel">
                <h2>Canais diretos</h2>
                <p class="contact-section-copy">Escolha o canal mais conveniente para falar com a equipe.</p>

                <div class="contact-cards">
                    <?php foreach ($contatoInfo as $item): ?>
                        <article class="contact-card">
                            <div class="contact-card__icon">
                                <i class="<?php echo htmlspecialchars($item['icone'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                            </div>
                            <div class="contact-card__body">
                                <h3><?php echo htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars($item['texto'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <a href="<?php echo htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo htmlspecialchars($item['cta'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="contact-form-panel">
                <h2>Envie sua mensagem</h2>
                <p class="contact-section-copy">Se preferir, preencha o formulário e retornaremos pelo melhor canal.</p>

                <form class="form-contato">
                    <input type="text" placeholder="Seu nome" required>
                    <input type="email" placeholder="Seu e-mail" required>
                    <input type="text" placeholder="Assunto">
                    <textarea rows="6" placeholder="Sua mensagem"></textarea>
                    <button type="submit" class="btn-enviar">Enviar mensagem</button>
                </form>
            </div>
        </section>
    </main>

    <?php include('includes/footer.php'); ?>
</body>
</html>