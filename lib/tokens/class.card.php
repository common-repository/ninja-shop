<?php
/**
 * CC Payment Token.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Payment_Token_Card
 */
class ITE_Payment_Token_Card extends ITE_Payment_Token {

	const CREDIT = 'credit';
	const DEBIT = 'debit';

	protected static $token_type = 'card';

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		if ( $label = parent::get_label() ) {
			return $label;
		} elseif ( $brand = $this->get_brand() ) {
			/* translators: %1$s refers to the card brand. %2$s refers to the last 4 numbers of the card */
			return sprintf( __( '%1$s ending in %2$s', 'it-l10n-ithemes-exchange' ), ucfirst( $brand ), $this->redacted );
		} else {
			return sprintf( __( 'Card ending in %s', 'it-l10n-ithemes-exchange' ), $this->redacted );
		}
	}

	/**
	 * Get the Card's brand.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_brand() { return $this->get_meta( 'brand', true ); }

	/**
	 * Set the Card's brand.
	 *
	 *
	 *
	 * @param string $brand
	 *
	 * @return bool
	 */
	public function set_brand( $brand ) { return (bool) $this->update_meta( 'brand', $brand ); }

	/**
	 * Get the card's expiration month
	 *
	 *
	 *
	 * @return string
	 */
	public function get_expiration_month() { return zeroise( $this->get_meta( 'expiration_month', true ), 2 ); }

	/**
	 * Set the Card's expiration month.
	 *
	 *
	 *
	 * @param string $month
	 *
	 * @return bool
	 */
	public function set_expiration_month( $month ) { return $this->set_expiration( $month, $this->get_expiration_year() ); }

	protected function _set_expiration_month( $month ) { return (bool) $this->update_meta( 'expiration_month', $month ); }

	/**
	 * Get the card's expiration year
	 *
	 *
	 *
	 * @return string
	 */
	public function get_expiration_year() { return $this->get_meta( 'expiration_year', true ); }

	/**
	 * Set the Card's expiration year.
	 *
	 *
	 *
	 * @param string $year
	 *
	 * @return bool
	 */
	public function set_expiration_year( $year ) { return $this->set_expiration( $this->get_expiration_month(), $year ); }

	protected function _set_expiration_year( $year ) {
		$year = $year > 2000 ? $year : $year + 2000;

		return (bool) $this->update_meta( 'expiration_year', $year );
	}

	/**
	 * Set the Card's expiration date.
	 *
	 *
	 *
	 * @param string $month
	 * @param string $year
	 *
	 * @return bool
	 */
	public function set_expiration( $month, $year ) {

		$r1 = $this->_set_expiration_month( $month );
		$r2 = $this->_set_expiration_year( $year );

		return ( $r1 || $r2 ) && $this->set_expires_at( $month, $year );
	}

	/**
	 * Set the expires_at column.
	 *
	 *
	 *
	 * @param string $month
	 * @param string $year
	 *
	 * @return bool
	 */
	protected function set_expires_at( $month, $year ) {
		$date = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$date->setDate( $year, $month, 1 );
		$date->modify( 'last day of this month' );
		$date->setTime( 23, 59, 59 );

		$this->expires_at = $date;

		return $this->save();
	}

	/**
	 * Get the Card's source of funding.
	 *
	 * Could be either 'credit' or 'debit'.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_funding() { return $this->get_meta( 'funding', true ); }

	/**
	 * Set the Card's source of funding.
	 *
	 *
	 *
	 * @param string $funding
	 *
	 * @return bool
	 */
	public function set_funding( $funding ) { return (bool) $this->update_meta( 'funding', $funding ); }
}
