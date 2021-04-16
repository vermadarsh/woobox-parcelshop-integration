jQuery(document).ready(function ($) {
    'use strict';

    var woopi_admin_fields = {

        init: function () {
            $(document).on('click', 'a.woopi-add-rate', this.woopiAddRateRowHTML);
            $(document).on('click', 'a.woopi-remove-rate', this.woopiRemoveRateRowHTML);
            $(document).on('click', '.woopi-select-rate', this.woopiConfirmAllChecked);
            $(document).on('click', '.woopi-select-all-rates', this.woopiSelectDeSelectRates);
            $(document).on('click', '.woopi-open-print-label-modal', this.woopiOpenPrintLabelModal);
            $(document).on('click', '.woopi-close', this.woopiCloseModal);
            $(document).on('click', '.woopi-submit-print-label-data', this.woopiSubmitPrintLabelData);
        },

        /**
         * Add row html.
         *
         * @param event
         */
        woopiAddRateRowHTML: function (event) {

            event.preventDefault();
            var shipping_method_id = $(this).data('id');
            var size = $('#' + shipping_method_id + '_rates tbody .woopi-rate').size();
            $('#' + shipping_method_id + '_rates tbody').append(woopi_admin_custom_functions.woopiGetRateRowHTML(shipping_method_id, size));

        },

        /**
         * Remove row html.
         *
         * @param event
         */
        woopiRemoveRateRowHTML: function (event) {

            event.preventDefault();
            var shipping_method_id = $(this).data('id');
            var removal_cnf = confirm(WOOPI_Admin_JS_Obj.woopi_remove_custom_shipping_rate_row);
            if (true === removal_cnf) {
                $('#' + shipping_method_id + '_rates tbody tr th.check-column input:checked').each(function (i, el) {
                    $(el).closest('tr').remove();
                });
            }

        },

        /**
         * Make sure that the main checkbox is checked if all the child checkboxes are checked.
         */
        woopiConfirmAllChecked: function () {

            var _unchecked_count = 0;
            $('.woopi-select-rate').each(function () {
                var _this_checkbox = $(this);
                var _is_checked = _this_checkbox.is(':checked');
                if (!_is_checked) {
                    _unchecked_count++;
                }
            });

            if (0 < _unchecked_count) {
                $('.woopi-select-all-rates').prop('checked', false);
            } else {
                $('.woopi-select-all-rates').prop('checked', true);
            }

        },

        /**
         * Select all the child checkboxes if the main checkbox is checked.
         * Do the vice-versa.
         */
        woopiSelectDeSelectRates: function () {
            let check_the_checkboxes;
            check_the_checkboxes = !!$(this).is(':checked');
            $('.woopi-select-rate').each(function () {
                $(this).prop('checked', check_the_checkboxes);
            });
        },

        /**
         * Open the modal for printing GLS label.
         * @param e
         */
        woopiOpenPrintLabelModal: function (e) {
            e.preventDefault();
            var orderid = $(this).data('orderid');
            $('.woopi-modal-header h2').text(WOOPI_Admin_JS_Obj.waiting_modal_header_text);
            $('.woopi-gls-carrier-modal-content').html('<p>' + WOOPI_Admin_JS_Obj.waiting_modal_header_text + '</p>');
            $('#woopi-gls-carrier-modal').show();
            var data = {
                action: 'woopi_fetch_print_label_modal_html',
                orderid: orderid,
            };

            $.ajax({
                dataType: 'JSON',
                url: WOOPI_Admin_JS_Obj.woopi_ajax_url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if ('gls-print-label-modal-html-fetched' === response.data.message) {
                        $('.woopi-gls-carrier-modal-content').html(response.data.html);
                        $('.woopi-modal-header h2').text(response.data.header_text);
                    }
                },
            });
        },

        /**
         * Hide all open modals.
         * @param e
         */
        woopiCloseModal: function (e) {
            e.preventDefault();
            $('.woopi-modal').hide();
        },

        /**
         * Send ajax to print labels.
         * @param e
         */
        woopiSubmitPrintLabelData: function (e) {
            e.preventDefault();
            var orderid = $(this).data('orderid');
            var packages = $('#woopi-package-weight').val();
            var gls_service = $('#woopi-gls-service').val();
            var shipping_date = $('#woopi-shipping-date').val();
            var incoterm = $('#woopi-incoterm').val();
            var supp_reference1 = $('#woopi-supp-reference-1').val();
            var supp_reference2 = $('#woopi-supp-reference-2').val();
            var data = {
                action: 'woopi_generate_label',
                orderid: orderid,
                packages: packages,
                gls_service: gls_service,
                shipping_date: shipping_date,
                incoterm: incoterm,
                supp_reference1: supp_reference1,
                supp_reference2: supp_reference2
            };
            $.ajax({
                dataType: 'JSON',
                url: WOOPI_Admin_JS_Obj.woopi_ajax_url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if ('gls-print-label-modal-html-fetched' === response.data.message) {
                        $('.woopi-gls-carrier-modal-content').html(response.data.html);
                        $('.woopi-modal-header h2').text(response.data.header_text);
                    }
                },
            });
        }
    };
    woopi_admin_fields.init();

    var woopi_admin_custom_functions = {

        /**
         * Function to add html - custom shipping rate.
         *
         * @param id
         * @param size
         * @returns {string}
         */
        woopiGetRateRowHTML: function (id, size) {

            var html = '<tr class="woopi-rate">';
            html += '<th class="check-column woopi-check-column"><input type="checkbox" class="woopi-select-rate"></th>';
            html += '<td><input type="number" step="any" min="0" name="woocommerce_' + id + '_rates][' + size + '][weight_min]]" placeholder="0.00" size="4" /></td>';
            html += '<td><input type="number" step="any" min="0" name="woocommerce_' + id + '_rates][' + size + '][weight_max]]" placeholder="0.00" size="4" /></td>';
            html += '<td><input type="number" step="any" min="0" name="woocommerce_' + id + '_rates][' + size + '][total_from]]" placeholder="0.00" size="4" /></td>';
            html += '<td><input type="number" step="any" min="0" name="woocommerce_' + id + '_rates][' + size + '][total_to]]" placeholder="0.00" size="4" /></td>';
            html += '<td><input type="number" step="any" min="0" name="woocommerce_' + id + '_rates][' + size + '][cost]]" placeholder="0.00" size="4" /></td>';
            html += '</tr>';
            return html;

        },

    };
});
