<?php
/**
 * This file contains the markup for the receipt email meta.
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
 * Example: theme/exchange/emails/receipt/meta-bottom.php
 */
?>
<tr>
	<td align="center">
		<!--[if mso]>
		<center>
			<table>
				<tr>
					<td width="640">
		<![endif]-->
		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; background: <?php it_exchange( 'email', 'body-background-color' ); ?>; padding-bottom: 20px; margin: 0 auto;" class="wrapper body-bkg-color">
			<tr>
				<td valign="top" style="padding: 20px 25px; ">
					<table width="100%">
						<tr>
							<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_begin' ); ?>

							<?php if ( it_exchange( 'transaction', 'has-note' ) ): ?>
								<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_before_note' ); ?>
								<td style="line-height: 1.4; vertical-align: top; width: 50%">
									<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_begin_note' ); ?>
									<strong><?php _e( 'Order Note', 'it-l10n-ithemes-exchange' ); ?></strong><br>
									<?php it_exchange( 'transaction', 'note' ); ?>
									<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_end_note' ); ?>
								</td>
								<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_after_note' ); ?>
							<?php endif; ?>

							<?php if ( it_exchange( 'transaction', 'has-shipping-method' ) ): ?>
								<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_before_shipping' ); ?>
								<td style="line-height: 1.4; vertical-align: top; width: 50%">
									<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_begin_shipping' ); ?>
									<strong><?php _e( 'Shipping Method', 'it-l10n-ithemes-exchange' ) ?></strong><br>
									<?php it_exchange( 'transaction', 'shipping-method' ); ?>
									<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_end_shipping' ); ?>
								</td>
								<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_after_shipping' ); ?>
							<?php endif; ?>

							<?php do_action( 'ninja_shop_email_template_receipt_meta-bottom_end' ); ?>
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
