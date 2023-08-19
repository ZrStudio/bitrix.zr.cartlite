<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

namespace ZrStudio\CartLite;

use Bitrix\Main,
	Bitrix\Main\Entity,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\ArrayField,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class FCartTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'zr_cart_lite_cart';
    }

    public static function getMap()
    {
        return array(
            'ID' => new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ZR_CART_LITE_ID'),
            )),
            'USER_ID' => new IntegerField('USER_ID'),
            (new Reference(
                'USER',
                \ZrStudio\StopList\FCUserTable::class,
                Join::on('this.USER_ID', 'ref.ID')
            ))->configureJoinType('left'),
            'DATE_CREATE' => new DatetimeField('DATE_CREATE', array(
                'default_value' => function() { return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('ZR_CART_LITE_DATE_CREATE'),
			)),
            'TIMESTAMP_X' => new DatetimeField('TIMESTAMP_X', array(
                'default_value' => function() { return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('ZR_CART_LITE_TIMESTAP_X'),
			)),
            'PRODUCTS' => (new ArrayField('PRODUCTS', array(
                'title' => Loc::getMessage('ZR_CART_LITE_PRODUCTS'),
            )))->configureSerializationPhp()
        );
    }

    public static function getBasketByFUserId($fuserId, $basketId = null)
    {
        $filter = array('USER_ID' => $fuserId);
        if ($basketId != null)
        {
            $filter['ID'] = $basketId;
        }
        return self::getList(array(
            'select' => array('*'),
            'filter' => $filter,
            'limit' => 10
        ));
    }

    public static function createNewBasketByFUserId($fuserId, $products = [])
    {
        $res = self::add(array(
            'USER_ID' => $fuserId,
            'PRODUCTS' => $products
        ));

        if ($res->isSuccess())
        {
            return self::getBasketByFUserId($fuserId, $res->getId());
        }

        return [];
    }

    public static function addProduct($rowId, $arProduct)
    {
        $arRow = self::getById($rowId)->fetch();
        if (!empty($arRow))
        {  
            $arProductsUpdate = [];
            if (empty($arRow['PRODUCTS']))
            {
                $arProductsUpdate[$arProduct['PRODUCT_ID']] = $arProduct;
            }
            else
            {
                $arProductsUpdate = $arRow['PRODUCTS'];
                if (in_array($arProduct['PRODUCT_ID'], array_keys($arRow['PRODUCTS'])))
                {
                    $arProductsUpdate[$arProduct['PRODUCT_ID']]['QUANTITY'] += $arProduct['QUANTITY'];

                    if ($arProductsUpdate[$arProduct['PRODUCT_ID']]['QUANTITY'] <= 0)
                    {
                        unset($arProductsUpdate[$arProduct['PRODUCT_ID']]);
                    }
                }
                else
                {
                    $arProductsUpdate[$arProduct['PRODUCT_ID']] = $arProduct;
                }
            }
            
            $res = self::update($rowId, [
                'TIMESTAMP_X' => new Main\Type\DateTime(),
                'PRODUCTS' => $arProductsUpdate
            ]);

            if ($res->isSuccess())
            {
                $arRowUpdated = self::getById($rowId)->fetch();
                if (!empty($arRowUpdated))
                {
                    return $arRowUpdated['PRODUCTS'];
                }
            }
            else
            {
                return $arRow['PRODUCTS'];
            }
        }
        else
        {
            return [];
        }
    }
}