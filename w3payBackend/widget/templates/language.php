<?php if(empty($data)){ exit; } // check data ?>
<?php
$L = $data['L'];
?>
<div class="w3payLanguageBlock">
            <div class="languageBlock">
                <label class="currentLangBlock" for="inputLanguage"><span><?= $L->sL('Language') ?>: <?= $data['activeLanguage']['Language'] ?></span></label>
                <input type="checkbox" id="inputLanguage" />
                <div class="selectLangBlock">
                    <?php
                    foreach ($data['LanguagesLinksArr'] as $LanguagesLinkData){
                        echo '<a class="selectLangRow" href="'.$LanguagesLinkData['link'].'">'.$LanguagesLinkData['Language'].'<br>'.$LanguagesLinkData['LanguageName'].'</a>';
                    }
                    ?>
                </div>
            </div>
</div>
<style>
    .w3payLanguageBlock div.languageBlock .selectLangBlock {
        display: none;
    }
    .w3payLanguageBlock div.languageBlock>input:checked+.selectLangBlock {
        display: block;
    }
    .w3payLanguageBlock input {
        visibility: hidden;
        display: none;
    }
    .w3payLanguageBlock label {
        cursor: pointer;
    }
</style>
