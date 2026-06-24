<?php 
// 1. Inicia a conexão com o banco ANTES de tudo, para o Footer não quebrar!
include_once 'includes/db.php'; 
include_once 'includes/header.php'; 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós | Revista ETHOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --vinho: #8C0303;
            --preto: #1a1a1a;
            --cinza-texto: #555555;
            --fundo: #f8f9fa;
        }

        body {
            background-color: var(--fundo);
            font-family: 'Roboto', sans-serif;
            margin: 0;
            color: #333;
        }

        /* Container principal com espaço para não esconder embaixo do Header */
        .page-sobre {
            max-width: 1200px;
            margin: 0 auto;
            padding: 160px 20px 80px; 
        }

        .sobre-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr; /* Texto um pouco mais largo que a imagem */
            gap: 60px;
            align-items: center;
            margin-bottom: 60px;
        }

        .sobre-texto h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            color: var(--vinho);
            margin-top: 0;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sobre-texto p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: var(--cinza-texto);
            margin-bottom: 20px;
        }

        .sobre-texto strong {
            color: var(--preto);
            font-weight: 600;
        }

        /* Estilo da Imagem do Casarão */
        .sobre-imagem {
            position: relative;
        }

        .sobre-imagem img {
            width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            object-fit: cover;
        }

        /* Detalhe estético atrás da imagem */
        .sobre-imagem::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100%;
            height: 100%;
            border: 3px solid var(--vinho);
            border-radius: 12px;
            z-index: -1;
            opacity: 0.3;
        }

        /* Citação / Destaque de Revista */
        .sobre-destaque {
            background: #ffffff;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            border-top: 5px solid var(--vinho);
        }

        .sobre-destaque i {
            color: rgba(140, 3, 3, 0.1);
            font-size: 4rem;
            position: absolute;
            top: 20px;
            left: 30px;
        }

        .sobre-destaque p {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            line-height: 1.7;
            color: var(--preto);
            font-style: italic;
            margin: 0 0 25px;
            position: relative;
            z-index: 1;
        }

        .sobre-destaque span {
            display: inline-block;
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--vinho);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sobre-destaque .cargo {
            display: block;
            font-size: 0.85rem;
            color: #888;
            font-weight: 400;
            margin-top: 5px;
        }

        /* RESPONSIVO */
        @media(max-width: 900px){
            .page-sobre {
                padding-top: 180px;
            }
            .sobre-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .sobre-texto h1 {
                font-size: 2rem;
                text-align: center;
            }
            .sobre-texto p {
                text-align: justify;
            }
            .sobre-imagem::after {
                top: -10px;
                right: -10px;
            }
            .sobre-destaque {
                padding: 40px 20px;
            }
            .sobre-destaque p {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

    <main class="page-sobre">
        <section class="sobre-grid">
            <div class="sobre-texto">
                <h1>Revista ETHOS</h1>

                <p>
                    Mais que uma palavra, <strong>ETHOS</strong> é um conceito. De origem grega,
                    representa identidade, costumes e o espírito que move uma coletividade.
                    É a harmonia entre ética, cultura e natureza — valores que inspiram nosso modo de ver o mundo.
                </p>

                <p>
                    Para os gregos antigos, ethos também significava “morada do homem”.
                    Assim também é a Revista ETHOS — um espaço que escuta, interpreta e compartilha os sinais do nosso tempo.
                </p>

                <p>
                    Nossa proposta é cultivar informação com sensibilidade e profundidade.
                    Publicamos matérias sobre arte, comportamento, moda, estética, saúde,
                    gastronomia, turismo, arquitetura e meio ambiente.
                </p>

                <p>
                    Em Barra Bonita (SP), a <strong>Revista ETHOS</strong> encontra morada em um <strong>casarão centenário</strong>, repleto de história e significado. À frente dele, duas <strong>palmeiras imponentes</strong> — as maiores da cidade — emolduram a fachada e dão um charme único ao lugar. Entre tradição e inspiração, é aqui que a <strong>ETHOS cria, conecta</strong> e <strong>compartilha novas ideias</strong>.
                </p>

                <p>
                    <strong>Acreditamos na cultura, na alegria e no potencial da nossa região.</strong>
                    Cada edição é um convite para refletir, inspirar e celebrar a vida coletiva.
                </p>
            </div>

            <div class="sobre-imagem">
                <img src="assets/img/casarao_revista.jpeg" alt="Casarão Histórico - Revista Ethos Barra Bonita">
            </div>
        </section>

        <section class="sobre-destaque">
            <i class="fas fa-quote-left"></i>
            <p>
                "Promover informação de qualidade, valorizando a cultura, a ética e o pensamento coletivo. 
                A Revista ETHOS tem como missão inspirar pessoas, fortalecer identidades e ampliar o olhar sobre o mundo."
            </p>
            <span>Janaina Cescato</span>
            <span class="cargo">Fundadora / Diretora</span>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>