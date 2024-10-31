<?php
/**
 * This is the default template part for the
 * company_name element in the billing-address
 * purchase-requriements in the super-widget-billing-address template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/super-widget-billing-address/elements/
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_super_widget_billing_address_purchase_requirement_before_company_name_element' ); ?>
<div class="ninja-shop-company-name">
	<?php it_exchange( 'billing', 'company-name' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_billing_address_purchase_requirement_after_company_name_element' ); ?>
