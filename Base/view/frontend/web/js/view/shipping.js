define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'mage/url'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    shippingService,
    url
) {
    'use strict';

    var zigzagAvailability = ko.observable([]);
    var zigzagAvailabilitySelected = ko.observable('');

    var isZigzag = function (carrierCode) {
        var regex = new RegExp('^zigzag[A-Za-z]+_zigzag[A-Za-z]+$');
        return regex.test(carrierCode)
    };

    var isSelected = ko.computed(function () {
        var method = quote.shippingMethod() ? quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] : null;
        if (method != null && isZigzag(method)) {
            var address = '';
            quote.shippingAddress().street.forEach(function (line) {
                address += ' ' + line;
            })
            $.ajax({
                url: url.build('zigzag/ajax/availability'),
                data: {'target': address, 'country_id': quote.shippingAddress().countryId},
                type: 'post',
                dataType: 'json',
                showLoader: true
            }).done(function (response) {
                if (response.length) {
                    var firstOption = response[0];
                    if (firstOption.hasOwnProperty('date') && firstOption.hasOwnProperty('time_from') && firstOption.hasOwnProperty('time_to')) {
                        zigzagAvailability(response);
                        zigzagAvailabilitySelected(firstOption.date + '_' + firstOption.time_from + '_' + firstOption.time_to)
                    }
                }
            }).fail(function (error) {
                console.log(JSON.stringify(error));
                zigzagAvailabilitySelected('');
            });
        } else {
            zigzagAvailability([]);
        }
        return method
    });

    zigzagAvailabilitySelected.subscribe(function (selection) {
        var result = quote.shippingAddress();
        if (result != null) {
            if (result['extension_attributes'] === undefined) {
                result['extension_attributes'] = {};
            }

            result['extension_attributes']['zigzag_availability'] = Number(selection) !== 0 ? selection : '';
        }
    });

    var mixin = {
        isZigzag: isZigzag,
        zigzagAvailability: zigzagAvailability,
        isSelected: isSelected,
        zigzagAvailabilitySelected: zigzagAvailabilitySelected,
    };

    return function (target) {
        return target.extend(mixin);
    };
});