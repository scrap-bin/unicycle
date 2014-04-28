<?php

header('Content-type: text/html; charset=utf-8');

$user = user();
$authenticated = $user->hasRole('ROLE_USER');

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= isset($title) ? e($title) : 'No title' ?></title>
</head>
<body>
<div class="header">
    <span>Hello <strong><?= e($user->username) ?>!</strong></span>
<?php if ($authenticated): ?><a href="<?= url('logout') ?>">Logout</a>
<?php else: ?><a href="<?= url('login') ?>">Login</a>
<?php endif; ?>
    <hr>
</div>
<?= $this->block('content') ?>
</body>
</html>
