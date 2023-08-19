(function(window) {
    if (window.JCZrShortCart)
    {
        return;
    }

    window.JCZrShortCart = function(params) {
        this.areaId = params.IDS;
        this.typeShowQuntity = params.TYPE_SHOW_QUANTITY;

        this.obCart = null;
        this.obCartLink = null;
        this.obCartQuantity = null;

        this.ajaxUrl = '/ajax/cartlite_get_actual_cart.php';

        this.init();
    }

    window.JCZrShortCart.prototype = {
        init: function() 
        {
            if (this.areaId.CART)
            {
                this.obCart = document.getElementById(this.areaId.CART);
            }

            if (this.areaId.CART_LINK)
            {
                this.obCartLink = document.getElementById(this.areaId.CART_LINK);
            }
            
            if (this.areaId.QUANTITY)
            {
                this.obCartQuantity = document.getElementById(this.areaId.QUANTITY);
            }

            BX.addCustomEvent('OnBasketChange', BX.delegate(this.updateData, this));
        },

        updateData: function()
        {
            BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.proxy(this.cartResult, this)
			});
        },

        cartResult: function(arResult)
        {
            if (arResult.STATUS == 'OK')
            {
                if (this.obCartQuantity)
                {
                    if (this.typeShowQuntity == 'PC')
                    {
                        this.obCartQuantity.innerHTML = arResult.DATA.ITEM_COUNT;
                    }
                    else if(this.typeShowQuntity == 'PCQ')
                    {
                        this.obCartQuantity.innerHTML = arResult.DATA.ITEM_QUANTITY;
                    }
                }
            }
        }
    }

})(window)