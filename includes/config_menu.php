<?php
// Define as categorias para o menu não quebrar em nenhuma página
if (!isset($menuCategorias)) {
    $menuCategorias = [
        ['label' => 'Turismo', 'slug' => 'turismo', 'children' => [['label' => 'Caminhos do Tietê', 'slug' => 'caminhos-do-tiete']]],
        ['label' => 'BioEthos', 'slug' => 'bioethos'],
        ['label' => 'EvenThos', 'slug' => 'eventhos'],
        ['label' => "Joana D'Arc", 'slug' => 'joana-darc'],
        ['label' => 'Poethos', 'slug' => 'poethos', 'children' => [['label' => 'Cultura', 'slug' => 'cultura']]],
        ['label' => 'Selethos', 'slug' => 'selethos'],
        ['label' => 'Arquitethos', 'slug' => 'arquitethos'],
        ['label' => 'Saúde', 'slug' => 'saude'],
        ['label' => 'Moda', 'slug' => 'moda'],
        ['label' => 'Estética', 'slug' => 'estetica'], 
    ];
}

if (!isset($quickMenuLinks)) {
    $quickMenuLinks = [
        ['label' => 'Home', 'href' => 'index.php'],
        ['label' => 'Sobre nós', 'href' => 'sobre.php'],
        ['label' => 'Site: Bio.Ethos', 'href' => '#'],
        ['label' => 'Caminhos do Tietê', 'href' => 'https://caminhosdotiete.com.br/', 'target' => '_blank'],
    ];
}
?>