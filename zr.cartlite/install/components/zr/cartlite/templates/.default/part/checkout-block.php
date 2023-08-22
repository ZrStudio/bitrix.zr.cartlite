<?
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 * @var array $areaId
 * @var string $mainSid
 */
?>

<div class="cart-checkout-block">
    <div class="basket-coupon-section smallest"></div>
    <div class="basket-checkout-section">
        <div class="basket-checkout-section-inner">
            <div class="basket-checkout-block basket-checkout-block-total">
                <div class="basket-checkout-block-total-inner">
                    <div class="basket-checkout-block-total-title">Итого:</div>
                </div>
            </div>
            <div class="basket-checkout-block basket-checkout-block-total-price">
                <div class="basket-checkout-block-total-price-inner">
                    <div class="basket-coupon-block-total-price-current" id="<?=$areaId['CART_TOTAL_PRICE']?>">
                        <?=$arResult['TOTAL_PRICE']?> ₽
                    </div>
                </div>
            </div>

            <div class="basket-checkout-block basket-checkout-block-btns">
                <div class="basket-checkout-block-btns-wrap">
                    <div class="basket-checkout-block basket-checkout-block-btn">
                        <button class="btn btn-primary" id="<?=$areaId['CART_CREATE_ORDER']?>" data-micromodal-trigger="create-order-modal">
                            Оформить заказ 
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>