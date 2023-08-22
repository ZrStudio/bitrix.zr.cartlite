<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("zr.cartlite")) {
	echo "failure";
	return;
}

try 
{
    $fuser = ZrStudio\CartLite\FUser::getInstance();
    $curBasket = $fuser->getFUserBasket();
    
    $arData = [
        'BASKET_ID' => $curBasket->getCartId(),
        'TOTAL_COST' => $curBasket->getTotalCost(),
        'ITEM_COUNT' => $curBasket->getProductsCount(),
        'ITEM_QUANTITY' => $curBasket->getProductsQuantity(),
    ];

    if ($_REQUEST['mode'] = 'add_js_products')
    {
        $arData['JS_ITEMS'] = [];
        $arData['ITEMS'] = $curBasket->getProductsObjects();

        if (!empty($arData['ITEMS']))
        {
            $arJsProducts = [];
            foreach($arData['ITEMS'] as $product)
            {
                /** @var \ZrStudio\CartLite\CartElement $_product */
                $_product = $product;
                $arJsProducts[] = $_product->getProductJs(['delete']);
            }
            $arData['JS_ITEMS'] = $arJsProducts;
        }
    }
    else
    {
        $arData['ITEMS'] = $curBasket->getProducts();
    }
    
    $arResult = ['STATUS' => 'OK', 'DATA' => $arData];
    echo json_encode($arResult);
    die();
} 
catch (Exception $e) 
{
    $arResult = ['STATUS' => 'ERROR', 'MESSAGE' => $e->getMessage()];
    echo json_encode($arResult);
    die();
}
