<?php

require __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect(app_url('conta.php'));
}

$mode  = $_GET['cadastro'] ?? null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || strlen($password) < 8) {
        $error = 'Informe um e-mail válido e uma senha com ao menos 8 caracteres.';

    } else {
        if (($_POST['mode'] ?? 'login') === 'register') {
            $name = trim($_POST['name'] ?? '');

            if ($name === '') {
                $error = 'Informe seu nome.';
            } else {
                try {
                    $s = db()->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)');
                    $s->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

                    $_SESSION['user'] = [
                        'id'    => (int) db()->lastInsertId(),
                        'name'  => $name,
                        'email' => $email,
                        'role'  => 'customer',
                    ];

                    flash('Conta criada. Seja bem-vindo à ÉdoLeo!');
                    redirect(app_url('conta.php'));

                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        $error = 'Este e-mail já possui cadastro.';
                    } else {
                        throw $e;
                    }
                }
            }

        } else {
            $s = db()->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email=?');
            $s->execute([$email]);
            $u = $s->fetch();

            if ($u && password_verify($password, $u['password_hash'])) {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'    => (int) $u['id'],
                    'name'  => $u['name'],
                    'email' => $u['email'],
                    'role'  => $u['role'],
                ];

                $next = $_SESSION['next'] ?? 'conta.php';
                unset($_SESSION['next']);
                redirect($next);

            } else {
                $error = 'E-mail ou senha incorretos.';
            }
        }
    }
}

$pageTitle = $mode ? 'Criar conta — ÉdoLeo' : 'Entrar — ÉdoLeo';
include __DIR__ . '/includes/header.php';
?>

<main class="wrap page-shell">

    <span class="eyebrow">É BOM TER VOCÊ AQUI</span>
    <h1 class="page-title text-center"><?= $mode ? 'Crie a sua conta.' : 'Entre na sua conta.' ?></h1>
    <p class="page-lead text-center"><?= $mode ? 'Salve suas escolhas e acompanhe seus pedidos.' : 'Continue sua história com a ÉdoLeo.' ?></p>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="mode" value="<?= $mode ? 'register' : 'login' ?>">

            <?php if ($mode): ?>
                <label class="form-label" for="name">Nome</label>
                <input class="form-control mb-3" id="name" name="name" required autocomplete="name">
            <?php endif; ?>

            <label class="form-label" for="email">E-mail</label>
            <input class="form-control mb-3" id="email" type="email" name="email" required autocomplete="email">

            <label class="form-label" for="password">Senha</label>
            <input class="form-control mb-4" id="password" type="password" name="password" required minlength="8" autocomplete="<?= $mode ? 'new-password' : 'current-password' ?>">

            <button class="button button-dark w-100 justify-content-between">
                <?= $mode ? 'Criar conta' : 'Entrar' ?> <span>↗</span>
            </button>
        </form>

        <p class="text-center mt-4 mb-0">
            <?php if ($mode): ?>
                Já tem uma conta? <a class="text-link" href="<?= e(app_url('entrar.php')) ?>">Entrar</a>
            <?php else: ?>
                Primeira vez por aqui? <a class="text-link" href="<?= e(app_url('entrar.php?cadastro=1')) ?>">Criar conta</a>
            <?php endif; ?>
        </p>

    </div>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
