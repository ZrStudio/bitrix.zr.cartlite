(function(window) {
    if (window.JCZrCartLite)
    {
        return;
    }

    window.JCZrCartLite = function(params) {
        this.areaId = params.IDS;
        this.products = params.PRODUCTS;

        this.obCart = null;
        this.obCartTable = null;

        this.obGridJs = null;

        this.ajaxItemUrl = '/ajax/cartlite_action.php';
        this.ajaxCartUrl = '/ajax/cartlite_get_actual_cart.php';

        this.init();
    }

    window.JCZrCartLite.prototype = {
        init: function() 
        {
            if (this.areaId.CART)
            {
                this.obCart = document.getElementById(this.areaId.CART);
            }

            if (this.areaId.CART_TABLE)
            {
                this.obCartTable = document.getElementById(this.areaId.CART_TABLE);
            }

            this.renderTableData(this.products);
        },

        setItemsEvents: function()
        {
            let obItemsQuantity = [...this.obCartTable.querySelectorAll('[data-pq-container]')];

            obItemsQuantity.forEach((obQuantityContainer) => {
                let productId = obQuantityContainer.dataset.pqContainer;
                let obQuantityMinus = obQuantityContainer.querySelector('[data-quantity-action="minus"]');
                let obQuantityPlus = obQuantityContainer.querySelector('[data-quantity-action="plus"]');
                let obQuantity = obQuantityContainer.querySelector('[data-quantity-field]');

                BX.bind(obQuantityMinus, 'click', BX.delegate((e) => this.quantityDown(obQuantity, productId), this));
                BX.bind(obQuantityPlus, 'click', BX.delegate((e) =>this.quantityUp(obQuantity, productId), this));
                BX.bind(obQuantity, 'change', BX.delegate((e) => this.quantitySet(e, productId), this));
            });
        },

        quantityUp: function(obQuantity, productId)
        {
            //obQuantity.value = Number(obQuantity.value) + 1;
            this.addItem(productId, 1);
        },

        quantityDown: function(obQuantity, productId)
        {
            if (Number(obQuantity.value) > 0)
            {
                //obQuantity.value = Number(obQuantity.value) - 1;
                this.addItem(productId, -1);
            }
            else
            {
                this.deleteItem(productId);
            }
        },

        quantitySet: function(e, productId)
        {
            let quantity = e.target.value;
            this.setItem(productId, quantity);
        },

        _getTableColumns: function()
        {
            return [
                {
                    name: 'product_id',
                    hidden: true
                },
                {
                    name: 'product_link',
                    hidden: true
                },
                {
                    name: 'Изображение',
                    formatter: (_, row) => gridjs.html(`<img src='${row.cells[2].data}'/>`),
                    attributes: (cell, row, column)  => {
                        return {
                            onclick: () => window.location.assign(row.cells[1].data),
                            style: 'cursor: pointer' 
                        };
                    }
                },
                {
                    name: 'Название',
                    formatter: (_, row) => gridjs.html(`<a href='${row.cells[1].data}'>${row.cells[3].data}</a>`),
                },
                {
                    name: 'Цена',
                    formatter: (_, row) => gridjs.html(`${row.cells[4].data} ₽`),
                },
                {
                    name: 'Колличество',
                    formatter: (cell) => gridjs.html(this._getQuantityHtml(cell)),
                    attributes: (_, row) => {
                        if (row)
                        {
                            return {
                                'data-pq-container': row.cells[0].data
                            }
                        }
                    }
                },
                {
                    name: 'Общая цена',
                    formatter: (_, row) => gridjs.html(`${row.cells[6].data} ₽`),
                },
                {
                    name: '',
                    formatter: (cell, row) => {
                        return gridjs.h('button', {
                          className: 'btn btn-error btn-small',
                          onClick: () => this.deleteItem(row.cells[0].data)
                        }, 'Удалить');
                      }
                }
            ]
        },

        _getQuantityHtml: function(quantity)
        {
            return `<div class="cart-quantity">
                <span class="cart-quantity__minus" data-quantity-action="minus">-</span>
                <input class="cart-quantity__field" type="number" value="${quantity}" data-quantity-field>
                <span class="cart-quantity__plus" data-quantity-action="plus">+</span>
            </div>`;
        },

        renderTableData: function(data)
        {
            if (this.obGridJs == null)
            {
                this.obGridJs = new gridjs.Grid({
                    resizable: true,
                    columns: this._getTableColumns(), 
                    data: data,
                    language: {
                        noRecordsFound: "Ваша корзина пуста"
                    }
                }).render(this.obCartTable);

                this.obGridJs.on('ready', BX.delegate(this.setItemsEvents, this));
            }
            else
            {
                this.obGridJs.updateConfig({
                    data: data
                }).forceRender();
            }
        },

        setItem: function(productId, quantity)
        {
            this.sendCartAction({
                action: 'add_item',
                mode: 'set_quantity',
                item: productId,
                quantity: quantity
            });
        },

        addItem: function(productId, quantity)
        {
            this.sendCartAction({
                action: 'add_item',
                item: productId,
                quantity: quantity
            });
        },

        deleteItem: function(productId)
        {
            this.sendCartAction({
                action: 'delete_item',
                item: productId
            });
        },

        sendCartAction: function(data)
        {
            BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.ajaxItemUrl,
                data: data,
				onsuccess: BX.proxy(this.updateCartResult, this)
			});
        },

        updateCartResult: function(arResult)
        {
            if (arResult.STATUS == 'OK')
            {
                BX.onCustomEvent('OnBasketChange');
                BX.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: this.ajaxCartUrl,
                    data: {
                        mode: 'add_js_products'
                    },
                    onsuccess: BX.delegate(function(basketResult) {
                        if (basketResult.STATUS == 'OK')
                        {
                            this.renderTableData(basketResult.DATA.JS_ITEMS);
                        }
                    }, this),
                });
            }
        }
    }
})(window)