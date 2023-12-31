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
                \ZrStudio\CartLite\FCUserTable::class,
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

        $rsBaskets = self::getList(array(
            'select' => array('*'),
            'filter' => $filter,
            'limit' => 10
        ));

        if ($rsBaskets->getSelectedRowsCount() > 0)
        {
            return $rsBaskets;
        }
        else 
        {
            return self::createNewBasketByFUserId($fuserId, []);
        }
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

    public static function addProduct($rowId, $arProduct, $setQunatity = false): array
    {
        $arRow = self::getById($rowId)->fetch();
        if (!empty($arRow) && is_array($arRow))
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
                    if ($setQunatity)
                    {
                        $arProductsUpdate[$arProduct['PRODUCT_ID']]['QUANTITY'] = $arProduct['QUANTITY'];
                    }
                    else
                    {
                        $arProductsUpdate[$arProduct['PRODUCT_ID']]['QUANTITY'] += $arProduct['QUANTITY'];
                        if ($arProductsUpdate[$arProduct['PRODUCT_ID']]['QUANTITY'] <= 0)
                        {
                            unset($arProductsUpdate[$arProduct['PRODUCT_ID']]);
                        }
                    }
                }
                else
                {
                    $arProductsUpdate[$arProduct['PRODUCT_ID']] = $arProduct;
                }
            }
            return self::_setProducts($rowId, $arProductsUpdate, $arRow['PRODUCTS']);
        }
        return [];
    }

    public static function deleteProduct($rowId, $productId): array
    {
        $arRow = self::getById($rowId)->fetch();
        if (!empty($arRow) && is_array($arRow))
        {  
            $arProductsUpdate = $arRow['PRODUCTS'];

            if (in_array($productId, array_keys($arProductsUpdate)))
            {
                unset($arProductsUpdate[$productId]);
            }
            
            return self::_setProducts($rowId, $arProductsUpdate, $arRow['PRODUCTS']);
        }

        return [];
    }

    private static function _setProducts($rowId, $arProducts, $arOldProducts = [])
    {
        $res = self::update($rowId, [
            'TIMESTAMP_X' => new Main\Type\DateTime(),
            'PRODUCTS' => $arProducts
        ]);

        if ($res->isSuccess())
        {
            $arRowUpdated = self::getById($rowId)->fetch();
            if (!empty($arRowUpdated) && is_array($arRowUpdated))
            {
                return $arRowUpdated['PRODUCTS'];
            }
        }
        else
        {
            return $arOldProducts;
        }
    }

    /**
     * Clear user cart
     */
    public static function clearCart($rowId)
    {
        return self::_setProducts($rowId, []);
    }
}