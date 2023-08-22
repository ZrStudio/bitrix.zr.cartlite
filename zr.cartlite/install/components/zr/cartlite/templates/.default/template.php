<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */
$this->setFrameMode(true);
$mainSid = $this->randString(6);
$areaId = [
    'CART' => 'cart_container_'.$mainSid,
    'CART_TABLE' => 'cart_table_'.$mainSid,
    'CART_TOTAL_BLOCK' => 'cart_total_block_'.$mainSid,
    'CART_TOTAL_PRICE' => 'cart_total_price_'.$mainSid,
    'CART_CREATE_ORDER' => 'cart_btn_create_order_'.$mainSid,
    'ORDER_FORM' => 'order_form_'.$mainSid,
    'ORDER_FORM_SAVE' => 'order_form_btn_save_'.$mainSid,
];

$this->addExternalCss($this->GetFolder().'/lib/grid.js/grid.min.css');
$this->addExternalJs($this->GetFolder().'/lib/grid.js/grid.min.js');

$this->addExternalCss($this->GetFolder().'/lib/micromodal/micromodal.min.css');
$this->addExternalJs($this->GetFolder().'/lib/micromodal/micromodal.min.js');

$this->addExternalJs($this->GetFolder().'/lib/phoneinput/phoneinput.js');

$showTotalBlockTop = $arParams['TYPE_SHOW_TOTAL_CONTAINER'] == 'TOP';
$showTotalBlockBottom = $arParams['TYPE_SHOW_TOTAL_CONTAINER'] == 'BOTTOM';

$orderId = $arResult['ORDER_ID'] ?: false;
?>

<? if ($orderId && $orderId > 0): ?>
    <? include ('success.php'); ?>
<? elseif (!empty($arResult['ITEMS'])): ?>
    <? if ($showTotalBlockTop):?>
        <? include ('part/checkout-block.php'); ?>
    <? endif; ?>

    <div id="<?=$areaId['CART']?>">
        <div id="<?=$areaId['CART_TABLE']?>"></div>
    </div>

    <? if ($showTotalBlockBottom):?>
        <? include ('part/checkout-block.php'); ?>
    <? endif; ?>

    <? include ('part/order-modal.php'); ?>

    <script>
        <? $jsConfig = ['IDS' => $areaId,'PRODUCTS' => $arResult['JS_PRODUCT'],'AJAX_ORDER_URL'=>$componentPath.'/action.php']; ?>
        let obCartLite<?=$mainSid?> = new JCZrCartLite(<?=CUtil::PhpToJSObject($jsConfig)?>);
    </script>
<? elseif ($orderId === 0): ?>
    <? include ('not_found_order.php'); ?>
<? else: ?>
    <? include ('empty.php'); ?>
<? endif; ?>