<?php
/**
 * Simplified checkout field customisation.
 *
 * Merges first/last name, makes email optional, removes clutter,
 * and splits full_name back on order save.
 *
 * Section headings and visual grouping are handled by JS + CSS
 * to avoid DOM nesting issues with WooCommerce's field wrapper.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =====================================================================
 * 1. MODIFY CHECKOUT FIELDS
 * =================================================================== */

/**
 * Customise WooCommerce checkout fields.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function hp_customise_checkout_fields( $fields ) {

	// ---- Remove unnecessary fields ----
	unset( $fields['billing']['billing_company'] );
	unset( $fields['billing']['billing_address_2'] );
	unset( $fields['billing']['billing_postcode'] );

	// ---- Replace first + last name with full name ----
	unset( $fields['billing']['billing_first_name'] );
	unset( $fields['billing']['billing_last_name'] );

	$fields['billing']['billing_full_name'] = array(
		'type'         => 'text',
		'label'        => __( 'Full Name', 'hitprice' ),
		'placeholder'  => __( 'Full Name', 'hitprice' ),
		'required'     => true,
		'class'        => array( 'form-row-wide', 'hp-co-field' ),
		'priority'     => 10,
		'autocomplete' => 'name',
	);

	// ---- Phone — required, primary contact ----
	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['required']    = true;
		$fields['billing']['billing_phone']['priority']    = 20;
		$fields['billing']['billing_phone']['placeholder'] = __( 'Phone Number', 'hitprice' );
		$fields['billing']['billing_phone']['label']       = __( 'Phone', 'hitprice' );
		$fields['billing']['billing_phone']['class']       = array( 'form-row-wide', 'hp-co-field' );
	}

	// ---- Email — optional, hidden by default ----
	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['required']    = false;
		$fields['billing']['billing_email']['priority']    = 30;
		$fields['billing']['billing_email']['placeholder'] = __( 'Email Address', 'hitprice' );
		$fields['billing']['billing_email']['label']       = __( 'Email', 'hitprice' );
		$fields['billing']['billing_email']['class']       = array( 'form-row-wide', 'hp-co-field', 'hp-co-email-wrap' );
	}

	// ---- Address ----
	if ( isset( $fields['billing']['billing_address_1'] ) ) {
		$fields['billing']['billing_address_1']['priority']    = 40;
		$fields['billing']['billing_address_1']['placeholder'] = __( 'House, Street, Area', 'hitprice' );
		$fields['billing']['billing_address_1']['label']       = __( 'Full Address', 'hitprice' );
		$fields['billing']['billing_address_1']['class']       = array( 'form-row-wide', 'hp-co-field' );
	}

	if ( isset( $fields['billing']['billing_city'] ) ) {
		$fields['billing']['billing_city']['priority']    = 50;
		$fields['billing']['billing_city']['placeholder'] = __( 'City', 'hitprice' );
		$fields['billing']['billing_city']['class']       = array( 'form-row-wide', 'hp-co-field' );
	}

	if ( isset( $fields['billing']['billing_state'] ) ) {
		$fields['billing']['billing_state']['priority'] = 60;
		$fields['billing']['billing_state']['label']    = __( 'Province', 'hitprice' );
		$fields['billing']['billing_state']['class']    = array( 'form-row-first', 'hp-co-field' );
	}

	if ( isset( $fields['billing']['billing_country'] ) ) {
		$fields['billing']['billing_country']['priority'] = 70;
		$fields['billing']['billing_country']['label']    = __( 'Country', 'hitprice' );
		$fields['billing']['billing_country']['class']    = array( 'form-row-last', 'hp-co-field' );
	}

	// ---- Remove shipping fields (billing = shipping) ----
	$fields['shipping'] = array();

	// ---- Remove order comments ----
	unset( $fields['order']['order_comments'] );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'hp_customise_checkout_fields', 20 );

/**
 * Override WooCommerce country locale to force Province label and half-width class.
 *
 * WooCommerce dynamically replaces state field label/class via JS based on
 * selected country. This filter ensures our labels and layout persist.
 *
 * @param array $locale Country locale data.
 * @return array
 */
function hp_override_country_locale( $locale ) {
	foreach ( $locale as $country_code => $fields ) {
		$locale[ $country_code ]['state'] = array(
			'label'    => __( 'Province', 'hitprice' ),
			'required' => true,
			'class'    => array( 'form-row-first', 'hp-co-field' ),
		);
	}

	// Also override the default locale.
	$locale['default']['state'] = array(
		'label'    => __( 'Province', 'hitprice' ),
		'required' => true,
		'class'    => array( 'form-row-first', 'hp-co-field' ),
	);

	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'hp_override_country_locale', 20 );

/* =====================================================================
 * 2. FORCE BILLING = SHIPPING
 * =================================================================== */

/**
 * Hide the "ship to different address" checkbox.
 *
 * @return bool
 */
function hp_ship_to_billing_only() {
	return false;
}
add_filter( 'woocommerce_cart_needs_shipping_address', 'hp_ship_to_billing_only' );

/**
 * Copy billing fields to shipping on the order.
 *
 * @param \WC_Order $order Order object.
 * @param array     $data  Posted checkout data.
 */
function hp_copy_billing_to_shipping( $order, $data ) {
	$order->set_shipping_address_1( $order->get_billing_address_1() );
	$order->set_shipping_city( $order->get_billing_city() );
	$order->set_shipping_state( $order->get_billing_state() );
	$order->set_shipping_country( $order->get_billing_country() );
	$order->set_shipping_first_name( $order->get_billing_first_name() );
	$order->set_shipping_last_name( $order->get_billing_last_name() );
}
add_action( 'woocommerce_checkout_create_order', 'hp_copy_billing_to_shipping', 20, 2 );

/* =====================================================================
 * 3. SPLIT FULL NAME → FIRST + LAST ON ORDER SAVE
 * =================================================================== */

/**
 * Before order is created, split billing_full_name into first/last.
 *
 * @param \WC_Order $order Order object.
 * @param array     $data  Posted checkout data.
 */
function hp_split_full_name_on_order( $order, $data ) {
	$full_name = isset( $data['billing_full_name'] ) ? sanitize_text_field( $data['billing_full_name'] ) : '';

	if ( ! $full_name ) {
		return;
	}

	$parts      = explode( ' ', $full_name, 2 );
	$first_name = $parts[0];
	$last_name  = isset( $parts[1] ) ? $parts[1] : '';

	$order->set_billing_first_name( $first_name );
	$order->set_billing_last_name( $last_name );
}
add_action( 'woocommerce_checkout_create_order', 'hp_split_full_name_on_order', 10, 2 );

/**
 * Add billing_full_name to the list of posted data keys WooCommerce processes.
 *
 * @param array $data Posted data.
 * @return array
 */
function hp_add_full_name_to_posted_data( $data ) {
	if ( isset( $_POST['billing_full_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce.
		$data['billing_full_name'] = sanitize_text_field( wp_unslash( $_POST['billing_full_name'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'hp_add_full_name_to_posted_data' );

/* =====================================================================
 * 4. VALIDATION
 * =================================================================== */

/**
 * Validate full name field.
 */
function hp_validate_full_name() {
	if ( empty( $_POST['billing_full_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		wc_add_notice( __( 'Please enter your full name.', 'hitprice' ), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'hp_validate_full_name' );
