<?php

require __DIR__ . '/includes/bootstrap.php';
require_login();

$id = (int) ($_SESSION['last_order'] ?? 0);

if (!$id) {
    redirect(app_url('conta.php'));
}

unset($_SESSION['last_order']);

$pageTitle = 'Pedido confirmado — ÉdoLeo';
include __DIR__ . '/includes/header.php';
?>

<main class="wrap page-shell text-center">

    <span class="eyebrow">UMA NOVA HISTÓRIA COMEÇA</span>
    <div style="font-size:54px;color:var(--wine);margin-top:20px">✳</div>
    <h1 class="page-title">Pedido confirmado.</h1>

    <p class="page-lead">
        O pedido #<?= str_pad((string) $id, 5, '0', STR_PAD_LEFT) ?> foi registrado com sucesso.
    </p>
    <p class="page-lead">
        Agradecemos por escolher uma peça com história. Seu pagamento foi simulado para este projeto acadêmico.
    </p>

    <a class="button button-dark" href="<?= e(app_url('conta.php')) ?>">
        Acompanhar meus pedidos <span>↗</span>
    </a>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
