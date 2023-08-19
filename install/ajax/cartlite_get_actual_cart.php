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
        'ITEMS' => $curBasket->getProducts()
    ];
    
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
