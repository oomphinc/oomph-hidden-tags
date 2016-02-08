<?php
/**
Plugin Name: Oomph Hidden Tags
Plugin URI: http://www.oomphinc.com/plugins-modules/oomph-hidden-tags
Description: Hide tags from front-end users
Author: Ben Doherty @ Oomph, Inc.
Version: 0.1
Author URI: http://www.oomphinc.com/thinking/author/bdoherty/
License: GPLv2 or later

		Copyright © 2014 Oomph, Inc. <http://oomphinc.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 

/**
 * @package Oomph Clone Widgets
 */
class Oomph_Hidden_Tags {
	private static $instance = false;
	public static function instance() {
		if( !self::$instance )
			self::$instance = new Oomph_Hidden_Tags;

		return self::$instance;
	}

	private function __clone() { }

	// Store plugin options here
	const OPTION_NAME = 'hidden_tags';

	// Capability to see hidden tags
	const CAPABILITY = 'see_hidden_tags';

	function __construct() {
		add_filter( 'term_links-post_tag', array( $this, 'filter_tag_links' ), 0 );
		add_filter( 'tag_cloud_sort', array( $this, 'filter_tag_cloud' ), 0, 2 );
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'wp_head', array( $this, 'action_wp_head' ) );
	}

	/**
	 * Add setting field
	 *
	 * @action admin_init
	 */
	function action_admin_init() {
		register_setting( 'reading', self::OPTION_NAME, array( $this, 'sanitize_options' ) );
		add_settings_section( 'hide-tags', __( 'Hide Tags', 'hidden-tags' ), array( $this, 'hidden_tags_settings' ), 'reading' );
	}

	/**
	 * Add default style to wp_head
	 *
	 * @action wp_head
	 */
	function action_wp_head() {
		if( !current_user_can( self::CAPABILITY ) ) {
			return;
		} ?>
		<style>a.hidden-tag { color: #999; }</style>
	<?php
	}

	/**
	 * Display tags setting form
	 */
	function hidden_tags_settings() {
		$comma = _x( ',', 'tag delimiter' );
		$tax_name = 'post_tag';
		$taxonomy = get_taxonomy( $tax_name );
		$tags = get_option( self::OPTION_NAME );

		if( !is_array( $tags ) ) {
			$tags = array();
		}

		$tags = wp_parse_args( $tags, array( 'tags' => array() ) );

		// Is there a tag, any tag?
		$a_tag = get_terms( 'post_tag', array( 'hide_empty' => false, 'number' => 1 ) );
?>
	<table class="form-table">

		<tr valign="top">
			<th scope="row"><?php _e('Hidden Tags:') ?></th>
			<td id="tagsdiv-post_tag">
				<?php if( empty( $a_tag ) ) { ?>
				<p class="description">No tags exist on your site. Please create tags before attempting to hide them!</p>
				<?php } else { ?>
				<div id="post_tag" class="tagsdiv">
					<div class="ajaxtag">
						<div class="nojs-tags hide-if-js">
						<p><?php echo $taxonomy->labels->add_or_remove_items; ?></p>
						<textarea name="<?php echo esc_attr( self::OPTION_NAME . '[tags]' ); ?>" rows="3" cols="20" class="the-tags" id="<?php echo esc_attr( 'tax-input' . $tax_name ); ?>"><?php echo esc_textarea( implode( ', ', $tags['tags'] ) ); ?></textarea></div>
						<div class="ajaxtag hide-if-no-js">
							<div class="taghint"><?php echo $taxonomy->labels->add_new_item; ?></div>
							<p><input type="text" id="new-tag-<?php echo esc_attr( $tax_name ); ?>" name="<?php echo esc_attr( self::OPTION_NAME . '[newtag]' ); ?>" class="newtag form-input-tip" autocomplete="off" value="" />
							<input type="button" class="button tagadd" value="<?php esc_attr_e('Add'); ?>" /></p>
						</div>
						<p class="howto"><?php echo $taxonomy->labels->separate_items_with_commas; ?></p>
					</div>
					<div class="tagchecklist"></div>
				</div>
	
				<p class="description">Hide these tags from in tag lists and clouds, unless the logged-in user has the <strong>see_hidden_tags</strong> capability.</p>
				<?php } ?>
			</td>
		</tr>
	</table>
<?php

		wp_enqueue_script( 'post' );
	}

	/**
	 * Sanitize this option
	 */
	function sanitize_options( $options ) {
		$current = get_option( self::OPTION_NAME );

		if( !is_array( $current ) ) {
			$current = array();
		}

		if( !isset( $options['tags'] ) || !is_string( $options['tags'] ) ) {
			$options['tags'] = '';
		}

		$current['tags'] = array_map( 'sanitize_text_field', preg_split( '/\s*,(\s*,?)*/', $options['tags'] ) );

		// Add any tags in the box, but we're not in the business of creating new tags
		if( isset( $options['newtag'] ) && !empty( $options['newtag'] ) ) {
			$current['tags'][] = array_map( 'sanitize_text_field', preg_split( '/\s*,(\s*,?)*/', $options['newtag'] ) );
		}

		$all_tags = get_terms( 'post_tag', array( 'hide_empty' => false, 'fields' => 'id=>name' ) );

		return array(
			'tags' => array_values( array_intersect( $all_tags, $current['tags'] ) )
		);
	}

	/**
	 * Filter tag links, omitting hidden ones (unless user has capability)
	 *
	 * This plugin assumes tag links are tag names in an <a> tag that matches 
	 * exactly. If other plugins modify the text content of the <a> tags before 
	 * this filter, it may not function correctly.
	 *
	 * Capable users will see hidden tags at the end of the tag list, and a 
	 * .hidden-tag class will be applied which by default is grayed out
	 *
	 * @action term_links-post_tag
	 */
	function filter_tag_links( $term_links ) {
		$option = get_option( self::OPTION_NAME );

		if( !is_array( $option ) || !isset( $option['tags'] ) || empty( $option['tags'] ) ) {
			return $term_links;
		}

		// Put hidden tags at the end for capable users, and add .hidden-tag class
		$result = array();
		$hidden = array();

		foreach( $term_links as $term_link ) {
			$name = strip_tags( $term_link );

			if( in_array( $name, $option['tags'] ) ) {
				if( current_user_can( self::CAPABILITY ) ) {
					// Add 'hidden-tag' to inner most tag (probably a) 
					$term_link = preg_replace( '/(?: class="(.+)"([^>]*))?>' . $name . '/', ' class="$1 hidden-tag"$2>' . $name, $term_link );
					$hidden[] = $term_link;
				}

				continue;
			}

			$result[] = $term_link;
		}

		return array_merge( $result, $hidden );
	}

	/**
	 * Remove hidden tags from tag cloud
	 *
	 * @filter tag_cloud_sort
	 */
	function filter_tag_cloud( $tags, $args ) {
		if( $args['taxonomy'] != 'post_tag' ) {
			return $tags;
		}

		// tag_cloud_sort is the only hook we can use to exclude from the tag cloud,
		// but if we modify the array, it is assumed that the sorting has already occured,
		// so we have to replicate the ordering types
		extract( $args );

		$option = get_option( self::OPTION_NAME );

		if( !is_array( $option ) || !isset( $option['tags'] ) || empty( $option['tags'] ) ) {
			return $tags;
		}

		$result = array();

		foreach( $tags as $idx => $tag ) {
			if( in_array( $tag->name, $option['tags'] ) ) {
				continue;
			}

			$result[$idx] = $tag;
		}

		if ( 'RAND' == $order ) {
			shuffle( $result );
		} else {
			// SQL cannot save you; this is a second (potentially different) sort on a subset of data.
			if ( 'name' == $orderby ) {
				uasort( $result, '_wp_object_name_sort_cb' );
			}
			else {
				uasort( $result, '_wp_object_count_sort_cb' );
			}

			if ( 'DESC' == $order ) {
				$result = array_reverse( $result, true );
			}
		}

		return $result;
	}
}
new Oomph_Hidden_Tags;
