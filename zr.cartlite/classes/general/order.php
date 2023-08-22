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

    protected function generateProductList($arOrder)
    {
        $strOrderList = "";
        foreach($arOrder['PRODUCTS'] as $arProduct)
        {
            $strOrderList .= $arProduct["NAME"]." (".$arProduct["QUANTITY"].")";
            $strOrderList .= "\n";
        }

        foreach(GetModuleEvents("zr.cartlite", "OnOrderEmailEventCreateTotalList", true) as $arEvent)
        {
            ExecuteModuleEventEx($arEvent, array($arOrder["ID"], &$eventName, &$strOrderList, $arOrder));
        }

        return $strOrderList;
    }

    private function sendEmailEvent($arOrderData)
    {
        $arCFileds = [
            "ORDER_ID" => $arOrderData['ID'],
            "ORDER_DATE" => $arOrderData['DATE_CREATE'],
            "ORDER_USER" => $arOrderData['FUSER_ID'],
            "PRICE" => $arOrderData['TOTAL_COST'],
            "ORDER_LIST" => $this->generateProductList($arOrderData)
        ];

        if (!empty($arOrderData['USER_FIELDS']))
        {
            $arUserFields = [];
            foreach($arOrderData['USER_FIELDS'] as $code => $value)
            {
                if ($code == 'USER_COMMENT')
                {
                    $arUserFields['COMMENT'] = $value;
                    continue;
                }
                $arUserFields[$code] = $value;
            }
            $arCFileds = array_merge($arCFileds, $arUserFields);
        }

        $bSend = true;
        foreach(GetModuleEvents("sale", "OnOrderEmailEventBeforeSend", true) as $arEvent)
        {
            if (ExecuteModuleEventEx($arEvent, Array($arOrderData["ID"], &$eventName, &$arCFileds))===false)
            {
                $bSend = false;
            }
        }

        if ($bSend)
        {
            $res = \Bitrix\Main\Mail\Event::send([
                "EVENT_NAME" => "ZR_CL_NEW_ORDER",
                "LID" => "s1",
                "C_FIELDS" => $arCFileds,
            ]);

            if ($res->isSuccess())
            {
                return true;
            }
            return false;
        }
        return false;
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
            $arOrderData = OrderTable::getById($res->getId())->fetch();
            $this->sendEmailEvent($arOrderData);
            $basket->clear();
        }

        return $res;
    }

}