<?php
/**
 * Plugin Name: Media Link for Elementor
 * Description: Allow users to create media links in Elementor Page Builder.
 * Plugin URI: https://github.com/caercam/elementor-media-link
 * Author: Charlie Merland
 * Version: 1.0.0
 * Author URI: https://caercam.org
 */

 /*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 Copyright 2005-2019 Charlie Merland
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add ellipsis overflow to Elementor autocomplete menu items. Media file names
 * can be quite long.
 *
 * @since 1.0.0
 */
function eml_enqueue_styles() {

	wp_add_inline_style( 'elementor-editor', '.elementor-autocomplete-menu > .ui-menu-item > span:first-child {overflow: hidden; padding: 2px 2px 2px 0; text-overflow: ellipsis; white-space: nowrap;}' );
}

add_action( 'elementor/editor/after_enqueue_styles', 'eml_enqueue_styles' );

/**
 * Filters the link query arguments.
 *
 * @since 1.0.0
 *
 * @param array $query An array of WP_Query arguments.
 *
 * @return array
 */
function eml_link_query_args( $query ) {

  if ( 'elementor' !== $_REQUEST['editor'] ) {
    return $query;
  }

  $post_types = (array) $query['post_type'];
  if ( in_array( 'attachment', $post_types, true ) ) {
    $query['post_status'] = array_merge( (array) $query['post_status'], array( 'inherit' ) );
  }

  return $query;
}

add_filter( 'wp_link_query_args', 'eml_link_query_args' );


/**
 * Add media type to the link query results.
 *
 * @since 1.0.0
 *
 * @param array $results Query results.
 * @param array $query   An array of WP_Query arguments.
 *
 * @return array
 */
function eml_wp_link_query( $results, $query ) {

  $post_type = get_post_type_object( 'attachment' );
  $name = $post_type->labels->singular_name;

  foreach ( $results as $i => $result ) {

    if ( $name === $result['info'] ) {

			$post = get_post( $result['ID'] );
			if ( is_null( $post ) ) {
				continue;
			}

      $url = wp_get_attachment_url( $result['ID'] );
      if ( $url ) {
        $results[ $i ]['permalink'] = $url;
      }

			$results[ $i ]['info'] = eml_get_file_readable_type( $post->post_mime_type );
    }
  }

  return $results;
}

add_filter( 'wp_link_query', 'eml_wp_link_query', 10, 2 );

/**
 * Translate MIME Types into readable file types.
 *
 * @see wp_get_mime_types()
 *
 * @since 1.0.0
 *
 * @param string $mime_type Post MIME Type.
 *
 * @return string Human readable MIME Type.
 */
function eml_get_file_readable_type( $mime_type ) {

	$mime_type = strtolower( $mime_type );

	switch ( $mime_type ) {
		// Image formats.
		case 'image/jpeg':
		case 'image/gif':
		case 'image/png':
		case 'image/bmp':
		case 'image/tiff':
		case 'image/x-icon':
			$type = __( 'Image' );
			break;
		// Video formats.
		case 'video/x-ms-asf':
		case 'video/x-ms-wmv':
		case 'video/x-ms-wmx':
		case 'video/x-ms-wm':
		case 'video/avi':
		case 'video/divx':
		case 'video/x-flv':
		case 'video/quicktime':
		case 'video/mpeg':
		case 'video/mp4':
		case 'video/ogg':
		case 'video/webm':
		case 'video/x-matroska':
		case 'video/3gpp': // Can also be audio
		case 'video/3gpp2': // Can also be audio
			$type = __( 'Video' );
			break;
		// Text formats.
		case 'text/plain':
		case 'text/csv':
		case 'text/tab-separated-values':
		case 'text/calendar':
		case 'text/richtext':
		case 'text/css':
		case 'text/html':
		case 'text/vtt':
		case 'application/ttaf+xml':
		case 'application/rtf':
			$type = __( 'Text' );
			break;
		// Audio formats.
		case 'audio/mpeg':
		case 'audio/aac':
		case 'audio/x-realaudio':
		case 'audio/wav':
		case 'audio/ogg':
		case 'audio/flac':
		case 'audio/midi':
		case 'audio/x-ms-wma':
		case 'audio/x-ms-wax':
		case 'audio/x-matroska':
			$type = __( 'Audio' );
			break;
		// Misc application formats.
		case 'application/javascript':
			$type = __( 'JavaScript' );
			break;
		case 'application/pdf':
			$type = __( 'PDF' );
			break;
		case 'application/x-shockwave-flash':
			$type = __( 'Flash' );
			break;
		case 'application/java':
			$type = __( 'Java' );
			break;
		case 'application/x-tar':
		case 'application/zip':
		case 'application/x-gzip':
		case 'application/rar':
		case 'application/x-7z-compressed':
			$type = __( 'Archive' );
			break;
		case 'application/x-msdownload':
		case 'application/octet-stream':
		case 'application/octet-stream':
			$type = __( 'Application' );
			break;
		// MS Office formats.
		case 'application/msword':
		case 'application/vnd.ms-powerpoint':
		case 'application/vnd.ms-write':
		case 'application/vnd.ms-excel':
		case 'application/vnd.ms-access':
		case 'application/vnd.ms-project':
		case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
		case 'application/vnd.ms-word.document.macroEnabled.12':
		case 'application/vnd.openxmlformats-officedocument.wordprocessingml.template':
		case 'application/vnd.ms-word.template.macroEnabled.12':
		case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
		case 'application/vnd.ms-excel.sheet.macroEnabled.12':
		case 'application/vnd.ms-excel.sheet.binary.macroEnabled.12':
		case 'application/vnd.openxmlformats-officedocument.spreadsheetml.template':
		case 'application/vnd.ms-excel.template.macroEnabled.12':
		case 'application/vnd.ms-excel.addin.macroEnabled.12':
		case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
		case 'application/vnd.ms-powerpoint.presentation.macroEnabled.12':
		case 'application/vnd.openxmlformats-officedocument.presentationml.slideshow':
		case 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12':
		case 'application/vnd.openxmlformats-officedocument.presentationml.template':
		case 'application/vnd.ms-powerpoint.template.macroEnabled.12':
		case 'application/vnd.ms-powerpoint.addin.macroEnabled.12':
		case 'application/vnd.openxmlformats-officedocument.presentationml.slide':
		case 'application/vnd.ms-powerpoint.slide.macroEnabled.12':
		case 'application/onenote':
		case 'application/oxps':
		case 'application/vnd.ms-xpsdocument':
			$type = __( 'MS Office' );
			break;
		// OpenOffice formats.
		case 'application/vnd.oasis.opendocument.text':
		case 'application/vnd.oasis.opendocument.presentation':
		case 'application/vnd.oasis.opendocument.spreadsheet':
		case 'application/vnd.oasis.opendocument.graphics':
		case 'application/vnd.oasis.opendocument.chart':
		case 'application/vnd.oasis.opendocument.database':
		case 'application/vnd.oasis.opendocument.formula':
				$type = __( 'OpenOffice' );
				break;
			// WordPerfect formats.
		case 'application/wordperfect':
			$type = __( 'WordPerfect' );
			break;
		// iWork formats.
		case 'application/vnd.apple.keynote':
		case 'application/vnd.apple.numbers':
		case 'application/vnd.apple.pages':
			$type = __( 'iWork' );
			break;
		default:
			$type = __( 'Media File' );
			break;
	}

	return $type;
}
