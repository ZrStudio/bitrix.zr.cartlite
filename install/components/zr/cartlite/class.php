<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

use Bitrix\Main\Localization\Loc,
    ZrStudio\CartLite\FUser,
    ZrStudio\CartLite\FCart;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CartLite extends \CBitrixComponent 
{
    private FUser $fuser;

    public function onIncludeComponentLang()
    {
        Loc::loadLanguageFile(__FILE__);
    }

    public function loadBasketData() 
    {
        $fuser = $this->getFuser();
        $this->fuser = $fuser;
        
        $basket = $this->getBasketFUser($fuser);
        if (!is_a($basket, 'ZrStudio\CartLite\FCart')) return;

        $this->arResult['ITEMS'] = $basket->getProductsObjects();
        $this->arResult['TOTAL_PRICE'] = $basket->getTotalCost();
        $this->arResult['ITEMS_COUNT'] = $basket->getProductsCount();
    }

    /**
     * Get fuser basket object
     * @param ZrStudio\CartLite\FUser $fuser object current user
     *
     * @return ZrStudio\CartLite\FUser
     */
    private function getBasketFUser($fuser): ZrStudio\CartLite\FCart
    {
        return $fuser->getFUserBasket();
    }

    private function getOrderId()
    {
        $orderId = $_REQUEST['ORDER_ID'] ? intval($_REQUEST['ORDER_ID']) : false;

        if ($orderId > 0)
        {
            $this->arResult['ORDER_ID'] = $orderId;
        }
    }

    private function checkPremissionUserOrder()
    {
        if ($this->arResult['ORDER_ID'] > 0)
        {
            $isAllowed = $this->fuser->checkPemissionByOrder($this->arResult['ORDER_ID']);

            if (!$isAllowed)
            {
                $this->arResult['ORDER_ID'] = 0;
            }
        }
    }
    
    /**
     * Get f user object
     *
     * @return ZrStudio\CartLite\FUser
     */
    private function getFuser(): ZrStudio\CartLite\FUser
    {
        return FUser::getInstance();
    }

    public function executeComponent()
	{
        $this->loadBasketData();
        $this->getOrderId();
        $this->checkPremissionUserOrder();
        $this->includeComponentTemplate();
	}
}