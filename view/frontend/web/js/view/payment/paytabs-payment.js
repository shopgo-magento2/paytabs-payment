define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paytabs',
                component: 'ShopGo_Paytabs/js/view/payment/method-renderer/paytabs-method'
            }
        );
        return Component.extend({});
    }
 );