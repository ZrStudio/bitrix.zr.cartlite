<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */
use Bitrix\Main\Loader;

$module_id = 'zr.cartlite';
$siteLid = SITE_ID == 'ru' ? 's1' : SITE_ID;

$isActiveModule = \Bitrix\Main\Config\Option::get($module_id, 'module_active_'. $siteLid, '', $siteLid) == 'Y';

if ($isActiveModule)
{
    Loader::registerAutoLoadClasses(
        $module_id,
        [
            'ZrStudio\CartLite\FCUserTable' => 'lib/fcuser.php',
            'ZrStudio\CartLite\FCartTable' => 'lib/fcart.php',
            'ZrStudio\CartLite\OrderTable' => 'lib/order.php',
            'ZrStudio\CartLite\FCart' => 'classes/general/fcart.php',
            'ZrStudio\CartLite\Order' => 'classes/general/order.php',
            'ZrStudio\CartLite\FUser' => 'classes/general/fuser.php',
            'ZrStudio\CartLite\CartElement' => 'classes/general/cartelement.php',
        ]
    );
}
