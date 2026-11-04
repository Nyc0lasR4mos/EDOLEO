<?php

require dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$section = $_GET['view'] ?? 'products';
if (!in_array($section, ['products', 'orders'], true)) {
    $section = 'products';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $id          = (int) ($_POST['id'] ?? 0);
            $name        = trim($_POST['name'] ?? '');
            $brand       = trim($_POST['brand'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category    = (int) ($_POST['category_id'] ?? 0);
            $price       = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
            $stock       = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
            $image       = trim($_POST['image'] ?? '');

            if (
                $name === '' ||
                $description === '' ||
                $category < 1 ||
                $price === false ||
                $price < 0 ||
                $stock === false ||
                $stock < 0 ||
                !filter_var($image, FILTER_VALIDATE_URL)
            ) {
                throw new RuntimeException('Preencha todos os campos com valores válidos.');
            }

            if ($id) {
                $s = db()->prepare('UPDATE products SET category_id=?,name=?,brand=?,description=?,price=?,stock=?,image=?,featured=? WHERE id=?');
                $s->execute([$category, $name, $brand, $description, $price, $stock, $image, isset($_POST['featured']) ? 1 : 0, $id]);
                flash('Peça atualizada.');
            } else {
                $s = db()->prepare('INSERT INTO products(category_id,name,brand,description,price,stock,image,featured) VALUES(?,?,?,?,?,?,?,?)');
                $s->execute([$category, $name, $brand, $description, $price, $stock, $image, isset($_POST['featured']) ? 1 : 0]);
                flash('Nova peça adicionada à curadoria.');
            }

            redirect(app_url('admin/index.php'));

        } elseif ($action === 'toggle') {
            $s = db()->prepare('UPDATE products SET active=1-active WHERE id=?');
            $s->execute([(int) $_POST['id']]);
            flash('Disponibilidade da peça atualizada.');
            redirect(app_url('admin/index.php'));
        }

    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare('SELECT * FROM products WHERE id=?');
    $s->execute([(int) $_GET['edit']]);
    $edit = $s->fetch();
}

$categories = db()->query('SELECT id,name,slug FROM categories ORDER BY name')->fetchAll();
$products   = db()->query('SELECT p.*,c.name category_name,c.slug category_slug FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC')->fetchAll();
$orders     = db()->query('SELECT o.*,u.name customer,u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC')->fetchAll();

$pageTitle = 'Painel da curadoria — ÉdoLeo';
include dirname(__DIR__) . '/includes/header.php';
?>

<main class="wrap page-shell">

    <span class="eyebrow">ÉDOLEO · CURADORIA</span>
    <h1 class="page-title">Painel administrativo.</h1>
    <p class="page-lead">Olá, <?= e(current_user()['name']) ?>. Gerencie o acervo e acompanhe as vendas.</p>

    <nav class="admin-nav">
        <a href="<?= e(app_url('admin/index.php?view=products')) ?>">Peças e estoque</a>
        <a href="<?= e(app_url('admin/index.php?view=orders')) ?>">Pedidos</a>
        <a href="<?= e(app_url('sair.php')) ?>">Sair</a>
    </nav>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>


    <?php if ($section === 'products'): ?>

        <section class="admin-card">
            <h2 class="admin-form-title"><?= $edit ? 'Editar peça' : 'Adicionar uma peça' ?></h2>

            <form method="post" class="row g-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">

                <div class="col-md-6">
                    <label class="form-label">Nome</label>
                    <input class="form-control" name="name" required value="<?= e($edit['name'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Marca</label>
                    <input class="form-control" name="brand" value="<?= e($edit['brand'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <select class="form-select" name="category_id" required>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                <?= e(category_label($c['slug'], $c['name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Preço (R$)</label>
                    <input class="form-control" type="number" name="price" min="0" step="0.01" required value="<?= e($edit['price'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Estoque</label>
                    <input class="form-control" type="number" name="stock" min="0" step="1" required value="<?= e($edit['stock'] ?? 1) ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="description" rows="3" required><?= e($edit['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">URL da imagem</label>
                    <input class="form-control" type="url" name="image" required value="<?= e($edit['image'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label>
                        <input type="checkbox" name="featured" <?= !empty($edit['featured']) ? 'checked' : '' ?>>
                        Destaque na vitrine
                    </label>
                </div>

                <div class="col-12">
                    <button class="button button-dark"><?= $edit ? 'Salvar alterações' : 'Adicionar à vitrine' ?> ↗</button>
                    <?php if ($edit): ?>
                        <a href="<?= e(app_url('admin/index.php')) ?>" class="text-link">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <h2 class="mt-5 admin-list-title">Acervo · <?= count($products) ?> peças</h2>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>PEÇA</th>
                        <th>CATEGORIA</th>
                        <th>PREÇO</th>
                        <th>ESTOQUE</th>
                        <th>STATUS</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <img class="product-thumb" src="<?= e($p['image']) ?>" alt="">
                                    <div>
                                        <b><?= e($p['name']) ?></b>
                                        <small><?= e($p['brand']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e(category_label($p['category_slug'], $p['category_name'])) ?></td>
                            <td><?= money($p['price']) ?></td>
                            <td><?= (int) $p['stock'] ?></td>
                            <td><?= $p['active'] ? 'Ativa' : 'Pausada' ?></td>
                            <td>
                                <a class="text-button" href="<?= e(app_url('admin/index.php?edit=' . (int) $p['id'])) ?>">Editar</a>
                                <form method="post" class="inline-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button class="text-button"><?= $p['active'] ? 'Desativar' : 'Ativar' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


    <?php else: ?>

        <h2 class="admin-list-title">Pedidos realizados · <?= count($orders) ?></h2>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>PEDIDO</th>
                        <th>CLIENTE</th>
                        <th>DATA</th>
                        <th>PAGAMENTO</th>
                        <th>ITENS</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o):
                        $it = db()->prepare('SELECT oi.quantity,oi.unit_price,p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?');
                        $it->execute([$o['id']]);
                    ?>
                        <tr>
                            <td>#<?= str_pad((string) $o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <?= e($o['customer']) ?>
                                <small class="d-block text-secondary"><?= e($o['email']) ?></small>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                            <td><?= e($o['payment_method']) ?></td>
                            <td>
                                <?php foreach ($it as $line): ?>
                                    <div>
                                        <?= e($line['name']) ?> × <?= (int) $line['quantity'] ?>
                                        <small>(<?= money($line['unit_price']) ?>)</small>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td><?= money($o['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
