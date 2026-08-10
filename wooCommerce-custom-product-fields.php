<?php
/**
 * Plugin Name: WooCommerce Custom Product Fields
 * Plugin URI: https://imran1.com/
 * Description: Adds a required product field and file upload field to WooCommerce product pages.
 * Version: 1.0.0
 * Author: Imran Khan
 * Author URI: https://imran1.com/
 * Text Domain: woo-custom-product-fields
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Check if WooCommerce is active.
 */
add_action( 'plugins_loaded', 'wc_custom_search_check_woocommerce' );

function wc_custom_search_check_woocommerce() {

	if ( ! class_exists( 'WooCommerce' ) ) {

		add_action( 'admin_notices', function() {
			?>
			<div class="notice notice-error">
				<p>
					<strong>WC Custom Search:</strong>
					WooCommerce must be installed and activated for this plugin to work.
				</p>
			</div>
			<?php
		} );

		return;
	}
}


/**
 * ---------------------------------------------------------
 * 1. Display Custom Fields on Product Page
 * ---------------------------------------------------------
 */
function wcpf_add_custom_fields_product_detail_page() {

	$product_field = '';

	if ( isset( $_POST['product_field'] ) ) {

		$product_field = sanitize_text_field(
			wp_unslash( $_POST['product_field'] )
		);
	}

	?>

	<div class="wcpf-custom-fields">

		<p>
			<strong>
				<label for="product_field">
					<?php esc_html_e( 'Product Field:', 'woo-custom-product-fields' ); ?>
				</label>
			</strong>

			<input
				type="text"
				id="product_field"
				name="product_field"
				value="<?php echo esc_attr( $product_field ); ?>"
			/>
		</p>

		<p>
			<strong>
				<label for="product_file_upload">
					<?php esc_html_e( 'Upload a File:', 'woo-custom-product-fields' ); ?>
				</label>
			</strong>

			<input
				type="file"
				id="product_file_upload"
				name="product_file_upload"
			/>
		</p>

	</div>

	<?php
}

add_action(
	'woocommerce_before_add_to_cart_button',
	'wcpf_add_custom_fields_product_detail_page'
);


/**
 * ---------------------------------------------------------
 * 2. Validate Custom Fields
 * ---------------------------------------------------------
 */
function wcpf_validate_custom_fields_product_detail_page(
	$passed,
	$product_id,
	$quantity
) {

	/**
	 * Validate Product Field.
	 */
	$product_field = '';

	if ( isset( $_POST['product_field'] ) ) {

		$product_field = sanitize_text_field(
			wp_unslash( $_POST['product_field'] )
		);
	}

	if ( empty( $product_field ) ) {

		wc_add_notice(
			__( 'Product Field is Empty.', 'woo-custom-product-fields' ),
			'error'
		);

		$passed = false;
	}


	/**
	 * Validate File Upload.
	 */
	if (
		! isset( $_FILES['product_file_upload'] ) ||
		empty( $_FILES['product_file_upload']['name'] )
	) {

		wc_add_notice(
			__(
				'Please attach a file to upload.',
				'woo-custom-product-fields'
			),
			'error'
		);

		$passed = false;
	}

	return $passed;
}

add_filter(
	'woocommerce_add_to_cart_validation',
	'wcpf_validate_custom_fields_product_detail_page',
	10,
	3
);


/**
 * ---------------------------------------------------------
 * 3. Save Custom Fields to Cart Item
 * ---------------------------------------------------------
 */
function wcpf_add_custom_product_field_single_page(
	$cart_item_data,
	$product_id
) {

	/**
	 * Save Product Field.
	 */
	if ( isset( $_POST['product_field'] ) ) {

		$cart_item_data['product_field'] = sanitize_text_field(
			wp_unslash( $_POST['product_field'] )
		);
	}


	/**
	 * Upload Product File.
	 */
	if (
		isset( $_FILES['product_file_upload'] ) &&
		! empty( $_FILES['product_file_upload']['name'] )
	) {

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';


		/**
		 * Upload options.
		 */
		$upload_overrides = array(
			'test_form' => false,
		);


		/**
		 * Upload file.
		 *
		 * Second parameter is the post ID.
		 * 0 means no parent post.
		 */
		$attachment_id = media_handle_upload(
			'product_file_upload',
			0,
			array(),
			$upload_overrides
		);


		/**
		 * Check upload error.
		 */
		if ( ! is_wp_error( $attachment_id ) ) {

			/**
			 * Store attachment ID.
			 */
			$cart_item_data['product_file_upload'] = absint(
				$attachment_id
			);
		}
	}


	/**
	 * Make customized products unique in cart.
	 */
	$cart_item_data['wcpf_unique_key'] = md5(
		uniqid( '', true )
	);


	return $cart_item_data;
}

add_filter(
	'woocommerce_add_cart_item_data',
	'wcpf_add_custom_product_field_single_page',
	10,
	2
);


/**
 * ---------------------------------------------------------
 * 4. Display Custom Fields in Cart
 * ---------------------------------------------------------
 */
function wcpf_display_in_cart(
	$item_data,
	$cart_item
) {

	/**
	 * Display Product Field.
	 */
	if ( isset( $cart_item['product_field'] ) ) {

		$item_data[] = array(
			'key'   => __(
				'Product Field',
				'woo-custom-product-fields'
			),
			'value' => esc_html(
				$cart_item['product_field']
			),
		);
	}


	/**
	 * Display Uploaded Product File.
	 */
	if ( ! empty( $cart_item['product_file_upload'] ) ) {

		$attachment_id = absint(
			$cart_item['product_file_upload']
		);

		$file_url = wp_get_attachment_url(
			$attachment_id
		);


		if ( $file_url ) {

			$file_name = basename(
				wp_parse_url(
					$file_url,
					PHP_URL_PATH
				)
			);

			$item_data[] = array(
				'key'     => __(
					'Uploaded File',
					'woo-custom-product-fields'
				),

				'value'   => sprintf(
					'<a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
					esc_url( $file_url ),
					esc_html( $file_name )
				),

				'display' => '',
			);
		}
	}


	return $item_data;
}

add_filter(
	'woocommerce_get_item_data',
	'wcpf_display_in_cart',
	10,
	2
);


/**
 * ---------------------------------------------------------
 * 5. Save Custom Fields to Order Item
 * ---------------------------------------------------------
 */
function wcpf_add_to_order_items(
	$item,
	$cart_item_key,
	$values,
	$order
) {

	/**
	 * Save Product Field.
	 */
	if ( isset( $values['product_field'] ) ) {

		$item->add_meta_data(
			__(
				'Product Field',
				'woo-custom-product-fields'
			),
			sanitize_text_field(
				$values['product_field']
			)
		);
	}


	/**
	 * Save Uploaded Product File.
	 */
	if ( ! empty( $values['product_file_upload'] ) ) {

		$attachment_id = absint(
			$values['product_file_upload']
		);

		$file_url = wp_get_attachment_url(
			$attachment_id
		);


		if ( $file_url ) {

			$item->add_meta_data(
				__(
					'Uploaded File',
					'woo-custom-product-fields'
				),
				esc_url_raw( $file_url )
			);
		}
	}
}

add_action(
	'woocommerce_checkout_create_order_line_item',
	'wcpf_add_to_order_items',
	10,
	4
);
```
