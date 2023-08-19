<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("zr.cartlite") || !CModule::IncludeModule("catalog") || !CModule::IncludeModule("iblock")) {
	echo "failure";
	return;
}

$arResult = ['STATUS' => 'NOTING', 'MESSAGE' => '', 'ERROR' => ''];

if (isset($_REQUEST["action"]))
{
    $itemId = $_REQUEST['item'];
    if ($itemId < 0)
    {
        $arResult['STATUS'] = 'ERROR';
        $arResult['MESSAGE'] = 'Not correct product id';
        $arResult['ERROR'] = ['PRODUCT ID NOT CORRECT'];
        echo json_encode($arResult);
        die();
    }

    $fuser = ZrStudio\CartLite\FUser::getInstance();
    $curBasket = $fuser->getFUserBasket();

    if ($_REQUEST["action"] == 'add_item')
    {
        $quantity = $_REQUEST["quantity"] ? intval($_REQUEST["quantity"]) : 1;
        $props = $_REQUEST["props"] ?: null;
        $price = floatval($_REQUEST["price"]) ?: false;

        $arProduct = [
            'PRODUCT_ID' => $itemId,
            'QUANTITY' => $quantity,
        ];

        if ($price && is_float($price))
        {
            $arProduct['PRICE'] = $price;
        }

        $res = $curBasket->add($arProduct);

        if ($res->isSuccess())
        {
            $arResult['STATUS'] = 'SUCCESS';
            $arResult['MESSAGE'] = 'Item with `'.$itemId.'` product id add cart';
            $arResult['ERROR'] = '';
            echo json_encode($arResult);
            die();
        }
        else
        {
            $arResult['STATUS'] = 'ERROR';
            $arResult['MESSAGE'] = 'Item with `'.$itemId.'` product not add the cart';
            $arResult['ERROR'] = $res->getErrors();
            echo json_encode($arResult);
            die();
        }
    } 
    elseif ($_REQUEST["action"] == 'delete_item')
    {

    }
}
else
{
    echo json_encode($arResult);
    die();
}