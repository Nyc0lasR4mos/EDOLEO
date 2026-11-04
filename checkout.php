<?php

require __DIR__ . '/includes/bootstrap.php';
require_login();

$items = cart_items();
if (!$items) {
    redirect(app_url('carrinho.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $method = $_POST['payment_method'] ?? '';

    if (!in_array($method, ['PIX', 'Boleto', 'Cartão'], true)) {
        $error = 'Selecione uma forma de pagamento.';
    } else {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            $total  = 0;
            $locked = [];

            foreach ($items as $it) {
                $s = $pdo->prepare('SELECT price,stock,name FROM products WHERE id=? AND active=1 FOR UPDATE');
                $s->execute([$it['id']]);
                $p = $s->fetch();

                if (!$p || (int) $p['stock'] < (int) $it['quantity']) {
                    throw new RuntimeException('Estoque insuficiente para ' . $it['name'] . '.');
                }

                $total   += (float) $p['price'] * (int) $it['quantity'];
                $locked[] = [
                    'id'       => $it['id'],
                    'quantity' => $it['quantity'],
                    'price'    => $p['price'],
                ];
            }

            $s = $pdo->prepare("INSERT INTO orders (user_id,total,payment_method,status) VALUES (?,?,?,'confirmado')");
            $s->execute([current_user()['id'], $total, $method]);
            $order = (int) $pdo->lastInsertId();

            $itemStmt  = $pdo->prepare('INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock=stock-? WHERE id=?');

            foreach ($locked as $it) {
                $itemStmt->execute([$order, $it['id'], $it['quantity'], $it['price']]);
                $stockStmt->execute([$it['quantity'], $it['id']]);
            }

            $pdo->commit();
            $_SESSION['cart']        = [];
            $_SESSION['last_order']  = $order;
            redirect(app_url('pedido-concluido.php'));

        } catch (RuntimeException $e) {
            if (db()->inTransaction()) db()->rollBack();
            $error = $e->getMessage();

        } catch (Throwable $e) {
            if (db()->inTransaction()) db()->rollBack();
            throw $e;
        }
    }
}

$total     = array_sum(array_column($items, 'subtotal'));
$pageTitle = 'Finalizar compra — ÉdoLeo';
include __DIR__ . '/includes/header.php';
?>

<main class="wrap page-shell">

    <span class="eyebrow">QUASE SEU</span>
    <h1 class="page-title">Finalizar compra.</h1>

    <div class="cart-layout">

        <section class="form-card m-0">

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= e($error) ?></div>
            <?php endif; ?>

            <h2 class="section-display-title">Forma de pagamento</h2>
            <p class="page-lead">Pagamento demonstrativo para fins acadêmicos.</p>

            <form method="post">
                <?= csrf_field() ?>

                <label class="form-label" for="payment">Escolha uma opção</label>
                <select class="form-select mb-4" id="payment" name="payment_method" required>
                    <option value="">Selecione</option>
                    <option>PIX</option>
                    <option>Boleto</option>
                    <option>Cartão</option>
                </select>

                <button class="button button-dark w-100 justify-content-between">
                    Confirmar pedido <span>↗</span>
                </button>
            </form>

        </section>

        <aside class="summary-card">
            <h3>Resumo da compra</h3>

            <?php foreach ($items as $it): ?>
                <div class="summary-line">
                    <span><?= e($it['name']) ?> × <?= (int) $it['quantity'] ?></span>
                    <span><?= money($it['subtotal']) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="summary-line total">
                <span>Total</span>
                <span><?= money($total) ?></span>
            </div>

            <span class="stock-note">✳ Seu pedido reserva estas peças especiais.</span>
        </aside>

    </div>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
