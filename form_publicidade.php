<form action="processa_banner.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label>Nome do Patrocinador / Campanha</label>
        <input type="text" name="cliente" placeholder="Ex: Lollapalooza 2026" required>
    </div>
    <div class="form-group">
        <label>Localização do Anúncio</label>
        <select name="posicao">
            <option value="topo">Mega Banner Topo (970x90 ou 1200x250)</option>
            <option value="lateral">Banner Lateral (300x600)</option>
            <option value="meio">Banner Entre Seções (970x250)</option>
        </select>
    </div>
    <div class="form-group">
        <label>Link de Destino (Quando o usuário clicar)</label>
        <input type="text" name="link_url" placeholder="https://site-do-patrocinador.com.br">
    </div>
    <div class="form-group">
        <label>Arte do Banner</label>
        <input type="file" name="banner_img" required>
    </div>
    <button type="submit" class="btn-save">ATIVAR CAMPANHA AGORA</button>
</form>