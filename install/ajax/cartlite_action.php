<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("zr.cartlite") || !CModule::IncludeModule("iblock")) {
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

        if ($props && is_array($props))
        {
            $arProduct['PROPS'] = $props;
        }

        if ($price && is_float($price))
        {
            $arProduct['PRICE'] = $price;
        }

        $setQuantity = false;
        if ($_REQUEST["mode"] == 'set_quantity')
        {
            $setQuantity = true;
        }

        $res = $curBasket->add($arProduct, $setQuantity);

        if ($res->isSuccess())
        {
            $arResult['STATUS'] = 'OK';
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
        $res = $curBasket->delete($itemId);

        if ($res->isSuccess())
        {
            $arResult['STATUS'] = 'OK';
            $arResult['MESSAGE'] = 'Item with `'.$itemId.'` product - deleted from cart';
            $arResult['ERROR'] = '';
            echo json_encode($arResult);
            die();
        }
        else
        {
            $arResult['STATUS'] = 'ERROR';
            $arResult['MESSAGE'] = 'Item with `'.$itemId.'` product - not deleted from cart';
            $arResult['ERROR'] = $res->getErrors();
            echo json_encode($arResult);
            die();
        }
    }
}
else
{
    echo json_encode($arResult);
    die();
}