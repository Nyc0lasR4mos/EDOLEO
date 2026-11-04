<?php
require __DIR__ . '/includes/bootstrap.php';
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['categoria'] ?? '');
$categories = db()->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
$sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id=p.category_id WHERE p.active=1';
$args = [];
if ($q !== '') { $sql .= ' AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)'; $like = "%$q%"; array_push($args,$like,$like,$like); }
if ($category !== '') { $sql .= ' AND c.slug=?'; $args[] = $category; }
$sql .= ' ORDER BY p.featured DESC, p.id DESC'; $stmt=db()->prepare($sql); $stmt->execute($args); $products=$stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<main>
  <section class="hero wrap">
    <div class="hero-copy"><span class="eyebrow">BOM GOSTO, NOVAS HISTÓRIAS</span><h1>Vista o que<br>fica para sempre.</h1><p>Peças de grife e alfaiataria com história, qualidade e um novo destino. Elegância de segunda mão, feita para você.</p><a class="button button-dark" href="#colecao">Descobrir a coleção <span>↗</span></a><p class="hero-note"><span class="note-mark" aria-hidden="true">✳</span><span>Garimpo peça a peça.<br><b>Grifes escolhidas para durar.</b></span></p></div>
    <div class="hero-photo" role="img" aria-label="Modelo vestindo terno azul de alfaiataria"><div class="photo-caption"><span>ACERVO ÉDOLEO</span><b>Elegância que circula.</b></div><span class="photo-seal">PEÇA ÚNICA</span></div>
  </section>
  <section class="brand-strip"><span>PEÇAS COM HISTÓRIA</span><span>ALFAIATARIA</span><span>GRIFES SELECIONADAS</span><span>UM NOVO CAPÍTULO</span></section>
  <section class="collection wrap" id="colecao"><div class="section-heading"><div><span class="eyebrow">ESCOLHAS DA CURADORIA</span><h2>O novo do que já é especial.</h2></div><span class="result-count"><?= count($products) ?> peças</span></div>
    <form class="filters" method="get" action="<?= e(app_url('index.php')) ?>"><label class="search-box"><span>⌕</span><input name="q" value="<?= e($q) ?>" placeholder="Busque por peça, marca ou estilo"><button aria-label="Buscar">↗</button></label><div class="category-list"><a class="<?= $category===''?'selected':'' ?>" href="<?= e(app_url('index.php')) ?>">Tudo</a><?php foreach($categories as $c): ?><a class="<?= $category===$c['slug']?'selected':'' ?>" href="<?= e(app_url('index.php?categoria='.rawurlencode($c['slug']))) ?>"><?= e(category_label($c['slug'],$c['name'])) ?></a><?php endforeach; ?></div></form>
    <div class="product-grid"><?php foreach($products as $i=>$p): ?><article class="product-card"><a class="product-image tone-<?= $i%4 ?>" href="<?= e(app_url('produto.php?id='.(int)$p['id'])) ?>" aria-label="Ver <?= e($p['name']) ?>"><img loading="lazy" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"><span class="product-tag"><?= (int)$p['stock']===1?'PEÇA ÚNICA':'PRE-LOVED' ?></span><span class="quick-view">Ver peça ↗</span></a><div class="product-meta"><div><span class="product-brand"><?= e($p['brand'] ?: 'ÉDOLEO CURADORIA') ?></span><a class="product-name" href="<?= e(app_url('produto.php?id='.(int)$p['id'])) ?>"><?= e($p['name']) ?></a></div><b class="price"><?= money($p['price']) ?></b></div><div class="product-submeta"><span><?= e(category_label($p['category_slug'],$p['category_name'])) ?></span><span><?= (int)$p['stock']>0?'Disponível':'Esgotado' ?></span></div></article><?php endforeach; ?></div>
    <?php if (!$products): ?><div class="empty-state"><span>✳</span><h3>Nenhuma peça por aqui.</h3><p>Tente outro termo ou explore toda a curadoria.</p><a href="<?= e(app_url('index.php')) ?>">Ver todas as peças ↗</a></div><?php endif; ?>
  </section>
  <section class="manifesto" id="manifesto"><div class="manifesto-inner"><span class="eyebrow">MENOS, COM MAIS SIGNIFICADO</span><h2>O luxo de escolher<br><em>com intenção.</em></h2><p>Uma peça bem feita merece mais de uma história. Garimpamos qualidade para você vestir com personalidade — e um preço que faz sentido.</p><a href="#colecao" class="text-link">Conheça a curadoria <span>↗</span></a><div class="manifesto-stamp" aria-label="ÉdoLeo, moda circular"><strong>ÉdoLeo</strong><span>MODA CIRCULAR</span></div></div><div class="manifesto-image" role="img" aria-label="Detalhe de um blazer de alfaiataria"></div></section>
  <section class="service-row wrap"><div><span>01</span><b>Curadoria de verdade</b><small>Qualidade revisada, peça por peça.</small></div><div><span>02</span><b>Preço com propósito</b><small>Grifes especiais com novos valores.</small></div><div><span>03</span><b>Consumo circular</b><small>Mais vida para o que merece durar.</small></div></section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
