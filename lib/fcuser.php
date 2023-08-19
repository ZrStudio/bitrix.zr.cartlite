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
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Application,
    Bitrix\Main\Web\Cookie,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class FCUserTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'zr_cart_lite_user';
    }

    public static function getMap()
    {
        return array(
            'ID' => new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ZR_CART_LITE_USER_ID'),
            )),
            'USER_ID' => new IntegerField('USER_ID'),
            (new Reference(
                'USER',
                \CAllUser::class,
                Join::on('this.USER_ID', 'ref.ID')
            ))->configureJoinType('left'),
            'CODE' => new StringField('CODE', array(
                'title' => Loc::getMessage('ZR_CART_LITE_USER_CODE'),
            )),
            'DATE_CREATE' => new DatetimeField('DATE_CREATE', array(
                'default_value' => function() { return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('ZR_CART_LITE_DATE_CREATE'),
			)),
            'TIMESTAMP_X' => new DatetimeField('TIMESTAMP_X', array(
                'default_value' => function() { return new Main\Type\DateTime(); },
				'title' => Loc::getMessage('ZR_CART_LITE_TIMESTAP_X'),
			)),
            'USER_TYPE' => new StringField('USER_TYPE', array(
				'required' => false,
				'title' => Loc::getMessage('ZR_CART_LITE_USER_TYPE'),
			)),
        );
    }

    private static function _getList($arFilter)
	{
		if (!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = $arFilter;

		return self::getList(array(
            "select" => array("ID", "CODE"),
            "filter" => $filter_keys,
            'order' => array('ID' => "DESC")
        ))->Fetch();
	}

    private static function getUniqCode()
    {
        return md5(time().\Bitrix\Main\Security\Random::getString(10));
    }

    public static function _add()
	{
		global $DB, $USER;

		$arFields = array(
            "DATE_CREATE" => new Main\Type\DateTime(),
			'TIMESTAMP_X' => new Main\Type\DateTime(),
            "ID" => (is_object($USER) && $USER->IsAuthorized() ? intval($USER->GetID()) : False),
            "CODE" => self::getUniqCode(),
        );

		$res = self::add($arFields);

        $ID = 0;
        if ($res->isSuccess())
        {
            $ID = intval($res->getId());
        }

		$cookie_name = \COption::GetOptionString("main", "cookie_name", "BITRIX_SM");

		self::setCookie($cookie_name . "_SALE_UID", $ID);

        $arRes = self::GetList([
            "filter" => array("ID" => $ID)
        ]);
        if(!empty($arRes))
        {
			self::setCookie($cookie_name . "_SALE_UID", $ID);
        }

		return $ID;
	}

    public static function _update($ID, $allowUpdate = true)
	{
		global $DB, $USER;

		if (!is_object($USER))
        {
            $USER = new \CUser;
        }
			
		$ID = intval($ID);

		$cookie_name = \COption::GetOptionString("main", "cookie_name", "BITRIX_SM");

		$arFields = array(
			"=DATE_UPDATE" => $DB->GetNowFunction(),
		);
		if ($USER->IsAuthorized())
			$arFields["USER_ID"] = intval($USER->GetID());

		if ($allowUpdate)
		{
            $ID = intval($ID);
            if ($ID > 0)
            {
                $res = self::update($ID, $arFields);
                if ($res->isSuccess() && $res->getData())
                {
                    $ID = intval($res->getId());
                }
            }
		}

		self::setCookie($cookie_name . "_SALE_UID", $ID);

		if(\COption::GetOptionString("zr.cartlite", "encode_fuser_id", "N") == "Y")
		{
			//$arRes = self::GetList(array("ID" => $ID));
			//if(!empty($arRes))
			//{
			//	if(strval($arRes["CODE"]) == "")
			//	{
			//		$arRes["UF_CODE"] = md5(time().randString(10));
			//		if ($allowUpdate)
			//		{
			//			self::_Update($arRes["ID"], array("UF_CODE" => $arRes["UF_CODE"]));
			//		}
			//	}

			//	self::setCookie($cookie_name . "_SALE_UID",$arRes["ID"]);
			//}
		}
		else
		{
			self::setCookie($cookie_name . "_SALE_UID", $ID);
		}

		return true;
	}

    public static function setCookie($name, $value)
	{
		$application = Application::getInstance();
		$context = $application->getContext();

		$cookie = new Cookie($name, $value, time() + 60*60*24*60);
		$cookie->setDomain($context->getServer()->getHttpHost());
		$cookie->setHttpOnly(false);

		$context->getResponse()->addCookie($cookie);
	}

    public static function _getId($bSkipFUserInit = false)
	{
		global $USER;

		$bSkipFUserInit = ($bSkipFUserInit !== false);

		$cookie_name = \COption::GetOptionString("main", "cookie_name", "BITRIX_SM");

		$ID = null;
		$filter = array();

		if (isset($_SESSION["SALE_USER_ID"]) && intval($_SESSION["SALE_USER_ID"]) > 0)
		{
			$ID = intval($_SESSION["SALE_USER_ID"]);
		}

		if (intval($ID) <= 0 && isset($_COOKIE[$cookie_name."_SALE_UID"]))
		{
			$CODE = (string)$_COOKIE[$cookie_name."_SALE_UID"];
			$filter = (array("ID" => $CODE));
		}

		if (intval($ID) <= 0)
		{
			if (!empty($filter))
			{
				$arRes = self::_getList($filter);
				if(!empty($arRes))
				{
					$ID = $arRes["ID"];
					self::_update($ID);
				}
				else
				{
					if ($USER && $USER->IsAuthorized())
					{
						$ID = self::getFUserCode();
					}

					if (intval($ID) <= 0 && !$bSkipFUserInit)
					{
						$ID = self::_add();
					}
				}
			}
			elseif (!$bSkipFUserInit)
			{
				$ID = self::_add();
			}
		}

		return (int)$ID;
	}

    public static function getFUserCode()
	{
		global $USER;

		$arRes = self::_getList(array("USER_ID" => (int)$USER->GetID()));
		if (!empty($arRes))
		{
			$_SESSION["SALE_USER_ID"] = $arRes['ID'];
			$arRes["UF_CODE"] = self::getUniqCode();

			self::_update($arRes["ID"], array("CODE" => $arRes["CODE"]));
			return $arRes["ID"];
		}
		return 0;
	}

    public static function getFUserIdByCUserId($cuserId)
    {  
        $arRes = self::_getList(['USER_ID' => $cuserId]);
        if (!empty($arRes) && $arRes['ID'] > 0)
        {
            return $arRes['ID'];
        }
        return 0;
    }
}