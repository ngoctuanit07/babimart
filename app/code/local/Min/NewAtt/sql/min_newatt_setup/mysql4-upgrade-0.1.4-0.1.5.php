<?php
$installer = $this;

$installer->startSetup();

$content = <<<EOD
    <li style="float:left;margin: 5px;">
        <span class="input-box">
            <img class="bank-img" id="bank-bidv" src="{{media url='bank/bidv.jpg}}" width="100px" height="50px"/>
        </span>
    </li>
    <li style="float:left;margin: 5px;">
        <span class="input-box">
            <img class="bank-img" id="bank-viettin" src="{{media url='bank/viettinbank.jpg}}" width="100px" height="50px"/>
        </span>
    </li>
    <li style="float:left;margin: 5px;">
        <span class="input-box">
            <img class="bank-img" id="bank-vietcom" src="{{media url='bank/vietcombank.jpg}}" width="100px" height="50px"/>
        </span>
    </li>
    <li style="float:left;margin: 5px;">
        <span class="input-box">
            <img class="bank-img" id="bank-techcom" src="{{media url='bank/techcombank.jpg}}" width="100px" height="50px"/>
        </span>
    </li>
    <li style="float:left;margin: 5px;">
        <span class="input-box">
            <img class="bank-img" id="bank-agri" src="{{media url='bank/agribank.jpg}}" width="100px" height="50px"/>
        </span>
    </li>
    <li>
        <textarea hidden="hidden" class="input-text required-entry" id="pay_bank_info" name="payment[bank_info]">
        </textarea>
    </li>
    <li>
        <div class="bank-info bank-bidv" style="display: none">
            <p>Ngân hàng: BIDV</p>
            <p>Chủ tài khoản:Pham Van A </p>
            <p>Số tài khoản:0123445689 </p>
        </div>
        <div class="bank-info bank-viettin" style="display: none">
            <p>Ngân hàng: Viettinbank</p>
            <p>Chủ tài khoản: </p>
            <p>Số tài khoản: </p>
        </div>
        <div class="bank-info bank-vietcom" style="display: none">
            <p>Ngân hàng: Vietcombank</p>
            <p>Chủ tài khoản: </p>
            <p>Số tài khoản: </p>
        </div>
        <div class="bank-info bank-techcom" style="display: none">
            <p>Ngân hàng: Techcombank</p>
            <p>Chủ tài khoản: </p>
            <p>Số tài khoản: </p>
        </div>
        <div class="bank-info bank-agri" style="display: none">
            <p>Ngân hàng: Agribank</p>
            <p>Chủ tài khoản: </p>
            <p>Số tài khoản: </p>
        </div>
    </li>
EOD;

$staticBlock = array(
    'title' => 'Phương thức thanh toán',
    'identifier' => 'min_pay',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);
$block = Mage::getModel('cms/block')->load('min_pay');
if (!$block->getId()) {
    Mage::getModel('cms/block')->setData($staticBlock)->save();
} else {
    $block->setContent($content)->save();
}

$installer->endSetup();