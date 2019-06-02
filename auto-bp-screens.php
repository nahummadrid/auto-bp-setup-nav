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

      bp_core_new_nav_item(
      array(
      'name' => $post_type->labels->name,
      'slug' =>  strtolower($post_type->labels->name), 
      'position' => 10, 
      'screen_function' => 'mdlr_bp_submenu_redirect_' .$post_type->name,
              
      )
      );
    

  } //foreach posttype

}

/*/////////////////////////////////////////////*/
/* Setup Sub Nav Menus */
/*/////////////////////////////////////////////*/

add_action('bp_setup_nav', 'mb_bp_user_submenus',10);

function mb_bp_user_submenus() {
global $bp, $wpdb;

$userid = bp_displayed_user_id();

//get the public post types //////////////////////////////////////
$args = array(
   'public'   => true,
   '_builtin' => false
);

$output = 'objects'; // names or objects, note names is the default
$operator = 'and'; // 'and' or 'or'

$post_types = get_post_types( $args, $output, $operator ); 
//get the public post types //////////////////////////////////////
foreach ( $post_types  as $post_type ) {

$parent_slug = strtolower($post_type->labels->name);

if(bp_is_user()) { //needed this check for some reason but I now forgot what! derp

 bp_core_new_subnav_item( array( 
 'name' => $post_type->labels->name,
 'slug' => 'added', 
 'parent_url' => $bp->displayed_user->domain . $parent_slug.'/', 
 'parent_slug' => $parent_slug, 
 'position' => 20,
 'screen_function' => 'mb_author_'.$post_type->name, //the function is declared below
    ) 
  ); 

} //bpisuser


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

}//foreach
  
}


//get post counts per cpt

function mdlr_count_cpt_post( $type ) {
  global $wpdb;
  if ( empty( $user_id ) )
  $user_id = bp_displayed_user_id();
  $posttype = $type;
  return $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_author = $user_id AND post_type = '$posttype'  AND post_status = 'publish'" );
}

//default subnavs don't work with post pagination so we redirect to the sub nav screen

function mdlr_bp_submenu_redirect_product() {
global $bp;
bp_core_redirect($bp->displayed_user->domain .'products/added' );
}

function mdlr_bp_submenu_redirect_event() {
global $bp;
bp_core_redirect($bp->displayed_user->domain .'events/added' );
}


/*/////////////////////////////////////////////*/
/* Setup Profile Screens */
/*/////////////////////////////////////////////*/
//here's where things get tricky

function mb_author_post() {
  add_action( 'bp_template_content', function() { mdlr_get_bp_loop(''.$post_type->name.''); } ); //template ready to for post type slug
  bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}


