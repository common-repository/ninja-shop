<?php
/**
 * File Logger.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_File_Logger
 */
class ITE_File_Logger extends \Psr\Log\AbstractLogger implements ITE_Purgeable_Logger, ITE_Retrievable_Logger {

	/** @var string */
	private $directory;

	/** @var string */
	private $minimum_severity;

	/** @var string */
	private $type;

	/** @var array */
	private static $handles = array();

	/** @var int[] */
	private static $line_count_cache = array();

	/** @var int[] */
	private static $file_modified_times = array();

	const LINE_FORMAT = '{time}||{level}||{group}||{message}||{user}||{ip}';
	const MAX_LINES = 250;
	const MAX_FILES = 10;

	/**
	 * ITE_File_Logger constructor.
	 *
	 * @param string $directory
	 * @param string $minimum_level
	 * @param string $type
	 */
	public function __construct( $directory, $minimum_level = '', $type = 'log' ) {
		$this->directory        = trailingslashit( $directory );
		$this->minimum_severity = $minimum_level ? ITE_Log_Levels::get_level_severity( $minimum_level ) : 0;
		$this->type             = $type;

		it_classes_load( 'it-file-utility.php' );
	}

	/**
	 * @inheritDoc
	 */
	public function log( $level, $message, array $context = array() ) {

		if ( ITE_Log_Levels::get_level_severity( $level ) < $this->minimum_severity ) {
			return;
		}

		$interpolated = $this->interpolate( $message, $context );
		$user         = isset( $context['_user'] ) ? $context['_user'] : get_current_user_id();

		$parts = array(
			'{time}'    => date( DateTime::ATOM ),
			'{level}'   => $level,
			'{group}'   => empty( $context['_group'] ) ? '' : $context['_group'],
			'{message}' => $interpolated,
			'{user}'    => empty( $user ) ? 0 : "#{$user}",
			'{ip}'      => it_exchange_get_ip(),
		);

		$line = str_replace( array_keys( $parts ), array_values( $parts ), self::LINE_FORMAT );

		$path = $this->make_file_path();

		if ( $this->file_should_rotate( $path ) ) {
			$this->rotate();
			$path = $this->make_file_path();
		}

		$fh = $this->acquire_file_handle( $path );

		if ( ! $fh ) {
			return;
		}

		flock( $fh, LOCK_EX );
		fwrite( $fh, $line . "\n" );
		flock( $fh, LOCK_UN );

		self::$file_modified_times[ $path ] = time();

		if ( isset( self::$line_count_cache[ $path ] ) ) {
			self::$line_count_cache[ $path ] ++;
		} else {
			self::$line_count_cache[ $path ] = 1;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function purge() {

		$files = $this->get_files();

		if ( is_wp_error( $files ) ) {
			return false;
		}

		$r = true;

		foreach ( $files as $file ) {
			$r = $r && @unlink( $file );
		}

		return $r;
	}

	/**
	 * @inheritDoc
	 */
	public function get_log_items( $page = 1, $per_page = 100, &$has_more ) {


		/*
		 Goal is to find which file we need to open.


		Log files that store 4 lines per-file. Arranged newest to oldest. Last line in each file is newest.

		------------    ------------    ------------
		------------    ------------    ------------
		------------    ------------    ------------
						------------    ------------

		Ex 1: Request page 1 with 2 per page.
		$offset = 0;
		$position = 0;
		$file = 0;
		$offset_in_file = 1;

		Ex 2: Request page 2 with 2 per page.
		$offset = 2;
		$position = .5;
		$file = 0;
		$offset_in_file = 0;

		Ex 3: Request page 3 with 2 per page.
		$offset = 4;
		$position = 1;
		$file = 1;
		$offset_in_file = 2;

		Ex 4: Request page 4 with 2 per page.
		$offset = 6;
		$position = 1.5;
		$file = 1;
		$offset_in_file = 0;
		 */

		$offset         = ( $page - 1 ) * $per_page;
		$position       = $offset / self::MAX_LINES;
		$file_to_open   = floor( $position );
		$offset_in_file = ( $position - $file_to_open ) * self::MAX_LINES;

		$lines = $this->read_lines( $file_to_open, $offset_in_file, $per_page );

		if ( $lines === null ) {
			return array();
		}

		if ( ! isset( $lines[ $offset_in_file ] ) ) {
			$offset_in_file = 0;
			$lines          = $this->read_lines( $file_to_open ++, $offset_in_file, $per_page );

			if ( $lines === null ) {
				return array();
			}
		}

		$total_lines = count( $lines );
		$tz          = new DateTimeZone( 'UTC' );
		$items       = array();
		$has_more    = false;

		for ( $i = $offset_in_file; $i < $total_lines; $i ++ ) {

			if ( empty( $lines[ $i ] ) ) {
				continue;
			}

			$line = $lines[ $i ];

			if ( ( $next_pos = strpos( $line, "\n" ) ) !== false ) {
				$line     = substr( $line, 0, $next_pos );
				$has_more = true;
			}

			$line = strrev( $line );

			list( $time, $level, $group, $message, $user, $ip ) = explode( '||', $line, 7 );

			$props = array(
				'level'   => $level,
				'message' => $message,
				'time'    => new DateTime( $time, $tz ),
				'ip'      => $ip,
				'user'    => (int) substr( $user, 1 ),
				'group'   => $group,
			);

			$items[] = new ITE_Log_Item( $props );
		}

		$has_more = $has_more ?: file_exists( $this->make_file_path( $file_to_open + 1 ) );

		return $items;
	}

	/**
	 * Read log lines from a file.
	 *
	 *
	 *
	 * @param int $file_to_open
	 * @param int $offset_in_file
	 * @param int $per_page
	 *
	 * @return array|null
	 */
	protected function read_lines( $file_to_open, $offset_in_file, $per_page ) {

		$file_path = $this->make_file_path( $file_to_open );

		if ( ! file_exists( $file_path ) ) {
			return array();
		}

		$file = $this->acquire_file_handle( $file_path, 'r' );

		if ( ! $file ) {
			return null;
		}

		if ( ! flock( $file, LOCK_SH ) ) {
			return null;
		}

		if ( ! $file_size = filesize( $file_path ) ) {
			return null;
		}

		// todo optimize

		$contents = fread( $file, $file_size );
		flock( $file, LOCK_UN );
		$contents = strrev( $contents );
		$contents = trim( $contents );

		$explode_limit = $offset_in_file + $per_page;

		return explode( "\n", $contents, $explode_limit );
	}

	/**
	 * Interpolates context values into the message placeholders.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return string
	 */
	protected function interpolate( $message, array $context = array() ) {

		// build a replacement array with braces around the context keys
		$replace = array();

		foreach ( $context as $key => $val ) {
			$replace[ '{' . $key . '}' ] = $this->convert_value_to_string( $val );
		}

		// interpolate replacement values into the message and return
		return strtr( $message, $replace );
	}

	/**
	 * Converts a value of unknown type to a string.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected function convert_value_to_string( $value ) {

		if ( $this->is_resource( $value ) ) {

			$type = get_resource_type( $value );

			return "(Resource:$type)";
		}

		if ( is_object( $value ) ) {

			if ( $value instanceof \Exception || $value instanceof \Throwable ) {
				return '(' . get_class( $value ) . "#{$value->getCode()}:{$value->getMessage()})";
			} elseif ( $value instanceof \DateTime || ( interface_exists( '\DateTimeInterface' ) && $value instanceof \DateTimeInterface ) ) {
				return $value->format( \DateTime::ATOM );
			} elseif ( method_exists( $value, '__toString' ) ) {
				return (string) $value;
			} else {

				$class = get_class( $value );

				return "($class)";
			}
		}

		if ( is_array( $value ) ) {
			return '(Array)';
		}

		if ( is_scalar( $value ) ) {
			return $value;
		}

		if ( $value === null ) {
			return '(Null)';
		}

		return '(Invalid)';
	}

	/**
	 * Check if a value is a resource.
	 *
	 *
	 *
	 * @param $maybe_resource
	 *
	 * @return bool
	 */
	protected function is_resource( $maybe_resource ) {
		return null !== @get_resource_type( $maybe_resource );
	}

	/**
	 * Get the current IP address.
	 *
	 * @link  http://stackoverflow.com/a/19189952
	 *
	 *
	 *
	 * @return string
	 */
	protected function get_ip() {
		return it_exchange_get_ip();
	}

	/**
	 * Make the file name based on the type and time.
	 *
	 *
	 *
	 * @param int $number
	 *
	 * @return string
	 */
	protected function make_file_name( $number = 0 ) {
		$hash = wp_hash( $this->type );

		return "{$this->type}-{$hash}-{$number}.log";
	}

	/**
	 * Make the file path.
	 *
	 *
	 *
	 * @param int $number
	 *
	 * @return string
	 */
	protected function make_file_path( $number = 0 ) {
		return $this->directory . $this->make_file_name( $number );
	}

	/**
	 * Acquire a file handle.
	 *
	 *
	 *
	 * @param string $path
	 * @param string $mode Mode to acquire the file handle in.
	 *
	 * @return resource|null
	 */
	protected function acquire_file_handle( $path, $mode = 'a' ) {

		if ( ! isset( self::$handles[ $path ][ $mode ] ) ) {

			if ( ! ITFileUtility::is_file_writable( $path ) ) {
				_doing_it_wrong( 'ITE_File_Logger', "{$path} is not writable by file logger.", '2.0.0' );

				return null;
			}

			@chmod( $path, 0644 );
			$handle = fopen( $path, $mode );

			if ( ! $handle ) {
				return null;
			}

			if ( ! isset( self::$handles[ $path ] ) ) {
				self::$handles[ $path ] = array();
			}

			self::$handles[ $path ][ $mode ] = $handle;
		}

		return self::$handles[ $path ][ $mode ];
	}

	/**
	 * Get a list of log file names.
	 *
	 *
	 *
	 * @return string[]|WP_Error
	 */
	protected function get_files() {
		$files = glob( "{$this->directory}{$this->type}*.log" );

		if ( is_array( $files ) ) {
			return $files;
		}

		return new WP_Error( 'locate_file_failed', __( 'Unable to find files.', 'it-l10n-ithemes-exchange' ) );
	}

	/**
	 * Get the total number of lines in a file.
	 *
	 * This operation is cached globally.
	 *
	 *
	 *
	 * @param string $path
	 *
	 * @return int|null Number of lines, or null on error.
	 */
	protected function get_lines_in_file( $path ) {

		if ( ! file_exists( $path ) ) {
			return null;
		}

		if ( isset( self::$file_modified_times[ $path ] ) ) {
			$fmtime = filemtime( $path );

			if ( $fmtime === false || $fmtime > self::$file_modified_times[ $path ] ) {
				unset( self::$line_count_cache[ $path ] );
			}
		}

		if ( isset( self::$line_count_cache[ $path ] ) ) {
			return self::$line_count_cache[ $path ];
		}

		$lines = null;

		if ( ! it_exchange_function_is_disabled( 'shell_exec' ) ) {
			$escaped_path = escapeshellarg( $path );

			$wc_return = @shell_exec( "wc -l {$escaped_path} 2>/dev/null" );

			if ( $wc_return && preg_match( '/^(\d+)/', $wc_return, $matches ) ) {
				$lines = (int) $matches[1];
			}
		}

		if ( $lines === null ) {
			$contents = file_get_contents( $path );

			if ( $contents ) {
				$lines = substr_count( $contents, "\n" );
			}
		}

		if ( $lines !== null ) {
			self::$line_count_cache[ $path ] = $lines;
		}

		return $lines;
	}

	/**
	 * Should a file be rotated.
	 *
	 *
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	protected function file_should_rotate( $path ) {

		$lines = $this->get_lines_in_file( $path );

		return $lines !== null && $lines >= self::MAX_LINES;
	}

	/**
	 * Rotate log files.
	 *
	 *
	 *
	 * @return bool
	 */
	protected function rotate() {

		$files = $this->get_files();

		if ( ! is_array( $files ) ) {
			return false;
		}

		// Transforms files into newest -> oldest order.
		sort( $files );

		// Delete all files greater than the maximum number of files allowed + 1
		for ( $i = ( count( $files ) - 1 ); $i >= self::MAX_FILES; $i -- ) {
			@unlink( $files[ $i ] );
			unset( $files[ $i ] );
		}

		$oldest_first = array_reverse( $files, true );

		foreach ( $oldest_first as $number => $old_name ) {
			$new_name = preg_replace( '/(\d+)\.log/', $number + 1, $old_name ) . '.log';

			if ( ! rename( $old_name, $new_name ) ) {
				return false;
			}
		}

		self::$line_count_cache = array();
		self::$handles          = array();

		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function __destruct() {

		foreach ( self::$handles as $name => $types ) {
			foreach ( $types as $handle ) {
				@fclose( $handle );
			}
		}
	}
}
