<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

namespace ZrStudio\CartLite;

use Bitrix\Main\Localization\Loc,
    ZrStudio\CartLite\OrderTable,
    Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class Order
{
    /** @var int $fuser id from \ZrStudio\CartLite\FUser */
    private int $fuserId = 0;

    /** @var int $cartId id from \ZrStudio\CartLite\FCart */
    private int $cartId = 0;

    /** @var int $arUserFileds fields foe send email event */
    private array $arUserFileds = [];

    public function __construct($fuserId = 0, $cartId = 0, $arUserFileds = [])
    {
        $this->fuserId = $fuserId;
        $this->cartId = $cartId;
        $this->arUserFileds = $arUserFileds;
    }

    public static function getFUserIdByOrderId($orderId): int
    {
        $arRowOrder = OrderTable::getById($orderId)->fetch();

        if (!empty($arRowOrder) && is_array($arRowOrder))
        {
            return $arRowOrder['FUSER_ID'];
        }
        else
        {
            return -1;
        }
    }

    /**
     * Set fuserId filed. Object \ZrStudio\CartLite\FUser
     * 
     * @param int $fuserId - fuserid
     * 
     * @return void
     */
    public function setFuserId(int $fuserId): void
    {
        $this->fuserId = $fuserId;
    }

    /**
     * Set cartId filed. Object \ZrStudio\CartLite\FCart
     * 
     * @param int $cartdId - cartId
     * 
     * @return void
     */
    public function setCartId(int $cartdId): void
    {
        $this->cartId = $cartdId;
    }

    /**
     * Set arUserFileds filed. Array with fields how send by email event
     * 
     * @param array $arUserFileds - user fileds
     * 
     * @return void
     */
    public function setUserFileds(array $arUserFileds): void
    {
        $this->arUserFileds = $arUserFileds;
    }

    private function sendEmailEvent()
    {

    }

    public function save(): \Bitrix\Main\ORM\Data\AddResult
    {
        $basket = FCart::getUserBasketByFUserId($this->fuserId, $this->cartId);

        if (!is_a($basket,'\ZrStudio\CartLite\FCart'))
        {
            throw new SystemException('User cart with `'.$this->cartId.'` id not found');
        }

        $basketItems = $basket->getProducts(true);
        if (count($basketItems) == 0)
        {
            $res =  new \Bitrix\Main\ORM\Data\AddResult();
            $res->addError(new \Bitrix\Main\Error('User cart is empty', '501'));
            return $res;
        }
       
        $res = OrderTable::add([
            'fields' => [
                'FUSER_ID' => $this->fuserId,
                'TOTAL_COST' => $basket->getTotalCost(),
                'PRODUCTS' => $basketItems,
                'USER_FIELDS' => $this->arUserFileds
            ]
        ]);

        if ($res->isSuccess())
        {
            $this->sendEmailEvent();
            $basket->clear();
        }

        return $res;
    }

}