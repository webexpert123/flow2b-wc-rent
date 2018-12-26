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

        }
      );

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