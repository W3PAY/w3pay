<?php if(empty($data)){ exit; } // check data ?>

<?php $L = $data['LanguageBlock']['L']; ?>

<div class="settingsW3payBlock">
    <?php echo $data['LanguageBlock']['html']; ?>
    <?php
    $SettingsData = $data['SettingsData'];
    $L = $data['LanguageBlock']['L'];
    /*echo '<pre>';
    print_r($SettingsDefaultFileArr);
    echo '</pre>';*/
    ?>
    <div class="settingsW3payForm" data-sendurl="<?= $data['sendurl'] ?>">
        <input name="settingsW3payForm" type="hidden" value="settingsW3payForm">
        <h2 class="settingsW3payBlockTitle">W3PAY - <?= $L->sL('Settings') ?></h2>

        <div class="settingsW3payBlockRow">
            <label class="settingsW3payBlockLabel"><?= $L->sL('Address Recipient (seller)') ?>. <span>eth. (0x...)</span></label>
            <?php $name = 'addressRecipient'; ?>
            <input autocomplete="off" name="<?= $name ?>" placeholder="0x000....." type="text"
                   class="settingsW3payBlockInput" required="required" value="<?= $SettingsData[$name] ?>">
        </div>

        <h3 class="settingsW3payBlockTitle"><?= $L->sL('Tokens to receive payment') ?></h3>
        <?php
        foreach ($SettingsData['SettingsDefaultFileArr']['chainsData'] as $chainsData) {
            $chainsData['chainData']['chainId'];
            $chainsData['chainData']['chainName'];

            ?>
            <div class="settingsW3payBlockRow">
                <label class="settingsW3payBlockLabel"><input <?= (!empty($SettingsData['PaymentPersonalSettings'][$chainsData['chainData']['chainId']]['NetworkStatus'])) ? 'checked' : '' ?>
                            type="checkbox" name="NetworksStatus[<?= $chainsData['chainData']['chainId'] ?>]"
                            value="true"><?= ($chainsData['chainData']['chainId'] == 97) ? $L->sL('Enable testnet').'.' : $L->sL('Enable network').'.' ?>
                    <?= $L->sL('Tokens to receive in') ?> <?= $chainsData['chainData']['chainName'] ?></label>
                <select class="settingsW3payBlockSelect"
                        name="receiveTokensAddresses[<?= $chainsData['chainData']['chainId'] ?>]">
                    <?php
                    if (is_array($chainsData['payTokens'])) {
                        foreach ($chainsData['payTokens'] as $payToken) {
                            $selected = '';
                            if (isset($SettingsData['PaymentPersonalSettings'][$chainsData['chainData']['chainId']]['receiveToken']['addressCoin']) && mb_strtolower($SettingsData['PaymentPersonalSettings'][$chainsData['chainData']['chainId']]['receiveToken']['addressCoin']) == mb_strtolower($payToken['addressCoin'])) {
                                $selected = 'selected';
                            }
                            ?>
                            <option <?= $selected ?>
                            value="<?= $payToken['addressCoin'] ?>"><?= $payToken['nameCoin'] ?></option><?
                        }
                    }
                    ?>
                </select>

            </div>
            <?php
        }
        ?>

        <h3 class="settingsW3payBlockTitle"><?= $L->sL('Additional settings') ?></h3>

        <div class="settingsW3payBlockRow">
            <label class="settingsW3payBlockLabel"><input type="checkbox" name="UpdateW3pay" value="true"> <?= $L->sL('Get an update from') ?> GitHub</label>
            <select class="settingsW3payBlockSelect" name="cms">
                <?php
                if (is_array($data['UpdatesArr'])) {
                    foreach ($data['UpdatesArr'] as $cms =>  $UpdateArr) {
                        $selected = '';
                        if (isset($data['cms']) && $data['cms'] == $cms) {
                            $selected = 'selected';
                        }
                        ?>
                        <option <?= $selected ?>
                        value="<?= $cms ?>"><?= $UpdateArr['nameCms'] ?></option><?
                    }
                }
                ?>
            </select>

        </div>


        <div style="display: none;" class="settingsW3payBlockRow">
            <?php $name = 'UpdateW3paySettingsDefaultGitHub'; ?>
            <label class="settingsW3payBlockLabel"><input <?= (!empty($SettingsData[$name])) ? 'checked' : '' ?>
                        type="checkbox" name="<?= $name; ?>" value="true"> <?= $L->sL('Get an update from') ?> GitHub (w3pay_settings_default.json).</label>
        </div>
        <div style="display: none;" class="settingsW3payBlockRow">
            <?php $name = 'LoadW3paySettingsDefaultGitHub'; ?>
            <label class="settingsW3payBlockLabel"><input <?= (!empty($SettingsData[$name])) ? 'checked' : '' ?>
                        type="checkbox" name="<?= $name; ?>" value="true"> Get the latest w3pay_settings_default.json
                file from github. Slows down page loading speed.</label>
        </div>
        <div class="settingsW3payBlockRow">
            <?php $name = 'useWeb3'; ?>
            <label class="settingsW3payBlockLabel"><input
                        class="useWeb3Input" <?= (!empty($SettingsData[$name])) ? 'checked' : '' ?> type="checkbox"
                        name="<?= $name; ?>" value="true"> <?= $L->sL('Use PHP web3p. If not selected, the API is used.') ?></label>
            <div class="useWeb3Text">
                <!--Important! If $useWeb3 = enable, then execute in the console: <pre>cd w3pay/w3payBackend/composer/; composer require web3p/web3.php dev-master</pre>-->
            </div>
        </div>
        <div class="ScanApiTokensBlock">
            <h3 class="settingsW3payBlockTitle"><?= $L->sL('Tokens for the block explorer') ?> (API).</h3>
            <?php
            foreach ($SettingsData['ScanApiTokens'] as $chainId => $ScanApiToken) {
                ?>
                <div class="settingsW3payBlockRow">
                    <label class="settingsW3payBlockLabel"><?= $L->sL('Token for') ?> <?= $ScanApiToken['ScanApiUrl'] ?></label>
                    <input name="ScanApiTokens[<?= $chainId ?>][ScanApiUrl]" value="<?= $ScanApiToken['ScanApiUrl'] ?>"
                           type="hidden">
                    <input name="ScanApiTokens[<?= $chainId ?>][ScanApiToken]" placeholder="Token" type="text"
                           class="settingsW3payBlockInput" value="<?= $ScanApiToken['ScanApiToken'] ?>">
                </div>
                <?php
            }
            ?>
        </div>

        <h3 style="display: none;" class="settingsW3payBlockTitle"><?= $L->sL('Settings files') ?></h3>
        <?php
        foreach ($SettingsData['SettingsFiles'] as $FileKey => $FilePath) {
            $FilePath = str_replace("\\", "/", $FilePath);
            $FilePath = str_replace("//", "/", $FilePath);
            $displayNone = '';
            //if ($FileKey == 'composerAutoload') {
                $displayNone = 'display: none;';
            //}
            ?>
            <div style="<?= $displayNone ?>" class="settingsW3payBlockRow">
                <label class="settingsW3payBlockLabel"><?= $L->sL('The path to the file') ?>: <?= $FileKey ?></label>
                <input name="SettingsFiles[<?= $FileKey ?>]" type="text" class="settingsW3payBlockInput"
                       required="required" value="<?= $FilePath ?>">
            </div>
            <?php
        }
        ?>
        <h3 class="settingsW3payBlockTitle"><?= $L->sL('Security Settings') ?></h3>
        <div class="settingsW3payBlockRow">
            <label class="settingsW3payBlockLabel"><?= $L->sL('Secret Key') ?>. <span><?= $L->sL('Come up with a secret key to generate contract signatures') ?>.</span></label>
            <?php $name = 'SecretSignKey'; ?>
            <input autocomplete="off" name="<?= $name ?>" placeholder="My secret key" type="text"
                   class="settingsW3payBlockInput" required="required" value="<?= $SettingsData[$name] ?>">
        </div>
        <!--<p <?= (!sAssistant::instance()->checkSettingsFile()) ? 'style="display:none;"' : '' ?>><?= $L->sL('Secret Key') ?>: <b><?= $SettingsData[$name] ?></b></p>-->
        <?php
        //if($data['checkAuthRequired']){
        //if(empty($data['PasswordSettings'])){
        ?>
        <div class="settingsW3payBlockRow">
            <label class="settingsW3payBlockLabel"><?= $L->sL('Password for the current settings file') ?></label>
            <?php $name = 'PasswordSettingsNew'; ?>
            <input autocomplete="off" name="<?= $name ?>" placeholder="<?= $L->sL('Password') ?>" type="password"
                   class="settingsW3payBlockInput" value="">
        </div>
        <?php
        /*} else {
            ?>
            <input name="PasswordSettings" type="hidden" value="<?= $data['PasswordSettings'] ?>">
            <?php
        }*/
        //}
        ?>
        <div class="formMessage"></div>
        <div class="settingsW3payBlockRow">
            <input name="loadPage" type="hidden" value="SaveSettings">
            <a href="#" class="FormBtn settingsW3payFormBtn settingsW3payBlockBtn w3payGreenBtn"><?= $L->sL('Save') ?></a>
        </div>
    </div>

    <div style="display: none;" class="deleteAllSettingsForm" data-sendurl="<?= $data['sendurl'] ?>">
        <input name="deleteAllSettingsForm" type="hidden" value="deleteAllSettingsForm">
        <input name="SettingsFiles[w3pay_settings]" type="hidden" class="settingsW3payBlockInput" required="required"
               value="<?= str_replace("\\", "/", $SettingsData['SettingsFiles']['w3pay_settings']) ?>">
        <div class="formMessage"></div>
        <div class="settingsW3payBlockRow">
            <input name="loadPage" type="hidden" value="SaveSettings">
            <a href="#" type="submit" class="FormBtn deleteAllSettingsFormBtn settingsW3payBlockBtn w3payRedBtn"><?= $L->sL('Delete all configuration files and secret key') ?></a>
        </div>
    </div>

    <div class="w3payFooter"><span class="w3payVersion">Version: <?= $data['VersionData']['version'] ?></span> <a target="_blank" href="https://w3pay.dev/">w3pay.dev</a> <a data-sendurl="<?= $data['sendurl'] ?>" class="signoutBtn" href="#">Sign out</a></div>

    <?php /* if(!empty($data['PasswordSettings'])){ ?><p><b>To remove the password to the current settings form, you need to delete the <?= 'w3pay/w3payBackend/settings/PasswordSettingsHash.php'; ?> file.</b></p><?php } */ ?>
    <?php /* if($data['checkSettingsFile']){ ?><p><b>To remove all W3PAY method settings, you must delete the <?= 'w3pay/w3payBackend/settings/sSettings.php'; ?> file.</b></p><?php } */ ?>
</div>
