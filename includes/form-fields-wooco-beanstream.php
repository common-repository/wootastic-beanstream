<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$escaped_id = esc_attr( $this->id );

return array(

    'card-number-field' => '<p class="form-row form-row-wide"> <label for="' . $escaped_id . '-card-number">' . __( 'Card Number', 'woocommerce' ) . ' <span class="required">*</span></label> <input id="' . $escaped_id . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name( 'card-number' ) . ' /> </p>',

    'card-expiry-field' => '<p class="form-row form-row-first"> <label for="' . $escaped_id . '-card-expiry">' . __( 'Expiry (MM/YY)', 'woocommerce' ) . ' <span class="required">*</span></label> <input id="' . $escaped_id . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="' . esc_attr__( 'MM / YY', 'woocommerce' ) . '" ' . $this->field_name( 'card-expiry' ) . ' /></p>',

    'card-cvc-field' => '<p class="form-row form-row-last"> <label for="' . $escaped_id . '-card-cvc">' . __( 'CVC/CVD', 'woocommerce' ) . ' <span class="required">*</span></label> <input id="' . $escaped_id . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="' . esc_attr__( 'CVC', 'woocommerce' ) . '" ' . $this->field_name( 'card-cvc' ) . ' /> </p>'
);