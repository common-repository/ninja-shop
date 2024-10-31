<?php

function it_exchange_opinionated_styles_settings_callback() {
  $addon = new IT_Exchange_Opinionated_Styles_Add_On();
  $addon->print_settings_page();
}

function it_exchange_opinionated_styles( $public_css ) {
  $settings = it_exchange_get_option( 'addon_opinionated_styles' );
  if( isset( $settings[ 'opinionated-styles-theme' ] ) ) {
    switch( $settings[ 'opinionated-styles-theme' ] ) {
      case 'light':
        return IT_Exchange::$url . '/lib/assets/styles/ninja-shop-light.css';
      case 'dark':
        return IT_Exchange::$url . '/lib/assets/styles/ninja-shop-dark.css';
      case 'none':
      default:
        return $public_css;
    }
  }
  return $public_css;
}
add_filter( 'ninja_shop_public_css_path', 'it_exchange_opinionated_styles' );

class IT_Exchange_Opinionated_Styles_Add_On {

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
	 * @var string $status_message will be displayed if not empty
	 *
	*/
	var $status_message;

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
	*/
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : sanitize_text_field( $_GET['page'] );
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : sanitize_text_field( $_GET['add-on-settings'] );

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'opinionated-styles' == $this->_current_add_on ) {
			add_action( 'ninja_shop_save_add_on_settings_opinionated_styles', array( $this, 'save_settings' ) );
			do_action( 'ninja_shop_save_add_on_settings_opinionated_styles' );
		}

		add_filter( 'ninja_shop_storage_get_defaults_exchange_addon_opinionated_styles', array( __CLASS__, 'set_default_settings' ) );
	}

  /**
   * (Maybe) Enqueue Opinionated Styles
   */
  function enqueue_opinionated_styles(){
    $settings = it_exchange_get_option( 'addon_opinionated_styles', $break_cache = true );
    var_dump( $settings );
    die();
  }

	/**
	 * Prints settings page
	 *
	 *
	 * @return void
	*/
	function print_settings_page() {
		$settings = it_exchange_get_option( 'addon_opinionated_styles', $break_cache = true );

		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'ninja_shop_add_on_opinionated_styles', 'it-exchange-add-on-opinionated-styles-settings' ),
			'enctype' => apply_filters( 'ninja_shop_add_on_opinionated_styles_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=opinionated-styles',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-opinionated-styles' ) );

		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );

		?>
		<div class="wrap">
			<?php ITUtility::screen_icon( 'it-exchange' ); ?>
			<h2><?php _e( 'Opinionated Styles Settings', 'it-l10n-ithemes-exchange' ); ?></h2>

			<?php do_action( 'ninja_shop_opinionated_styles_settings_page_top' ); ?>
			<?php do_action( 'ninja_shop_addon_settings_page_top' ); ?>
			<?php $form->start_form( $form_options, 'ninja-shop-opinionated-styles-settings' ); ?>

				<?php
				do_action( 'ninja_shop_digital_downloads_settings_form_top' );

				if ( ! empty( $settings ) )
					foreach ( $settings as $key => $var )
						$form->set_option( $key, $var );

				?>
				<div class="it-exchange-addon-settings it-exchange-opinionated-styles-addon-settings">
          <?php _e( 'Use default Ninja Shop styling conventions.', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'If \'none\' selected, only structural styles will be applied.', 'it-l10n-ithemes-exchange' ); ?>">i</span>
					<p>
						<?php $form->add_drop_down( 'opinionated-styles-theme', [
              'none' => __( 'None', 'it-l10n-ithemes-exchange' ),
              'light' => __( 'Light', 'it-l10n-ithemes-exchange' ),
              'dark' => __( 'Dark', 'it-l10n-ithemes-exchange' ),
            ]); ?>
					</p>
				</div>
				<?php

				do_action( 'ninja_shop_opinionated_styles_settings_form_bottom' );
				?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'ninja_shop_opinionated_styles_settings_page_bottom' ); ?>
			<?php do_action( 'ninja_shop_addon_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 *
	 * @return void
	*/
	function save_settings() {
		$defaults = it_exchange_get_option( 'addon_opinionated_styles' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ninja-shop-opinionated-styles-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'it-l10n-ithemes-exchange' );
			return;
		}

		$errors = apply_filters( 'ninja_shop_add_on_opinionated_styles_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_opinionated_styles', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'it-l10n-ithemes-exchange' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'it-l10n-ithemes-exchange' );
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
	function get_form_errors( $values ) {

		$errors = array();

		return $errors;
	}

	/**
	 * Sets the default options for opinionated styles theme settings
	 *
	 *
	 *
	 * @param array $defaults
	 *
	 * @return array settings
	*/
	public static function set_default_settings( $defaults ) {
		$defaults['opinionated-styles-theme'] = 'light';
		return $defaults;
	}

}
