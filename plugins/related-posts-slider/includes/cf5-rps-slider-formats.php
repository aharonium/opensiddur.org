<?php //Slider format functions

//format = h_carousel

function cf5_rps_wp_init_h_carousel() {
    global $cf5_rps;

    if ( ! is_array( $cf5_rps ) ) {
        return;
    }

    $format_style = ! empty( $cf5_rps['format_style'] )
        ? $cf5_rps['format_style']
        : 'default';

    $css = 'formats/h_carousel/styles/' . $format_style . '/style.css';

    wp_enqueue_style(
        'cf5_rps_h_carousel_css',
        cf5_rps_url( $css ),
        array(),
        CF5_RPS_VER,
        'all'
    );

    /* ---------- Swiper (replaces the old caroufredsel/jcarousel engine) ---------- */

    wp_enqueue_style(
        'cf5_rps_swiper_css',
        cf5_rps_url( 'assets/swiper/swiper-bundle.min.css' ),
        array(),
        CF5_RPS_VER,
        'all'
    );

    wp_enqueue_script(
        'cf5_rps_swiper_js',
        cf5_rps_url( 'assets/swiper/swiper-bundle.min.js' ),
        array(),
        CF5_RPS_VER,
        true
    );

    wp_enqueue_script(
        'cf5_rps_swiper_init',
        cf5_rps_url( 'assets/swiper/rps-swiper-init.js' ),
        array( 'cf5_rps_swiper_js' ),
        CF5_RPS_VER,
        true
    );

    $per_page = isset( $cf5_rps['per_page'] ) ? max( 1, (int) $cf5_rps['per_page'] ) : 1;
    $scroll   = isset( $cf5_rps['scroll'] ) ? max( 1, (int) $cf5_rps['scroll'] ) : 1;

    wp_localize_script( 'cf5_rps_swiper_init', 'cf5RpsSwiperSettings', array(
        'slidesPerView'  => $per_page,
        'slidesToScroll' => $scroll,
        'autoplayDelay'  => 4000,
        'rtl'            => is_rtl(),
    ) );
}

function cf5_rps_wp_head_h_carousel() {
    global $cf5_rps;

    if ( ! is_array( $cf5_rps ) ) {
        return;
    }

    if ( ( $cf5_rps['format_style'] ?? '' ) === 'default' ) :
    ?>
    <style type="text/css">
    .rps_sldrtitle{
        font-family:<?php echo $cf5_rps['stitle_font'] ?? ''; ?>;
        font-size:<?php echo (int) ( $cf5_rps['stitle_size'] ?? 0 ); ?>px;
        font-weight:<?php echo $cf5_rps['stitle_weight'] ?? ''; ?>;
        font-style:<?php echo $cf5_rps['stitle_style'] ?? ''; ?>;
        <?php if ( ! empty( $cf5_rps['stitle_color'] ) ) { ?>
            color:<?php echo $cf5_rps['stitle_color']; ?>;
        <?php } ?>
    }

    .rps_wrapper{
        <?php if ( ! empty( $cf5_rps['bgcolor'] ) ) { ?>
            background:<?php echo $cf5_rps['bgcolor']; ?>;
        <?php } ?>
        <?php if ( ! empty( $cf5_rps['ltitle_color'] ) ) { ?>
            color:<?php echo $cf5_rps['ltitle_color']; ?>;
        <?php } ?>
        border:<?php echo (int) ( $cf5_rps['obrwidth'] ?? 0 ); ?>px solid <?php echo $cf5_rps['obrcolor'] ?? 'transparent'; ?>;
        font-family:<?php echo $cf5_rps['ltitle_font'] ?? ''; ?>;
        font-size:<?php echo (int) ( $cf5_rps['ltitle_size'] ?? 0 ); ?>px;
        line-height:<?php echo (int) ( ( $cf5_rps['ltitle_size'] ?? 0 ) + 4 ); ?>px;
    }

    img.rps_thumb{
        width:<?php echo (int) ( $cf5_rps['img_width'] ?? 0 ); ?>px !important;
        height:<?php echo (int) ( $cf5_rps['img_height'] ?? 0 ); ?>px !important;
        border:<?php echo (int) ( $cf5_rps['ibrwidth'] ?? 0 ); ?>px solid <?php echo $cf5_rps['ibrcolor'] ?? 'transparent'; ?>;
        <?php
        if ( ( $cf5_rps['img_align'] ?? '' ) === 'left' ) {
            echo 'float:left;margin:0 5px 5px 0 !important;';
        } elseif ( ( $cf5_rps['img_align'] ?? '' ) === 'right' ) {
            echo 'float:right;margin:0 0 5px 5px !important;';
        }
        ?>
    }

    .rps_item{
        <?php if ( ! empty( $cf5_rps['fgcolor'] ) ) { ?>
            background:<?php echo $cf5_rps['fgcolor']; ?>;
        <?php } ?>
        box-sizing:border-box;
        width:100%;
        height:<?php echo (int) ( $cf5_rps['height'] ?? 0 ); ?>px;
    }

    .rps_item a{
        <?php if ( ! empty( $cf5_rps['ltitle_color'] ) ) { ?>
            color:<?php echo $cf5_rps['ltitle_color']; ?> !important;
        <?php } ?>
    }

    .rps_item a:hover,
    .rps_item a:active{
        <?php if ( ! empty( $cf5_rps['hvtext_color'] ) ) { ?>
            color:<?php echo $cf5_rps['hvtext_color']; ?> !important;
        <?php } ?>
        <?php if ( ! empty( $cf5_rps['hvcolor'] ) ) { ?>
            border-color:<?php echo $cf5_rps['hvcolor']; ?>;
            background-color:<?php echo $cf5_rps['hvcolor']; ?>;
        <?php } ?>
    }
    </style>
    <?php endif;
}

/*
 * No cf5_rps_wp_footer_h_carousel() function anymore.
 *
 * The old version of this function enqueued the unused jquery.jcarousel.min.js
 * library (its .jcarousel() method was never called anywhere in this plugin)
 * plus the caroufredsel-based rps.js. Both are replaced by Swiper, which is
 * enqueued in cf5_rps_wp_init_h_carousel() above.
 *
 * It was also wired up via an explicit `add_action('wp_footer', ...)` call
 * IN ADDITION to being picked up automatically by the cf5_rps_wp_footer()
 * dispatcher in related-posts-slider.php (since its name matched the
 * cf5_rps_wp_footer_{format} pattern). That meant it ran twice whenever
 * h_carousel was active, and ran on every page regardless of which format
 * was selected. If a future format needs its own wp_footer hook, just define
 * cf5_rps_wp_footer_{format}() and let the dispatcher pick it up naturally —
 * don't also add an explicit add_action for it.
 */


function cf5_rps_h_carousel( $echo = true, $rps_posts = array() ) {
    global $cf5_rps, $rps_slider_shown;

    if ( empty( $rps_posts ) || $rps_slider_shown ) {
        return;
    }

    $slider  = '<div class="rps_wrapper">';
    $slider .= '<div id="rps_hcarousel" class="rps_instance">';

    /* ---------- Image selection flags ---------- */

    $custom_key         = ( ! empty( $cf5_rps['img_pick'][0] ) && $cf5_rps['img_pick'][0] === '1' )
        ? array( $cf5_rps['img_pick'][1] )
        : '';

    $the_post_thumbnail = ! empty( $cf5_rps['img_pick'][2] ) && $cf5_rps['img_pick'][2] === '1';

    $attachment         = ! empty( $cf5_rps['img_pick'][3] ) && $cf5_rps['img_pick'][3] === '1';
    $order_of_image     = $attachment ? $cf5_rps['img_pick'][4] : '1';

    $image_scan         = ! empty( $cf5_rps['img_pick'][5] ) && $cf5_rps['img_pick'][5] === '1';

    /* ---------- Crop size ---------- */

    switch ( (string) $cf5_rps['crop'] ) {
        case '0': $extract_size = 'full';      break;
        case '1': $extract_size = 'large';     break;
        case '2': $extract_size = 'medium';    break;
        default:  $extract_size = 'thumbnail'; break;
    }

    /* ---------- Loop posts ---------- */

    foreach ( $rps_posts as $post_id ) {

        $post_id = (int) $post_id;
        if ( ! $post_id || ! get_post_status( $post_id ) ) {
            continue;
        }

        $permalink = get_permalink( $post_id );
        if ( ! $permalink ) {
            continue;
        }

        $img_args = array(
            'custom_key'        => $custom_key,
            'post_id'           => $post_id,
            'attachment'        => $attachment,
            'size'              => $extract_size,
            'the_post_thumbnail'=> $the_post_thumbnail,
            'default_image'     => false,
            'order_of_image'    => $order_of_image,
            'link_to_post'      => false,
            'image_class'       => 'rps_thumb',
            'image_scan'        => $image_scan,
            'width'             => (int) $cf5_rps['img_width'],
            'height'            => (int) $cf5_rps['img_height'],
            'echo'              => false,
            'permalink'         => ''
        );

        $ltitle = get_the_title( $post_id );

        $limit = (int) $cf5_rps['ltitle_words'];
        if ( $limit > 0 ) {
            $ltitle = cf5_rps_word_limiter( $ltitle, $limit, false );
        }

        $slider .=
            '<div class="rps_item">' .
                '<a href="' . esc_url( $permalink ) . '" target="' . esc_attr( $cf5_rps['target'] ) . '">' .
                    cf5_rps_get_the_image( $img_args ) .
                    '<br />' . esc_html( $ltitle ) .
                '</a>' .
            '</div>';
    }

    /* ---------- Controls ---------- */

    $slider .= '</div>
        <a class="rps_prev" id="rps_prev" href="#"><span>prev</span></a>
        <a class="rps_next" id="rps_next" href="#"><span>next</span></a>
    </div>';

    $sldr_title = '<div class="rps_sldrtitle">' . esc_html( $cf5_rps['sldr_title'] ) . '</div>';
    $rpsslider  = '<div class="cf5_rps">' . $sldr_title . $slider .
                  '<div class="cf5_rps_cl"></div><div class="cf5_rps_cr"></div></div>';

    $rps_slider_shown = true;

    if ( $echo ) {
        echo $rpsslider;
        return true;
    }

    return $rpsslider;
}
