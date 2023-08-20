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
];

$this->addExternalCss($this->GetFolder().'/lib/grid.js/grid.min.css');
$this->addExternalJs($this->GetFolder().'/lib/grid.js/grid.min.js');

$showTotalBlockTop = $arParams['TYPE_SHOW_TOTAL_CONTAINER'] == 'TOP';
$showTotalBlockBottom = $arParams['TYPE_SHOW_TOTAL_CONTAINER'] == 'BOTTOM';
?>

<? if (!empty($arResult['ITEMS'])): ?>
    <? if ($showTotalBlockTop):?>
        <? include ('part/checkout-block.php'); ?>
    <? endif; ?>

    <div id="<?=$areaId['CART']?>">
        <div id="<?=$areaId['CART_TABLE']?>"></div>
    </div>

    <? if ($showTotalBlockBottom):?>
        <? include ('part/checkout-block.php'); ?>
    <? endif; ?>

    <script>
        <? $jsConfig = ['IDS' => $areaId,'PRODUCTS' => $arResult['JS_PRODUCT']]; ?>
        let obCartLite<?=$mainSid?> = new JCZrCartLite(<?=CUtil::PhpToJSObject($jsConfig)?>);
    </script>
<? else: ?>
    <? include ('empty.php'); ?>
<? endif; ?>