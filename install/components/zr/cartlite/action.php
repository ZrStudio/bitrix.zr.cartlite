<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define('NOT_CHECK_PERMISSIONS', true);

use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');

Loader::includeModule('zr.cartlite');

CUtil::JSPostUnescape();

$result = array(
	'MESSAGE' => '',
	'DATA' => array()
);

$arUserParams = [];
if ($_REQUEST['USER_ORDER_PARAMS'] <> '')
{
    $arUserParams = $_REQUEST['USER_ORDER_PARAMS'];
}

$location = '';
if ($_REQUEST['LOCATION'] <> '')
{
    $location = $_REQUEST['LOCATION'];
}

$fuser = ZrStudio\CartLite\FUser::getInstance();
$basket = $fuser->getFUserBasket();


if (is_a($basket, '\ZrStudio\CartLite\FCart'))
{
    $order = new ZrStudio\CartLite\Order($fuser->getFUserId(), $basket->getCartId(), $arUserParams);
    $res = $order->save();

    if ($res->isSuccess())
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $uri = new \Bitrix\Main\Web\Uri($location);

        $uri->deleteParams(\Bitrix\Main\HttpRequest::getSystemParameters());
        $uri->addParams(array("ORDER_ID"=>$res->getId()));

        $result['MESSAGE'] = '';
        $result['DATA'] = [
            'ORDER_ID' => $res->getId(),
            'REDIRECT' => $uri->getUri()
        ];
    }
    else
    {
        $result['MESSAGE'] = $res->getErrorMessages();
    }
}
else
{
    $result['MESSAGE'] = 'User cart not load';
}

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

print(CUtil::PhpToJSObject(array(
	'STATUS' => empty($result['MESSAGE']) ? 'OK' : 'ERROR',
	'MESSAGE' => $result['MESSAGE'],
	'DATA' => $result['DATA']
), false, false, true));