<?php
/*
Plugin Name: Mention comment's Authors
Plugin URI: 
Description: 
Version: 0.9
Author: Willy Bahuaud
Author URI: http://wabeo.fr
License: GPLv2 or later
*/
DEFINE( 'CTL_PLUGIN_URL', trailingslashit( WP_PLUGIN_URL ) . basename( dirname( __FILE__ ) ) );
DEFINE( 'CTL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
global $mcaAuthors;

/**
LOAD JS ON FRONT OFFICE
*/
function mca_enqueue_comments_scripts() {
    wp_register_style( 'mca-styles', CTL_PLUGIN_URL . '/mca-styles.css', false, '0.9', 'all' );
    if( apply_filters( 'mca-load-styles', true ) )
        wp_enqueue_style( 'mca-styles' );

    wp_register_script( 'caretposition', CTL_PLUGIN_URL . '/js/jquery.caretposition.js', array( 'jquery' ), '0.9', true );
    wp_register_script( 'sew', CTL_PLUGIN_URL . '/js/jquery.sew.min.js', array( 'jquery','caretposition' ), '0.9', true );
    wp_register_script( 'mca-comment-script', CTL_PLUGIN_URL . '/js/mca-comment-script.js', array( 'jquery','caretposition','sew' ), '0.9', true );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'caretposition' );
    wp_enqueue_script( 'sew' );
    wp_enqueue_script( 'mca-comment-script' );
}
add_action( 'wp_enqueue_scripts', 'mca_enqueue_comments_scripts' ); //wp_enqueue_scripts

/**
CATCH NAME IN COMMENTS & ADD ANCHOR LINK (OR OPACITY)
*/
function mca_modify_comment_text( $content, $com ) {
    global $mcaAuthors;

    if( ! is_array( $mcaAuthors ) )
        $mcaAuthors = array();

    $newEntry = $com->comment_author;
    if( ! in_array( $newEntry, $mcaAuthors ) )
        $mcaAuthors[ sanitize_title( $com->comment_author ) ] = $newEntry;

    //Rearrange content
    $modifiedcontent = preg_replace_callback('/\@([a-zA-Z0-9-]*)/', 'mca_comment_callback', $content);

    return '<div class="mca-author" data-name="' . sanitize_title( $com->comment_author ) . '">' . $modifiedcontent . '</div>';
}
add_filter('comment_text', 'mca_modify_comment_text', 10, 2);

function mca_comment_callback( $matches ) {
    global $mcaAuthors;
    $name = ( isset( $mcaAuthors[ $matches[1] ] ) ) ? $mcaAuthors[ $matches[1] ] : $matches[1];
    return '<button data-target="' . $matches[1] . '" class="mca-button">@' . $name . '</button>';
}

/**
RETRIEVE AUTHORS NAMES ON THE OTHER SIDE (SAVING ONE)
*/
function printnames(){
    global $mcaAuthors;

    echo '<input type="hidden" name="mcaAuthors" value="' . json_encode( $mcaAuthors ) . '">';

    //reorder $mcaAuthors
    $authors = array();
    foreach( $mcaAuthors as $k => $a )
        $authors[] = array( 'val' => $k, 'meta' => $a );
    wp_localize_script( 'mca-comment-script', 'mcaAuthors', $authors );
}
add_action('comment_form','printnames');
