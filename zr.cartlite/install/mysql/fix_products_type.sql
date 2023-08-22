ALTER TABLE `zr_cart_lite_cart`
MODIFY COLUMN `PRODUCTS` longtext;

ALTER TABLE `zr_cart_lite_order`
MODIFY COLUMN `USER_FIELDS` longtext;

ALTER TABLE `zr_cart_lite_order`
MODIFY COLUMN `PRODUCTS` longtext;