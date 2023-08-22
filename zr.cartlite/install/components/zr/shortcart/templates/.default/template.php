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
    'CART' => 'cart_'.$mainSid,
    'CART_LINK' => 'cart_link_'.$mainSid,
    'QUANTITY' => 'cart_quantity_'.$mainSid,
];

$isShowCart = $arResult['QUANTITY'] > 0;
?>

<div class="cart fly<?=$isShowCart?'':' cart--hidden'?>" id="<?=$areaId['CART']?>">
    <a href="<?=$arParams['BASKET_URL']?>" class="cart__wrapper" id="<?=$areaId['CART_LINK']?>">
        <img class="image box" src="<?=$templateFolder?>/images/box.png">
        <div class="cart__quantity" id="<?=$areaId['QUANTITY']?>">
            <?=$arResult['QUANTITY']?>
        </div>
    </div>
    <script>
        <? $jsParams = ['IDS' => $areaId, 'TYPE_SHOW_QUANTITY' => $arResult['TYPE_SHOW_QUANTITY']]; ?>
        let obShortCart<?=$mainSid?> = new JCZrShortCart(<?=CUtil::PhpToJSObject($jsParams)?>);
    </script>
</div>
