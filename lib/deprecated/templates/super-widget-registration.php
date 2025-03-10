<?php
/**
 * The registration template for the Super Widget.
 *
 * 
 * @version 1.1.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 *
 * Example: theme/exchange/super-widget-registration.php
*/
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<div class="registration-info it-exchange-sw-processing-registration">
	<?php if ( it_exchange( 'registration', 'is-enabled' ) ) : ?>
		<?php it_exchange( 'registration', 'form-open' ); ?>
			<div class="user-name">
				<?php it_exchange( 'registration', 'username' ); ?>
			</div>
			<div class="first-name">
				<?php it_exchange( 'registration', 'first-name' ); ?>
			</div>
			<div class="last-name">
				<?php it_exchange( 'registration', 'last-name' ); ?>
			</div>
			<div class="email-name">
				<?php it_exchange( 'registration', 'email' ); ?>
			</div>
			<div class="password1">
				<?php it_exchange( 'registration', 'password1' ); ?>
			</div>
			<div class="password2">
				<?php it_exchange( 'registration', 'password2' ); ?>
			</div>
			<?php it_exchange( 'registration', 'save' ); ?>
			<div class="cancel_url">
				<?php it_exchange( 'registration', 'cancel', array( 'label' => __( 'Log in', 'it-l10n-ithemes-exchange' ) ) ); ?>
			</div>
		<?php it_exchange( 'registration', 'form-close' ); ?>
	<?php else : ?>
		<?php it_exchange( 'registration', 'disabled-message' ); ?>
	<?php endif; ?>
</div>
