<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arTypeQuantityAllowed = ['PC', 'PCQ'];

$typeShowQuntity = $arParams['TYPE_SHOW_QUANTITY'];

if (!in_array($typeShowQuntity, $arTypeQuantityAllowed))
{
    $typeShowQuntity = $arTypeQuantityAllowed[0];
}

$arResult['TYPE_SHOW_QUANTITY'] = $typeShowQuntity;
switch($typeShowQuntity)
{
    case 'PC':
        $arResult['QUANTITY'] = $arResult['BASKET_DATA']['ITEM_COUNT'];
        break;
    case 'PCQ':
        $arResult['QUANTITY'] = $arResult['BASKET_DATA']['ITEM_QUANTITY'];
        break;
} 