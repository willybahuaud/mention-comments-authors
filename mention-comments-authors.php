<?php
/*
Plugin Name: Mention comment's Authors
Plugin URI: http://wabeo.fr
Description: "Mention comment's authors" is a plugin that improves the WordPress comments fonctionality, adding a response system between authors.
When adding a comment, your readers can directly mentioning the author of another comment, like facebook or twitter do,using the "@" symbol.
Version: 0.9
Author: Willy Bahuaud
Author URI: http://wabeo.fr
License: GPLv2 or later
*/

/**
INIT CONSTANT & LANGS
*/
DEFINE( 'MCA_PLUGIN_URL', trailingslashit( WP_PLUGIN_URL ) . basename( dirname( __FILE__ ) ) );
DEFINE( 'MCA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
global $mcaAuthors;

function mca_lang_init() {
    load_plugin_textdomain( 'mca', false, basename( dirname( __FILE__ ) ) . '/langs/' );
}
add_action( 'init', 'mca_lang_init' );

/**
LOAD JS ON FRONT OFFICE
* a classic script enqueue

* @uses mca-load-styles FILTER HOOK to allow/disallow css enqueue
* @uses mcaajaxenable FILTER HOOK to turn plugin into ajax mod (another script is loaded, different functions are used)
*/
function mca_enqueue_comments_scripts() {
    wp_register_style( 'mca-styles', MCA_PLUGIN_URL . '/mca-styles.css', false, '0.9', 'all' );
    if( apply_filters( 'mca-load-styles', true ) )
        wp_enqueue_style( 'mca-styles' );

    wp_register_script( 'caretposition', MCA_PLUGIN_URL . '/js/jquery.caretposition.js', array( 'jquery' ), '0.9', true );
    wp_register_script( 'sew', MCA_PLUGIN_URL . '/js/jquery.sew.min.js', array( 'jquery','caretposition' ), '0.9', true );
    wp_register_script( 'mca-comment-script', MCA_PLUGIN_URL . '/js/mca-comment-script.js', array( 'jquery','caretposition','sew' ), '0.9', true );
    wp_register_script( 'mca-comment-script-ajax', MCA_PLUGIN_URL . '/js/mca-comment-script-ajax.js', array( 'jquery','caretposition','sew' ), '0.9', true );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'caretposition' );
    wp_enqueue_script( 'sew' );

    if( ! apply_filters( 'mcaajaxenable', false ) )
        wp_enqueue_script( 'mca-comment-script' );
    else 
        wp_enqueue_script( 'mca-comment-script-ajax' );
}
add_action( 'wp_enqueue_scripts', 'mca_enqueue_comments_scripts' );

/**
CATCH NAME IN COMMENTS & ADD ANCHOR LINK (OR OPACITY)
* mca_modify_comment_text FUNCTION will rewrite the comment text, including buttons + some usefull datas. It based on comment_text FILTER HOOK !!
* mca_comment_callback FUNCTION is the working callback

* @var mcaAuthors ARRAY to receive list of authors
* @var modifiedcontent VARCHAR contant avec preg_replace_all

* @uses mca_get_previous_commentators FUNCTION to retrieve full list of authors (only ajax mod)
* @uses mcaajaxenable FILTER HOOK to turn plugin into ajax mod (another script is loaded, different functions are used)
*/
function mca_modify_comment_text( $content, $com ) {
    global $mcaAuthors;

    if( apply_filters( 'mcaajaxenable', false ) )
        $mcaAuthors = mca_get_previous_commentators( $com->comment_post_ID, $com->comment_ID );
    else {
        if( ! is_array( $mcaAuthors ) )
            $mcaAuthors = array();

        $newEntry = $com->comment_author;
        if( ! in_array( $newEntry, $mcaAuthors ) )
            $mcaAuthors[ sanitize_title( $com->comment_author ) ] = $newEntry;
    }
    //Rearrange content
    $modifiedcontent = preg_replace_callback('/(?:^|\s)\@([a-zA-Z0-9-]*)(?:$|\s)/', 'mca_comment_callback', $content);
    if( apply_filters( 'mcaajaxenable', false ) )
        return '<div class="mca-author" data-name="' . sanitize_title( $com->comment_author ) . '" data-realname="' . esc_attr( $com->comment_author ) . '">' . $modifiedcontent . '</div>';
    else
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
* only on non-ajax mod, will push authors names in script. Start at comment_form ACTION HOOK !!

* @var mcaAuthors ARRAY contain full list of authors
* @var authors ARRAY to receive ordered list of authors

* @uses mcaajaxenable FILTER HOOK to turn plugin into ajax mod (another script is loaded, different functions are used)
*/
function mca_printnames(){
    global $mcaAuthors;

    //reorder $mcaAuthors
    $authors = array();
    foreach( $mcaAuthors as $k => $a )
        $authors[] = array( 'val' => $k, 'meta' => $a );

    if( ! apply_filters( 'mcaajaxenable', false ) )
        wp_localize_script( 'mca-comment-script', 'mcaAuthors', $authors );
}
if( ! apply_filters( 'mcaajaxenable', false ) )
    add_action( 'comment_form', 'mca_printnames' );

/**
RETRIEVE LAST COMMENTATORS KEYS/NAMES
* usefull function to collect authors names, slug and emails

* @uses mca_get_previous_commentators FUNCTION take 3 args : post ID, comment ID, and a BOOL fore retrieve emails or only names
*/
function mca_get_previous_commentators( $postid, $commid, $email = false ) {
    global $wpdb;
    $prev = $wpdb->get_results( $wpdb->prepare("SELECT DISTINCT comment_author,comment_author_email FROM $wpdb->comments WHERE comment_post_ID = $postid AND comment_ID < $commid", 'ARRAY_N' ) );
    $out = array();
    if( $email )
        foreach( $prev as $p )
            $out[ sanitize_title( $p->comment_author ) ] = array( $p->comment_author, $p->comment_author_email );
    else
        foreach( $prev as $p )
            $out[ sanitize_title( $p->comment_author ) ] = $p->comment_author;
    return $out;
}

/**
SEND EMAILS TO POKED ONES
* this function send email for poked commentators

* @uses mca_email_poked_ones FUNCTION to send emails (if have to...). It based on comment_post ACTION HOOK

* @var comment OBJECT to store current comment datas
* @var prev_authors ARRAY contain lists of comment's authors (including emails...)
* @var pattern REGEX PATTERN
* @var matches ARRAY results of the preg_match_all()


*/
function mca_email_poked_ones( $comment_id ) {
    $comment = get_comment( $comment_id );
    $prev_authors = mca_get_previous_commentators( $comment->comment_post_ID, $comment_id, true );
    //do preg_match
    $pattern = '/(?:^|\s)\@(' . implode( '|', array_keys( $prev_authors ) ) . ')(?:$|\s)/';
    preg_match_all( $pattern, $comment->comment_content, $matches );

    foreach( $matches[1] as $m ) {
        $mail = $prev_authors[ $m ][1];
        $name = $prev_authors[ $m ][0];
        $titre = get_the_title( $comment->comment_post_ID );

        $subject = wp_sprintf( __( ' %s replied to your comment on the article &laquo;%s&raquo;' , 'mca' ), $comment->comment_author, $titre );
        $subject = apply_filters( 'mca-email-subject', $subject, $comment, $name, $mail, $titre );

        $message = '<div><h1>' . $subject . '</h1><div style="Border:5px solid grey;padding:1em;">' . apply_filters( 'the_content', wp_trim_words( $comment->comment_content, 25 ) ) . "</div></div><p>" . __( 'Read post', 'mca' ) . ' : <a href="' . get_permalink( $comment->comment_post_ID ) . '">' . $titre . '</a> ' . __( 'on', 'mca' ) . ' <a href="' . get_bloginfo( 'url' ) . '">' . get_bloginfo( 'name' ) . '</a></p>';
        $message = apply_filters( 'mca-email-message', $message, $comment, $name, $mail, $titre );

        add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html"; ' ) );
        wp_mail( $mail, $subject, $message );
    }
}
add_action( 'comment_post', 'mca_email_poked_ones', 90 ); // Launching after spam test
