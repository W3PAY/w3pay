<?php
if(empty($data)){ exit; } // check data

$PaymentSettings = $data['PaymentSettings'];
$chainid = $data['chainid'];
$tx = $data['tx'];
?>
<form method="get" class="W3payForm" action="<?= $_SERVER['REQUEST_URI'] ?>">
    <?php if(!empty($_GET)){ $getArr = $_GET;
        foreach ($getArr as $getName => $getVal){ if(is_string($getVal)){ echo '<input type="hidden" name="'.$getName.'" value="'.$getVal.'">'; } }
    } ?>
    <div class="W3payBlockRow">
        <label class="W3payBlockLabel"><?= $L->sL('Chain name') ?></label>
        <select class="W3payBlockSelect" name="chainid">
            <?php
            if (is_array($PaymentSettings['chainsData'])) {
                foreach ($PaymentSettings['chainsData'] as $chainsData) {
                    if(!empty($chainsData['chainData']['status'])){
                        $selected = '';
                        if (isset($chainid) && $chainid == $chainsData['chainData']['chainId']) {
                            $selected = 'selected';
                        }
                        ?>
                        <option <?= $selected ?> value="<?= $chainsData['chainData']['chainId'] ?>"><?= $chainsData['chainData']['chainName'] ?></option><?
                    }
                }
            }
            ?>
        </select>
    </div>
    <div class="W3payBlockRow">
        <label class="W3payBlockLabel"><?= $L->sL('Transaction Hash') ?></label>
        <input autocomplete="off" name="tx" placeholder="0x..........." type="text" class="W3payBlockInput" required="required" value="<?= !empty($tx)?$tx:'' ?>">
    </div>
    <div class="W3payBlockRow">
        <button type="submit" class="W3payBlockBtn w3payGreenBtn"><?= $L->sL('Check') ?></button>
    </div>
</form>