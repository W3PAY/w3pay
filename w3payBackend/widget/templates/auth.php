<?php if(empty($data)){ exit; } // check data ?>

<div class="loadPageContent">
<?php $L = $data['LanguageBlock']['L']; ?>

<div class="settingsW3payBlock">
<?php echo $data['LanguageBlock']['html']; ?>
<?php
$L = $data['LanguageBlock']['L'];
// save Password Settings Form
if (empty($data['getPasswordSettingsHash'])) {
    ?>
    <div class="savePasswordSettingsForm" data-sendurl="<?= $data['sendurl'] ?>">
        <input name="loadPage" type="hidden" value="SaveSettings">
        <input name="savePasswordSettingsForm" type="hidden" value="savePasswordSettingsForm">
        <h2 class="settingsW3payBlockTitle">W3PAY <?= $L->sL('Registration') ?></h2>
        <div class="settingsW3payBlockRow">
            <label class="settingsW3payBlockLabel"><?= $L->sL('Set a new password') ?></label>
            <input name="PasswordSettings" placeholder="<?= $L->sL('Password') ?>" type="password" class="settingsW3payBlockInput"
                   required="required" value="">
        </div>
        <div class="formMessage"></div>
        <div class="settingsW3payBlockRow">
            <a href="#" class="settingsW3payBlockBtn savePasswordSettingsFormBtn"><?= $L->sL('Save') ?></a>
        </div>
    </div>
    <?php
} else {
    // AuthForm
    //if (!$data['AuthSettings']) {
        ?>
        <div class="settingsW3payAuthForm" data-sendurl="<?= $data['sendurl'] ?>">
            <input name="loadPage" type="hidden" value="<?= $data['loadPage'] ?>">
            <h2 class="settingsW3payBlockTitle">W3PAY <?= $L->sL('Authorization') ?></h2>
            <div class="settingsW3payBlockRow">
                <label class="settingsW3payBlockLabel"><?= $L->sL('Insert the password') ?></label>
                <input name="PasswordSettings" placeholder="<?= $L->sL('Password') ?>" type="password" class="settingsW3payBlockInput"
                       required="required" value="">
            </div>
            <div class="formMessage"></div>
            <div class="settingsW3payBlockRow">
                <a href="#" class="settingsW3payBlockBtn settingsW3payAuthFormBtn"><?= $L->sL('Login in') ?></a>
            </div>
        </div>
        <?php
    //}
}
?></div>

</div>