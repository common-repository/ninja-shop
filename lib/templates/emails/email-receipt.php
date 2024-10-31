<?php
/**
 * This file contains the markup for the email template.
 *
 * 
 * @link    http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 *
 * Example: theme/exchange/emails/email-receipt.php
 */
?>
<?php it_exchange_get_template_part( 'emails/partials/head' ); ?>

	<!-- HIDDEN PREHEADER TEXT -->
	<div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
		<?php printf( __( 'Receipt for your purchase of %s.', 'it-l10n-ithemes-exchange' ), it_exchange( 'transaction', 'get-description' ) ); ?>
	</div>

<?php it_exchange_get_template_part( 'emails/partials/header' ); ?>

	<!-- begin content heading -->
	<tr>
		<td align="center">
			<!--[if mso]>
			<center>
				<table>
					<tr>
						<td width="640">
			<![endif]-->
			<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; background: <?php it_exchange( 'email', 'body-background-color' ); ?>;  margin: 0 auto; border-bottom: 1px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>;" class="wrapper border-highlight-color body-bkg-color">
				<tr>
					<td valign="top" style="padding: 20px 25px;">
						<table width="100%">
							<tr>
								<td style="font-weight: bold; ">
									<strong><?php it_exchange( 'transaction', 'date' ); ?></strong>
								</td>
								<td align="right" style="font-weight: bold; ">
									<strong>
										<?php it_exchange( 'transaction', 'total' ); ?>&nbsp;&mdash;
										<?php it_exchange( 'transaction', 'status', 'label=<span class="%s">%s</span>' ); ?>
									</strong>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<!--[if mso]>
			</td></tr></table>
			</center>
			<![endif]-->
		</td>
	</tr>
	<!-- end content heading -->

<?php if ( it_exchange( 'email', 'has-message' ) ): ?>
	<?php it_exchange_get_template_part( 'emails/partials/message' ); ?>
<?php endif; ?>

<?php it_exchange_get_template_part( 'emails/receipt/meta-top' ); ?>
<?php if ( it_exchange( 'transaction', 'has-line-items' ) ) : ?>
	<?php it_exchange_get_template_part( 'emails/receipt/cart-details' ); ?>
<?php endif; ?>
<?php it_exchange_get_template_part( 'emails/receipt/cart-totals' ); ?>

<?php if ( it_exchange( 'transaction', 'has-note' ) || it_exchange( 'transaction', 'has-shipping-method' ) ): ?>
	<?php it_exchange_get_template_part( 'emails/receipt/meta-bottom' ); ?>
<?php endif; ?>

<?php if ( it_exchange( 'transaction', 'has-downloads' ) ): ?>
	<?php it_exchange_get_template_part( 'emails/receipt/downloads' ); ?>
<?php endif; ?>

<?php it_exchange_get_template_part( 'emails/partials/footer' ); ?>
<?php it_exchange_get_template_part( 'emails/partials/foot' ); ?>
