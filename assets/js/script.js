jQuery(function () {

  var wrp_main = {

    /**
     * Initialize elements
     */
    init: function () {

      //Initialize date picker
      this.datepicker();

      /**
       * Begin event listeners
       */
      jQuery(document.body).on(
        'updated_cart_totals',
        this.datepicker
      );

      jQuery(document).on(
        'change input',
        '.woocommerce-cart-form .wrp_date_range_row :input',
        this.input_changed
      );

      /**
       * Trigger cart update on load so that the daterange data can be placed in the cart session
       * Note: Do this only on the cart page with the rental products added
       */
      if (jQuery('tr').is('.wrp_date_range_row')) {
        jQuery(document).ready(function () {
          jQuery('.woocommerce-cart-form :input[name="update_cart"]').trigger('wc_update_cart');
        });
      }

    },

    /**
     * For Daterange picker
     */
    datepicker: function () {

      //Check if object is supplied with value
      if (!window.wrp_date_picker) {
        window.wrp_date_picker = {};
      }

      //Set initial values
      startDate = (window.wrp_date_picker.startDate) ? window.wrp_date_picker.startDate : moment().startOf('hour');
      endDate = (window.wrp_date_picker.endDate) ? window.wrp_date_picker.endDate : moment().startOf('hour').add(32, 'hour');

      jQuery('input[name="wrp_date_range"]').daterangepicker(
        {
          autoApply: true,
          timePicker: true,
          opens: 'center',
          drops: 'down',
          minDate: moment().startOf('hour'),
          startDate: startDate,
          endDate: endDate,
          locale: {
            format: 'M/DD hh:mm A'
          }
        }, function (start, end) {

          //Set object value
          window.wrp_date_picker.startDate = start;
          window.wrp_date_picker.endDate = end;

          //Set the input fields
          jQuery('input[name="wrp_date_start"]').val(start.valueOf());
          jQuery('input[name="wrp_date_end"]').val(end.valueOf());

        }
      );

      //Only do this if the hidden input fields are not yet set
      if (jQuery('input[name="wrp_date_start"]').val() == '' && jQuery('input[name="wrp_date_end"]').val() == '') {

        //Set initial date input variables on datepicker initialization
        init_startDate = jQuery('input[name="wrp_date_range"]').data('daterangepicker').startDate;
        init_endDate = jQuery('input[name="wrp_date_range"]').data('daterangepicker').endDate;

        jQuery('input[name="wrp_date_start"]').val(init_startDate.valueOf());
        jQuery('input[name="wrp_date_end"]').val(init_endDate.valueOf());

      }

    },

    /**
     * After an input is changed, enable the update cart button.
     */
    input_changed: function () {
      jQuery('.woocommerce-cart-form :input[name="update_cart"]').prop('disabled', false);
    }

  };

  wrp_main.init();

});