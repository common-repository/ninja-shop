<?php

class Ninja_Shop_Basic_US_Sales_Taxes_Addon_Settings {

	/**
	 * @var boolean $_is_admin true or false
	 *
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 *
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 *
	*/
	var $_current_add_on;

	/**
	 * @var string $error_message will be displayed if not empty
	 *
	*/
	var $error_message;

	/**
 	 * Class constructor
	 *
	 * Sets up the class.
	 *
	 * @return void
	*/
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'basic-us-sales-taxes' == $this->_current_add_on ) {
			add_action( 'ninja_shop_save_add_on_settings_basic_us_sales_taxes', array( $this, 'save_settings' ) );
			do_action( 'ninja_shop_save_add_on_settings_basic_us_sales_taxes' );
		}

    add_filter( 'ninja_shop_storage_get_defaults_exchange_addon_basic_us_sales_taxes', [ $this, 'default_settings' ] );
	}

  static function settings_callback() {
    $addon_settings = new self();
    $addon_settings->print_settings_page();
  }

	function print_settings_page() {
		global $new_values;
		$settings = it_exchange_get_option( 'addon_basic_us_sales_taxes', true, false );
		if ( empty( $settings['tax-rates'] ) )
			$settings = it_exchange_get_option( 'addon_basic_us_sales_taxes', true );

		$form_values  = empty( $this->error_message ) ? $settings : $new_values;
		$form_options = array(
			'id'      => 'it-exchange-add-on-basic-us-sales-taxes-settings',
			'enctype' => false,
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=basic-us-sales-taxes',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-basic-us-sales-taxes' ) );

		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );

		?>
		<div class="wrap">
			<h2>Basic US Sales Taxes</h2>

			<?php $form->start_form( $form_options, 'ninja-shop-basic-us-sales-taxes-settings' ); ?>
				<?php $this->get_sales_taxes_form_table( $form, $form_values ); ?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
		</div>
		<?php
	}

	function get_sales_taxes_form_table( $form, $settings = array() ) {
		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );
		?>

        <div class="it-exchange-addon-settings it-exchange-basic-us-sales-taxes-addon-settings">
            <h4>
            	<?php _e( 'Current Tax Rates and Settings', 'LION' ) ?>
            </h4>
			<div id="us-tax-rate-table">
			<?php
			$headings = array(
				__( 'US State', 'ninja-shop' ),
				__( 'Tax Rate %', 'ninja-shop' ),
				__( 'Apply to Shipping?', 'ninja-shop' )
			);
			?>
			<div class="heading-row block-row">
				<?php $column = 0; ?>
				<?php foreach ( (array) $headings as $heading ) : ?>
				<?php $column++ ?>
				<div class="heading-column block-column block-column-<?php echo $column; ?>">
				<p class="heading"><?php echo $heading; ?></p>
				</div>
				<?php endforeach; ?>
				<div class="heading-column block-column block-column-delete"></div>
			</div>
			<?php
			$row = 0;
			//Alpha Sort
			$tax_rates = $settings['tax-rates'];
			ksort( $tax_rates );
			foreach( $tax_rates as $province => $rates ) {
				foreach( $rates as $rate ) {
					echo it_exchange_basic_us_sales_taxes_get_tax_row_settings( $row, $province, $rate );
					$row++;
				}
			}
			?>
			</div>
			<script type="text/javascript" charset="utf-8">
	            var it_exchange_basic_us_sales_taxes_addon_iteration = <?php echo $row; ?>;
	        </script>

			<p class="add-new">
				<?php $form->add_button( 'new-tax-rate', array( 'value' => __( 'Add New Tax Rate', 'ninja-shop' ), 'class' => 'button button-secondary button-large' ) ); ?>
			</p>

		</div>
		<?php
	}

  public function default_settings( $defaults ) {
		$defaults = array(
			'tax-rates' => array(
				'TN' => array( //Province
					array(
						'rate'     => '9.75',   //Rate
						'shipping' => false, //Apply to Shipping
					),
				),
			),
		);
		return $defaults;
	}

	/**
	 * Save settings
	 *
	 *
	 * @return void
	*/
    function save_settings() {
    	global $new_values; //We set this as global here to modify it in the error check

        $defaults = it_exchange_get_option( 'addon_basic_us_sales_taxes' );
        $new_values = ITForm::get_post_data();
        $organized_values = array();

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ninja-shop-basic-us-sales-taxes-settings' ) ) {
            $this->error_message = __( 'Error. Please try again', 'ninja-shop' );
            return;
        }

        $errors = apply_filters( 'ninja_shop_add_on_basic_us_sales_taxes_validate_settings', $this->get_form_errors( $new_values ), $new_values );

        if ( !empty( $new_values['tax-rates'] ) ) {
	        foreach( $new_values['tax-rates'] as $value ) {
	        	if ( !empty( $organized_values['tax-rates'][$value['state']] ) ) {
			        array_push( $organized_values['tax-rates'][$value['state']], array(
			        	'rate'     => !empty( $value['rate'] ) ? $value['rate'] : '',
			        	'shipping' => !empty( $value['shipping'] ) ? $value['shipping'] : '',
			        ) );
	        	} else {
			        $organized_values['tax-rates'][$value['state']] = array(
				        array(
				        	'rate'     => !empty( $value['rate'] ) ? $value['rate'] : '',
				        	'shipping' => !empty( $value['shipping'] ) ? $value['shipping'] : '',
				        ),
			        );
		        }
	        }
	        $new_values['tax-rates'] = $organized_values['tax-rates'];
        } else {
	        $new_values = $defaults;
        }

        if ( ! $errors && it_exchange_save_option( 'addon_basic_us_sales_taxes', $new_values ) ) {
            ITUtility::show_status_message( __( 'Settings saved.', 'ninja-shop' ) );
        } else if ( $errors ) {
            $errors = implode( '<br />', $errors );
            $this->error_message = $errors;
        } else {
            $this->status_message = __( 'Settings not saved.', 'ninja-shop' );
        }
    }

    /**
     * Validates for values
     *
     * Returns string of errors if anything is invalid
     *
     *
     * @return void
    */
    public function get_form_errors( $values ) {
			$states = it_exchange_get_data_set( 'states', array( 'country' => 'US' ) );
    	$errors = array();

    	if ( !empty( $values['tax-rates'] ) )
    		$tax_rates = $values['tax-rates'];
    	else
	        return array( __( 'Unable to find tax rates to save, please try again.', 'ninja-shop' ) );

        foreach( $tax_rates as $tax_rate ) {
        	if ( empty( $tax_rate['state'] ) || empty( $states[$tax_rate['state']] ) ) {
                $errors[] = __( 'Missing or Invalid US State.', 'ninja-shop' );
	        	break;
        	} else if ( empty( $tax_rate['rate'] ) ) {
                $errors[] = __( 'Missing or Invalid Tax Rate.', 'ninja-shop' );
	        	break;
        	}
        }

        return $errors;
    }
}
