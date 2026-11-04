<?php

require __DIR__ . '/includes/bootstrap.php';

unset($_SESSION['user']);
session_regenerate_id(true);
flash('Você saiu da sua conta.');
redirect(app_url('index.php'));
