<?php
/**
 * Contains the email tag replacer interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Shortcode_Tag_Replacer
 */
interface IT_Exchange_Email_Tag_Replacer extends IT_Exchange_Email_Middleware {

	/**
	 * Replace the email tags.
	 *
	 *
	 *
	 * @param string $content
	 * @param array  $context
	 *
	 * @return string
	 */
	public function replace( $content, $context );

	/**
	 * Format a tag.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Tag|string $tag
	 *
	 * @return string
	 */
	public function format_tag( $tag );

	/**
	 * Add a tag to be replaced.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Tag $tag
	 *
	 * @return self
	 */
	public function add_tag( IT_Exchange_Email_Tag $tag );

	/**
	 * Register multiple tags at once.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Tag[] $tags
	 *
	 * @return self
	 */
	public function add_tags( array $tags );

	/**
	 * Get a tag object for a given tag.
	 *
	 *
	 *
	 * @param string $tag
	 *
	 * @return IT_Exchange_Email_Tag|null
	 */
	public function get_tag( $tag );

	/**
	 * Get all registered tags.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags();

	/**
	 * Get all tags for a given notification.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Notification $notification
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags_for( IT_Exchange_Email_Notification $notification );

	/**
	 * Get a map of tags to their replacements.
	 *
	 *
	 *
	 * @param string $content
	 * @param array  $context
	 *
	 * @return array
	 */
	public function get_replacement_map( $content, $context );

	/**
	 * Transform all tags in a set of content to another format.
	 *
	 * Used when passing content to the templating system of the mail provider.
	 *
	 *
	 *
	 * @param string $open_tag  Format to be used for opening a tag.
	 * @param string $close_tag Format to be used for closing a tag.
	 * @param string $content   Content to be operated on.
	 *
	 * @return string
	 */
	public function transform_tags_to_format( $open_tag, $close_tag, $content );
}
