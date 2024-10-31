<?php
/**
 * This file prints the add-ons page in the Admin
 *
 * 
 * @package IT_Exchange
*/
?>
<div id="it-exchange-add-ons-wrap" class="wrap">
	<?php ITUtility::screen_icon( 'it-exchange-add-ons' );  ?>
	<h2>Features</h2>
	<p class="top-description"><?php _e( 'Features that you can add or remove depending on your needs. Selling your stuff should only be as complicated as you need it to be. Visit the Get More tab to see what else Ninja Shop can do.', 'it-l10n-ithemes-exchange' ); ?></p>

	<?php $this->print_add_ons_page_tabs(); ?>
	<?php do_action( 'ninja_shop_add_ons_page_top' ); ?>

	<?php
		$tab = ! empty( $_REQUEST['tab'] ) ? sanitize_text_field( $_REQUEST['tab'] ) : 'all';
		$propack = false;

		switch ( $tab ) {
			case 'enabled':
				$addons = it_exchange_get_enabled_addons( array( 'show_required' => false ) );
				break;

			case 'disabled':
				$addons = it_exchange_get_disabled_addons( array( 'show_required' => false ) );
				break;

			case 'all':
			default:
				$addons = it_exchange_get_addons( array( 'show_required' => false ) );
				$propack = true;
				break;
		}

		// Sort Add-ons by display name
		$display_names = array();
		foreach( (array) $addons as $key => $data ) {
			if ( empty( $key ) ) { continue; }
			$name = empty( $data['name'] ) ? $key : strtolower( $data['name'] );
			$display_names[$key] = $name;
		}
		if ( apply_filters( 'ninja_shop_sort_addons_by_display_name', true ) ) {
			array_multisort( $display_names, SORT_ASC, $addons );
		}
	?>
	<div class="add-ons-wrapper">
		<?php if ( ! empty( $addons ) ) : ?>
			<?php if ( $propack ) { 
				
			?>
				<div class="add-on-block pro-pack">
					<h3><?php _e( 'Do Even More With Ninja Shop!', 'it-l10n-ithemes-exchange' ); ?><span><a href="https://getninjashop.com/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Shop+Addons+Page">Expand</a></span></h3>
					<p><?php _e( 'Do even more with Ninja Shop by unlocking powerful features… like custom invoicing, accepting credit cards with Stripe, mailing list integrations and so much more available now and coming soon.' ); ?>
					<br /><a href="https://getninjashop.com/?utm_medium=plugin&utm_source=plugin-addons-page&utm_campaign=Ninja+Shop+Addons+Page" class="upsell">Find out more here</a>.</p>
					<a class="btn-pro dismiss" href="#"><?php _e( 'Close', 'it-l10n-ithemes-exchange' ); ?></a>
				</div>
			<?php
				}
			?>

			<?php $default_icon = '<i class="fas fa-plug"></i>'; ?>

			<?php foreach( (array) $addons as $addon ) : ?>

				<?php

				if ( !empty( $addon['options']['tag'] ) && 'required' === $addon['options']['tag'] )
					continue;

				?>

				<?php $icon = empty( $addon['options']['icon'] ) ? $default_icon : $addon['options']['icon']; ?>
				<div class="add-on-block add-on-slug-<?php echo esc_attr( $addon['slug'] ); ?>">
					<div class="add-on-icon">
						<div class="icon-wrapper">
							<?php echo $icon; ?>
						</div>
					</div>
					<div class="add-on-info">
						<h4><?php echo $addon['name']; ?></h4>
						<span class="add-on-author">by <a href="<?php echo $addon['author_url']; ?>"><?php echo $addon['author']; ?></a></span>
                        <?php if ( !empty( $addon['options']['tag'] ) ) { ?>
						<span class="add-on-tag"><?php echo $addon['options']['tag']; ?></span>
                        <?php } ?>
						<p class="add-on-description"><?php echo $addon['description']; ?></p>
					</div>
					<div class="add-on-actions">

						<?php if ( $addon['slug'] === 'ithemes-security' && ( ! class_exists( 'ITSEC_Core' ) || ! ITSEC_Core::is_pro() ) ): ?>
							<div class="add-on-buy-now">
								<a href="https://ithemes.com/security/">
									<?php _e( 'Purchase', 'it-l10n-ithemes-exchange' ); ?>
								</a>
							</div>
						<?php else : ?>
							<?php if ( it_exchange_is_addon_enabled( $addon['slug'] ) ) : ?>
								<?php $url = wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-disable-addon=' . $addon['slug'] . '&tab=' . $tab, 'exchange-disable-add-on' ); ?>
								<div class="add-on-enabled"><a href="<?php echo $url; ?>" data-text-disable="Disable" data-text-enabled="Enabled">Enabled</a></div>
							<?php else : ?>
								<div class="add-on-disabled"><a href="<?php echo wp_nonce_url( get_site_url() . '/wp-admin/admin.php?page=it-exchange-addons&it-exchange-enable-addon=' . $addon['slug'] . '&tab=' . $tab, 'exchange-enable-add-on' ); ?>" data-text-enable="Enable" data-text-disabled="Disabled">Disabled</a></div>
							<?php endif; ?>

							<?php if ( it_exchange_is_addon_enabled( $addon['slug'] ) && ! empty( $addon['options']['settings-callback'] ) && is_callable( $addon['options']['settings-callback'] ) ) : ?>
								<div class="add-on-settings"><a href="<?php echo admin_url( 'admin.php?page=it-exchange-addons&add-on-settings=' . $addon['slug'] ); ?>">S</a></div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else : ?>
			<?php
			if ( 'all' == $tab ) {
				$vars = array(
					'target' => add_query_arg( 'tab', 'get-more' ),
					'text'   => __( 'No features installed. How\'d you do that?', 'it-l10n-ithemes-exchange' ),
					'link'   => __( 'Go get some now', 'it-l10n-ithemes-exchange' )
				);
			} else if ( 'enabled' == $tab ) {
				$vars = array(
					'target' => add_query_arg( 'tab', 'disabled' ),
					'text'   => __( 'No features are currently enabled', 'it-l10n-ithemes-exchange' ),
					'link'   => __( 'Go enable some now', 'it-l10n-ithemes-exchange' )
				);
			} else if ( 'disabled' == $tab ) {
				$vars = array(
					'target' => add_query_arg( 'tab', 'get-more' ),
					'text'   => __( 'All features are currently enabled', 'it-l10n-ithemes-exchange' ),
					'link'   => __( 'Go get more', 'it-l10n-ithemes-exchange' )
				);
			}
			?>
			<div class="addons-achievement">
				<h2><?php echo $vars['text']; ?></h2>
				<p>
					<a href="<?php echo esc_url( $vars['target'] ); ?>" class="it-exchange-button"><?php echo $vars['link'] ?></a>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
