<div class="ninja-shop-visual-cc-wrap ninja-shop-clearfix" <?php it_exchange( 'purchase-dialog', 'wrap-attributes' ); ?>>
	<div class="ninja-shop-visual-cc">
		<div class="ninja-shop-visual-cc-line-1 ninja-shop-visual-cc-holder ninja-shop-columns-wrapper">
			<div class="ninja-shop-cc-holder-first-name ninja-shop-column">
				<div class="ninja-shop-column-inner">
					<?php it_exchange( 'purchase-dialog', 'cc-first-name', array( 'format' => 'field', 'placeholder' => __( 'First name', 'it-l10n-ithemes-exchange' ) ) ) ?>
				</div>
			</div>
			<div class="ninja-shop-cc-holder-last-name ninja-shop-column">
				<div class="ninja-shop-column-inner">
					<?php it_exchange( 'purchase-dialog', 'cc-last-name', array( 'format' => 'field', 'placeholder' => __( 'Last name', 'it-l10n-ithemes-exchange' ) ) ) ?>
				</div>
			</div>
		</div>
		<div class="ninja-shop-visual-cc-line-2 ninja-shop-visual-cc-number">
			<div class="ninja-shop-cc-number-inner">
				<?php it_exchange( 'purchase-dialog', 'cc-number', array( 'format' => 'field', 'placeholder' => __( 'Card Number', 'it-l10n-ithemes-exchange' ) ) ); ?>
			</div>
		</div>
		<div class="ninja-shop-visual-cc-line-3 ninja-shop-visual-cc-data ninja-shop-columns-wrapper">
			<div class="ninja-shop-visual-cc-expiration ninja-shop-column">
				<div class="ninja-shop-column-inner">
					<?php it_exchange( 'purchase-dialog', 'cc-expiration-month', array( 'format' => 'field', 'placeholder' => __( 'MM', 'it-l10n-ithemes-exchange' ) ) ); ?>
					<?php it_exchange( 'purchase-dialog', 'cc-expiration-year', array( 'format' => 'field', 'placeholder' => __( 'YY', 'it-l10n-ithemes-exchange' ) ) ); ?>
				</div>
			</div>
			<div class="ninja-shop-visual-cc-code ninja-shop-column">
				<div class="ninja-shop-column-inner">
					<?php it_exchange( 'purchase-dialog', 'cc-code', array( 'format' => 'field', 'placeholder' => __( 'CVC' ) ) ); ?>
				</div>
			</div>
		</div>
	</div>

</div>
