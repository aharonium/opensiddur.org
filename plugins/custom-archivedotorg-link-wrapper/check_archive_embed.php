<?php

require_once dirname( __FILE__, 5 ) . '/wp-load.php';

$tests = array(

'https://archive.org/details/bim_eighteenth-century_ahiman-rezon-or-a-help_dermott-laurence_1756?view=theater&ui=embed&wrapper=false',

'https://archive.org/stream/bim_eighteenth-century_ahiman-rezon-or-a-help_dermott-laurence_1756',

'https://archive.org/details/americana'

);

$results = array();

foreach ( $tests as $url ) {

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 15,
            'redirection' => 5,
            'user-agent' => 'OpenSiddur Archive Health Check'
        )
    );

    if ( is_wp_error( $response ) ) {

        $results[] = array(
            'url' => $url,
            'healthy' => false,
            'reason' => $response->get_error_message(),
        );

        continue;
    }

    $code = wp_remote_retrieve_response_code( $response );

    $headers = wp_remote_retrieve_headers( $response );

    $body = wp_remote_retrieve_body( $response );

    $healthy = true;

    $reason = '';

    if ( $code >= 400 ) {

        $healthy = false;

        $reason = "HTTP $code";
    }

    if ( stripos( $body, 'Bad Request' ) !== false ) {

        $healthy = false;

        $reason = 'Bad Request page';
    }

    $results[] = compact(
        'url',
        'healthy',
        'reason',
        'code'
    );
}

$healthy = false;

foreach ( $results as $test ) {

    if ( $test['healthy'] ) {

        $healthy = true;

        break;
    }
}

$status = array(

    'checked' => gmdate( 'c' ),

    'healthy' => $healthy,

    'tests' => $results

);

$file = WP_CONTENT_DIR .
    '/uploads/archive-org-health.json';

file_put_contents(

    $file,

    wp_json_encode(
        $status,
        JSON_PRETTY_PRINT
    )
);