<?php

if (!isset($title)) $title = 'Info';

?>
<h2><?= $title ?></h2>
<div class="box">
    <div class="inbox">
        <p><?= $message ?></p>
        <p><a href="javascript:history.go(-1)">Go back</a></p>
    </div>
</div>
