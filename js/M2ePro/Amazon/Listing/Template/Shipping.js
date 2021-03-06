window.AmazonListingTemplateShipping = Class.create(Action, {

    // ---------------------------------------

    openPopUp: function(productsIds)
    {
        var self = this;
        self.gridHandler.unselectAll();

        new Ajax.Request(M2ePro.url.viewTemplateShippingPopup, {
            method: 'post',
            parameters: {
                products_ids:  productsIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                var response = transport.responseText.evalJSON();

                if (!response.data) {
                    if (response.messages.length > 0) {
                        MessageObj.clearAll();
                        response.messages.each(function(msg) {
                            MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                        });
                    }

                    return;
                }

                var title = M2ePro.text.templateShippingPopupTitle;

                templateShippingPopup = Dialog.info(null, {
                    draggable: true,
                    resizable: true,
                    closable: true,
                    className: "magento",
                    windowClassName: "popup-window",
                    title: title,
                    top: 70,
                    width: 800,
                    height: 550,
                    zIndex: 100,
                    hideEffect: Element.hide,
                    showEffect: Element.show
                });
                templateShippingPopup.options.destroyOnClose = true;

                templateShippingPopup.productsIds = response.products_ids;

                $('modal_dialog_message').insert(response.data);

                $('template_shipping_grid').observe('click', function(event) {
                    if (!event.target.hasClassName('assign-shipping-template')) {
                        return;
                    }

                    self.assign(event.target.getAttribute('templateShippingId'));
                });

                $('template_shipping_grid').on('click', '.new-shipping-template', function() {
                    self.createInNewTab(self.newTemplateUrl);
                });

                self.loadGrid();

                setTimeout(function() {
                    Windows.getFocusedWindow().content.style.height = '';
                    Windows.getFocusedWindow().content.style.maxHeight = '600px';
                }, 50);
            }
        });
    },

    loadGrid: function() {

        var self = this;

        new Ajax.Request(M2ePro.url.viewTemplateShippingGrid, {
            method: 'post',
            parameters: {
                products_ids:  templateShippingPopup.productsIds
            },
            onSuccess: function(transport) {
                $('template_shipping_grid').update(transport.responseText);
                $('template_shipping_grid').show();
            }
        });
    },

    // ---------------------------------------

    assign: function (templateId)
    {
        var self = this;

        if (!confirm(M2ePro.translator.translate('Are you sure?'))) {
            return;
        }

        new Ajax.Request(M2ePro.url.assignShippingTemplate, {
            method: 'post',
            parameters: {
                products_ids:  templateShippingPopup.productsIds,
                template_id:   templateId
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                self.gridHandler.unselectAllAndReload();

                var response = transport.responseText.evalJSON();

                MessageObj.clearAll();
                response.messages.each(function(msg) {
                    MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });
            }
        });

        templateShippingPopup.close();
    },

    // ---------------------------------------

    unassign: function (productsIds)
    {
        var self = this;

        new Ajax.Request(M2ePro.url.unassignShippingTemplate, {
            method: 'post',
            parameters: {
                products_ids:  productsIds
            },
            onSuccess: function(transport) {

                if (!transport.responseText.isJSON()) {
                    alert(transport.responseText);
                    return;
                }

                self.gridHandler.unselectAllAndReload();

                var response = transport.responseText.evalJSON();

                MessageObj.clearAll();
                response.messages.each(function(msg) {
                    MessageObj['add' + msg.type[0].toUpperCase() + msg.type.slice(1)](msg.text);
                });
            }
        });
    },

    // ---------------------------------------

    createInNewTab: function(url)
    {
        var self = this;
        var win = window.open(url);

        var intervalId = setInterval(function() {
            if (!win.closed) {
                return;
            }

            clearInterval(intervalId);

            self.loadGrid();
        }, 1000);
    }

    // ---------------------------------------
});