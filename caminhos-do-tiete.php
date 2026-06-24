<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/db.php');
// include('functions.php'); // Descomente se precisar

// ==========================================================
// LISTA DAS 13 CIDADES COM OS LINKS DO CAMINHOS DO TIETÊ
// ==========================================================
$cidades = [
    [
        "nome" => "Arealva", 
        "foto" => "arealva.png",
        "link" => "https://caminhosdotiete.com.br/arealva/",
        "descricao" => "Belas paisagens fazem da Praia Municipal de Arealva \"Prefeito José Ruiz\" o atrativo mais quente do município."
    ],
    [
        "nome" => "Bariri", 
        "foto" => "bariri.png",
        "link" => "https://caminhosdotiete.com.br/bariri/",
        "descricao" => "O Lago Municipal Prefeito \"Acácio Masson\" é o principal ponto de encontro para caminhadas, comemorações e eventos."
    ],
    [
        "nome" => "Bocaina", 
        "foto" => "bocaina.png",
        "link" => "https://caminhosdotiete.com.br/bocaina/",
        "descricao" => "Igreja Matriz de São João Batista. Nesse templo, encontram-se as telas sacras do pintor Benedito Calixto de Jesus."
    ],
    // ESTE BLOCO VAI SER GRANDE (Barra Bonita)
    [
        "nome" => "Barra Bonita", 
        "foto" => "barra-bonita.png",
        "link" => "https://caminhosdotiete.com.br/barrabonita/",
        "descricao" => "Situada às margens do rio Tietê, Barra Bonita se destaca como a única cidade no Brasil a oferecer passeios de barco em dois níveis na lendária Eclusa."
    ],
    [
        "nome" => "Borborema", 
        "foto" => "borborema.png",
        "link" => "https://caminhosdotiete.com.br/borborema/",
        "descricao" => "Área para camping, quiosques, restaurantes, passeios náuticos e shows ao vivo na Praia José da Silva Correia (Juqueta)."
    ],
    [
        "nome" => "Dois Córregos", 
        "foto" => "dois-corregos.png", 
        "link" => "https://caminhosdotiete.com.br/doiscorregos/",
        "descricao" => "Cidade acolhedora conhecida regionalmente pela produção significativa de macadâmia e belas paisagens rurais."
    ],
    [
        "nome" => "Iacanga", 
        "foto" => "iacanga.png", 
        "link" => "https://caminhosdotiete.com.br/iacanga/",
        "descricao" => "Abraçada por suas orlas calorosas, Iacanga oferece belos locais para lazer e contemplação às margens do Tietê."
    ],
    // ESTE BLOCO VAI SER GRANDE (Ibitinga)
    [
        "nome" => "Ibitinga", 
        "foto" => "ibitinga.png", 
        "link" => "https://caminhosdotiete.com.br/ibitinga/",
        "descricao" => "A \"Capital Nacional do Bordado\" oferece um fascinante shopping a céu aberto e abriga o Pantaninho no preservado Rio Jacaré-Pepira."
    ],
    [
        "nome" => "Igaraçu do Tietê", 
        "foto" => "igaracu-do-tiete.png", 
        "link" => "https://caminhosdotiete.com.br/igaracudotiete/",
        "descricao" => "Banhada pelo rio Tietê logo abaixo da eclusa, a \"prainha\" Municipal é um dos principais atrativos da cidade."
    ],
    [
        "nome" => "Itapuí", 
        "foto" => "itapui.png", 
        "link" => "https://caminhosdotiete.com.br/itapui/",
        "descricao" => "Conhecida pela travessia de balsa gratuita sobre o Rio Tietê, um verdadeiro presente aos olhos dos visitantes."
    ],
    [
        "nome" => "Jahu", 
        "foto" => "jahu.png", 
        "link" => "https://caminhosdotiete.com.br/jahu/",
        "descricao" => "Capital Nacional do Calçado Feminino, Jahu abriga o maior shopping de calçados da América Latina e preserva sua arquitetura histórica."
    ],
    // AS DUAS ÚLTIMAS (NO FINAL, CENTRALIZADAS)
    [
        "nome" => "Mineiros do Tietê", 
        "foto" => "mineiros-do-tiete.png", 
        "link" => "https://caminhosdotiete.com.br/mineirosdotiete/",
        "descricao" => "A Serra do Morro Alto e o Baixão da Serra revelam a natureza exuberante do município, repleto de história."
    ],
    [
        "nome" => "Tabatinga", 
        "foto" => "tabatinga.png", 
        "link" => "https://caminhosdotiete.com.br/tabatinga/",
        "descricao" => "Capital Nacional dos Bichos de Pelúcia, se destaca por possuir mais de 50 fábricas ativas oferecendo artigos infantis."
    ],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminhos do Tietê | Revista Ethos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ESTILOS EXCLUSIVOS DA PÁGINA CAMINHOS DO TIETÊ */
        
        /* Cabeçalho Minimalista e Elegante */
        .tiete-header {
            background-color: #fff;
            color: #111;
            padding: 50px 20px;
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 1px solid #eee;
        }
        
        .tiete-header h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;
            margin: 0;
            font-size: 2.3rem;
            text-transform: uppercase;
            color: #8C0303;
            letter-spacing: 1px;
        }
        
        .tiete-header p {
            margin: 10px auto 0;
            font-size: 1rem;
            max-width: 600px;
            color: #666;
            font-weight: 500;
        }

        /* CONTAINER DO GRID PRINCIPAL (3 colunas iguais padrão) */
        .caminhos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px; /* Espaço estreito como no print */
            padding: 0 20px 80px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* CLASSES ESPECIAIS PARA O LAYOUT (3-1-3-1-3-2) */
        /* O Grandão ocupando 3 colunas (Barra Bonita e Ibitinga) */
        .flip-span-3 {
            grid-column: span 3;
            height: 380px !important; /* Altura maior para os grandes */
        }
        
        /* O Container centralizado para as duas últimas */
        .final-row-container {
            grid-column: span 3; /* Ocupa a linha inteira do grid principal */
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Cria 2 colunas para Tabatinga e Mineiros */
            gap: 15px;
            width: 100%;
            max-width: 790px; /* Largura máxima para alinhar com as colunas de cima */
            margin: 0 auto; /* Isso é o que centraliza as duas de baixo sem esmagar elas! */
        }

        /* A MÁGICA DO CSS FLIP CARD 3D - AGORA É UM LINK */
        .flip-card {
            background-color: transparent;
            width: 100%;
            height: 290px; /* Altura padrão para os pequenos */
            perspective: 1200px; /* Cria profundidade 3D */
            border-radius: 12px;
            overflow: visible;
            display: block; /* Essencial porque a tag <a> é inline por padrão */
            text-decoration: none; /* Tira o sublinhado do link */
        }

        .flip-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.8s cubic-bezier(0.4, 0.2, 0.2, 1);
            transform-style: preserve-3d;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Gira o cartão quando passa o mouse (PC) ou toca (Celular) */
        .flip-card:hover .flip-card-inner {
            transform: rotateY(180deg);
        }

        .flip-card-front, .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #eee;
        }

        /* FRENTE: Apenas a Foto com o Pin no canto */
        .flip-card-front {
            background-color: #eee;
        }
        .flip-card-front img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        /* O Alfinete (Pin) na frente */
        .flip-card-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255,255,255,0.95);
            color: #111;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            font-family: 'Poppins', sans-serif;
            text-transform: uppercase;
        }
        .flip-card-label i { color: #8C0303; }

        /* VERSO DO CARTÃO: Nome, Descrição e o Botãozinho Limpo */
        .flip-card-back {
            background-color: #fff;
            transform: rotateY(180deg);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border: 2px solid #8C0303; /* Borda vermelha em todo o verso */
        }
        
        .flip-card-back h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0 0 15px;
            text-transform: uppercase;
            color: #8C0303;
        }
        
        .flip-card-back p {
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0 0 20px;
            color: #444;
            font-weight: 500;
        }

        /* O botãozinho elegante que aparece nas costas do card */
        .btn-visitar {
            background: #8C0303;
            color: #fff;
            padding: 8px 20px;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            transition: 0.3s;
        }
        .flip-card:hover .btn-visitar {
            box-shadow: 0 5px 15px rgba(140, 3, 3, 0.4);
        }

        /* Ajuste pro celular */
        @media(max-width: 768px) {
            .caminhos-grid, .final-row-container {
                grid-template-columns: 1fr !important; /* 1 coluna no celular */
                gap: 10px;
            }
            .tiete-header h1 { font-size: 1.8rem; }
            .flip-card, .flip-span-3 { height: 280px !important; } /* Altura padrão no celular */
            .flip-span-3 { grid-column: auto; }
            .final-row-container { grid-column: auto; width: 100%; }
        }
    </style>
</head>
<body>

    <?php include('includes/header.php'); ?>

    <header class="tiete-header">
        <h1>Caminhos do Tietê</h1>
        <p>Conheça as 13 cidades encantadoras banhadas pelo lendário Rio Tietê, repletas de história e cultura.</p>
    </header>

    <main class="caminhos-grid">
        
        <?php for($i=0; $i<3; $i++): ?>
            <a href="<?= $cidades[$i]['link'] ?>" target="_blank" class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <img src="assets/img/cidades/<?= $cidades[$i]['foto'] ?>" alt="<?= $cidades[$i]['nome'] ?>">
                        <div class="flip-card-label"><i class="fas fa-map-marker-alt"></i> <?= $cidades[$i]['nome'] ?></div>
                    </div>
                    <div class="flip-card-back">
                        <h3><?= $cidades[$i]['nome'] ?></h3>
                        <p><?= $cidades[$i]['descricao'] ?></p>
                        <span class="btn-visitar">Visitar Portal <i class="fas fa-external-link-alt"></i></span>
                    </div>
                </div>
            </a>
        <?php endfor; ?>
        
        <a href="<?= $cidades[3]['link'] ?>" target="_blank" class="flip-card flip-span-3">
            <div class="flip-card-inner">
                <div class="flip-card-front">
                    <img src="assets/img/cidades/<?= $cidades[3]['foto'] ?>" alt="<?= $cidades[3]['nome'] ?>">
                    <div class="flip-card-label"><i class="fas fa-map-marker-alt"></i> <?= $cidades[3]['nome'] ?></div>
                </div>
                <div class="flip-card-back">
                    <h3><?= $cidades[3]['nome'] ?></h3>
                    <p><?= $cidades[3]['descricao'] ?></p>
                    <span class="btn-visitar">Visitar Portal <i class="fas fa-external-link-alt"></i></span>
                </div>
            </div>
        </a>

        <?php for($i=4; $i<7; $i++): ?>
            <a href="<?= $cidades[$i]['link'] ?>" target="_blank" class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <img src="assets/img/cidades/<?= $cidades[$i]['foto'] ?>" alt="<?= $cidades[$i]['nome'] ?>">
                        <div class="flip-card-label"><i class="fas fa-map-marker-alt"></i> <?= $cidades[$i]['nome'] ?></div>
                    </div>
                    <div class="flip-card-back">
                        <h3><?= $cidades[$i]['nome'] ?></h3>
                        <p><?= $cidades[$i]['descricao'] ?></p>
                        <span class="btn-visitar">Visitar Portal <i class="fas fa-external-link-alt"></i></span>
                    </div>
                </div>
            </a>
        <?php endfor; ?>

        <a href="<?= $cidades[7]['link'] ?>" target="_blank" class="flip-card flip-span-3">
            <div class="flip-card-inner">
                <div class="flip-card-front">
                    <img src="assets/img/cidades/<?= $cidades[7]['foto'] ?>" alt="<?= $cidades[7]['nome'] ?>">
                    <div class="flip-card-label"><i class="fas fa-map-marker-alt"></i> <?= $cidades[7]['nome'] ?></div>
                </div>
                <div class="flip-card-back">
                    <h3><?= $cidades[7]['nome'] ?></h3>
                    <p><?= $cidades[7]['descricao'] ?></p>
                    <span class="btn-visitar">Visitar Portal <i class="fas fa-external-link-alt"></i></span>
                </div>
            </div>
        </a>

        <?php for($i=8; $i<11; $i++): ?>
            <a href="<?= $cidades[$i]['link'] ?>" target="_blank" class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <img src="assets/img/cidades/<?= $cidades[$i]['foto'] ?>" alt="<?= $cidades[$i]['nome'] ?>">
                        <div class="flip-card-label"><i class="fas fa-map-marker-alt"></i> <?= $cidades[$i]['nome'] ?></div>
                    </div>
                    <div class="flip-card-back">
                        <h3><?= $cidades[$i]['nome'] ?></h3>
                        <p><?= $cidades[$i]['descricao'] ?></p>
                        <span class="btn-visitar">Visitar Portal <i class="fas fa-external-link-alt"></i></span>
                    </div>
                </div>
            </a>
        <?php endfor; ?>

        <div class="final-row-container">
            <?php for($i=11; $i<13; $i++): ?>
                <a href="<?= $cidades[$i]['link'] ?>" target="_blank" class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <img src="assets/img/cidades/<?= $cidades[$i]['foto'] ?>" alt="<?= $cidades[$i]['nome'] ?>">
                            <div class="flip-card-label"><i class="fas fa-map-marker-alt"></i> <?= $cidades[$i]['nome'] ?></div>
                        </div>
                        <div class="flip-card-back">
                            <h3><?= $cidades[$i]['nome'] ?></h3>
                            <p><?= $cidades[$i]['descricao'] ?></p>
                            <span class="btn-visitar">Visitar Portal <i class="fas fa-external-link-alt"></i></span>
                        </div>
                    </div>
                </a>
            <?php endfor; ?>
        </div>

    </main>

    <?php include('includes/footer.php'); ?>

</body>
</html>