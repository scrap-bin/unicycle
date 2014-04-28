<?php

header('Content-type: text/html; charset=utf-8');

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="refresh" content="<?= $delay ?>;url=<?= $url ?>">
<title><?= $message ?></title>
<link href="<?= baseUrl() ?>/css/styles.css" rel="stylesheet">
</head>
<body id="message">
    <div class="box">
        <div class="inbox">
            <?= $message ?>
        </div>
    </div>
</body>
</html>
