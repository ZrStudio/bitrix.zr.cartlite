<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

namespace ZrStudio\CartLite;

use Bitrix\Main,
    Bitrix\Sale\Internals,
    Bitrix\Main\Localization\Loc,
    ZrStudio\CartLite\FCartTable,
    ZrStudio\CartLite\CartElement;

Loc::loadMessages(__FILE__);

class FCart 
{
    private int $basketId;
    private int $fuserId;
    private array $products;

    public function __construct($basketId, $fuserId, $products)
    {
        $this->basketId = $basketId;
        $this->fuserId = $fuserId;
        $this->products = $products;
    }

    public static function getUserBasketByFUserId($fuserId, $basketId = 0)
    {
        $ID = $fuserId > 0 ? intval($fuserId): 0;
        $basketId = intval($basketId) ?: 0;
        if ($ID <= 0) return null;
    
        $cartUserCollection = FCartTable::getBasketByFUserId($fuserId)->fetchAll();
        if (count($cartUserCollection) > 0)
        {
            $baskets = self::_initInstanceByCartResult($cartUserCollection);
            if ($basketId > 0)
            {
                return $baskets[$basketId];
            }
            return $baskets[array_key_first($baskets)];
        }
        else
        {
            $arNewBasketCollection = FCartTable::createNewBasketByFUserId($fuserId)->fetchAll();
            $baskets = self::_initInstanceByCartResult($arNewBasketCollection);
            if ($basketId > 0)
            {
                return $baskets[$basketId];
            }
            return $baskets[array_key_first($baskets)];
        }
    }

    private static function _initInstanceByCartResult($cartUserCollection)
    {
        $arBasketInstance = [];
        foreach ($cartUserCollection as $arBasketObject) 
        {
            $arBasketInstance[] = new self(
                $arBasketObject['ID'],
                $arBasketObject['USER_ID'],
                $arBasketObject['PRODUCTS'],
            );
        }

        return $arBasketInstance;
    }

    private function _createResult($arData, $arErrors = [])
    {
        $res = new \Bitrix\Main\Result();
        $res->setData($arData);
        if (!empty($arErrors))
        {
            foreach($arErrors AS $errorStr)
            {
                $res->addError(new \Bitrix\Main\Error($errorStr));
            }
        }
        return $res;
    }

    public function add($arProducts)
    {
        $product = new CartElement($arProducts);

        if ($product->isValid())
        {
            $products = FCartTable::addProduct(
                $this->basketId,
                $product->toArray()
            );
            $this->products = $products;
        }

        return $this->_createResult($this->products, $product->getErrors());
    }

}