window.WizardInstallationAmazon = Class.create(Common, {

    // ---------------------------------------

    continueStep: function ()
    {
        if (WizardObj.steps.current.length) {
            this[WizardObj.steps.current + 'Step']();
        }
    },

    registrationStep: function ()
    {
        WizardObj.registrationStep(M2ePro.url.get('adminhtml_wizard_installationAmazon/createLicense'));
    },

    marketplaceChange: function()
    {
        var marketplaceId = $('marketplace_id').value;

        if (!marketplaceId || !$('manual_authorization_marketplace_developer_key_container_'+marketplaceId)) {
            $$('.manual-authorization').each(function (el) {
                el.hide();
            });
            return;
        }

        $('manual_authorization_marketplace').show();
        $('amazon_wizard_installation_account_manual_authorization').show();
        $('manual_authorization_marketplace_application_name_container').show();
        $('manual_authorization_marketplace_developer_key_container_'+marketplaceId).show();
        $('manual_authorization_marketplace_register_url_container_'+marketplaceId).show();
        $('manual_authorization_marketplace_merchant_id_container_'+marketplaceId).show();
        $('manual_authorization_marketplace_token_container_'+marketplaceId).show();
    },

    accountStep: function ()
    {
        MessageObj.clearAll();

        var marketplaceId = $('marketplace_id').value;

        if (!marketplaceId) {
            MessageObj.addError(M2ePro.translator.translate('Please select Marketplace first.'));
            return CommonObj.scroll_page_to_top();
        }

        if ($('manual_authorization_marketplace_developer_key_container_'+marketplaceId)) {

            var merchantId = $('manual_authorization_marketplace_merchant_id_'+marketplaceId).value;
            var token      = $('manual_authorization_marketplace_token_'+marketplaceId).value;

            if (!merchantId || !token) {
                MessageObj.addError(M2ePro.translator.translate('Please fill Merchant ID and MWS Auth Token fields.'));
                return CommonObj.scroll_page_to_top();
            }

            var checkResult = false;
            var checkReason = null;

            new Ajax.Request(M2ePro.url.get('adminhtml_amazon_account/checkAuth'), {
                method: 'post',
                asynchronous: false,
                parameters: {
                    merchant_id    : merchantId,
                    token          : token,
                    marketplace_id : marketplaceId
                },
                onSuccess: function(transport) {
                    var response = transport.responseText.evalJSON();
                    checkResult = response['result'];
                    checkReason = response['reason'];
                }
            });

            if (!checkResult) {
                if (checkReason) {
                    MessageObj.addError(M2ePro.translator.translate('M2E Pro was not able to get access to the Amazon Account. Reason: %error_message%').replace('%error_message%', checkReason));
                    return CommonObj.scroll_page_to_top();
                }

                MessageObj.addError(M2ePro.translator.translate('M2E Pro was not able to get access to the Amazon Account. Please, make sure, that you choose correct Option on MWS Authorization Page and enter correct Merchant ID.'));
                return CommonObj.scroll_page_to_top();
            }

            return setLocation(M2ePro.url.get('adminhtml_wizard_installationAmazon/afterGetTokenManual', {"merchant_id": merchantId, "token": token, "marketplace_id": marketplaceId}));
        }

        new Ajax.Request(M2ePro.url.get('adminhtml_wizard_installationAmazon/beforeToken'), {
            method       : 'post',
            asynchronous : true,
            parameters   : $('edit_form').serialize(),
            onSuccess: function(transport) {

                var response = transport.responseText.evalJSON();

                if (response && response['message']) {
                    MessageObj.addError(response['message']);
                    return CommonObj.scroll_page_to_top();
                }

                if (!response['url']) {
                    MessageObj.addError(M2ePro.translator.translate('An error during of account creation.'));
                    return CommonObj.scroll_page_to_top();
                }

                window.location.href = response['url'];
            }
        });
    },

    listingTutorialStep: function ()
    {
        WizardObj.setStep(WizardObj.getNextStep(), setLocation.bind(window, location.href));
    }

    // ---------------------------------------
});