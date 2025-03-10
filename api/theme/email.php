<?php
/**
 * Contains the email theme API class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Theme_API_Email
 */
class IT_Theme_API_Email implements IT_Theme_API {

	/**
	 * @var array
	 */
	private $context;

	/**
	 * IT_Theme_API_Email constructor.
	 */
	public function __construct() {
		$this->context = isset( $GLOBALS['it_exchange']['email_context'] ) ? $GLOBALS['it_exchange']['email_context'] : array();
	}

	/**
	 * @var array
	 */
	public $_tag_map = array(
		'message'                 => 'message',
		'context'                 => 'context',
		'date'                    => 'date',
		'logo'                    => 'logo',
		'layout'                  => 'layout',
		'bodyelbkg'               => 'body_el_bkg',
		'backgroundelstyles'      => 'background_el_styles',
		'headerlogo'              => 'header_logo',
		'headerlogosize'          => 'header_logo_size',
		'headerstorename'         => 'header_store_name',
		'headerstorenamefont'     => 'header_store_name_font',
		'headerstorenamesize'     => 'header_store_name_size',
		'headerstorenamecolor'    => 'header_store_name_color',
		'headerimage'             => 'header_image',
		'headerbackground'        => 'header_background',
		'headertransparent'       => 'header_transparent',
		'bodyfont'                => 'body_font',
		'bodytextcolor'           => 'body_text_color',
		'bodyhighlightcolor'      => 'body_highlight_color',
		'bodybuttoncolor'         => 'body_button_color',
		'bodyfontsize'            => 'body_font_size',
		'bodybackgroundcolor'     => 'body_background_color',
		'bodybordercolor'         => 'body_border_color',
		'footertext'              => 'footer_text',
		'footertextcolor'         => 'footer_text_color',
		'footerlogo'              => 'footer_logo',
		'footerlogosize'          => 'footer_logo_size',
		'footerbackground'        => 'footer_background',
		'backgroundcolor'         => 'background_color',
		'backgroundimage'         => 'background_image',
		'backgroundimageposition' => 'background_image_position',
		'backgroundimagerepeat'   => 'background_image_repeat'
	);

	/**
	 * Retrieve the API context.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_api_context() {
		return 'email';
	}

	/**
	 * Returns the custom message for the email.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function message( $options = array() ) {

		$message = empty( $this->context['message'] ) ? '' : trim( $this->context['message'] );

		if ( ! empty( $options['has'] ) ) {
			return (bool) $message;
		}

		return $message;
	}

	/**
	 * Get a context item.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string|bool
	 */
	public function context( $options = array() ) {

		if ( empty( $options['key'] ) ) {
			return false;
		}

		if ( ! empty( $options['has'] ) ) {
			return array_key_exists( $options['key'], $this->context );
		}

		return $this->context[ $options['key'] ];
	}

	/**
	 * Return the current date.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function date( $options = array() ) {

		// Set options
		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => get_option( 'date_format' ),
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		$date = empty( $this->context['date'] ) ? time() : $this->context['date'];

		return $options['before'] . date( $options['format'], $date ) . $options['after'];
	}

	/**
	 * Retrieve the logo.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function logo( $options = array() ) {

		$options = ITUtility::merge_defaults( $options, array(
			'size' => 'full'
		) );

		$attachment = IT_Exchange_Email_Customizer::get_setting( 'logo' );
		$url        = is_numeric( $attachment ) ? wp_get_attachment_image_url( $attachment, $options['size'] ) : $attachment;

		if ( $options['has'] ) {
			return (bool) $url;
		}

		return $url;
	}

	/**
	 * Retrieve the layout mode.
	 *
	 * Either 'boxed' or 'full'.
	 *
	 *
	 *
	 * @return string
	 */
	public function layout() {
		return IT_Exchange_Email_Customizer::get_setting( 'layout' );
	}

	/**
	 * Get the background color for the <bode> element.
	 *
	 *
	 *
	 * @return string
	 */
	public function body_el_bkg() {

		$color = IT_Exchange_Email_Customizer::get_setting( 'footer_background' );

		if ( ! $color || $this->layout() === 'boxed' ) {
			$color = IT_Exchange_Email_Customizer::get_setting( 'background_color' );
		}

		return $color;
	}

	/**
	 * Concatenated background styles.
	 *
	 *
	 *
	 * @return string
	 */
	public function background_el_styles() {

		$styles = "background-color: {$this->background_color()};";

		if ( $this->background_image( array( 'has' => true ) ) ) {
			$styles .= "background-image: url({$this->background_image( array( 'has' => false ) )});";
			$styles .= "background-position: {$this->background_image_position()};";
			$styles .= "background-repeat: {$this->background_image_repeat()};";
		}

		return $styles;
	}

	/**
	 * Retrieve the header logo.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function header_logo( $options = array() ) {

		$show = IT_Exchange_Email_Customizer::get_setting( 'header_show_logo' );
		$logo = $this->logo( $options );

		if ( $options['has'] ) {
			return $show && $logo;
		}

		return $show ? $logo : '';
	}

	/**
	 * Retrieve the header logo size.
	 *
	 *
	 *
	 * @return int
	 */
	public function header_logo_size() {
		return IT_Exchange_Email_Customizer::get_setting( 'header_logo_size' );
	}

	/**
	 * Retrieve the header store name.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function header_store_name( $options = array() ) {

		if ( $options['has'] ) {
			return IT_Exchange_Email_Customizer::get_setting( 'header_show_store_name' );
		}

		$general = it_exchange_get_option( 'settings_general' );

		return $general['company-name'];
	}

	/**
	 * Retrieve the font to use for the header.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function header_store_name_font( $options = array() ) {
		return $this->make_font_stack( IT_Exchange_Email_Customizer::get_setting( 'header_store_name_font' ) );
	}

	/**
	 * Get the store name font size.
	 *
	 *
	 *
	 * @return int
	 */
	public function header_store_name_size() {
		return IT_Exchange_Email_Customizer::get_setting( 'header_store_name_size' );
	}

	/**
	 * Get the store name color.
	 *
	 *
	 *
	 * @return int
	 */
	public function header_store_name_color() {
		return IT_Exchange_Email_Customizer::get_setting( 'header_store_name_color' );
	}

	/**
	 * Get the header image.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function header_image( $options = array() ) {

		$options = ITUtility::merge_defaults( $options, array(
			'size' => 'full'
		) );

		$attachment = IT_Exchange_Email_Customizer::get_setting( 'header_image' );

		if ( $options['has'] ) {
			return (bool) $attachment;
		}

		return is_numeric( $attachment ) ? wp_get_attachment_image_url( $attachment, $options['size'] ) : $attachment;
	}

	/**
	 * Get the header background color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function header_background( $options = array() ) {
		$color = IT_Exchange_Email_Customizer::get_setting( 'header_background' );

		return $color ?: 'transparent';
	}

	/**
	 * Check if the header is transparent.
	 *
	 *
	 *
	 * @return bool
	 */
	public function header_transparent() {
		return ! IT_Exchange_Email_Customizer::get_setting( 'header_background' ) && ! IT_Exchange_Email_Customizer::get_setting( 'header_image' );
	}

	/**
	 * Get the body font.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_font( $options = array() ) {
		return $this->make_font_stack( IT_Exchange_Email_Customizer::get_setting( 'body_font' ) );
	}

	/**
	 * Get the body text color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_text_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_text_color' );
	}

	/**
	 * Check if a color is light.
	 *
	 * @link  http://stackoverflow.com/a/1855903
	 *
	 *
	 *
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return bool
	 */
	protected function is_color_light( $r, $g, $b ) {

		$a = 1 - ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

		return $a < 0.5;
	}

	/**
	 * Convert hex to RGB.
	 *
	 *
	 *
	 * @param string $hex
	 *
	 * @return object
	 */
	protected function hex2rgb( $hex ) {
		$hex = str_replace( "#", "", $hex );

		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}

		return (object) array( 'r' => $r, 'g' => $g, 'b' => $b );
	}

	/**
	 * Get the body highlight color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_highlight_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_highlight_color' );
	}

	/**
	 * Get the body button color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_button_color( $options = array() ) {

		$highlight = IT_Exchange_Email_Customizer::get_setting( 'body_highlight_color' );

		$rgb = $this->hex2rgb( $highlight );

		if ( $this->is_color_light( $rgb->r, $rgb->g, $rgb->b ) ) {
			return '#000000';
		} else {
			return '#ffffff';
		}
	}

	/**
	 * Get the body font size.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_font_size( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_font_size' );
	}

	/**
	 * Get the body background color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_background_color( $options = array() ) {
		$color = IT_Exchange_Email_Customizer::get_setting( 'body_background_color' );

		return $color ? $color : 'transparent';
	}

	/**
	 * Get the body border color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function body_border_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'body_border_color' );
	}

	/**
	 * Get the footer text.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_text( $options = array() ) {

		$text = IT_Exchange_Email_Customizer::get_setting( 'footer_text' );

		if ( ! empty( $options['has'] ) ) {
			return trim( $text ) !== '';
		}

		return $text;
	}

	/**
	 * Get the footer text color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_text_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'footer_text_color' );
	}

	/**
	 * Get the footer logo.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_logo( $options = array() ) {

		$show = IT_Exchange_Email_Customizer::get_setting( 'footer_show_logo' );
		$logo = $this->logo( $options );

		if ( $options['has'] ) {
			return $show && $logo;
		}

		return $logo;
	}

	/**
	 * Get the footer logo size.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_logo_size( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'footer_logo_size' );
	}

	/**
	 * Get the footer background color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function footer_background( $options = array() ) {
		$color = IT_Exchange_Email_Customizer::get_setting( 'footer_background' );

		return $color ? $color : 'transparent';
	}

	/**
	 * Get the background color.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function background_color( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'background_color' );
	}

	/**
	 * Get the background image.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function background_image( $options = array() ) {

		$options = ITUtility::merge_defaults( $options, array(
			'size' => 'full'
		) );

		$attachment = IT_Exchange_Email_Customizer::get_setting( 'background_image' );
		$url        = is_numeric( $attachment ) ? wp_get_attachment_image_url( $attachment, $options['size'] ) : $attachment;

		if ( $options['has'] ) {
			return (bool) $url;
		}

		return $url;
	}

	/**
	 * Get the background image position.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function background_image_position( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'background_image_position' );
	}

	/**
	 * Get the background image repeat.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function background_image_repeat( $options = array() ) {
		return IT_Exchange_Email_Customizer::get_setting( 'background_image_repeat' );
	}

	/**
	 * Make a font stack from a choice.
	 *
	 *
	 *
	 * @param string $choice
	 *
	 * @return string
	 */
	protected function make_font_stack( $choice ) {

		switch ( $choice ) {
			case 'serif':
				return "'Georgia', 'Times New Roman', serif";
			case 'sans-serif':
				return "'Helvetica', Arial, sans-serif";
			case 'monospace':
				return 'Courier, Monaco, monospace';
			default:
				return $choice;
		}
	}
}
