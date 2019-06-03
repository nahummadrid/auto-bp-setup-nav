<?php 
/*/////////////////////////////////////////////*/
/* Setup Nav */
/*/////////////////////////////////////////////*/
add_action('bp_setup_nav', 'mdlr_bp_user_menus',15);

function mdlr_bp_user_menus() {

global $bp, $wpdb;

$args = array(
   'public'   => true,
   '_builtin' => false
);

$output = 'objects'; // names or objects, note names is the default
$operator = 'and'; // 'and' or 'or'

$post_types = get_post_types( $args, $output, $operator ); 

foreach ( $post_types  as $post_type ) {

$user_post_count = mdlr_count_cpt_post($post_type->name);
$count_class  = ( 0 === $user_post_count ) ? 'no-count' : 'count';

    if($user_post_count > 0) {
      bp_core_new_nav_item(
      array(
      'name' => sprintf( __( '%s <span class="%s">%s</span>', 'mdlr' ),  $post_type->labels->name,  esc_attr( $count_class ), number_format_i18n( $user_post_count ) ),
      'slug' =>  strtolower($post_type->labels->name), 
      'position' => 10, 
      'screen_function' => 'mdlr_bp_submenu_redirect_' .$post_type->name,
              
      )
      );
  

$parent_slug = strtolower($post_type->labels->name);

    if(bp_is_user()) { //needed this check for some reason but I now forgot what! derp
      bp_core_new_subnav_item( array( 
      'name' => sprintf( __( '%s <span class="%s">%s</span>', 'mdlr' ), $post_type->labels->name, esc_attr( $count_class ), number_format_i18n( $user_post_count ) ),
      'slug' => 'added', 
      'parent_url' => $bp->displayed_user->domain . $parent_slug.'/', 
      'parent_slug' => $parent_slug, 
      'position' => 20,
      'screen_function' => 'mb_author_'.$post_type->name, //the function is declared below
      ) 
    ); 
      }//if user has posts

  } //bpisuser


  //Favorites //////////////////////////////////////
  $user_favorites = (class_exists('favorites')) ? get_user_favorites($userid) : (array('1')); //just go with it
  $favorite_args = array( 'post_type' => $post_type->name, 'numberposts' => -1, 'post__in'=> $user_favorites );
  $favorite_posts = get_posts($favorite_args);
  $favorite_count = (!empty($favorite)) ? count($favorite_posts) : '0';
  $favorite_class = ( 0 === $playcount ) ? 'no-count' : 'count';
  $favorite_name = sprintf( __( 'Favorites <span class="%s">%s</span>', 'mdlr' ), esc_attr($favorite_class ), $favorite_count );
  //Favorites //////////////////////////////////////

    if(bp_is_user()) {  //again needed this check for some reason but I now forgot what! 
      bp_core_new_subnav_item( array( 
      'name' => $favorite_name,
      'slug' => 'favorites', 
      'parent_url' => $bp->displayed_user->domain . $parent_slug.'/', 
      'parent_slug' => $parent_slug, 
      'position' =>30,
      'screen_function' => 'mb_author_'.$post_type->name.'_likes' //the function is declared below
      ) 
    ); 
    }//bpisuser

  } //foreach posttype

} //end function

function mdlr_count_cpt_post( $type ) {
  global $wpdb;
  if ( empty( $user_id ) )
  $user_id = bp_displayed_user_id();
  $posttype = $type;
  return $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_author = $user_id AND post_type = '$posttype'  AND post_status = 'publish'" );
}



function mb_author_post($posttype) {
  //get the public post types //////////////////////////////////////
  add_action( 'bp_template_content', function() { mdlr_get_bp_loop($posttype); } );
  bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}


function mb_author_post_likes($posttype){ 
  //add_action( 'bp_template_content', 'mb_show_product_likes' );
  add_action( 'bp_template_content', function() { mdlr_get_bp_loop_likes($posttype); } );
  bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}
