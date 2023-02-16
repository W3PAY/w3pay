<?php if(empty($data)){ exit; } // check data ?>
<?php
$PaymentSettingsNew = [];
foreach ($data['PaymentSettings']['chainsData'] as $PaymentSettingsRow){
    $PaymentSettingsNew[$PaymentSettingsRow['chainData']['chainId']] = $PaymentSettingsRow;
}
/*echo '<pre>';
print_r($data['TransactionsPage']);
echo '</pre>';*/
?>
<div class="txRow txHead">
    <div class="txOrder">Order</div>
    <div class="txChain">Chain</div>
    <!--<div class="txStatus">Status</div>-->
    <div class="txDate">Date</div>
    <div class="txTx">Transaction Hash</div>
</div>
<?php
foreach ($data['TransactionsPage'] as $txRow){
    $del = (strpos($data['checkPaymentPageUrl'], '?') !== false)?'&':'?';
    $link = $data['checkPaymentPageUrl'].$del.'chainid='.$txRow['chainId'].'&tx='.$txRow['tx'].'&lang='.$data['LanguageBlock']['activeLanguage']['ISOCode'];
    ?>
    <div class="txRow">
        <div class="txOrder"><span class="txName">Order:</span><?= $txRow['orderId'] ?></div>
        <div class="txChain"><span class="txName">Chain:</span><?= $PaymentSettingsNew[$txRow['chainId']]['chainData']['chainName'] ?></div>
        <!--<div class="txStatus"><span class="txName">Status: </span><?= $data['StatusArr'][$txRow['status']] ?></div>-->
        <div class="txDate"><span class="txName">Date:</span><?= date('Y-m-d H:i', strtotime($txRow['date'])) ?></div>
        <div class="txTx"><span class="txName">Tx:</span><a target="_blank" href="<?= $link ?>"><?= $txRow['tx'] ?></a></div>
    </div>
    <?php
}
?>
