<?php
/*
Plugin Name: Custom Dynamic Date 
Plugin URI:  https://github.com/aharonium/opensiddur.org/tree/master/plugins/custom-dynamic-date
Description: Provides a shortcode [dyn_date] that dynamically displays a calculated date or year (supports offset, unit, and base year).
Version:     1.0
Author:      Aharon Varady
License:     LGPL3
License URI: https://www.gnu.org/licenses/lgpl-3.0.html
*/

/**
 * Dynamic Date Shortcode
 * Usage: [dyn_date by="1980" offset="-95" unit="years" format="Y"]
 */
function dyn_date_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'by'     => '',       // base year (integer)
        'offset' => '0',      // offset value (integer)
        'unit'   => 'years',  // units: years, months, days
        'format' => 'Y',      // PHP date() format
    ), $atts, 'dyn_date' );

    $offset = intval( $atts['offset'] );
    $unit   = strtolower( trim( $atts['unit'] ) );
    $format = sanitize_text_field( $atts['format'] );

    // Determine base timestamp
    if ( $atts['by'] !== '' ) {
        $base_year = intval( $atts['by'] );
        // Build timestamp from base year (Jan 1 of that year)
        $base_ts = strtotime( "{$base_year}-01-01" );
    } else {
        // Use WordPress' current time respecting timezone
        $base_ts = current_time( 'timestamp' );
    }

    // Normalize unit keyword
    if ( ! in_array( $unit, array( 'years', 'months', 'days' ), true ) ) {
        $unit = 'years';
    }

    // Apply offset using strtotime()
    $operator = ( $offset >= 0 ) ? '+' : '';
    $expression = "{$operator}{$offset} {$unit}";
    $result_ts = strtotime( $expression, $base_ts );

    // Fallback if parsing fails
    if ( $result_ts === false ) {
        return '';
    }

    return date_i18n( $format, $result_ts );
}
add_shortcode( 'dyn_date', 'dyn_date_shortcode' );
