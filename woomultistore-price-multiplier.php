<?php
/**
 * Plugin Name: WooMultistore Price Multiplier
 * Plugin URI: https://woomultistore.com/
 * Description: An addon for WooMultistore to multiply the price by a given percentage.
 * Author: Lykke Media AS
 * Version: 1.0.2
 * Author URI: https://woomultistore.com/
 */


final class WOOMULTISTORE_PRICE_MULTIPLIER {
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		/**
		 * Run the code after WordPress has been initialized.
		 */
		add_action( 'init', array( $this, 'init' ), PHP_INT_MAX, 0 );
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_submenu' ), PHP_INT_MAX );
			add_action( 'WOO_MSTORE_admin_product/slave_product_updated', array( $this, 'multiply_price' ), PHP_INT_MAX, 1 );
		} else {
			if ( get_option( 'woonet_network_type' ) == 'master' ) {
				add_action( 'admin_menu', array( $this, 'add_submenu' ), PHP_INT_MAX );
			} else {
				// hook on child site to save options.
				add_action( 'WOO_MSTORE_SYNC/CUSTOM/price_multiplier_settings', array( $this, 'save_price_multiplier_settings' ), PHP_INT_MAX );
				add_action( 'WOO_MSTORE_SYNC/sync_child/complete', array( $this, 'multiply_price' ), PHP_INT_MAX, 3 );
			}
		}
	}

	/**
	 * add_submenu
	 *
	 * @return void
	 */
	public function add_submenu() {
		$hookname = add_submenu_page(
			'woonet-woocommerce',
			'Price Multiplier',
			'Price Multiplier',
			'manage_options',
			'woonet-price-multiplier',
			array( $this, 'submenu_callback' )
		);

		add_action( 'load-' . $hookname, array( $this, 'submenu_hook' ) );
	}

	/**
	 * submenu_callback
	 *
	 * @return void
	 */
	public function submenu_callback() {
		require_once dirname( __FILE__ ) . '/template.php';
	}

	/**
	 * submenu_hook
	 *
	 * @return void
	 */
	public function submenu_hook() {
		if ( ! empty( $_POST['mstore_price_multiplier_submit'] ) ) {
			if ( is_multisite() ) {
				update_site_option( 'woonet_price_multiplier', $_POST['woomulti_price_multiplier'] );
			} else {
				update_option( 'woonet_price_multiplier', $_POST['woomulti_price_multiplier'] );

				$data = array(
					'payload_contents' => $_POST['woomulti_price_multiplier'],
					'payload_type'     => 'price_multiplier_settings',
				);

				$_engine = new WOO_MSTORE_SINGLE_NETWORK_SYNC_ENGINE();
				$_engine->request_child( 'woomulti_custom_payload', $data );
			}
		}
	}

	/**
	 * save_price_multiplier_settings
	 *
	 * @return void
	 */
	public function save_price_multiplier_settings( $data ) {
		if ( ! empty( $data['payload_contents'] ) ) {
			update_option( 'woonet_price_multiplier', $data['payload_contents'] );
		}
	}

	/**
	 * multiply_price
	 *
	 * @return void
	 */
	public function multiply_price( $product_id, $parent_id = null, $product = null ) {

		if ( is_multisite() ) {
			/**
			 * If multisite product data with array is passed instead of $product_id
			 * We neeed to get the product ID from the slave product data.
			 */
			$product_id = $product_id['slave_product']->get_id();
		}

		$wc_product = wc_get_product( $product_id );

		if ( is_multisite() ) {
			$price_settings        = get_site_option( 'woonet_price_multiplier' );
			$woonet_master_connect = array(
				'uuid' => get_current_blog_id(),
			);
		} else {
			$price_settings        = get_option( 'woonet_price_multiplier' );
			$woonet_master_connect = get_option( 'woonet_master_connect' );
		}

		if ( $wc_product && ! empty( $price_settings [ $woonet_master_connect['uuid'] ] ) ) {
			if ( $price_settings [ $woonet_master_connect['uuid'] ] == 0 || empty( $price_settings [ $woonet_master_connect['uuid'] ] ) ) {
				return;
			}

			$multiplier = (int) $price_settings [ $woonet_master_connect['uuid'] ] / 100;

			if ( $wc_product->get_regular_price() > 0 ) {
				$wc_product->set_regular_price( $wc_product->get_regular_price() + $wc_product->get_regular_price() * $multiplier );
				$wc_product->save();
			}

			if ( $wc_product->get_sale_price() > 0 ) {
				$wc_product->set_sale_price( $wc_product->get_sale_price() + $wc_product->get_sale_price() * $multiplier );
				$wc_product->save();
			}

			if ( $wc_product->get_type() == 'variable' ) {
				$variations = $wc_product->get_available_variations();

				if ( ! empty( $variations ) ) {
					foreach ( $variations as $variation ) {
						$variation = wc_get_product( $variation['variation_id'] );

						if ( $variation->get_regular_price() > 0 ) {
							$variation->set_regular_price( $variation->get_regular_price() + $variation->get_regular_price() * $multiplier );
							$variation->save();
						}

						if ( $variation->get_sale_price() > 0 ) {
							$variation->set_sale_price( $variation->get_sale_price() + $variation->get_sale_price() * $multiplier );
							$variation->save();
						}
					}
				}
			}
		}
	}
}

new WOOMULTISTORE_PRICE_MULTIPLIER();
