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
<div class="modal micromodal-slide" id="create-order-modal" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
        <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="create-order-modal-title">
        <header class="modal__header">
            <h2 class="modal__title" id="create-order-modal-title">
                Оформление заказа
            </h2>
            <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
        </header>
        <main class="modal__content" id="create-order-modal-content">
            <form method="POST" action="<?=POST_FORM_ACTION_URI?>" name="order-form" class="order-form" id="<?=$areaId['ORDER_FORM']?>">
                <div class="order-form__errors" data-entity="form-errors"></div>
                <div class="order-form__fields">
                    <div class="order-form__field">
                        <input name="USER[NAME]" placeholder="Ваше имя" id="order-from-user_name" required>
                    </div>
                    <div class="order-form__field">
                        <input name="USER[PHONE]" placeholder="Телефон" id="order-from-user_phone" data-tel-input type="tel" required>
                    </div>
                    <div class="order-form__field">
                        <input name="USER[EMAIL]" placeholder="Почта" id="order-from-user_email" type="email">
                    </div>
                    <div class="order-form__field">
                        <textarea name="USER[COMMENT]" placeholder="Комментарий" id="order-from-user_comment"></textarea>
                    </div>
                </div>

                <div class="order-form__footer">
                    <button class="btn btn-primary">Оформить</button>
                </div>
            </form>
        </main>
        </div>
    </div>
</div>