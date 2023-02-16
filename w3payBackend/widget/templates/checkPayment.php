<?php if(empty($data)){ exit; } // check data ?>
<?php $L = $data['LanguageBlock']['L']; ?>

<div class="checkPaymentBlock">
<?php echo $data['LanguageBlock']['html']; ?>
<div class="h1Title"><?= $L->sL('W3PAY Server-side payment verification') ?></div>
<?php
if($data['showSuccess']){
    include __DIR__.'/checkPayment/success.php';
}
if($data['showError']){
    include __DIR__.'/checkPayment/error.php';
}
if(!empty($data['showForm'])){
    include __DIR__.'/checkPayment/form.php';
}
?>
</div>