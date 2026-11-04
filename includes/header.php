<?php
$pageTitle    = $pageTitle ?? 'ÉdoLeo — elegância que circula';
$flashMessage = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$cartCount = array_sum($_SESSION['cart'] ?? []);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f4ee">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(app_url('assets/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,500&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(app_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<div class="announcement">
    CURADORIA CIRCULAR, ESTILO QUE FICA <span>✳</span> PEÇAS ÚNICAS, NOVAS HISTÓRIAS
</div>

<header class="site-header">
    <div class="wrap nav-wrap">
        <a href="<?= e(app_url('index.php')) ?>" class="wordmark" aria-label="ÉdoLeo início">ÉdoLeo<span>.</span></a>

        <nav class="main-nav" id="primary-navigation">
            <a href="<?= e(app_url('index.php#colecao')) ?>">Coleção</a>
            <a href="<?= e(app_url('index.php?categoria=ternos')) ?>">Alfaiataria</a>
            <a href="<?= e(app_url('index.php?categoria=vestidos')) ?>">Vestidos</a>
            <a href="<?= e(app_url('index.php#manifesto')) ?>">Nossa história</a>
        </nav>

        <div class="nav-actions">
            <a class="nav-login" href="<?= e(app_url(current_user() ? 'conta.php' : 'entrar.php')) ?>">
                <?= current_user() ? 'Minha conta' : 'Entrar' ?>
            </a>

            <a class="cart-link" href="<?= e(app_url('carrinho.php')) ?>" aria-label="Carrinho com <?= $cartCount ?> itens">
                <svg aria-hidden="true" viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L21 8H6"/>
                    <circle cx="10" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>
                <i><?= $cartCount ?></i>
            </a>

            <button class="menu-toggle" aria-label="Abrir menu" aria-controls="primary-navigation" aria-expanded="false">☰</button>
        </div>
    </div>
</header>

<?php if ($flashMessage): ?>
    <div class="flash-message <?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div>
<?php endif; ?>
