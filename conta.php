<?php

require __DIR__ . '/includes/bootstrap.php';
require_login();

$s = db()->prepare('SELECT id,created_at,total,payment_method,status FROM orders WHERE user_id=? ORDER BY created_at DESC');
$s->execute([current_user()['id']]);
$orders = $s->fetchAll();

$pageTitle = 'Minha conta — ÉdoLeo';
include __DIR__ . '/includes/header.php';
?>

<main class="wrap page-shell">

    <span class="eyebrow">SEU ESPAÇO ÉDOLEO</span>
    <h1 class="page-title">Olá, <?= e(current_user()['name']) ?>.</h1>
    <p class="page-lead">
        <?= e(current_user()['email']) ?> ·
        <a href="<?= e(app_url('sair.php')) ?>" class="text-link">Sair</a>
    </p>

    <h2 class="mt-5 section-display-title">Seus pedidos</h2>

    <?php if (!$orders): ?>

        <p class="page-lead mt-3">Seus próximos achados aparecerão aqui.</p>
        <a class="button button-dark" href="<?= e(app_url('index.php#colecao')) ?>">Descobrir a coleção ↗</a>

    <?php else: ?>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>PEDIDO</th>
                        <th>DATA</th>
                        <th>PAGAMENTO</th>
                        <th>STATUS</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= str_pad((string) $o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                            <td><?= e($o['payment_method']) ?></td>
                            <td><?= e($o['status']) ?></td>
                            <td><?= money($o['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
