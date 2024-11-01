<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


return array(
	'enabled' => array(
			'title' => __( 'Enable/Disable', 'wooco-beanstream' ),
			'type' => 'checkbox',
			'label' => __( 'Enable Beanstream', 'wooco-beanstream' ),
			'default' => 'yes'
	),
	'title' => array(
		'title'   		=> __( 'Title', 'wooco-beanstream' ),
		'type'    		=> 'text',
		'description'   => __( 'This will show on the checkout page as a title of the beanstream payment option.', 'wooco-beanstream' ),
		'default' 		=> 'Beanstream'
	),
	'beanstream_merchant_id' => array(
		'title'   		=> __( 'Merchant ID', 'wooco-beanstream' ),
		'type'    		=> 'text',
		'description'   => __( 'Your Merchant ID was emailed to you when you set up your account but if you lost it you can find it again by heading to the <a href="https://www.beanstream.com/admin/sDefault.asp">Member Area</a>, logging in, and copying your merchant ID located at the top-right corner of the screen.', 'wooco-beanstream' ),
		'default' 		=> ''
	),
	'beanstream_payments_api_passcode' => array(
		'title'   		=> __( 'Payments API Passcode', 'wooco-beanstream' ),
		'type'   		=> 'text',
		'description'   => __( 'Head to the <a href="https://www.beanstream.com/admin/sDefault.asp">Member Area</a> and log in. Then navigate to Administration -> Account -> Order Settings. Locate the API access passcode field and copy the passcode. If one is not there, you can generate a new one by hitting the ‘Generate’ button.', 'wooco-beanstream' ),
		'default' 		=> ''
	),
);