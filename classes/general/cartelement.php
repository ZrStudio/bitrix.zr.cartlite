<?/**
 * ZrStudio
 * @package zr.cartlite
 * @subpackage cart
 * @copyright 2023 zr
 */

namespace ZrStudio\CartLite;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

enum ProductType: string
{
    case NORMAL = 'normal';
    case SKU = 'sku';
}

class CartElement 
{

    private int $id;
    private int $productId;
    private string $name;
    private bool $active;
    private string $previewImageSrc;
    private string $detailImageSrc;
    private float $price;
    private int $quantity;
    private array $props;
    private \ZrStudio\CartLite\ProductType $type;

    private int $productIblockId;
    private array $errors = [];

    function __construct($arProductFields)
    {
        $this->productId = $arProductFields['PRODUCT_ID'];
        $this->quantity = $arProductFields['QUANTITY'];

        $this->name = '';
        $this->active = false;
        $this->previewImageSrc = '';
        $this->detailImageSrc = '';
        $this->price = 0;
        $this->productIblockId = 0;

        $this->_loadProductInfo($arProductFields['PRODUCT_ID']);
        $this->_setProductPrice($arProductFields['PRICE']);
        $this->_initType($arProductFields['TYPE']);
        $this->_initProps($arProductFields['PROPS']);
    }

    private function _initType($type)
    {
        if (!empty($type))
        {
            if (is_a($type, '\ZrStudio\CartLite\ProductType'))
            {
                $this->type = $type;
                return;
            }
            $this->type = ProductType::tryFrom($type);
        }
        else
        {
            $this->type = ProductType::NORMAL;
        }
    }

    private function _initProps($arProps)
    {
        // todo create function
        $this->props = $arProps ?: [];
        return;
    }

    private function _loadProductInfo($productId)
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) return;
        
        $arElement = \CIBlockElement::GetByID($productId)->fetch();
        if(is_array($arElement) && !empty($arElement))
        {
            $this->name = $arElement['NAME'];
            $this->active = $arElement['ACTIVE'] == 'Y';

            $previewImage = null;
            if (!empty($arElement['PREVIEW_PICTURE']) && $arElement['PREVIEW_PICTURE'] > 0)
            {
                $previewImage = \CFile::GetPath($arElement['PREVIEW_PICTURE']);
            }

            $detailImage = null;
            if (!empty($arElement['DETAIL_PICTURE']) && $arElement['DETAIL_PICTURE'] > 0)
            {
                $detailImage = \CFile::GetPath($arElement['DETAIL_PICTURE']);
            }

            $this->previewImageSrc = $previewImage;
            $this->detailImageSrc = $detailImage;

            $this->productIblockId = $arElement['IBLOCK_ID'];
        }
        else
        {
            $this->errors[] = 'Product not found in catalog iblock';
        }
    }

    private function _setProductPrice($customPrice)
    {
        $sideLid = SITE_ID == 'ru' ? 's1' : SITE_ID;
        $isGetPriceFromProp = \Bitrix\Main\Config\Option::get('zr.cartlite', 'get_price_product_from_props_'. $sideLid, '', $sideLid);
        if ($isGetPriceFromProp == 'N')
        {
            $this->price = floatval($customPrice);
            return;
        }

        if ($this->productIblockId && $this->productIblockId > 0)
        {
            $propCodeByPrice = \Bitrix\Main\Config\Option::get(
                'zr.cartlite', 
                'catalog_'. $this->productIblockId .'_iblock_props_'. $sideLid, 
                '', 
                $sideLid
            );

            if (!empty($propCodeByPrice) && is_string($propCodeByPrice))
            {
                if ($this->isLoadIblockModule())
                {
                    
                    $arPriceProp = \CIBlockElement::GetProperty(
                        $this->productIblockId, 
                        $this->productId,
                        "sort",
                        "asc", 
                        array('CODE' => $propCodeByPrice)
                    )->Fetch();

                    if (!empty($arPriceProp))
                    {
                        $this->price = floatval($arPriceProp['VALUE']);
                        return;
                    }
                }
            }
        }

        $this->price = floatval($customPrice);
    }

    private function isLoadIblockModule()
    {
        $isLoad = \Bitrix\Main\Loader::includeModule('iblock');
        if (!$isLoad) $this->errors[] = 'Not load iblock module';
        return \Bitrix\Main\Loader::includeModule('iblock');
    }

    public function isValid()
    {
        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function toArray()
    {
        return [
            'PRODUCT_ID' => $this->productId,
            'PRICE' => $this->price,
            'QUANTITY' => $this->quantity,
            'NAME' => $this->name,
            'PREVIEW_PICTURE' => $this->previewImageSrc,
            'DETAIL_PICTURE' => $this->detailImageSrc,
            'PROPS' => $this->props
        ];
    }
}