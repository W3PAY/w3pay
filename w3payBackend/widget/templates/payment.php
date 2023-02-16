<?php if(empty($data)){ exit; } // check data ?>
<?php
// Include php class sW3pay.php
include_once($data['FolderBackend'] . '/services/sW3pay.php');
// Add a backend signature to the order and data
$data['OrderData'] = sW3pay::instance()->getOrderDataAddData($data['OrderData']);
?>

<!-- Display the payment block. data-function-after-pay=functionAfterPay1 - The function starts after payment  -->
<div class="payBlock" data-SelectPayTokens='<?= json_encode($data['OrderData']) ?>' data-function-after-pay="functionAfterPay1"></div>

<!-- The result of the payment will be passed to the function specified in the payment block functionAfterPay1.
After payment, you can send the user to the backend to check the payment and save the result to the database. -->
<script type="application/javascript">
    // The function starts after payment
    function functionAfterPay1(dataPayResult) {
        if (dataPayResult.error == 0) { console.log('Frontend payment successful.'); }
        // redirect to backend check
        if (dataPayResult.data.tx) { window.location.href = '<?= $data['checkPaymentPageUrl'] ?><?= (strpos($data['checkPaymentPageUrl'], '?') !== false)?'&':'?' ?>chainid=' + dataPayResult.data.PaymentSettingsChain.chainData.chainId + '&tx=' + dataPayResult.data.tx+'&lang='+dataPayResult.data.lang; }
    }
</script>