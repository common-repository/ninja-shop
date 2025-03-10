<?php
/**
 * This file contains the markup for the order note email template.
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
 * Example: theme/exchange/emails/email-order-note.php
 */
?>

<?php it_exchange_get_template_part( 'emails/partials/head' ); ?>
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
									<strong><?php it_exchange( 'email', 'date' ); ?></strong>
								</td>
								<td align="right" style="font-weight: bold; ">
									<strong><?php it_exchange( 'transaction', 'order-number', array( 'label' => __( 'Order: %s', 'it-l10n-ithemes-exchange' ) ) ); ?></strong>
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

<?php it_exchange_get_template_part( 'emails/partials/message' ); ?>
<?php it_exchange_get_template_part( 'emails/order-note/activity' ); ?>

<?php it_exchange_get_template_part( 'emails/receipt/cart-details' ); ?>
<?php it_exchange_get_template_part( 'emails/receipt/cart-totals' ); ?>

<?php it_exchange_get_template_part( 'emails/partials/footer' ); ?>
<?php it_exchange_get_template_part( 'emails/partials/foot' ); ?>
