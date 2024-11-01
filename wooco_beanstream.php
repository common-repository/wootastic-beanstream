<?php
/**
 * Plugin Name:     Wootastic Beanstream
 * Plugin URI:      http://wootastic.co/
 * Description:     Beanstream payment gateway integrated for WooCommerce.
 * Author:          Calvin Canas
 * Author URI:      http://wootastic.co/
 * Text Domain:     wooco_beanstream
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wooco_beanstream
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is active
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

add_action( 'plugins_loaded', 'WOOCO_BEANSTREAM' );

/**
 * Way on how Woocommerce Payment Gateway API works.
 *
 * @see https://docs.woocommerce.com/document/payment-gateway-api/
 */
function WOOCO_BEANSTREAM() {

	/**
	 * Class that enable beanstream as payment gateway.
	 */
	class WOOCO_Beanstream extends WC_Payment_Gateway {

		public $api_version = 'v1';

		public $platform = 'www';

		public $merchant_id = null;

		public $payments_api_passcode = null;

		/**
		 * WOOCO_Beanstream.
		 */
		public function __construct() {

			$this->id = 'wooco_beanstream';
			$this->has_fields = true;
			$this->method_title = __( 'Beanstream', 'wooco_beanstream' );
			$this->method_description = __( 'Beanstream, a Bambora Company, has enabled businesses of every size to receive and make payments online, in-store and in-app since early 2000. We have the largest payment suite in the industry, which means whatever you want to do, we can help. From a small business just getting started to platforms offering payments to their users, we got you covered. Find out how we can be your partner in payments!', 'wooco_beanstream' );


			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
			$this->title          = $this->get_option( 'title' );
			$this->description    = $this->get_option( 'description' );
			$this->testmode       = 'yes' === $this->get_option( 'testmode', 'no' );
			$this->debug          = 'yes' === $this->get_option( 'debug', 'no' );


			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			$this->specific_includes();
			// $this->define_constants();

			$this->merchant_id = $this->get_option( 'beanstream_merchant_id', '' );
			$this->payments_api_passcode = $this->get_option( 'beanstream_payments_api_passcode', '' );
		}

		public function get_supported_cards() {
			return array(
				'visa',
				'amex',
				'discover'
			);
		}

		public function is_card_supported( $type ) {
			return in_array( $type, $this->get_supported_cards() );
		}

		/**
		 * Weird name but I don't want to collide with parent's own includes function
		 *
		 */
		public function specific_includes() {
			include_once 'vendor/autoload.php';
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$this->form_fields = include( 'includes/settings-wooco-beanstream.php' );
		}

		public function add_error_notice( $message ) {
			wc_add_notice( $message, 'error' );
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param 	$order_id
		 * @return 	array
		 */
		public function process_payment( $order_id ) {

			if( empty( $_POST['wooco_beanstream-card-number'] ) ) {
				$this->add_error_notice( 'Please enter a credit card number' );
				return;
			}

			$credit_card_number_result = \Inacho\CreditCard::validCreditCard( $_POST['wooco_beanstream-card-number'] );

			if( false == $credit_card_number_result['valid'] ) {
				$this->add_error_notice( 'Please enter a valid credit card number' );
				return;
			}

			if( false == $this->is_card_supported( $credit_card_number_result['type'] ) ) {
				$this->add_error_notice( 'Sorry but Beanstream only support these cards: Visa, American Express, and Discover' );
				return;
			}

			if( empty( $_POST['wooco_beanstream-card-expiry'] ) ) {
				$this->add_error_notice( 'Please enter a credit card expiration date' );
				return;
			}

			$card_expiry_info = array_filter( explode( '/', $_POST['wooco_beanstream-card-expiry'] ), 'strlen' );

			if( empty( $this->strip_non_numeric_character( $card_expiry_info[0] ) ) ) {
				$this->add_error_notice( 'Please enter a valid expiration month' );
				return;
			}

			if( empty( $this->strip_non_numeric_character( $card_expiry_info[1] ) ) ) {
				$this->add_error_notice( 'Please enter a valid expiration year' );
				return;
			}

			$card_expiry_info_month = $this->strip_non_numeric_character( $card_expiry_info[0] );
			$card_expiry_info_year = $this->strip_non_numeric_character( $card_expiry_info[1] );

			if( 2 !== strlen( $card_expiry_info_month ) ) {
				$this->add_error_notice( 'Invalid month format. Please enter the month like this: 06 for June.' );
				return;
			}

			if( 4 !== strlen( $card_expiry_info_year ) ) {
				$this->add_error_notice( 'Invalid year format. Please enter the year like this: 2020' );
				return;
			}

			if( false === \Inacho\CreditCard::validDate( $card_expiry_info_year, $card_expiry_info_month ) ) {
				$this->add_error_notice( 'The card is already expired.' );
				return;
			}

			if( empty( $_POST['wooco_beanstream-card-cvc'] ) ) {
				$this->add_error_notice( 'Please enter the card cvc' );
				return;
			}

			if( false == \Inacho\CreditCard::validCvc( $_POST['wooco_beanstream-card-cvc'], $credit_card_number_result['type'] ) ) {
				$this->add_error_notice( 'Invalid CVC. Please enter a valid one.' );
				return;
			}

			$card_cvc = $_POST['wooco_beanstream-card-cvc'];
			$card_year = $dt = DateTime::createFromFormat( 'Y', $card_expiry_info_year );
			$order = wc_get_order( $order_id );

			$beanstream = new \Beanstream\Gateway( $this->merchant_id, $this->payments_api_passcode, $this->platform, $this->api_version );

			$payment_data = array(
				'order_number' => $order->get_order_number(),
				'amount' => $order->get_total(),
				'payment_method' => 'card',
				'card' => array(
				    'name' 				=> 			$order->get_formatted_billing_full_name(),
				    'number' 			=> 			$credit_card_number_result['number'],
				    'expiry_month' 		=> 			$card_expiry_info_month,
				    'expiry_year' 		=> 			$card_year->format('y'),
				    'cvd' 				=> 			$card_cvc
				)
			);

			$complete = TRUE;

			try {

			    $result = $beanstream->payments()->makeCardPayment($payment_data, $complete);

			    if( isset( $result['approved'] ) && 1 == $result['approved'] ) {
			    	$order->update_status( 'completed' );
			    }

			    return array (
			    	'result'   => 'success',
			    	'redirect' => $this->get_return_url( $order ),
			    );

			} catch (\Beanstream\Exception $e) {
				$order->update_status( 'failed' );
			    wc_add_notice( $e->getMessage(), 'error' );
			    return;
			}

		}

		public function strip_non_numeric_character( $number ) {
			return preg_replace( '/[^0-9]/', '', $number );
		}

		/**
		 * Method provided by the WC_Payment_Gateway class.
		 * Render the form fields.
		 *
		 * @return void
		 */
		public function payment_fields() {

			$this->form();
		}

		/**
		 * Provide the value for name attribute(html).
		 *
		 * @return false|string
		 */
		public function field_name( $name ) {
			return $this->supports( 'tokenization' ) ? '' : ' name="' . esc_attr( $this->id . '-' . $name ) . '" ';
		}

		public function form() {
			wp_enqueue_script( 'wc-credit-card-form' );

			$fields = array();
			$default_fields = include( 'includes/form-fields-wooco-beanstream.php' );
			$fields = wp_parse_args( $fields, apply_filters( 'woocommerce_credit_card_form_fields', $default_fields, $this->id ) );

			?>

			<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class='wc-credit-card-form wc-payment-form'>
				<?php
					do_action( 'woocommerce_credit_card_form_start', $this->id );
					foreach ( $fields as $field ) {
						echo $field;
					}
					do_action( 'woocommerce_credit_card_form_end', $this->id );
				?>
				<div class="clear"></div>
			</fieldset>
			<?php
		}
	}

}


function wooco_beanstream_add_payment_gateway( $methods ) {
	$methods[] = 'WOOCO_Beanstream';
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'wooco_beanstream_add_payment_gateway' );