jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle Stripe admin functions.
	 */
	var wc_busha_admin = {
		isTestMode: function() {
			return $( '#woocommerce_busha_testmode' ).is( ':checked' );
		},

		getSecretKey: function() {
			if ( wc_busha_admin.isTestMode() ) {
				return $( '#woocommerce_busha_test_api_key' ).val();
			} else {
				return $( '#woocommerce_busha_api_key' ).val();
			}
		},

		/**
		 * Initialize.
		 */
		init: function() {
			$( document.body ).on( 'change', '#woocommerce_busha_testmode', function() {
				var test_secret_key = $( '#woocommerce_busha_test_api_key' ).parents( 'tr' ).eq( 0 ),
					test_publishable_key = $( '#woocommerce_busha_test_webhook_secret' ).parents( 'tr' ).eq( 0 ),
					
					live_secret_key = $( '#woocommerce_busha_api_key' ).parents( 'tr' ).eq( 0 ),
					live_publishable_key = $( '#woocommerce_busha_webhook_secret' ).parents( 'tr' ).eq( 0 );
					

				if ( $( this ).is( ':checked' ) ) {
					test_secret_key.show();
					test_publishable_key.show();
					live_secret_key.hide();
					live_publishable_key.hide();
				} else {
					test_secret_key.hide();
					test_publishable_key.hide();
					live_secret_key.show();
					live_publishable_key.show();
				}
			} );

			$( '#woocommerce_busha_testmode' ).change();

			
		}
	};

	wc_busha_admin.init();
} );
