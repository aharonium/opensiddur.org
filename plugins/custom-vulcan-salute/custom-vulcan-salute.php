<?php
/**
 * Plugin Name:       Custom Vulcan Salute
 * Plugin URI:        https://opensiddur.org/
 * Description:       Adds a [vulcan_salute] shortcode that renders the Vulcan Salute
 *                     (U+1F596) — used here in reference to the hand posture of the
 *                     Priestly Blessing (Birkat Kohanim) — as inline, bidi-safe <span>
 *                     markup. Defaults to showing both hands side by side (right hand
 *                     before left hand, thumbs facing inward); either hand alone is
 *                     also selectable.
 *                     Supports full-color emoji (with optional Unicode skin tone
 *                     modifiers) vs. line-art ("vs15") rendering, and two independent
 *                     sizing modes: inherited/text-matching (default), viewport-fluid
 *                     (CSS clamp()), or layout-relative (CSS Container Query units,
 *                     filling a percentage of the available horizontal space).
 * Version:           1.3.0
 * Author:            Aharon Varady
 * Author URI:        https://aharonvarady.org/
 * License:           LGPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/lgpl-3.0.html
 * Text Domain:       custom-vulcan-salute
 *
 * Usage:
 *   [vulcan_salute]                          Both hands, right before left, sized to
 *                                             match the surrounding text (default)
 *   [vulcan_salute hand="left"]              Left hand (mirrored) only
 *   [vulcan_salute hand="right"]             Right hand only
 *   [vulcan_salute rendering="vs15"]         Line-art / text-style presentation
 *   [vulcan_salute skin_tone="medium"]       Skin tone modifier (emoji rendering only;
 *                                             'none' (default) | 'light' | 'medium-light' |
 *                                             'medium' | 'medium-dark' | 'dark'. Silently
 *                                             ignored when rendering="vs15".)
 *   [vulcan_salute size="large"]             Named viewport-fluid preset (opt-in)
 *   [vulcan_salute size="100%"]              Layout-relative: pair fills 100% of the
 *                                             available horizontal layout width and
 *                                             scales to match, via CSS Container Query
 *                                             units. Requires a browser supporting CSS
 *                                             Container Queries (all evergreen browsers
 *                                             as of 2023+); older browsers fall back to
 *                                             normal inherited text size.
 *   [vulcan_salute min="1rem" preferred="5vw" max="3rem"]   Raw clamp() override
 *   [vulcan_salute label="Priestly Blessing gesture"]       Custom accessible label
 *
 *   All output is a single inline <span> (or, in "both" mode, one inline-flex <span>
 *   wrapping two inner spans) — safe to place mid-sentence in a paragraph without
 *   forcing a line break. Note that requesting a large layout-relative percentage
 *   inside running text may push the whole pair onto its own line if there isn't
 *   room for it next to preceding text on the current line — the two hands will
 *   never split from each other, but the pair as a unit follows normal inline
 *   line-wrapping rules like any other wide inline element.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Named fluid-size presets, expressed as clamp() triples.
 * "preferred" uses vw so the glyph scales fluidly between min and max
 * as the viewport changes, per the standard CSS clamp() fluid-typography approach.
 */
function cvs_size_presets() {
	return array(
		'small'  => array(
			'min'       => '1rem',
			'preferred' => '2.5vw',
			'max'       => '1.5rem',
		),
		'medium' => array(
			'min'       => '1.5rem',
			'preferred' => '4vw',
			'max'       => '2.5rem',
		),
		'large'  => array(
			'min'       => '2rem',
			'preferred' => '6vw',
			'max'       => '4rem',
		),
		'xl'     => array(
			'min'       => '3rem',
			'preferred' => '8vw',
			'max'       => '6rem',
		),
	);
}

/**
 * Determine whether a 'size' attribute value requests layout-relative
 * (percent/Container Query) sizing rather than a named viewport-fluid preset.
 * Accepts a bare number ('100') or an explicit percentage ('100%').
 *
 * @return float|null The requested percentage, or null if this is not percent mode.
 */
function cvs_parse_percent_size( $size_raw ) {
	if ( preg_match( '/^(\d+(?:\.\d+)?)%?$/', trim( $size_raw ), $matches ) ) {
		return (float) $matches[1];
	}
	return null;
}

/**
 * Resolve a viewport-fluid clamp() font-size from named presets + raw overrides.
 * Only used when NOT in percent/layout-relative mode.
 */
function cvs_resolve_fluid_font_size( $atts ) {
	$presets    = cvs_size_presets();
	$preset_key = strtolower( trim( $atts['size'] ) );
	$preset     = isset( $presets[ $preset_key ] ) ? $presets[ $preset_key ] : $presets['medium'];

	$min       = ( '' !== trim( $atts['min'] ) ) ? $atts['min'] : $preset['min'];
	$preferred = ( '' !== trim( $atts['preferred'] ) ) ? $atts['preferred'] : $preset['preferred'];
	$max       = ( '' !== trim( $atts['max'] ) ) ? $atts['max'] : $preset['max'];

	return sprintf( 'clamp(%s, %s, %s)', $min, $preferred, $max );
}

/**
 * Glyph font-size (in Container Query inline-size units) used in percent/
 * layout-relative mode. This is a fixed ratio, NOT multiplied by the
 * requested percentage — the percentage already scales the container itself
 * (via its width), so cqi units on the glyph automatically scale right along
 * with it. Tuned assuming the default 0.2em gap; a heavily customized gap may
 * need a small manual adjustment to avoid the pair overflowing its container.
 *
 * @param int $num_hands 1 for a single hand, 2 for the "both hands" pair.
 */
function cvs_percent_glyph_font_size( $num_hands ) {
	return ( 2 === $num_hands ) ? '42cqi' : '90cqi';
}

/**
 * Build the base container-query styles for a layout-relative ("percent") wrapper.
 * container-type:inline-size both establishes the Container Query context for
 * descendant cqi units AND applies the size containment required to avoid a
 * circular dependency between the wrapper's own width and its content's size.
 */
function cvs_percent_wrapper_styles( $percent ) {
	return sprintf( 'width:%s%%;container-type:inline-size;', esc_attr( $percent ) );
}

/**
 * UTF-8 byte sequences for the standard Unicode Fitzpatrick skin tone modifiers.
 * These attach to certain "Emoji_Modifier_Base" characters (U+1F596 is one)
 * to form an "emoji modifier sequence" — the same mechanism behind skin-tone
 * variants of emoji like a thumbs-up. Per the Unicode spec, the recommended
 * sequence is simply base + modifier, with no variation selector — the
 * modifier itself already implies emoji (color) presentation.
 */
function cvs_skin_tone_modifier_bytes( $skin_tone ) {
	$map = array(
		'light'        => "\xF0\x9F\x8F\xBB", // U+1F3FB EMOJI MODIFIER FITZPATRICK TYPE-1-2
		'medium-light' => "\xF0\x9F\x8F\xBC", // U+1F3FC EMOJI MODIFIER FITZPATRICK TYPE-3
		'medium'       => "\xF0\x9F\x8F\xBD", // U+1F3FD EMOJI MODIFIER FITZPATRICK TYPE-4
		'medium-dark'  => "\xF0\x9F\x8F\xBE", // U+1F3FE EMOJI MODIFIER FITZPATRICK TYPE-5
		'dark'         => "\xF0\x9F\x8F\xBF", // U+1F3FF EMOJI MODIFIER FITZPATRICK TYPE-6
	);
	return isset( $map[ $skin_tone ] ) ? $map[ $skin_tone ] : '';
}

/**
 * Build the raw glyph string for a given rendering style.
 *
 * U+1F596 VULCAN SALUTE, expressed as raw UTF-8 byte sequences so the
 * invisible variation selectors survive editing in any text editor.
 *
 * @param string $rendering  'emoji' | 'vs15'.
 * @param string $skin_tone  'none' | 'light' | 'medium-light' | 'medium' | 'medium-dark' | 'dark'.
 *                            Only applied when $rendering is 'emoji' — the vs15
 *                            line-art presentation has no color to modify, so a
 *                            skin tone request is silently ignored there.
 */
function cvs_build_glyph( $rendering, $skin_tone = 'none' ) {
	$glyph_base = "\xF0\x9F\x96\x96"; // U+1F596
	$vs_emoji   = "\xEF\xB8\x8F";     // U+FE0F  VARIATION SELECTOR-16 (force emoji presentation)
	$vs_text    = "\xEF\xB8\x8E";     // U+FE0E  VARIATION SELECTOR-15 (force text/line-art presentation, "vs15")

	if ( 'vs15' === $rendering ) {
		return $glyph_base . $vs_text;
	}

	$modifier = cvs_skin_tone_modifier_bytes( $skin_tone );
	if ( '' !== $modifier ) {
		return $glyph_base . $modifier;
	}

	return $glyph_base . $vs_emoji;
}

/**
 * Render a single hand as a <span>.
 *
 * @param bool $mirrored     True for the left (mirrored) hand.
 * @param string $rendering  'emoji' | 'vs15'.
 * @param string $skin_tone  'none' | 'light' | 'medium-light' | 'medium' | 'medium-dark' | 'dark'.
 *                            Ignored when $rendering is 'vs15'.
 * @param string $font_size  Pre-built CSS font-size value (clamp(...) or an N cqi value).
 * @param bool $standalone   True if this span is used on its own (gets its own
 *                            role="img"/aria-label); false if it is one half of a
 *                            "both hands" pair (gets aria-hidden so the outer
 *                            wrapper's label is what screen readers announce).
 * @param string $label      Accessible label to use when $standalone is true.
 */
function cvs_render_hand_span( $mirrored, $rendering, $skin_tone, $font_size, $standalone, $label ) {
	$styles = array(
		'display'     => 'inline-block',
		'line-height' => '1',
		'font-size'   => $font_size,
	);
	if ( $mirrored ) {
		$styles['transform'] = 'scaleX(-1)';
	}

	$style_pairs = array();
	foreach ( $styles as $prop => $value ) {
		$style_pairs[] = $prop . ':' . $value;
	}
	$style_attr = esc_attr( implode( ';', $style_pairs ) );
	$glyph      = cvs_build_glyph( $rendering, $skin_tone );
	$hand_attr  = esc_attr( $mirrored ? 'left' : 'right' );
	$rend_attr  = esc_attr( $rendering );
	$skin_attr  = esc_attr( ( 'emoji' === $rendering ) ? $skin_tone : 'none' );

	if ( $standalone ) {
		return sprintf(
			'<span class="cvs-vulcan-salute" role="img" aria-label="%1$s" data-hand="%2$s" data-rendering="%3$s" data-skin-tone="%4$s" style="%5$s">%6$s</span>',
			esc_attr( $label ),
			$hand_attr,
			$rend_attr,
			$skin_attr,
			$style_attr,
			$glyph
		);
	}

	return sprintf(
		'<span class="cvs-vulcan-salute" aria-hidden="true" data-hand="%1$s" data-rendering="%2$s" data-skin-tone="%3$s" style="%4$s">%5$s</span>',
		$hand_attr,
		$rend_attr,
		$skin_attr,
		$style_attr,
		$glyph
	);
}

/**
 * Resolve the font-size to use for a hand glyph, across all three sizing modes:
 *  - percent/layout-relative (Container Query cqi units) — when $percent is not null
 *  - inherit — the default: matches the surrounding text's font-size exactly,
 *    via the CSS keyword 'inherit', with no clamp()/vw or Container Query math
 *    involved at all
 *  - fluid — named preset ('small'|'medium'|'large'|'xl') or raw min/preferred/max
 *    overrides, producing a viewport-relative clamp() expression. Reached only
 *    when the person explicitly asks for a named preset, or supplies at least
 *    one of min/preferred/max.
 *
 * @param array $atts      Shortcode attributes.
 * @param float|null $percent  Parsed percent value from cvs_parse_percent_size(), or null.
 * @param int $num_hands   1 for a single hand, 2 for the "both hands" pair (percent mode only).
 */
function cvs_determine_font_size( $atts, $percent, $num_hands ) {
	if ( null !== $percent ) {
		return cvs_percent_glyph_font_size( $num_hands );
	}

	$size_raw         = strtolower( trim( $atts['size'] ) );
	$has_custom_clamp = ( '' !== trim( $atts['min'] ) ) || ( '' !== trim( $atts['preferred'] ) ) || ( '' !== trim( $atts['max'] ) );
	$is_inherit       = in_array( $size_raw, array( '', 'inherit', 'default', 'text' ), true );

	if ( $is_inherit && ! $has_custom_clamp ) {
		return 'inherit';
	}

	return cvs_resolve_fluid_font_size( $atts );
}

/**
 * [vulcan_salute] shortcode handler.
 */
function cvs_vulcan_salute_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'hand'      => 'both',    // 'both' (default, right then left) | 'left' | 'right'
			'rendering' => 'emoji',   // 'emoji' (full color) | 'vs15' (line-art/text style)
			'skin_tone' => 'none',    // 'none' (default) | 'light' | 'medium-light' | 'medium' | 'medium-dark' | 'dark'
			                          // Applies only when rendering="emoji"; ignored for vs15.
			'size'      => 'inherit', // 'inherit' (default, matches surrounding text) | 'small' | 'medium' | 'large' | 'xl' | a percentage e.g. '100%'
			'min'       => '',        // optional raw override for clamp() minimum (fluid mode only)
			'preferred' => '',        // optional raw override for clamp() preferred value (fluid mode only)
			'max'       => '',        // optional raw override for clamp() maximum (fluid mode only)
			'gap'       => '0.2em',   // spacing between hands in "both" mode
			'label'     => '',        // optional custom aria-label
		),
		$atts,
		'vulcan_salute'
	);

	$hand      = strtolower( trim( $atts['hand'] ) );
	$rendering = strtolower( trim( $atts['rendering'] ) );
	$percent   = cvs_parse_percent_size( $atts['size'] );

	$valid_skin_tones = array( 'none', 'light', 'medium-light', 'medium', 'medium-dark', 'dark' );
	$skin_tone_raw    = strtolower( trim( $atts['skin_tone'] ) );
	$skin_tone        = in_array( $skin_tone_raw, $valid_skin_tones, true ) ? $skin_tone_raw : 'none';

	// --- Single hand: 'left' or 'right' -------------------------------------
	if ( 'left' === $hand || 'right' === $hand ) {
		$mirrored      = ( 'left' === $hand );
		$default_label = $mirrored
			? __( 'Vulcan salute (mirrored, left hand)', 'custom-vulcan-salute' )
			: __( 'Vulcan salute', 'custom-vulcan-salute' );
		$label = ( '' !== trim( $atts['label'] ) ) ? sanitize_text_field( $atts['label'] ) : $default_label;

		if ( null !== $percent ) {
			$inner_span    = cvs_render_hand_span( $mirrored, $rendering, $skin_tone, cvs_determine_font_size( $atts, $percent, 1 ), false, '' );
			$wrapper_style = esc_attr(
				'display:inline-flex;white-space:nowrap;vertical-align:middle;' . cvs_percent_wrapper_styles( $percent )
			);
			return sprintf(
				'<span class="cvs-vulcan-salute-scale" role="img" aria-label="%1$s" style="%2$s">%3$s</span>',
				esc_attr( $label ),
				$wrapper_style,
				$inner_span
			);
		}

		$font_size = cvs_determine_font_size( $atts, null, 1 );
		return cvs_render_hand_span( $mirrored, $rendering, $skin_tone, $font_size, true, $label );
	}

	// --- Default: both hands, right before left, thumbs facing inward -------
	$default_label = __( 'Priestly Blessing hand gesture', 'custom-vulcan-salute' );
	$label         = ( '' !== trim( $atts['label'] ) ) ? sanitize_text_field( $atts['label'] ) : $default_label;
	$gap           = esc_attr( $atts['gap'] );

	// Unmirrored (right hand) renders first/leftmost, mirrored (left hand)
	// second/rightmost. This is not arbitrary: the base glyph's thumb sits on
	// its own right edge, so this specific ordering is what puts both thumbs
	// toward the center gap (inward) rather than out toward the edges.
	$glyph_font_size = cvs_determine_font_size( $atts, $percent, 2 );
	$right_span      = cvs_render_hand_span( false, $rendering, $skin_tone, $glyph_font_size, false, '' );
	$left_span       = cvs_render_hand_span( true, $rendering, $skin_tone, $glyph_font_size, false, '' );

	// direction:ltr + unicode-bidi:isolate lock the visual arrangement above
	// regardless of the surrounding paragraph's text direction. This composition
	// depicts a specific physical posture (the Kohen's actual right and left
	// hands, as the congregation sees them) rather than flowing text, so it
	// should not silently mirror itself inside RTL content.
	$base_wrapper_style = sprintf(
		'display:inline-flex;flex-wrap:nowrap;white-space:nowrap;align-items:center;vertical-align:middle;gap:%s;direction:ltr;unicode-bidi:isolate;',
		$gap
	);
	$wrapper_style = esc_attr(
		$base_wrapper_style . ( ( null !== $percent ) ? cvs_percent_wrapper_styles( $percent ) : '' )
	);

	return sprintf(
		'<span class="cvs-vulcan-salute-pair" role="img" aria-label="%1$s" style="%2$s">%3$s%4$s</span>',
		esc_attr( $label ),
		$wrapper_style,
		$right_span,
		$left_span
	);
}
add_shortcode( 'vulcan_salute', 'cvs_vulcan_salute_shortcode' );