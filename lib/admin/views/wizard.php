<?php
/**
 * This file prints the wizard page in the Admin
 *
 *
 * @package IT_Exchange
*/
$flat_rate_cost = it_exchange_get_option( 'simple-shipping', true );
$flat_rate_cost = empty( $flat_rate_cost['flat-rate-shipping-amount'] ) ? 500 : $flat_rate_cost['flat-rate-shipping-amount'];
$flat_rate_cost = it_exchange_format_price( it_exchange_convert_from_database_number( $flat_rate_cost ) );
?>
<div class="wrap">
	<?php ITUtility::screen_icon( 'it-exchange' );  ?>

	<h2>Ninja Shop <?php _e( 'Setup', 'it-l10n-ithemes-exchange' ); ?></h2>

	<?php $form->start_form( $form_options, 'exchange-general-settings' ); ?>
		<div class="it-exchange-wizard">
			<div class="welcome">
				<div class="welcome-title"><p class="section-label"><?php printf( __( 'Welcome to %s', 'it-l10n-ithemes-exchange' ), 'Ninja Shop' ); ?></p></div>
				<p>
					<?php printf( __( 'You can customize your store features by filling out some information below. (Don\'t worry, you can always change these settings on the Feautures page and the %s Settings page later. And you can get back to this setup screen from the link on the help page.) If you have features, please go to the %splugins page%s and install them now before starting the setup.' ), 'Ninja Shop', '<a href="' . admin_url( 'plugins.php' ) . '">', '</a>' ); ?>
			</div>
			<div class="fields">
				<div class="field product-types">
					<p class="section-label"><?php _e( 'What are you going to sell?', 'it-l10n-ithemes-exchange' ); ?><span class="tip" title="<?php _e( "You can always add or remove these later on the Features page.", 'it-l10n-ithemes-exchange' ); ?>">i</span></p>
					<ul class="clearfix">
						<?php
							$addons = it_exchange_get_addons( array( 'category' => 'product-type', 'show_required' => false ) );
							if ( isset( $addons['simple-product-type'] ) )
								unset( $addons['simple-product-type'] );

							$show_shipping = 'hide-if-js';
							it_exchange_temporarily_load_addons( $addons );
							foreach( (array) $addons as $addon ) {
								if ( ! empty( $addon['options']['wizard-icon'] ) ) {
									$name  = '<img src="' . $addon['options']['wizard-icon'] . '" alt="' . $addon['name'] . '" />';
									$name .= '<span class="product-name">' . $addon['name'] . '</span>';
								} else {
									$name = $addon['name'];
								}

								if ( it_exchange_is_addon_enabled( $addon['slug'] ) )
									$selected_class = 'selected';
								else
									$selected_class = '';

								$toggle_ships = 'physical-product-type' == $addon['slug'] ? ' data-ships="shipping-types"' : '';
								if ( 'physical-product-type' == $addon['slug'] ) {
									$show_shipping = empty( $selected_class ) ? 'hide-if-js' : '';
								}

								$core_external_class = it_exchange_is_core_addon( $addon['slug'] ) ? 'core-product-type' : 'external-product-type';
								echo '<li class="product-option ' . $addon['slug'] . '-product-option ' . $core_external_class . ' ' . $selected_class . '" product-type="' . $addon['slug']. '" data-toggle="' . $addon['slug'] . '-wizard"' . $toggle_ships . '>';
								echo '<div class="option-spacer">';
								echo $name;
								echo '</div>';
								if ( ! empty( $selected_class ) )
									echo '<input class="enable-' . esc_attr( $addon['slug'] ) . '" type="hidden" name="it-exchange-product-types[]" value="' . esc_attr( $addon['slug'] ) . '" />';
								echo '</li>';
							}
						?>

						<?php if ( ! it_exchange_is_addon_registered( 'membership-product-type' ) ) : ?>
						<!--
							<li class="membership-product-option inactive" data-toggle="membership-wizard">
								<div class="option-spacer">
									<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/wizard-membership.png' ); ?>" alt="<?php _e( 'Membership', 'it-l10n-ithemes-exchange' ); ?>" />
									<span class="product-name"><?php _e( 'Membership', 'it-l10n-ithemes-exchange' ); ?></span>
									<span class="product-paid-sign">$</span>
								</div>
							</li>
						-->
						<?php endif; ?>

						<!--
						<li class="product-option pro-pack-product-option" data-toggle="pro-pack-wizard">
							<div class="option-spacer">
								<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/wizard-pro-pack.png' ); ?>" alt="<?php _e( 'Get more Features', 'it-l10n-ithemes-exchange' ); ?>" />
								<span class="product-name"><?php _e( 'Get more Features', 'it-l10n-ithemes-exchange' ); ?></span>
							</div>
						</li>
						-->

					</ul>
				</div>

				<!--
				<div class="field pro-pack-wizard inactive hide-if-js">
					<div class="pro-pack-left-column">
						<h3><?php _e( 'Do more with your store with the Pro Pack', 'it-l10n-ithemes-exchange' ); ?></h3>
						<p><?php _e( 'Membership, Invoices, Stripe Payments, MailChimp Integration and more!', 'it-l10n-ithemes-exchange' ); ?></p>
					</div>
					<div class="pro-pack-right-column">
						<span class="pro-pack-coupon"><?php _e( 'Use Code GOPRO25 to get 25% off!', 'it-l10n-ithemes-exchange' ); ?></span><br />
						<a href="http://ithemes.com/exchange/#pricing" target="_blank" class="pro-pack-cta"><?php _e( 'Get the Pro Pack', 'it-l10n-ithemes-exchange' ); ?></a>
					</div>
				</div>
				-->

				<?php if ( ! it_exchange_is_addon_registered( 'membership-product-type' ) ) : ?>
				<!--
					<div class="field membership-wizard inactive hide-if-js">
						<h3><?php _e( 'Membership', 'it-l10n-ithemes-exchange' ); ?></h3>
						<p><?php _e( 'To use Membership, you need to install the Membership add-on.', 'it-l10n-ithemes-exchange' ); ?></p>
						<div class="membership-action activate-membership">
							<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/plugin32.png' ); ?>" />
							<p><?php _e( 'I have the Membership add-on and just need to install and/or activate it.', 'it-l10n-ithemes-exchange' ); ?></p>
							<p><a href="<?php echo admin_url( 'plugins.php' ); ?>" target="_self"><?php _e( 'Go to the plugins page', 'it-l10n-ithemes-exchange' ); ?></a></p>
						</div>
						<div class="membership-action buy-membership">
							<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/icon32.png' ); ?>" />
							<p><?php _e( "I don't have the Membership add-on yet, but I want to use Membership.", 'it-l10n-ithemes-exchange' ); ?></p>
							<p><a href="http://ithemes.com/purchase/membership-add-on/" target="_blank"><?php _e( 'Get the Membership Add-on', 'it-l10n-ithemes-exchange' ); ?></a></p>
						</div>
					</div>
				-->
				<?php endif; ?>

				<?php
				foreach( (array) $addons as $addon ) {
					do_action( 'ninja_shop_print_' . $addon['slug'] . '_wizard_settings', $form );
				}
				?>

				<div class="field shipping-types <?php esc_attr_e( $show_shipping ); ?>">
					<p class="section-label"><?php _e( 'How will you ship your products?', 'it-l10n-ithemes-exchange' ); ?><span class="tip" title="<?php _e( "You can always add or remove these later on the Shipping Settings page.", 'it-l10n-ithemes-exchange' ); ?>">i</span></p>
					<ul class="clearfix">
						<?php
							$addons = it_exchange_get_addons( array( 'category' => 'shipping', 'show_required' => false ) );
							it_exchange_temporarily_load_addons( $addons );

							// Add Simple Shipping's free and flat rate methods as providers since Brad thinks they're so special
							$flat_rate_selected = 'hide-if-js';
							$addons['simple-shipping-flat-rate'] = array(
								'name' => __( 'Flat Rate', 'it-l10n-ithemes-exchange' ),
								'slug' => 'simple-shipping-flat-rate',
								'options' => array( 'wizard-icon' => false ),
							);
							$addons['simple-shipping-free'] = array(
								'name' => __( 'Free Shipping', 'it-l10n-ithemes-exchange' ),
								'slug' => 'simple-shipping-free',
								'options' => array( 'wizard-icon' => false ),
							);

							// Loop through them and print their settings
							foreach( (array) $addons as $addon ) {
								// Skip simple shipping
								if ( 'simple-shipping' == $addon['slug'] )
									continue;

								if ( ! empty( $addon['options']['wizard-icon'] ) )
									$name = '<img src="' . $addon['options']['wizard-icon'] . '" alt="' . $addon['name'] . '" />';
								else
									$name = $addon['name'];

								if ( it_exchange_is_addon_enabled( $addon['slug'] ) )
									$selected_class = 'selected';
								else
									$selected_class = '';

								// Set selected for free and flat rate
								if ( 'simple-shipping-free' == $addon['slug'] || 'simple-shipping-flat-rate' == $addon['slug'] ) {
									$option_key = ( 'simple-shipping-free' == $addon['slug'] ) ? 'enable-free-shipping' : 'enable-flat-rate-shipping';
									$simple_shipping_options = it_exchange_get_option( 'simple-shipping' );
									$selected_class = it_exchange_is_addon_enabled( 'simple-shipping' ) && ! empty( $simple_shipping_options[$option_key] ) ? 'selected' : '';
								}

								if ( 'simple-shipping-flat-rate' == $addon['slug'] && ! empty( $selected_class ) )
									$flat_rate_selected = '';

								echo '<li class="shipping-option ' . $addon['slug'] . '-shipping-option ' . $selected_class . '" shipping-method="' . $addon['slug']. '" data-toggle="' . $addon['slug'] . '-wizard">';
								echo '<div class="option-spacer">';
								echo $name;
								echo '</div>';
								if ( $selected_class )
									echo '<input class="enable-' . esc_attr( $addon['slug'] ) . '" type="hidden" name="it-exchange-shipping-methods[]" value="' . esc_attr( $addon['slug'] ) . '" />';
								echo '</li>';
							}
						?>
					</ul>

                    <div class="field simple-shipping-flat-rate-wizard <?php esc_attr_e( $flat_rate_selected ); ?>">
                        <h3><?php _e( 'Flat Rate Shipping', 'it-l10n-ithemes-exchange' ); ?></h3>
                        <p>
                            <label for="simple-shipping-flat-rate-cost"><?php _e( 'Flat Rate Default Amount', 'it-l10n-ithemes-exchange' ); ?><span class="tip" title="<?php _e( 'Default shipping costs for flat rate. Multiplied by quantity purchased. Customizable per product by Store Admin.', 'it-l10n-ithemes-exchange' ); ?>" >i</span></label>
							<input class="normal-text" type="text" name="it_exchange_settings-simple-shipping-flat-rate-cost" id="simple-shipping-flat-rate-cost" value="<?php esc_attr_e( $flat_rate_cost ); ?>" />
                        </p>
                    </div>
				</div>

				<?php
				foreach( (array) $addons as $addon ) {
					do_action( 'ninja_shop_print_' . $addon['slug'] . '_wizard_settings', $form );
				}
				?>

				<div class="field payments">
					<p class="section-label"><?php _e( 'How will you accept payments?', 'it-l10n-ithemes-exchange' ); ?><span class="tip" title="<?php _e( "Choose your preferred payment gateway for processing transactions. You can select more than one option but it's not recommended.", 'it-l10n-ithemes-exchange' ); ?>">i</span></p>
					<ul class="clearfix">
						<?php
							$addons = it_exchange_get_addons( array( 'category' => 'transaction-methods', 'show_required' => false ) );
							it_exchange_temporarily_load_addons( $addons );

							// This action is documented in lib/gateways/load.php
							do_action( 'ninja_shop_register_gateways', new ITE_Gateways() );
							foreach( (array) $addons as $addon ) {
								if ( ! empty( $addon['options']['wizard-icon'] ) )
									$name = '<img src="' . $addon['options']['wizard-icon'] . '" alt="' . $addon['name'] . '" />';
								else
									$name = $addon['name'];

								if ( it_exchange_is_addon_enabled( $addon['slug'] ) )
									$selected_class = 'selected';
								else
									$selected_class = '';

								echo '<li class="payoption ' . $addon['slug'] . '-payoption ' . $selected_class . '" transaction-method="' . $addon['slug']. '" data-toggle="' . $addon['slug'] . '-wizard">';
								echo '<div class="option-spacer">';
								echo $name;
								echo '<input type="hidden" class="remove-if-js" name="it-exchange-transaction-methods[]" value="' . $addon['slug'] . '" />';
								echo '</div>';
								echo '</li>';
							}
						?>

						<?php if ( ! it_exchange_is_addon_registered( 'stripe' ) ) : ?>
						<!--
							<li class="stripe-payoption inactive" data-toggle="stripe-wizard">
								<div class="option-spacer">
									<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/stripe32.png' ); ?>" alt="<?php _e( 'Stripe', 'it-l10n-ithemes-exchange' ); ?>" />
								</div>
							</li>
						-->
						<?php endif; ?>
					</ul>
				</div>

				<?php if ( ! it_exchange_is_addon_registered( 'stripe' ) ) : ?>
					<!--
					<div class="field stripe-wizard inactive hide-if-js">
						<h3><?php _e( 'Stripe', 'it-l10n-ithemes-exchange' ); ?></h3>
						<p><?php _e( 'To use Stripe, you need to install the Stripe add-on.', 'it-l10n-ithemes-exchange' ); ?></p>
						<div class="stripe-action activate-stripe">
							<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/plugin32.png' ); ?>" />
							<p><?php _e( 'I have the Stripe add-on and just need to install and/or activate it.', 'it-l10n-ithemes-exchange' ); ?></p>
							<p><a href="<?php echo admin_url( 'plugins.php' ); ?>" target="_self"><?php _e( 'Go to the plugins page', 'it-l10n-ithemes-exchange' ); ?></a></p>
						</div>
						<div class="stripe-action buy-stripe">
							<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/icon32.png' ); ?>" />
							<p><?php _e( "I don't have the Stripe add-on yet, but I want to use Stripe.", 'it-l10n-ithemes-exchange' ); ?></p>
							<p><a href="http://ithemes.com/exchange/stripe/" target="_blank"><?php _e( 'Get the free Stripe Add-on', 'it-l10n-ithemes-exchange' ); ?></a></p>
						</div>
					</div>
					-->
				<?php endif; ?>

				<?php
				foreach( (array) $addons as $addon ) {
					do_action( 'ninja_shop_print_' . $addon['slug'] . '_wizard_settings', $form );
				}
				?>

				<div class="field general-settings-wizard">
					<h3><?php _e( 'General', 'it-l10n-ithemes-exchange' ); ?></h3>
					<label for="company-email"><?php _e( 'E-mail Notifications', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'At what email address would you like to receive store notifications?', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
					<?php $form->add_text_box( 'company-email', array( 'value' => get_bloginfo( 'admin_email' ), 'class' => 'clearfix' ) ); ?>
					<p>
						<?php $form->add_check_box( 'email-notifications' ); ?>
						<label for="exchange-notifications"><?php _e( 'Get e-mail updates from us about Ninja Shop', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Subscribe to get Ninja Shop news, updates, discounts and swag &hellip; oh, and our endless love.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
					</p>
					<p>
						<?php $form->add_check_box( 'telemetry-opt-in' ); ?>
						<label for="telemetry-opt-in"><?php _e( 'Enable basic diagnostic reporting to help improve Ninja Shop.', 'ninja-shop' ); ?> <span class="tip" title="<?php _e( 'This only includes environment and configuration data. Your customer data is never reported.', 'ninja-shop' ); ?>">i</span></label>
					</p>
				</div>

				<!--
				NOTE: We are removing this for now, but will probably add this later.
				<div class="field add-on-banner">
					<img src="<?php echo ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/images/icon32.png' ); ?>" />
					<p><?php _e( 'You\'re almost ready to start selling digital products using PayPal and Ninja Shop.', 'it-l10n-ithemes-exchange' ); ?></p>
					<p><strong><?php _e( 'Remember, if you want to do more with Ninja Shop, check out our Features Library.', 'it-l10n-ithemes-exchange' ); ?></strong></p>
					<a class="get-add-ons " href="javascript:void(0);" target="_blank"><span><?php _e( "Get Features", 'it-l10n-ithemes-exchange' ); ?></span></a>
				</div>
				-->

				<div class="field submit-wrapper">
					<?php $form->add_submit( 'submit', array( 'class' => 'button button-primary button-large', 'value' => __( 'Save Settings', 'it-l10n-ithemes-exchange' ) ) ); ?>
					<?php $form->add_hidden( 'dismiss-wizard-nag', true ); ?>
					<?php $form->add_hidden( 'wizard-submitted', true ); ?>
				</div>
			</div>
		</div>
	<?php $form->end_form(); ?>
</div>
