<?php
function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

function formatarDataCompleta($data) {
    return date('d/m/Y \à\s H\hi', strtotime($data));
}

function resumirTexto($texto, $limite = 150) {
    $texto = trim(html_entity_decode(strip_tags((string) $texto), ENT_QUOTES, 'UTF-8'));

    if ($texto === '') {
        return '';
    }

    if (mb_strlen($texto) <= $limite) {
        return $texto;
    }

    return rtrim(mb_substr($texto, 0, $limite)) . '...';
}

function normalizarSlugCategoria($slug) {
    $slug = trim((string) $slug);

    if ($slug === '') {
        return '';
    }

    $slug = mb_strtolower($slug, 'UTF-8');

    $mapa = [
        'joana_darc' => 'joana-darc',
        'joanadarc' => 'joana-darc',
        'caminhos_tiete' => 'caminhos-do-tiete',
        'caminhos do tiete' => 'caminhos-do-tiete',
    ];

    return $mapa[$slug] ?? str_replace('_', '-', $slug);
}

function obterSlugsRelacionadosCategoria($slug) {
    $normalizado = normalizarSlugCategoria($slug);
    $slugs = array_filter([
        trim((string) $slug),
        $normalizado,
        str_replace('-', '_', $normalizado),
        str_replace('_', '-', trim((string) $slug)),
    ]);

    return array_values(array_unique($slugs));
}

function obterNomeCategoria($slug, $fallback = 'Geral') {
    $categorias = [
        'turismo' => 'Turismo',
        'caminhos-do-tiete' => 'Caminhos do Tietê',
        'caminhos_tiete' => 'Caminhos do Tietê',
        'bioethos' => 'BioEthos',
        'eventhos' => 'EvenThos',
        'joana-darc' => "Joana D'Arc",
        'joana_darc' => "Joana D'Arc",
        'joanadarc' => "Joana D'Arc",
        'poethos' => 'Poethos',
        'cultura' => 'Cultura',
        'selethos' => 'Selethos',
        'arquitethos' => 'Arquitethos',
        'inquiethos' => 'Inquiethos',
        'estetica' => 'Estética',
        'saude' => 'Saúde',
        'moda' => 'Moda',
    ];

    $slug = normalizarSlugCategoria($slug);

    return $categorias[$slug] ?? $fallback;
}

function buscarCategoriaPorSlug($conn, $slug) {
    if (!$conn) {
        return null;
    }

    $slugs = obterSlugsRelacionadosCategoria($slug);

    if (empty($slugs)) {
        return null;
    }

    $slugsEscapados = array_map(function ($item) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $item) . "'";
    }, $slugs);

    $sql = "SELECT id, nome, slug FROM categorias WHERE slug IN (" . implode(', ', $slugsEscapados) . ") LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        $categoria = mysqli_fetch_assoc($res);
        $categoria['slug'] = normalizarSlugCategoria($categoria['slug']);
        return $categoria;
    }

    $slugNormalizado = normalizarSlugCategoria($slug);

    if (in_array($slugNormalizado, ['caminhos-do-tiete', 'cultura'], true)) {
        return [
            'id' => null,
            'nome' => obterNomeCategoria($slugNormalizado),
            'slug' => $slugNormalizado,
        ];
    }

    return null;
}

function obterCategoriasAdmin($conn) {
    $categorias = [];

    if ($conn) {
        $res = mysqli_query($conn, "SELECT nome, slug FROM categorias ORDER BY id ASC");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $categorias[] = [
                    'nome' => $row['nome'],
                    'slug' => normalizarSlugCategoria($row['slug']),
                ];
            }
        }
    }

    if (empty($categorias)) {
        $categorias = [
            ['nome' => 'TURISMO', 'slug' => 'turismo'],
            ['nome' => 'BIOETHOS', 'slug' => 'bioethos'],
            ['nome' => 'EVENTHOS', 'slug' => 'eventhos'],
            ['nome' => 'JOANA DARC', 'slug' => 'joana-darc'],
            ['nome' => 'POETHOS', 'slug' => 'poethos'],
            ['nome' => 'SELETHOS', 'slug' => 'selethos'],
            ['nome' => 'ARQUITETHOS', 'slug' => 'arquitethos'],
            ['nome' => 'INQUIETHOS', 'slug' => 'inquiethos'],
            ['nome' => 'ESTÉTICA', 'slug' => 'estetica'],
            ['nome' => 'MODA', 'slug' => 'moda'],
            ['nome' => 'SAÚDE', 'slug' => 'saude'],
        ];
    }

    return $categorias;
}

function obterMenuCategorias() {
    return [
        ['label' => 'Turismo', 'slug' => 'turismo', 'children' => [['label' => 'Caminhos do Tietê', 'slug' => 'caminhos-do-tiete']]],
        ['label' => 'BioEthos', 'slug' => 'bioethos'],
        ['label' => 'EvenThos', 'slug' => 'eventhos'],
        ['label' => "Joana D'Arc", 'slug' => 'joana-darc'],
        ['label' => 'Poethos', 'slug' => 'poethos', 'children' => [['label' => 'Cultura', 'slug' => 'cultura']]],
        ['label' => 'Selethos', 'slug' => 'selethos'],
        ['label' => 'Arquitethos', 'slug' => 'arquitethos'],
        ['label' => 'Saúde', 'slug' => 'saude'],
        ['label' => 'Moda', 'slug' => 'moda'],
    ];
}

function obterLabelNoticia($noticia) {
    if (!empty($noticia['subcategoria'])) {
        return $noticia['subcategoria'];
    }

    if (!empty($noticia['categoria_id'])) {
        return obterNomeCategoria($noticia['categoria_id']);
    }

    return 'Geral';
}

function obterImagemNoticia($arquivo, $base = 'assets/img/') {
    return $base . (!empty($arquivo) ? $arquivo : 'logo.png');
}

function exibirPublicidade($conn, $local, $tamanho) {
    $sql = "SELECT * FROM banners 
            WHERE (exibicao = '$local' OR exibicao = 'portal') 
            AND tamanho = '$tamanho' 
            AND status = 1 
            ORDER BY rotacao ASC 
            LIMIT 5";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        echo '<div class="ethos-ad-container ad-' . $tamanho . '">';
        echo '<span class="ad-label">Publicidade</span>';

        while ($ad = mysqli_fetch_assoc($result)) {
            echo '<div class="ad-slide">';
            echo '<a href="' . $ad['url_link'] . '" target="_blank" rel="noopener noreferrer">';
            echo '<img src="assets/img/ads/' . $ad['arquivo_arte'] . '" alt="' . htmlspecialchars($ad['cliente'], ENT_QUOTES, 'UTF-8') . '">';
            echo '</a>';
            echo '</div>';
        }
        echo '</div>';
    }
}

function getEthosPlay($conn) {
    $sql = "SELECT ethos_play_id FROM configuracoes WHERE id = 1";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['ethos_play_id'] ?? '';
}
?>