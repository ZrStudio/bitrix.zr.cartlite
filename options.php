<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;

$module_id = 'zr.cartlite';
$prefix = 'ZR_CARTLITE_';

include($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/'.$module_id.'/default_option.php');

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
Loc::loadMessages(__FILE__);

// проверка прав на настройки модуля
if ($APPLICATION->GetGroupRight($module_id) < 'S')
{
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

Loader::includeModule($module_id);

$request = HttpApplication::getInstance()->getContext()->getRequest();

if (\Bitrix\Main\Loader::includeModule('iblock'))
{
    $rsIblocks = CIBlock::GetList($by = 'sort', ['ACTIVE' => 'Y']);
    $arIBlocks = [];
    while ($arIblock = $rsIblocks->Fetch())
    {
        $arIBlocks[$arIblock['ID']] = '['.$arIblock['ID'].'] '.$arIblock['NAME'];
    }

}

$aTabs = [];
$rsSites = CSite::GetList($by = 'sort', $order = 'asc', ['ACTIVE' => 'Y']);

while ($arSite = $rsSites->Fetch())
{
    $isActiveModule = \Bitrix\Main\Config\Option::get($module_id, 'module_active_'. $arSite['LID'], '', $arSite['LID']);
    $getPriceFromProp = \Bitrix\Main\Config\Option::get($module_id, 'get_price_product_from_props_'. $arSite['LID'], '', $arSite['LID']);
    $catalogIblocsSelect = \Bitrix\Main\Config\Option::get($module_id, 'catalog_iblocks_'. $arSite['LID'], '', $arSite['LID']);

    $arCatalogIblock = [];
    if (is_string($catalogIblocsSelect) && $catalogIblocsSelect != '')
    {
        $arCatalogIblock = array_map(fn($x) => trim($x), explode(',', $catalogIblocsSelect));
    }

    $arOptions = [];

    // Main setting
    $arOptions = array_merge($arOptions,
    [
        Loc::getMessage($prefix .'HEADER_BASE_SETTINGS'),
        [
            'module_active_'. $arSite['LID'],
            Loc::getMessage($prefix .'ACTIVE'),
            $zr_cartlite_default_option['module_active_s1'],
            ['checkbox']
        ]
    ]);

    if ($isActiveModule == 'Y')
    {
        $arOptions = array_merge($arOptions, [
            [
                'get_price_product_from_props_'. $arSite['LID'],
                Loc::getMessage($prefix .'GET_PRICE_FROM_PROP'),
                $zr_cartlite_default_option['get_price_product_from_props_s1'],
                ['checkbox']
            ]
        ]);

        if ($getPriceFromProp == 'Y')
        {
            $arOptions = array_merge($arOptions, [
                Loc::getMessage($prefix .'CATALOG_IBLOCK_PROPS_TITLE'),
                [
                    'catalog_iblocks_'. $arSite['LID'],
                    Loc::getMessage($prefix .'CATALOG_IBLOCKS'),
                    false,
                    ['multiselectbox', $arIBlocks]
                ]
            ]);
    
            if (!empty($arCatalogIblock))
            {
                foreach($arCatalogIblock as $catalogId) 
                {
                    $rsProps = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$catalogId));
    
                    $arProps = [0 => ''];
                    while ($arProp = $rsProps->GetNext())
                    {
                        $arProps[$arProp["CODE"]] = "[".$arProp['ID']."] ".$arProp['NAME'];
                    }
    
                    $arOptions = array_merge($arOptions,
                    [
                        [
                            'catalog_'. $catalogId.'_iblock_props_'. $arSite['LID'],
                            Loc::getMessage($prefix .'CATALOG_IBLOCK_PROPS')." [".$catalogId."]",
                            false,
                            ['selectbox', $arProps]
                        ]
                    ]);
                }
            }
        }
    }

    $aTabs[] =
    [
        'DIV' => 'settings_'. $arSite['LID'],
        'TAB' => $arSite['NAME'].' ('.$arSite['LID'].')',
        'OPTIONS' => $arOptions
    ];
}


// сохранение настроек
if ($request->isPost() && $request['Update'] && check_bitrix_sessid())
{
    foreach ($aTabs as $aTab)
    {
        foreach ($aTab['OPTIONS'] as $arOption)
        {
            if (!is_array($arOption)) continue;
            if ($arOption['note']) continue;
            __AdmSettingsSaveOption($module_id, $arOption);
        }
    }
}

// вывод формы
$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>

<?$tabControl->Begin();?>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&lang=<?=$request['lang']?>" name="zr_cartlite_settings">
    <?=bitrix_sessid_post()?>
    <?foreach ($aTabs as $aTab)
    {
        if ($aTab['OPTIONS'])
        {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
        }
    }?>
    <? $tabControl->Buttons(); ?>

    <input type="submit" name="Update" value="<?=Loc::getMessage('MAIN_SAVE')?>">
    <input type="reset" name="reset" value="<?=Loc::getMessage('MAIN_RESET')?>">
</form>
<?$tabControl->End();?>