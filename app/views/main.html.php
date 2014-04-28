<?php

header('Content-type: text/html; charset=utf-8');

$_currentUser = user();
$_authenticated = $_currentUser->hasRole('ROLE_USER');
//$_currentLanguage = currentLanguage();
//$_languages = languageDescriptions();

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= isset($title) ? e($title) : 'No title' ?></title>
</head>
<body>
<div class="header">
    <span>Hello <strong><?= e($_currentUser->username) ?>!</strong></span>
<?php if ($_authenticated): ?><a href="<?= url('logout', ['token' => $_currentUser->getCsrfToken()]) ?>">Logout</a>
<?php else: ?><a href="<?= url('login') ?>">Login</a>
<?php endif; ?>
    <hr>
</div>
<?= $this->block('content') ?>
</body>
</html>
