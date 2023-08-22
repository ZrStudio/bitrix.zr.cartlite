<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

use Bitrix\Main\Localization\Loc,
    ZrStudio\CartLite\FUser;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class ShortCart extends \CBitrixComponent 
{
    public function onPrepareComponentParams($params): array
	{
		return parent::onPrepareComponentParams($params);
	}

    public function onIncludeComponentLang()
    {
        Loc::loadLanguageFile(__FILE__);
    }

    /**
     * Get cart data by products params
     * 
     * @return array data cart
     */
    private function getCartShortData(ZrStudio\CartLite\FCart $basket): array
    {
        $basket->calc();

        return [
            'BASKET_ID' => $basket->getCartId(),
            'TOTAL_COST' => $basket->getTotalCost(),
            'ITEM_COUNT' => $basket->getProductsCount(),
            'ITEM_QUANTITY' => $basket->getProductsQuantity(),
            'ITEMS' => $basket->getProducts()
        ];
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
        $fuser = $this->getFuser();
        $basket = $this->getBasketFUser($fuser);
        $this->arResult['BASKET_DATA'] = $this->getCartShortData($basket);
        $this->includeComponentTemplate();
	}
}