<?php
use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\DateTime;
use IronBound\DB\Table\Column\DecimalBased;
use IronBound\DB\Table\Column\IntegerBased;
use IronBound\DB\Table\Column\StringBased;
use IronBound\DB\Table\TimestampedTable;

/**
 * Transaction Line Item Table.
 *
 * 
 * @license GPLv2
 */
class ITE_Transaction_Line_Item_Table extends BaseTable implements TimestampedTable {

	/**
	 * Overridding the parent method.
	 *
	 * @NOTE Seeing some issues exceeding the "max key size".
	 *			 Specifying `ROW_FORMAT=dynamic` for this table.
	 */
	public function get_creation_sql( \wpdb $wpdb ) {

		$tn = $this->get_table_name( $wpdb );

		$sql = "CREATE TABLE {$tn} (\n";
		$sql .= $this->get_columns_definition();

		if ( $keys = $this->get_keys_definition() ) {
			$sql .= ",\n{$keys}";
		}

		$sql .= "\n)";

		// @NOTE Specifying the ROW_FORMAT to resolve Max Key Length issue.
		$sql .= " ROW_FORMAT = dynamic ";

		$sql .= " {$wpdb->get_charset_collate()};";

		return $sql;
	}

	/** @var array */
	private $columns = array();

	/**
	 * @inheritDoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return $wpdb->prefix . 'ninja_shop_line_items';
	}

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'ninja-shop-line-items';
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = array(
			'pk'           =>
				new IntegerBased( 'BIGINT', 'pk', array( 'unsigned', 'NOT NULL', 'auto_increment' ), array( 20 ) ),
			'id'           => new StringBased( 'VARCHAR', 'id', array( 'NOT NULL' ), array( 255 ) ),
			'type'         => new StringBased( 'VARCHAR', 'type', array( 'NOT NULL' ), array( 20 ) ),
			'name'         => new StringBased( 'VARCHAR', 'name', array(), array( 255 ) ),
			'description'  => new StringBased( 'TEXT', 'description' ),
			'object_id'    => new IntegerBased( 'BIGINT', 'object_id', array(), array( 20 ) ),
			'amount'       => new DecimalBased( 'DECIMAL', 'amount', array(), array( 16, 6 ) ),
			'quantity'     => new IntegerBased( 'INT', 'quantity', array( 'unsigned' ), array() ),
			'total'        => new DecimalBased( 'DECIMAL', 'total', array(), array( 16, 6 ) ),
			'summary_only' => new IntegerBased( 'TINYINT', 'summary_only', array( 'unsigned' ), array() ),
			'transaction'  => new IntegerBased( 'BIGINT', 'transaction', array(), array( 20 ) ),
			'created_at'   => new DateTime( 'created_at' ),
			'updated_at'   => new DateTime( 'updated_at' ),
			'_class'       => new StringBased( 'VARCHAR', '_class', array(), array( 255 ) ),
			'_parent'      => new IntegerBased( 'BIGINT', '_parent', array(), array( 20 ) ),
			'_scoped_from' => new IntegerBased( 'BIGINT', '_scoped_from', array(), array( 20 ) ),
		);

		return $this->columns;
	}

	/**
	 * @inheritDoc
	 */
	public function get_column_defaults() {
		return array(
			'pk'           => 0,
			'id'           => 0,
			'type'         => '',
			'name'         => '',
			'description'  => '',
			'object_id'    => 0,
			'amount'       => 0.00,
			'quantity'     => 0,
			'total'        => 0.00,
			'summary_only' => 0,
			'transaction'  => 0,
			'created_at'   => '',
			'updated_at'   => '',
			'_class'       => '',
			'_parent'      => 0,
			'_scoped_from' => 0,
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function get_keys() {

		$keys   = parent::get_keys();
		$keys[] = 'KEY transaction__type (transaction,type)';

		return $keys;
	}

	/**
	 * @inheritDoc
	 */
	public function get_created_at_column() {
		return 'created_at';
	}

	/**
	 * @inheritDoc
	 */
	public function get_updated_at_column() {
		return 'updated_at';
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary_key() {
		return 'pk';
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() {
		return 1;
	}
}
