<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

namespace ZrStudio\CartLite;

use Bitrix\Main\Localization\Loc,
    ZrStudio\CartLite\FCartTable,
    ZrStudio\CartLite\CartElement,
    Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class FCart 
{
    private int $cartId;
    private int $fuserId;
    private array $products;
    private int $itemsCount = 0;
    private int $itemsQuantity = 0;
    private float $totalPrice = 0;
    private array $productsIds = [];

    public function __construct($cartId, $fuserId, $products)
    {
        $this->cartId = $cartId;
        $this->fuserId = $fuserId;
        $this->products = $products;
    }

    public static function getUserBasketByFUserId($fuserId, $cartId = 0)
    {
        $ID = $fuserId > 0 ? intval($fuserId): 0;
        $cartId = intval($cartId) ?: 0;
        if ($ID <= 0) return null;
    
        $arFUserBaskets = FCartTable::getBasketByFUserId($fuserId)->fetchAll();
        $baskets = self::_initInstanceByCartResult($arFUserBaskets);
        return self::_getSelectedBasket($baskets, $cartId);
    }

    private static function _getSelectedBasket($baskets, $basketId)
    {
        $basketSelectId = array_key_first($baskets);
        if ($basketId > 0)
        {
            $basketSelectId = $basketId;
        }
        
        // todo: multi cart
        $basket = $baskets[$basketSelectId];
        $basket->calc();
        return $basket;
    }

    private static function _initInstanceByCartResult($cartUserCollection)
    {
        $arBasketInstance = [];
        foreach ($cartUserCollection as $arBasketObject) 
        {
            $arBasketInstance[$arBasketObject['ID']] = new self(
                $arBasketObject['ID'],
                $arBasketObject['USER_ID'],
                $arBasketObject['PRODUCTS'],
            );
        }

        return $arBasketInstance;
    }

    private function _createResult($arData, $arErrors = []): \Bitrix\Main\Result
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


    /**
     *  Update cart parameters.
     * 
     * @return void
     */
    private function _updateCartData(bool $getActualData = true)
    {
        if ($getActualData)
        {
            $this->_getActualCart();
        }

        $itemsCount = 0;
        $itemsQuantity = 0;
        $totalPrice = 0;

        foreach($this->products as $arProduct)
        {
            $product = new CartElement($arProduct);

            $itemsCount += 1;
            $itemsQuantity += $product->getQuantity();
            $totalPrice += $product->getProductTotalCost();
        }

        $this->itemsCount = $itemsCount;
        $this->itemsQuantity = $itemsQuantity;
        $this->totalPrice = $totalPrice;
    }

    /**
     *  Get actual cart data. Update product files
     * 
     * @return bool is success get or raise error
     * @throws \Bitrix\Main\SystemException if the user is not found
     */
    private function _getActualCart(): bool
    {
        $arCart = FCartTable::getById($this->cartId)->fetch();

        if (!empty($arCart))
        {
            $this->products = $arCart['PRODUCTS'];
            return true;
        }

        throw new SystemException('Cart with `'.$this->cartId.'` id not found');
    }

    protected function _getProductOnlyOrderFields(): array
    {
        $arProducts = [];
        foreach($this->products as $arProduct)
        {
            $product = new CartElement($arProduct);

            $arProducts[$product->getId()] = [
                'ID' => $product->getId(),
                'NAME' => $product->getName(),
                'PRICE' => $product->getPrice(),
                'QUANTITY' => $product->getQuantity()
            ];
        }
        return $arProducts;
    }

    /**
     * Add product to cart. Need send third params PRODUCT_ID, QUANTITY, PRICE.
     * PRICE - optional if you set in options get price from iblock prop product.
     * 
     * @param array $arProducts array with fields PRODUCT_ID, QUANTITY, PRICE
     * @param bool $setQunatity is need set quantity product
     * 
     * @return \Bitrix\Main\Result result have actual product basket
     */
    public function add($arProducts, $setQunatity = false): \Bitrix\Main\Result
    {
        $product = new CartElement($arProducts);

        if ($product->isValid())
        {
            $products = FCartTable::addProduct(
                $this->cartId,
                $product->toArray(),
                $setQunatity
            );
            $this->products = $products;
        }

        $this->_updateCartData(false);
        return $this->_createResult($this->products, $product->getErrors());
    }

    /**
     * Delete product from cart. Need send third params PRODUCT_ID.
     * 
     * @param int $productId product id
     * 
     * @return \Bitrix\Main\Result result have actual product basket
     */
    public function delete($productId): \Bitrix\Main\Result
    {
        $products = FCartTable::deleteProduct($this->cartId, $productId);
        $this->products = $products;

        $this->_updateCartData(false);
        return $this->_createResult($this->products);
    }

    /**
     * Get cart id
     * 
     * @return int cart id
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * Get total cost cart products
     * 
     * @return float total cost products in basket
     */
    public function getTotalCost()
    {
        return $this->totalPrice;
    }

    /**
     * Get count products in basket
     * 
     * @return int count products
     */
    public function getProductsCount()
    {
        return $this->itemsCount;
    }

    /**
     * Get count quantity products in basket
     * 
     * @return int quantity products
     */
    public function getProductsQuantity()
    {
        return $this->itemsQuantity;
    }

    /**
     * Get products array
     * 
     * @return array products
     */
    public function getProducts(bool $onlyOrderField = false)
    {
        if ($onlyOrderField)
        {
            return $this->_getProductOnlyOrderFields();
        }
        return $this->products;
    }

    public function getProductsObjects()
    {
        return array_map(fn($i) => new CartElement($i), $this->products);
    }

    /**
     *  Recalculate basket
     * 
     * @return FCart
     */
    public function calc()
    {
        $this->_updateCartData(true);
        return $this;
    }

    /**
     * Clear fuser basket. Delete only 
     */
    public function clear(): bool
    {
        $res = FCartTable::clearCart($this->cartId);
        if (is_array($res))
        {
            return count($res);
        }
        return false;
    }
}