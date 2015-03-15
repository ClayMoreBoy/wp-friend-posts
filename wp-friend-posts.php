<?php
/*
Plugin Name: Wp friend posts
Plugin URI: http://fatesinger.com/
Description: 展示友情链接最新文章
Version: 1.0.0
Author: Bigfa
Author URI: http://fatesinger.com/
*/


//load css start

function wfp_scripts(){
    wp_enqueue_style( 'wfp', plugins_url('', __FILE__) .'/static/style.css ', array(), '1.0.0' );
}
add_action('wp_enqueue_scripts', 'wfp_scripts', 20, 1);

//load css end

include_once( ABSPATH . WPINC . '/feed.php' );
function get_feed_posts( $url ){
    $cache = get_transient($url);
    if ( $cache )
        return $cache;
    $rss = fetch_feed( $url );
    $output = array();
    if ( ! is_wp_error( $rss ) ) :
        delete_transient($url);
        $maxitems = $rss->get_item_quantity( 5 );
        $rss_items = $rss->get_items( 0, $maxitems );
        foreach ( $rss_items as $item ) {
            $output[] = array('title'=>$item->get_title(),'url'=>esc_url( $item->get_permalink() ),'date'=>$item->get_date('c'));
        }
        set_transient($url,$output,60*60*24);
        return $output;
    endif;
}

function get_the_link_items_with_posts($id = null){
    $bookmarks = get_bookmarks('orderby=date&category=' .$id );
    $output = '';
    if ( !empty($bookmarks) ) {
        $output .= '<div class="friend-posts--wrap fontSmooth">';
        foreach ($bookmarks as $bookmark) {
            $output .=  '<div class="friend-posts"><h4 class="friend-posts-title"><a style="display:block" href="' . $bookmark->link_url . '" title="' . $bookmark->link_description . '" target="_blank" >'. get_avatar($bookmark->link_notes,24) . $bookmark->link_name .'</a></h4>';
            $url = $bookmark->link_rss ? $bookmark->link_rss : rtrim($bookmark->link_url,'/') . '/feed/';
            $rss_items = get_feed_posts( $url );
            if( !empty($rss_items) ) {
                $output .= '<ul class="friend-posts-items">';
                foreach ($rss_items as $rss_item){
                    $output .= '<li><a target="_blank" rel="external nofollow" href="'.$rss_item['url'].'">'.$rss_item['title'].'</a></li>';
                }
                $output .= '</ul>';
            }
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    return $output;
}

function friends_shortcode( $atts, $content = null ) {

    extract( shortcode_atts( array(
            'cat' => ''
        ),
        $atts ) );

    return get_the_link_items_with_posts($cat);

}
add_shortcode('friends', 'friends_shortcode');

?>