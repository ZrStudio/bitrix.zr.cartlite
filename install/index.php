<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use ZrStudio\CartLite\FCUserTable;
use ZrStudio\CartLite\FCartTable;

Loc::loadMessages(__FILE__);

class zr_cartlite extends CModule
{
    private $moduleId = 'zr.cartlite';

    private string $docRoot = '';

    protected array $files = [
        '/ajax/cartlite_action.php' => '/ajax/cartlite_action.php',
        '/ajax/cartlite_get_actual_cart.php' => '/ajax/cartlite_get_actual_cart.php',
        '/components/zr/cartlite/' => '/bitrix/components/zr/cartlite/',
        '/components/zr/shortcart/' => '/bitrix/components/zr/shortcart/',
    ];

    protected array $tables = [
        'ZrStudio\CartLite\FCUserTable',
        'ZrStudio\CartLite\FCartTable',
        'ZrStudio\CartLite\OrderTable'
    ];

    protected array $fixedMySql = [
        'fix_products_type.sql'
    ];

    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = $this->moduleId;
        $this->MODULE_NAME = Loc::getMessage('ZR_CART_LITE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('ZR_CART_LITE_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('ZR_CART_LITE_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https:/zrstudio.com/';

        $context = \Bitrix\Main\Application::getInstance()->getContext();
		$server = $context->getServer();
		$this->docRoot = $server->getDocumentRoot();
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->requireInstallLibs();

        //\CAgent::AddAgent('\Zrstudio\StopList\UserActionsTable::clearOldActions();', $this->MODULE_ID, 'N', 86400, '', 'Y', '', 100);
        //\CAgent::AddAgent('\Zrstudio\StopList\UserIpRuleTable::clearUsers();', $this->MODULE_ID, 'N', 86400 * 3, '', 'Y', '', 100);

        $this->installDB();
        $this->copyFiles();
        $this->InstallEvents();
    }

    public function doUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION, $step;

		$step = intval($step);
		if($step<2)
		{   
			$APPLICATION->IncludeAdminFile(GetMessage("ZR_CART_LITE_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/".$this->moduleId."/install/unstep1.php");
		}
		elseif($step==2)
		{
            $this->requireInstallLibs();
        
            if (!$_REQUEST["savedata"])
            {
                $this->uninstallDB();
            }
			
            //\CAgent::RemoveModuleAgents($this->MODULE_ID);
            
            $this->removeFiles();
            $this->UnInstallEvents();

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(GetMessage("ZR_CART_LITE_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/".$this->moduleId."/install/unstep2.php");
		}
    }

    public function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler('main', 'OnBeforeProlog', $this->MODULE_ID, 'Zrstudio\\CartLite\\FUser', 'getInstance');

        global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/events.php");
		return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler('main', 'OnBeforeProlog', $this->MODULE_ID, 'Zrstudio\\CartLite\\FUser', 'getInstance');

        global $DB;

		$statusMes[] = "ZR_CL_NEW_ORDER";

		$eventType = new CEventType;
		$eventM = new CEventMessage;
		foreach($statusMes as $v)
		{
			$eventType->Delete($v);
			$dbEvent = CEventMessage::GetList("id", "asc", Array("EVENT_NAME" => $v));
			while($arEvent = $dbEvent->Fetch())
			{
				$eventM->Delete($arEvent["ID"]);
			}
		}

		return true;
    }

    public function installDB()
    {
        $this->_upworkTable('install');
        $this->_fixTableTypes();
    }

    public function uninstallDB()
    {
        $this->_upworkTable('drop');
    }

    private function _upworkTable($mode = 'install')
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            $appConnect = Application::getInstance()->getConnection();
            foreach($this->tables as $tableName)
            {
                $_tableName = Bitrix\Main\Entity\Base::getInstance($tableName)->getDBTableName();

                if ($mode == 'install')
                {
                    if (!$appConnect->isTableExists($_tableName))
                    {
                       $tableName::getEntity()->createDbTable();
                    }
                }
                else if ($mode == 'drop')
                {
                    if ($appConnect->isTableExists($_tableName))
                    {
                        $appConnect->dropTable($_tableName);
                    }
                }
            }
        }
    }

    private function _fixTableTypes()
    {
        global $DB, $APPLICATION;
        foreach($this->fixedMySql as $mysqlFile)
        {
            $errors = $DB->runSQLBatch(
                $this->docRoot.'/bitrix/modules/'.$this->moduleId.'/install/mysql/'.$mysqlFile
            );

            if ($errors !== false)
            {
                $APPLICATION->throwException(implode('', $errors));
                return false;
            }
        }
    }

    /**
     * @return array
     */
    private function copyFiles(): array
    {
        $documentRoot = Application::getDocumentRoot();
        $errors       = [];

        foreach ($this->files as $from => $to) 
        {
            if (!CopyDirFiles(__DIR__ . $from, $documentRoot . $to, true, true)) 
            {
                $errors[] = $from.':'.$to.'<br/>';
            }
        }

        return $errors;
    }

    private function removeFiles()
    {
        foreach ($this->files as $to) 
        {
            DeleteDirFilesEx($to);
        }
    }

    /**
     * @author ZtStudio (Alexandr Drachenin)
     */
    protected function requireInstallLibs()
    {
        require_once __DIR__ . '/../lib/fcuser.php';
        require_once __DIR__ . '/../lib/fcart.php';
        require_once __DIR__ . '/../lib/order.php';
    }
}
