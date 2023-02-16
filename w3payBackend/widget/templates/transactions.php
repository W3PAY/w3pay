<?php if(empty($data)){ exit; } // check data ?>

<?php
/*echo '<pre>';
print_r($data);
echo '</pre>';*/
?>
<div class="transactionsW3payBlock">
<?php echo $data['LanguageBlock']['html']; ?>
    <h2 class="transactionsW3payBlockTitle">Transactions Log</h2>
    <div class="txBlock">

    </div>
    <div class="txPages">
        <?php
        foreach ($data['TransactionsPages']['txPageData'] as $txPageData){
            ?><a class="loadTxPage" data-page="<?= $txPageData['page'] ?>" data-checkPaymentPageUrl="<?= $data['checkPaymentPageUrl'] ?>" data-sendurl="<?= $data['sendurl'] ?>" title="Orders: <?= $txPageData['order_start'] ?> - <?= $txPageData['order_finish'] ?>" href="#"><?= $txPageData['page'] ?></a><?php
        }
        if(empty($data['TransactionsPages']['txPageData'])){
            echo '<h3>Transaction log is empty</h3>';
        }
        ?>
    </div>
</div>
<?php
//echo '<pre>';
//print_r($data['LanguageBlock']['activeLanguage']['ISOCode']);
//print_r($data['TransactionsPage']);
//print_r($data['TransactionsPages']);
//print_r($data['PaymentSettings']);
//echo '</pre>';
?>