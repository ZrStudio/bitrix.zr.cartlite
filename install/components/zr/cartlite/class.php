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
    public function onIncludeComponentLang()
    {
        Loc::loadLanguageFile(__FILE__);
    }

    public function loadBasketData() 
    {
        $fuser = $this->getFuser();
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
        $this->includeComponentTemplate();
	}
}