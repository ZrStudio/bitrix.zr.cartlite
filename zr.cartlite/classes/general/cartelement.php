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
    private string $name = '';
    private bool $active = false;
    private string $detailUrl = '';
    private string $previewImageSrc = '';
    private string $detailImageSrc = '';
    private $stockQunatity = false;
    private float $price = 0;
    private int $quantity;
    private array $props;
    private \ZrStudio\CartLite\ProductType $type;

    private int $productIblockId = 0;
    private array $errors = [];

    function __construct($arProductFields)
    {
        $this->productId = $arProductFields['PRODUCT_ID'];
        $this->quantity = $arProductFields['QUANTITY'];

        $this->_loadProductInfo($arProductFields['PRODUCT_ID']);
        $this->_setProductPrice($arProductFields['PRICE']);
        $this->_setProductStockQuantity();
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
        
        $arElement = \CIBlockElement::GetByID($productId)->GetNext();
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

            $this->detailUrl = $arElement['DETAIL_PAGE_URL'];
        }
        else
        {
            $this->errors[] = 'Product not found in catalog iblock';
        }
    }

    private function _setProductPrice($customPrice)
    {
        $isGetPriceFromProp = $this->_getOptionByCode('get_price_product_from_props');
        if ($isGetPriceFromProp == 'N')
        {
            $this->price = floatval($customPrice);
            return;
        }

        if ($this->productIblockId && $this->productIblockId > 0)
        {
            $propCodeByPrice = $this->_getOptionByCode('catalog_'. $this->productIblockId .'_iblock_props');
            $value = $this->_getPropValueByCode($propCodeByPrice);

            if ($value)
            {
                $this->price =floatval($value);
                return;
            }
        }

        $this->price = floatval($customPrice);
    }

    private function _setProductStockQuantity()
    {
        $isGetStockQuantityFromProp = $this->_getOptionByCode('get_stock_quantity_product_from_props');
        if ($isGetStockQuantityFromProp == 'N')
        {
            return;
        }

        if ($this->productIblockId && $this->productIblockId > 0)
        {
            $propCode = $this->_getOptionByCode('catalog_'. $this->productIblockId .'_iblock_stock_quantity_props');
            $value = $this->_getPropValueByCode($propCode);

            if ($value)
            {
                $this->stockQunatity = intval($value);
            }
        }
    }

    private function _getOptionByCode($code)
    {
        $sideLid = SITE_ID == 'ru' ? 's1' : SITE_ID;
        return \Bitrix\Main\Config\Option::get('zr.cartlite', $code . '_' . $sideLid, '', $sideLid);
    }

    private function _getPropValueByCode($code)
    {
        if (!empty($code) && is_string($code))
        {
            if ($this->isLoadIblockModule())
            {
                $arPriceProp = \CIBlockElement::GetProperty(
                    $this->productIblockId, 
                    $this->productId,
                    "sort",
                    "asc", 
                    array('CODE' => $code)
                )->Fetch();

                if (!empty($arPriceProp))
                {
                    return $arPriceProp['VALUE'];
                }
            }
        }

        return '';
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

    public function getId()
    {
        return $this->productId;
    }

    public function getQuantity()
    {
        if ($this->quantity < 0) 
        {
            $this->quantity = 0;
        }

        if ($this->stockQunatity && $this->quantity >= $this->stockQunatity)
        {
            $this->quantity = $this->stockQunatity;
        }

        return $this->quantity;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getProductJs($actions = [])
    {
        return [
            $this->productId,
            $this->detailUrl,
            $this->stockQunatity,
            $this->previewImageSrc,
            [$this->name, $this->props],
            $this->price,
            $this->getQuantity(),
            $this->getProductTotalCost(),
            $actions
        ];
    }

    /**
     * Get product price for one item
     * 
     * @return float price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get total product cost. price * quantity
     * 
     * @return float total cost
     */
    public function getProductTotalCost()
    {
        return round($this->price * $this->quantity, 2);
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