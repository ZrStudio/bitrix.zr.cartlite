<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

namespace ZrStudio\CartLite;

use Bitrix\Main;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;
use ZrStudio\CartLite\FCUserTable;
use ZrStudio\CartLite\FCart;

Loc::loadMessages(__FILE__);

class FUser 
{
    private int $id;
    private int $cuserId;

    private static $instance;

    private function __construct() { $this->_init(); }
    private function __clone() {}
    private function __wakeup() {}

    private function _init() {
        $id = self::getId();
        if ($id == null) $id = -1;

        global $USER;

        $this->id = $id;
        $this->cuserId = $USER->GetID();
    }


    public static function getInstance(): FUser
    {
        if (self::$instance === null) 
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

	/**
	 * Return fuserId.
	 *
	 * @param bool $skipCreate Create, if not exist.
	 * @return int|null
	 */
	protected static function getId($skipCreate = false)
	{
		global $USER;

		$id = null;

		static $fuserList = array();

		if ((isset($USER) && $USER instanceof \CUser) && $USER->IsAuthorized())
		{
			$currentUserId = (int)$USER->GetID();
			if (!isset($fuserList[$currentUserId]))
			{
				$fuserList[$currentUserId] = static::getIdByUserId($currentUserId);
			}
			$id = $fuserList[$currentUserId];
		}

		if ((int)$id <= 0)
		{
			$id = FCUserTable::_getId($skipCreate);
		}

		static::updateSession($id);
		return $id;
	}

	/**
	 * Update session data
	 *
	 * @param int $id FuserId.
	 * @return void
	 */
	protected static function updateSession($id)
	{
		if (!isset($_SESSION['SALE_USER_ID']) || (string)$_SESSION['SALE_USER_ID'] == '' || $_SESSION['SALE_USER_ID'] === 0)
        {
			$_SESSION['SALE_USER_ID'] = $id;
        }
	}

	/**
	 * Return fuserId for user.
	 *
	 * @param int $userId			User Id.
	 * @return false|int
	 * @throws Main\ArgumentException
	 */
	protected static function getIdByUserId($userId)
	{
		return FCUserTable::getFUserIdByCUserId($userId);
	}

    public function getFUserId()
    {
        return $this->id;
    }

    public function getCUserId()
    {
        return $this->cuserId;
    }

    public function getFUserBasket()
    {
        $basket = FCart::getUserBasketByFUserId($this->id);
        if ($basket)
        {
            $basket->calc();
        }
        return $basket;
    }
}