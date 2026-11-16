<?php
declare(strict_types=1);
session_start();

try {
    require __DIR__ . '/connection.php';
} catch (Throwable $e) {
    error_log('Shop database connection failed: ' . $e->getMessage());
    http_response_code(500);
    $dbErrorCode = $e instanceof PDOException && isset($e->errorInfo[1])
        ? ' MySQL error code: ' . (int)$e->errorInfo[1] . '.'
        : '';
    exit('The shop could not connect to its database. Check that connection.secrets.php is beside index.php, or set the DB_* environment variables in your hosting panel, then verify the database host, name, username, password, and user permissions.' . $dbErrorCode);
}

$_SESSION['cart'] ??= [];
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $id = (int)($_POST['product_id'] ?? 0);
        $stmt = $db->prepare('SELECT id, stock FROM products WHERE id=?'); $stmt->execute([$id]); $product = $stmt->fetch();
        if ($product && (int)($_SESSION['cart'][$id] ?? 0) < (int)$product['stock']) { $_SESSION['cart'][$id] = (int)($_SESSION['cart'][$id] ?? 0) + 1; $notice = 'Added to your bag.'; }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][(int)($_POST['product_id'] ?? 0)]); $notice = 'Item removed from your bag.';
    } elseif ($action === 'checkout') {
        $customer = trim((string)($_POST['customer'] ?? '')); $email = trim((string)($_POST['email'] ?? ''));
        if ($customer === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$_SESSION['cart']) $notice = 'Please add an item and enter a name and valid email.';
        else {
            $ids = array_map('intval', array_keys($_SESSION['cart'])); $marks = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($marks)"); $stmt->execute($ids); $items = $stmt->fetchAll();
            $total = 0; foreach ($items as $p) $total += $p['price'] * min((int)$_SESSION['cart'][$p['id']], (int)$p['stock']);
            if (!$items || $total <= 0) $notice = 'Your bag is empty.';
            else {
                $db->beginTransaction();
                $stmt = $db->prepare('INSERT INTO orders(customer,email,total) VALUES(?,?,?)'); $stmt->execute([$customer,$email,$total]); $orderId = (int)$db->lastInsertId();
                $add = $db->prepare('INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?,?,?,?)');
                $stock = $db->prepare('UPDATE products SET stock=GREATEST(0,stock-?) WHERE id=?');
                foreach ($items as $p) { $qty = min((int)$_SESSION['cart'][$p['id']], (int)$p['stock']); if ($qty) { $add->execute([$orderId,$p['id'],$qty,$p['price']]); $stock->execute([$qty,$p['id']]); } }
                $db->commit(); $_SESSION['cart'] = []; $_SESSION['last_order'] = $orderId; header('Location: ?page=thanks'); exit;
            }
        }
    }
}

$page = $_GET['page'] ?? 'shop';
$products = $db->query('SELECT * FROM products ORDER BY id')->fetchAll();
$cart = []; $cartTotal = 0.0;
if ($_SESSION['cart']) {
    $ids = array_map('intval', array_keys($_SESSION['cart'])); $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($marks)"); $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) { $p['quantity'] = min((int)$_SESSION['cart'][$p['id']], (int)$p['stock']); $p['line_total'] = $p['quantity'] * $p['price']; $cartTotal += $p['line_total']; $cart[] = $p; }
}
function money(float $amount): string { return '$' . number_format($amount, 2); }
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#f7f5ef"><title>Little Things — good things, thoughtfully found</title><link rel="stylesheet" href="style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">l.</span><span>little things<small>GOOD THINGS, THOUGHTFULLY FOUND</small></span></a><nav><a href="index.php#shop">Shop</a><a href="index.php#story">Our story</a><a class="bag-link" href="?page=cart">Bag <span><?= array_sum($_SESSION['cart']) ?></span></a></nav></header>
<main>
<?php if ($notice): ?><div class="notice"><?= e($notice) ?></div><?php endif; ?>
<?php if ($page === 'cart'): ?>
<section class="page-heading"><p class="eyebrow">YOUR LITTLE FINDS</p><h1>Your bag<span class="period">.</span></h1><p>Good picks. Excellent taste.</p></section>
<section class="cart-layout"><div class="cart-items"><?php if (!$cart): ?><div class="empty"><div>🛍️</div><h2>Your bag is taking a little break.</h2><p>Find something lovely to bring home.</p><a class="button" href="index.php#shop">Explore the shop</a></div><?php else: foreach ($cart as $p): ?><article class="cart-row"><div class="product-art small-art"><?= e($p['emoji']) ?></div><div class="cart-name"><h3><?= e($p['name']) ?></h3><span><?= e($p['category']) ?> · Qty <?= $p['quantity'] ?></span></div><strong><?= money((float)$p['line_total']) ?></strong><form method="post"><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><button class="remove" aria-label="Remove <?= e($p['name']) ?>">×</button></form></article><?php endforeach; endif; ?></div>
<?php if ($cart): ?><aside class="checkout"><h2>Order summary</h2><div class="summary-line"><span>Subtotal</span><span><?= money($cartTotal) ?></span></div><div class="summary-line"><span>Shipping</span><span class="free">On us</span></div><div class="summary-total"><span>Total</span><strong><?= money($cartTotal) ?></strong></div><form method="post" class="checkout-form"><input type="hidden" name="action" value="checkout"><label>Your name<input name="customer" required placeholder="Alex Morgan"></label><label>Email address<input name="email" type="email" required placeholder="alex@example.com"></label><button class="button full" type="submit">Place demo order <span>↗</span></button></form><p class="demo-note">Demo checkout · no payment collected</p></aside><?php endif; ?></section>
<?php elseif ($page === 'thanks' && isset($_SESSION['last_order'])): ?><section class="thank-you"><div class="thanks-icon">✳</div><p class="eyebrow">ORDER #<?= (int)$_SESSION['last_order'] ?> · JUST FOR YOU</p><h1>Well, this is<br>exciting<span class="period">!</span></h1><p>Your demo order is safely in the database. Imagine a little happy dance happening right now.</p><a href="index.php#shop" class="button">Keep exploring <span>↗</span></a></section>
<?php else: ?>
<section class="hero"><div class="hero-copy"><p class="eyebrow"><span class="sparkle">✳</span> A SMALL SHOP FOR EVERYDAY JOY</p><h1>Good things<br>for <em>ordinary</em><br>days<span class="period">.</span></h1><p class="hero-intro">Thoughtful little finds, made to make the everyday feel a little more lovely.</p><a class="button" href="#shop">Find your favorite <span>↘</span></a><div class="hero-foot"><span>✳&nbsp; Thoughtfully picked</span><span>✳&nbsp; Made for everyday</span></div></div><div class="hero-art"><div class="sun"></div><div class="arch"><div class="art-item art-mug">☕</div><div class="art-item art-flower">🌼</div><div class="art-item art-book">📓</div><span class="art-note">a little<br>something<br>good</span></div><span class="floating-star star-one">✳</span><span class="floating-star star-two">✦</span><span class="art-caption">THE EVERYDAY EDIT — VOL. 01</span></div></section>
<div class="ticker"><div>FIND YOUR EVERYDAY FAVORITE <span>✳</span> GOOD THINGS, MADE TO LAST <span>✳</span> A LITTLE JOY GOES A LONG WAY <span>✳</span> FIND YOUR EVERYDAY FAVORITE <span>✳</span></div></div>
<section class="shop-section" id="shop"><div class="section-heading"><div><p class="eyebrow">THE GOOD STUFF</p><h2>Meet your new<br>favorite things<span class="period">.</span></h2></div><p>Useful, joyful, and just a little bit special.<br>Picked with you in mind.</p></div><div class="product-grid"><?php foreach ($products as $i => $p): ?><article class="product-card"><div class="product-art tone-<?= $i % 4 ?>"><span class="product-emoji"><?= e($p['emoji']) ?></span><span class="product-category"><?= e($p['category']) ?></span><form method="post"><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><button class="quick-add" aria-label="Add <?= e($p['name']) ?> to bag" <?= (int)$p['stock'] === 0 ? 'disabled' : '' ?>>+</button></form></div><div class="product-info"><div><h3><?= e($p['name']) ?></h3><p><?= e($p['description']) ?></p></div><strong><?= money((float)$p['price']) ?></strong></div></article><?php endforeach; ?></div></section>
<section class="story" id="story"><div class="story-art"><div class="story-circle">✳</div><span>little<br>things<br>matter</span></div><div class="story-copy"><p class="eyebrow">A NOTE FROM US</p><h2>Life is made of<br>little things<span class="period">.</span></h2><p>That first sip of coffee. The notebook you reach for without thinking. The candle that makes home feel like yours. We gather the everyday essentials that turn little moments into the good ones.</p><p>Welcome to your new favorite little corner of the internet.</p><a class="text-link" href="#shop">A little look around <span>↗</span></a></div></section>
<section class="newsletter"><p class="eyebrow">GOOD THINGS, OCCASIONALLY</p><h2>A little note from us<span class="period">.</span></h2><p>New finds, small joys, and very good things in your inbox.</p><form onsubmit="event.preventDefault();this.innerHTML='<span class=\'subscribed\'>You’re on the list. ✳</span>'"><input type="email" required placeholder="Your email address"><button type="submit">Count me in ↗</button></form><small>Just the good stuff. Unsubscribe whenever.</small></section>
<?php endif; ?>
</main><footer><a class="brand" href="index.php"><span class="brand-mark">l.</span><span>little things<small>GOOD THINGS, THOUGHTFULLY FOUND</small></span></a><p>Small joys. Everyday things. © <?= date('Y') ?> Little Things.</p><a href="?page=cart">Your bag ↗</a></footer></body></html>
