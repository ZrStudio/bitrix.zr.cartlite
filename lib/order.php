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
    Bitrix\Main\ORM\Fields\FloatField,
    Bitrix\Main\ORM\Fields\ArrayField,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class OrderTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'zr_cart_lite_order';
    }

    public static function getMap()
    {
        return array(
            'ID' => new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ZR_CART_LITE_ID'),
            )),
            'FUSER_ID' => new IntegerField('FUSER_ID'),
            (new Reference(
                'FUSER',
                \ZrStudio\CartLite\FCUserTable::class,
                Join::on('this.FUSER_ID', 'ref.ID')
            ))->configureJoinType('left'),
            'DATE_CREATE' => new DatetimeField('DATE_CREATE', array(
                'default_value' => function() { return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('ZR_CART_LITE_DATE_CREATE'),
			)),
            'TIMESTAMP_X' => new DatetimeField('TIMESTAMP_X', array(
                'default_value' => function() { return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('ZR_CART_LITE_TIMESTAP_X'),
			)),
            'TOTAL_COST' => new FloatField('TOTAL_COST', array(
                'title' => Loc::getMessage('ZR_CART_LITE_TOTAL_COST'),
            )),
            'PRODUCTS' => (new ArrayField('PRODUCTS', array(
                'title' => Loc::getMessage('ZR_CART_LITE_PRODUCTS'),
            )))->configureSerializationPhp(),
            'USER_FIELDS' => (new ArrayField('USER_FIELDS', array(
                'title' => Loc::getMessage('ZR_CART_LITE_USER_FIELDS'),
            )))->configureSerializationPhp()
        );
    }
}