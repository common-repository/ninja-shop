<?php
/**
 * This file contains the contents of the Help/Support page
 * 
 * @package IT_Exchange
*/
?>
<div class="wrap help-wrap">
	<?php ITUtility::screen_icon( 'it-exchange' );  ?>
	<h2><?php _e( 'Help and Resources', 'it-l10n-ithemes-exchange' ); ?></h2>

	<p class="top-description"><?php printf( __( 'We\'ve built %s to simplify ecommerce for WordPress. However, ecommerce is not always easy, so we\'ve taken the time to create some resources to help you get started.', 'it-l10n-ithemes-exchange' ), '<a title="Ninja Shop" href="http://ninjashop.site" target="_blank">Ninja Shop</a>' ); ?></p>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Quick Links', 'it-l10n-ithemes-exchange' ); ?></h3>
		<div class="help-action exchange-wizard help-tip" title="<?php _e( 'This is a link back to the Quick Setup page that opens after Ninja Shop is installed. This page walks through the necessary information and settings needed to set up your store.', 'it-l10n-ithemes-exchange' ); ?>">
			<p><?php _e( 'Go back to the Ninja Shop Quick Setup Wizard.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="<?php echo get_admin_url( NULL, 'admin.php?page=it-exchange-setup' ); ?>" target="_self"><?php _e( 'Open the Wizard', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
	<h3><?php _e( 'Documentation', 'it-l10n-ithemes-exchange' ); ?></h3>
		<div class="help-action exchange-codex" title="">
			<p><?php _e( 'Read through the Ninja Shop documentation.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="https://getninjashop.com/documentation/" target="_blank"><?php _e( 'Dig Deep into Ninja Shop', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
	</div>

	<div class="help-section-wrap clearfix">
		<h3><?php _e( 'Support', 'it-l10n-ithemes-exchange' ); ?></h3>
		<div class="help-action exchange-support" title="">
			<p><?php _e( 'Ask a question, or help us fix an issue.', 'it-l10n-ithemes-exchange' ); ?></p>
			<p><a href="https://getninjashop.com/contact/" target="_blank"><?php _e( 'Contact Us', 'it-l10n-ithemes-exchange' ); ?></a></p>
		</div>
	</div>
</div>
