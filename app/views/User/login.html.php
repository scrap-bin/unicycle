<?php

$this->extend('main');

if (!isset($title)) $title = t('Login');

?>
<h2><span><?= $title ?></span></h2>

<?php if (!empty($errors)): ?>
<div class="errorbox">
<?php   foreach ($errors as $error): ?>
    <p><?= $error ?></p>
<?php   endforeach; ?>
</div>
<?php endif; ?>

<div>
    <form id="login" method="post" action="<?= url('login_check') ?>">
        <fieldset>
            <legend><?= t('Login legend', 'login') ?></legend>
            <div class="infldset">
<?php if (!empty($redirect_url)): ?>
                <input type="hidden" name="redirect_url" value="<?= e($redirect_url) ?>" />
<?php endif; ?>
                <label class="conl required"><strong><?= t('Username') ?> <span><?= t('Required') ?></span></strong><br /><input type="text" name="username" size="25" maxlength="25" tabindex="1" /><br /></label>
                <label class="conl required"><strong><?= t('Password') ?> <span><?= t('Required') ?></span></strong><br /><input type="password" name="password" size="25" tabindex="2" /><br /></label>

                <div class="rbox clearb">
                    <label><input type="checkbox" name="save_pass" value="1" tabindex="3" />&nbsp;<?= t('Remember me', 'login') ?><br /></label>
                </div>
            </div>
        </fieldset>
        <p class="buttons"><input type="submit" name="login" value="<?= t('Login') ?>" tabindex="4" /></p>
    </form>
</div>
