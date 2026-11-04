<?php
function load_env(string $path): void {
    if (!is_file($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line); if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$key,$value] = explode('=', $line, 2); $key=trim($key); $value=trim(trim($value), "\"'");
        if (!array_key_exists($key, $_ENV)) $_ENV[$key]=$value;
    }
}
function env(string $key, string $fallback=''): string { return (string)($_ENV[$key] ?? getenv($key) ?: $fallback); }
function db(): PDO {
    static $pdo; if ($pdo instanceof PDO) return $pdo;
    foreach (['DB_HOST','DB_NAME','DB_USER','DB_PASS'] as $key) {
        if (!array_key_exists($key,$_ENV) && getenv($key)===false) throw new RuntimeException('Configure as variáveis DB_HOST, DB_NAME, DB_USER e DB_PASS no arquivo .env.');
    }
    $host=env('DB_HOST'); $name=env('DB_NAME'); $user=env('DB_USER'); $pass=env('DB_PASS');
    if ($host==='' || $name==='' || $user==='') throw new RuntimeException('DB_HOST, DB_NAME e DB_USER não podem ficar vazios no arquivo .env.');
    $dsn='mysql:host='.$host.';dbname='.$name.';charset=utf8mb4';
    $pdo=new PDO($dsn,$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); return $pdo;
}
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function app_url(string $path): string {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
    if (str_ends_with($base, '/admin')) $base = substr($base, 0, -6);
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}
function category_label(string $slug, string $name): string {
    return $slug === 'acessorios' ? 'Acessórios' : $name;
}
function money(mixed $v): string { return 'R$ '.number_format((float)$v,2,',','.'); }
function redirect(string $url): never { header('Location: '.$url); exit; }
function flash(string $message, string $type='success'): void { $_SESSION['flash']=['message'=>$message,'type'=>$type]; }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void { if (!current_user()) { $_SESSION['next']= $_SERVER['REQUEST_URI'] ?? app_url('index.php'); redirect(app_url('entrar.php')); } }
function require_admin(): void { require_login(); if (current_user()['role'] !== 'admin') { http_response_code(403); exit('Acesso não autorizado.'); } }
function csrf_field(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Sua sessão expirou. Atualize a página e tente novamente.'); } }
function cart_items(): array {
    if (empty($_SESSION['cart'])) return [];
    $ids=array_map('intval',array_keys($_SESSION['cart'])); $marks=implode(',',array_fill(0,count($ids),'?'));
    $s=db()->prepare("SELECT id,name,brand,price,stock,image FROM products WHERE active=1 AND id IN ($marks)"); $s->execute($ids); $items=[];
    foreach($s as $p) { $qty=min((int)$_SESSION['cart'][$p['id']],(int)$p['stock']); if($qty>0){$p['quantity']=$qty;$p['subtotal']=$qty*(float)$p['price'];$items[]=$p;} }
    return $items;
}
