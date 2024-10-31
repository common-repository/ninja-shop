<?php
/**
 * Personal Data Hooks.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop;

// Policy
add_action( 'admin_init', [ PersonalData\Policy::class, 'register' ] );

// Addresses
add_filter( 'wp_privacy_personal_data_erasers', [ PersonalData\AddressEraser::class, 'register' ] );

// Transactions
add_filter( 'wp_privacy_personal_data_erasers', [ PersonalData\TransactionEraser::class, 'register' ] );
add_filter( 'wp_privacy_personal_data_exporters', [ PersonalData\TransactionExporter::class, 'register' ] );
