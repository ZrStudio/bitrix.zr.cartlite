<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use \ZrStudio\CartLite\CartElement;

$arResult['JS_PRODUCT'] = [];

if (!empty($arResult['ITEMS']))
{
    foreach($arResult['ITEMS'] as $product)
    {
        /** @var \ZrStudio\CartLite\CartElement $_product */
        $_product = $product;

        $arResult['JS_PRODUCT'][] = $_product->getProductJs(['delete']);
    }
}