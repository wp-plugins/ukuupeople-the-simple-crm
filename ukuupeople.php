<?php
defined( 'ABSPATH' ) OR exit;
define( 'UKUUPEOPLE_ABSPATH', dirname( __FILE__ ) );
define( 'UKUUPEOPLE_RELPATH', plugins_url() . '/' . basename( UKUUPEOPLE_ABSPATH ) );

/**
 * @package UKUU CRM
 */
/*
  Plugin Name: UKUU PEOPLE
  Plugin URI: http://ukuupeople.com/
  Description: Ukuu People is the premium plugin that helps you elegantly manage all of your human relationships.
  Ukuu People effortlessly ties all of your contact interactions and contact data collection tools together to form one authoritative master list of all of your contacts and a record of your interactions with them.
  Version: 1.0.2
  Author: UKUU Logic
  Author URI: http://ukuulogic.com/
  License: GPL 3
  Text Domain:
*/
/*
  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

global $ukuupeople_db_version;
$ukuupeople_db_version = '1.0.2';

register_activation_hook( __FILE__, 'on_activation' );
register_deactivation_hook( __FILE__, 'on_deactivation' );
register_uninstall_hook( __FILE__ , 'on_uninstall' );

// Check for upgraded version
global $ukuupeople_db_version;
$installed_ver = get_site_option( "ukuupeople_db_version" );
if ( !empty( $installed_ver ) &&  $installed_ver != $ukuupeople_db_version ) {
  //UkuuPeople Updater
  if( !class_exists( 'ukuupeople_update' ) ) {
    // load our custom updater
    include( dirname( __FILE__ ) . '/ukuupeople-update.php' );
  }
  // setup and run the updater
  $ukuupeople_updater = new ukuupeople_update( $installed_ver, $ukuupeople_db_version );
  $ukuupeople_updater->onUpdate();
  update_option( "ukuupeople_db_version", $ukuupeople_db_version );
}
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';
//Home Page
if( !class_exists( 'ukuupeople_home' ) ) {
  include( dirname( __FILE__ ) . '/ukuupeople-home.php' );
}
$ukuupeople_home = new ukuupeople_home( );

add_action( 'ukuupeople_register', 'ukuupeople_register_required_plugins' );

/**
 * Register the required plugins.
 *
 **/
function ukuupeople_register_required_plugins() {
  /**
   * Array of plugin arrays. Required keys are name and slug.
   */
  $plugins = array(
    array(
      'name'      => 'types',
      'slug'      => 'types',
      'required'  => true,
    ),
    array(
      'name'      => 'Simple Fields',
      'slug'      => 'simple-fields',
      'required'  => true,
    ),
  );

  /**
   * Array of configuration settings.
   */
  $config = array(
    'id' => 'ukuupeople',
    'default_path' => '',                      // Default absolute path to pre-packaged plugins.
    'menu'         => 'ukuupeople-install-plugins', // Menu slug.
    'has_notices'  => true,                    // Show admin notices or not.
    'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
    'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
    'is_automatic' => true,                   // Automatically activate plugins after installation or not.
    'message'      => '',                      // Message to output right before the plugins table.
    'strings'      => array(
      'page_title'                      => __( 'Install Required Plugins', 'ukuupeople' ),
      'menu_title'                      => __( 'Install Plugins', 'ukuupeople' ),
      'installing'                      => __( 'Installing Plugin: %s', 'ukuupeople' ), // %s = plugin name.
      'oops'                            => __( 'Something went wrong with the plugin API.', 'ukuupeople' ),
      'notice_can_install_required'     => _n_noop( 'UkuuPeople plugin requires the following plugin: %1$s.', 'UkuuPeople plugin requires the following plugins: %1$s.' ), // %1$s = plugin name(s).
      'notice_can_install_recommended'  => _n_noop( 'UkuuPeople plugin recommends the following plugin: %1$s.', 'UkuuPeople plugin recommends the following plugins: %1$s.' ), // %1$s = plugin name(s).
      'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
      'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
      'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
      'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
      'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s).
      'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
      'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
      'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
      'return'                          => __( 'Return to Required Plugins Installer', 'ukuupeople' ),
      'plugin_activated'                => __( 'Plugin activated successfully.', 'ukuupeople' ),
      'complete'                        => __( 'All plugins installed and activated successfully. %s', 'ukuupeople' ), // %s = dashboard link.
      'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
    )
  );
  tgmpa( $plugins, $config );
}

/**
 * Activation hook.
 *
 * Create/activate some of data.
 */
function on_activation() {
  if ( ! current_user_can( 'activate_plugins' ) )
    return;
  require_once( UKUUPEOPLE_ABSPATH. '/ukuupeople-config.php' );
  $custom_types = get_option( 'wpcf-custom-types', array() );
  if (is_array($custom_types)) {
    $custom_types = array_merge($custom_types, $customterm['custom_types']);
  } else {
    $custom_types = $customterm['custom_types'];
  }
  update_option( 'wpcf-custom-types', $custom_types );
  update_option('ukuupeople_tax_terms', 'set');

  $relationships = get_option( 'wpcf_post_relationship', array() );
  $save_has_data = array();
  // Reset has
  foreach ( $customterm['custom_types'] as $data ) {
    if ( !empty( $relationships[$data['slug']] ) ) {
      foreach ( $relationships[$data['slug']] as $post_type_has => $rel_data ) {
        if ( !isset( $data['post_relationship']['has'][$post_type_has] ) ) {
          unset( $relationships[$data['slug']][$post_type_has] );
        }
      }
    }
    if ( !empty( $data['post_relationship']['has'] ) ) {
      foreach ( $data['post_relationship']['has'] as $post_type => $true ) {
        if ( empty( $relationships[$data['slug']][$post_type] ) ) {
          $save_has_data[$data['slug']][$post_type] = array();
        } else {
          $save_has_data[$data['slug']][$post_type] = $relationships[$data['slug']][$post_type];
        }
      }
      $relationships[$data['slug']] = $save_has_data[$data['slug']];
    }
    // Reset belongs
    foreach ( $relationships as $post_type => $rel_data ) {
      if ( empty( $data['post_relationship']['belongs'] )
        || !array_key_exists( $post_type, $data['post_relationship']['belongs'] ) ) {
        unset( $relationships[$post_type][$data['slug']] );
      }
    }
    if ( !empty( $data['post_relationship']['belongs'] ) ) {
      foreach ( $data['post_relationship']['belongs'] as $post_type => $true ) {
        if ( empty( $relationships[$post_type][$data['slug']] )
          && !isset( $relationships[$data['slug']][$post_type] ) ) {
          // Check that can't exist same belongs and has
          $relationships[$post_type][$data['slug']] = array();
        }
      }
    }
    update_option( 'wpcf_post_relationship', $relationships );
  }

  //Add Custom taxonomy
  $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
  if (is_array( $custom_taxonomies)) {
    $custom_taxonomies =  array_merge( $custom_taxonomies,  $customterm['custom_taxonomies']);
  } else {
    $custom_taxonomies = $customterm['custom_taxonomies'];
  }
  update_option( 'wpcf-custom-taxonomies', $custom_taxonomies);

  $group_post = array(
    'post_status' => 'publish',
    'post_type' => 'wp-types-group',
    'post_title' => 'Edit Contact Info',
    'post_name' => 'Edit Contact Info',
    //'post_content' => 'Contact Information',
  );
  $group_id = get_page_by_title( 'Edit Contact Info', OBJECT, 'wp-types-group' );
  if ( empty($group_id) ) {
    $group_id = wp_insert_post( $group_post, true );
  } else {
    $group_id = $group_id->ID;
  }
  $args['fields'] = $customterm['fields'];
  foreach ( $customterm['fields'] as $field_id => $field_val) {
    $field_type = !empty( $args['type'] ) ? $args['type'] : 'textfield';
    if ( strpos( $field_id, md5( 'wpcf_not_controlled' ) ) !== false ) {
      $field_id_name = str_replace( '_' . md5( 'wpcf_not_controlled' ), '', $field_id );
      $field_id_add =   $field_id_name ;
      $adding_field_with_wpcf_prefix = $field_id_add != $field_id_name;
      // Activating field that previously existed in Types
      // Adding from outside
      $fields[$field_id_add]['id'] = $field_id_add;
      $fields[$field_id_add]['type'] = $field_type;
      if ($adding_field_with_wpcf_prefix) {
        $fields[$field_id_add]['name'] = $field_id_add;
        $fields[$field_id_add]['slug'] = $field_id_add;
      } else {
        $fields[$field_id_add]['name'] = $field_id_name;
        $fields[$field_id_add]['slug'] = $field_id_name;
      }
      $fields[$field_id_add]['description'] = '';
      $fields[$field_id_add]['data'] = array();
      if ($adding_field_with_wpcf_prefix) {
        // This was most probably a previous Types field
        // let's take full control
        $fields[$field_id_add]['data']['controlled'] = 0;
      } else {
        // @TODO WATCH THIS! MUST NOT BE DROPPED IN ANY CASE
        $fields[$field_id_add]['data']['controlled'] = 1;
      }
      $unset_key = array_search( $field_id, $args['fields'] );
      if ( $unset_key !== false ) {
        unset( $args['fields'][$unset_key] );
        $args['fields'][$unset_key] = $field_id_add;
      }
    }
  }
  $fields = $args['fields'];
  $original = get_option( 'wpcf-fields', array() );
  if( is_array($original) ) {
    $fields = array_merge( $original, $fields );
  }
  update_option( 'wpcf-fields', $fields );
  $meta_name = 'wpcf-fields';
  $actual_fields = array(
    0 => 'first-name',
    1 => 'last-name',
    2 => 'display-name',
    3 => 'email',
    4 => 'phone',
    5 => 'mobile',
    6 => 'website',
    7 => 'contactimage',
    8 => 'ukuu-job-title',
    9 => 'ukuu-twitter-handle',
    10 => 'ukuu-facebook-url',
    11 => 'ukuu-date-of-birth',
  );
  $fields = ',' . implode( ',', (array) $actual_fields ) . ',';
  update_post_meta( $group_id, '_wp_types_group_fields', $fields );
  $post_types = 'wp-type-contacts';
  update_post_meta( $group_id, '_wp_types_group_post_types', $post_types );

  do_action( 'wpcf_save_group', $group_post );

  $group_post = array(
    'post_status' => 'publish',
    'post_type' => 'wp-types-group',
    'post_title' => 'Edit Contact Address',
    'post_name' => 'edit_contact_address',
    //'post_content' => 'Contact Address',
  );
  $group_id = get_page_by_title( 'Edit Contact Address', OBJECT, 'wp-types-group' );
  if ( empty($group_id) ) {
    $group_id = wp_insert_post( $group_post, true );
  } else {
    $group_id = $group_id->ID;
  }

  $actual_fields = array(
    0 => 'streetaddress',
    1 => 'streetaddress2',
    2 => 'city',
    3 => 'state',
    4 => 'postalcode',
    5 => 'country',
  );
  $fields = ',' . implode( ',', (array) $actual_fields ) . ',';
  update_post_meta( $group_id, '_wp_types_group_fields', $fields );
  $post_types = 'wp-type-contacts';
  update_post_meta( $group_id, '_wp_types_group_post_types', $post_types );

  do_action( 'wpcf_save_group', $group_post );

  $group_post = array(
    'post_status' => 'publish',
    'post_type' => 'wp-types-group',
    'post_title' => 'Edit Privacy Settings',
    'post_name' => 'edit_contact_privacy_settings',
    //'post_content' => 'Contact Address',
  );

  $group_id = get_page_by_title( 'Edit Privacy Settings', OBJECT, 'wp-types-group' );
  if ( empty($group_id) ) {
    $group_id = wp_insert_post( $group_post, true );
  } else {
    $group_id = $group_id->ID;
  }

  $actual_fields = array(
    0 => 'privacy-settings',
    1 => 'bulk-mailings',
  );
  $fields = ',' . implode( ',', (array) $actual_fields ) . ',';
  update_post_meta( $group_id, '_wp_types_group_fields', $fields );
  $post_types = 'wp-type-contacts';
  update_post_meta( $group_id, '_wp_types_group_post_types', $post_types );

  do_action( 'wpcf_save_group', $group_post );
  //Touchpoint data
  $group_post = array(
    'post_status' => 'publish',
    'post_type' => 'wp-types-group',
    'post_title' => 'Activity information',
    'post_name' => 'Activity information',
    'post_content' => 'Activity information',
  );

  $group_id = get_page_by_title( 'Activity information', OBJECT, 'wp-types-group' );
  if ( empty($group_id) ) {
    $group_id = wp_insert_post( $group_post, true );
  } else {
    $group_id = $group_id->ID;
  }

  $meta_name = 'wpcf-fields';
  $actual_fields = array(
    0 => 'startdate',
    1 => 'enddate',
    2 => 'status',
    3 => 'details',
    4 => 'attachments',
  );
  $fields = ',' . implode( ',', (array) $actual_fields ) . ',';
  update_post_meta( $group_id, '_wp_types_group_fields', $fields );
  $post_types = 'wp-type-activity';
  update_post_meta( $group_id, '_wp_types_group_post_types', $post_types );

  do_action( 'wpcf_save_group', $group_post );
  //Simple fields
  update_option("simple_fields_groups", $customterm['field_groups']);
  update_option("simple_fields_post_connectors", $customterm['post_connectors']);
  update_option("simple_fields_post_type_defaults", $customterm['post_type_defaults']);

  global $wpdb;
  global $ukuupeople_db_version;

  $table_name = $wpdb->prefix . 'ukuu_favorites';
  $post_table = $wpdb->prefix . 'posts';
  $author_table = $wpdb->prefix . 'users';
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE if not exists $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      user_id BIGINT( 20 ) UNSIGNED NOT NULL,
      user_favs BIGINT( 20 ) UNSIGNED NOT NULL,
      UNIQUE KEY id (id ) ,
      INDEX (user_favs),
      CONSTRAINT Favourite FOREIGN KEY (user_favs) REFERENCES $post_table (ID) ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT Author FOREIGN KEY (user_id) REFERENCES $author_table (ID) ON UPDATE CASCADE ON DELETE CASCADE
	) $charset_collate;";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
  global $ukuupeople_db_version;
  update_option( "ukuupeople_db_version", $ukuupeople_db_version );
}

/**
 * Deactivation hook.
 *
 * Reset some of data.
 */
function on_deactivation() {
  if ( ! current_user_can( 'activate_plugins' ) )
    return;
  delete_option('ukuupeople_plugin_deferred_admin_notices');
  delete_option('ukuupeople_license_verify_notices');
  $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
}

/**
 * Change actions links after plugin install.
 *
 */
add_filter( 'install_plugin_complete_actions', 'ukuupeople_return_to_installer' , 99, 3 );
function ukuupeople_return_to_installer($install_act, $api , $plugin_file) {
  if ( $plugin_file == 'types/wpcf.php' || $plugin_file == 'simple-fields/simple_fields.php' ) {
    include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
    $admin_url = admin_url('plugins.php?page=ukuupeople-license&activate=types');
    $api = plugins_api('plugin_information', array('slug' => 'types', 'fields' => array('sections' => false) ) );
    $install_act['plugins_page'] = '<a href="' . $admin_url . '" title="' . esc_attr__('Go to plugins page') . '" target="_parent">' . __('Return to My page') . '</a>';
  }
  return $install_act;
}

/**
 * set locale for wp-type-activity post type.
 *
 */
add_filter( 'locale', 'set_my_locale' ,99);
function set_my_locale( $lang ) {
  global $typenow;
  if ( 'en_US' == $lang && ( $typenow == 'wp-type-activity' || $typenow == 'wp-type-contacts' ) ) {
    return 'en_GB';
  }
  return $lang;
}

// instantiate the plugin class
  require_once( UKUUPEOPLE_ABSPATH.'/includes/class-ukuupeople.php' );
global $wp_ukuu_people;
$wp_ukuu_people = new UkuuPeople();

function change_notice( $translated_text, $untranslated_text ) {
  $old = array("Plugin <strong>deactivated</strong>.",);
  $new = "Plugin is the dependant plugin. To deactivate this plugin <strong>Deactivate -- UKUUPEOPLE</strong> plugin first !!";
  if ( is_admin() && in_array( $untranslated_text, $old, true ) ) {
    $translated_text =  $new;
  }
  return $translated_text;
}
add_action('admin_notices', 'ukuupeople_admin_notices');
function ukuupeople_admin_notices() {
  if ($notices = get_option('ukuupeople_deferred_admin_notices')) {
    delete_option('ukuupeople_deferred_admin_notices');
    foreach ($notices as $notice)
      echo "<div class='updated'><p>$notice</p></div>";
  }
}
/*
 * Add Ukuu css file
 */
add_action( 'admin_enqueue_scripts', 'ukuupeople_style' );
function ukuupeople_style() {
  wp_enqueue_style( 'ukuupeople-style', UKUUPEOPLE_RELPATH.'/ukuupeople.css');
}

function on_uninstall() {
  if ( ! current_user_can( 'activate_plugins' ) )
    return;
  if ( __FILE__ != WP_UNINSTALL_PLUGIN )
    return;
}
