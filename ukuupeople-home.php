<?php
defined( 'ABSPATH' ) OR exit;

class ukuupeople_home {
  public function __construct( ) {
    add_action( 'admin_menu', array( $this, 'ukuupeople_home_menu' ) );
  }

  /**
   * To add/remove menu/submenu page.
   */
  function ukuupeople_home_menu() {
    add_plugins_page( 'UkuuPeople Home', 'UkuuPeople Home', 'manage_options', 'ukuupeople-home', array( $this, 'ukuupeople_home_page' ) );
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( !is_plugin_active('simple-fields/simple_fields.php') || !is_plugin_active('types/wpcf.php') ) {
      add_menu_page( 'Ukuu People', 'UkuuPeople', 'manage_options', 'plugins.php?page=ukuupeople-home','', '', 26 );
    }
    remove_submenu_page( 'edit.php?post_type=wp-type-contacts', 'post-new.php?post_type=wp-type-contacts' );
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=category&amp;post_type=wp-type-contacts');
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=wp-type-group&amp;post_type=wp-type-contacts');
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=wp-type-tags&amp;post_type=wp-type-contacts');
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=wp-type-contacts-subtype&amp;post_type=wp-type-contacts');
    remove_menu_page( 'edit.php?post_type=wp-type-activity' );
  }

  /**
   * submenu page for UkuuPeople License.
   */
  function ukuupeople_home_page() {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $pluginarr = array( "types/wpcf.php" => '' , "simple-fields/simple_fields.php" => '' );
    $active = 1;
    foreach ( $pluginarr as $key => $val ) {
      if ( is_plugin_active( $key ) ) {
        $pluginarr[$key] = 'active';
      }
      elseif ($active) {
        $active = 0;
      }
    }
    wp_enqueue_script( 'plugin-install' );
    ?>
    <div class="wrap">
      <h2 class="pg-title"></h2>
      <div class="welcome box<?php echo $active ? ' inactive' : ''; ?>"><div class="pl-number"><?php _e('1');  ?></div><div class="content"><h2 class="pg-title"><?php _e('Welcome to UkuuPeople');  ?></h2>
      <h4><?php _e('You\'re almost ready!');  ?></h4>
      <h4><?php
        if ( $pluginarr['simple-fields/simple_fields.php'] != 'active' || $pluginarr['types/wpcf.php'] != 'active' ) {
          $url=admin_url( 'plugins.php?page=ukuupeople-install-plugins');
          _e("You just need to install/activate few more plugins Click <Strong><a href='$url'> here </a></Strong>"); }?></h4>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row" valign="">
              <input type="checkbox" name="Iplugins" value="types" <?php if ( $pluginarr['types/wpcf.php'] == 'active' ) echo 'checked disabled'; ?> > <span class="label">Types</span>
            </th>
          </tr>
          <tr valign="top">
            <th scope="row" valign="">
              <input type="checkbox" name="Iplugins" value="simple-fields" <?php if ( $pluginarr['simple-fields/simple_fields.php'] == 'active' ) echo 'checked disabled'; ?> > <span class="label"> Simple Fields </span>
            </th>
          </tr>
        </tbody>
      </table>
      <span class="instruction"><?php if ( $pluginarr['simple-fields/simple_fields.php'] == 'active' && $pluginarr['types/wpcf.php'] == 'active' ) _e('All plugins are installed'); ?></span>
      </div></div>
      <div class="ready box <?php if ( $pluginarr['simple-fields/simple_fields.php'] == 'active' && $pluginarr['types/wpcf.php'] == 'active' ) { echo ''; }else echo 'inactive';?>"><div class="pl-number"><?php _e('3');  ?></div><div class="content"><h2 class="pg-title"><?php _e('You are ready to go!'); ?></h2></div>
  <?php
}

}
