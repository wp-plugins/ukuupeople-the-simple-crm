<?php
defined( 'ABSPATH' ) OR exit;
/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */
if ( file_exists( UKUUPEOPLE_ABSPATH . '/includes/cmb2/init.php' ) ) {
	require_once UKUUPEOPLE_ABSPATH . '/includes/cmb2/init.php';
} elseif ( file_exists( UKUUPEOPLE_ABSPATH . '/includes/CMB2/init.php' ) ) {
	require_once UKUUPEOPLE_ABSPATH . '/includes/CMB2/init.php';
}

/**
 * Get the custom fields array
 */
global $customterm;
if ( file_exists( UKUUPEOPLE_ABSPATH . '/custom-fields.php' ) ) {
  require_once UKUUPEOPLE_ABSPATH . '/custom-fields.php';
}
/**
 * To create custom post type
 */
add_action( 'init','create_post_type' );

/**
 * To create custom fields for ukuupeople
 */
add_action( 'cmb2_init', 'ukuu_register_contact_metabox' );
add_action( 'cmb2_init', 'ukuu_register_setting_metabox' );
add_action( 'cmb2_init', 'ukuu_register_address_metabox' );
add_action( 'cmb2_init', 'ukuu_register_related_org_metabox' );

/**
 * To create custom fields for Touchpoints
 */
add_action( 'cmb2_init', 'touchpoints_register_metabox' );
add_action( 'cmb2_init', 'touchpoints_register_contacts_metabox' );
add_action( 'cmb2_init', 'touchpoints_register_assigned_to_metabox' );

/**
 * To change the year range
 */
add_filter( 'cmb2_localized_data', 'update_date_picker_defaults' );

function create_post_type() {
	$labels = array(
		'name'               => _x( 'UkuuPeople', 'post type general name', 'ukuu' ),
		'singular_name'      => _x( 'Human', 'post type singular name', 'human' ),
		'menu_name'          => _x( 'UkuuPeople', 'admin menu', 'your-plugin-textdomain' ),
		'name_admin_bar'     => _x( 'Human', 'add new on admin bar', 'your-plugin-textdomain' ),
		'add_new'            => _x( 'Add New', 'Human', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Human', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Human', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Human', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Human', 'your-plugin-textdomain' ),
		'all_items'          => __( 'People', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Human', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent text:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No People found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No People found in Trash.', 'your-plugin-textdomain' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => '',
    'menu_icon'          => 'dashicons-admin-users',
		'supports'           => array()
	);

  register_post_type( 'wp-type-contacts', $args );

  $labels =  array(
    'name'               => _x( 'Touchpoints', 'post type general name', 'touchpoint' ),
    'singular_name'      => _x( 'Touchpoint', 'post type singular name', 'touchpoint' ),
    'menu_name'          => _x( 'Touchpoints', 'admin menu', 'touchpoint-menu-name' ),
    'name_admin_bar'     => _x( 'Touchpoints', 'add new on admin bar', 'touchpoint-menu-name' ),
    'add_new'            => _x( 'Add New', 'Touchpoint', 'add-new-touchpoints' ),
    'add_new_item'       => __( 'Add New Touchpoint', 'add-new-touchpoint' ),
    'new_item'           => __( 'New Touchpoint', 'new-touchpoint' ),
    'edit_item'          => __( 'Edit Touchpoint', 'edit-touchpoint' ),
    'view_item'          => __( 'View Touchpoint', 'view-touchpoint' ),
    'all_items'          => __( 'All Touchpoints', 'all-touchpoint' ),
    'search_items'       => __( 'Search Touchpoints', 'search-touchpoints' ),
    'parent_item_colon'  => __( 'Parent text:', 'parent-text' ),
    'not_found'          => __( 'No Touchpoints found.', 'no-touchpoint-found' ),
    'not_found_in_trash' => __( 'No Touchpoints found in Trash.', 'no-touchpoints-found-in-trash' )
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array(),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => false,
    'menu_position'      => '',
    'supports'           => array( 'title' )
  );

  register_post_type( 'wp-type-activity', $args );


	// create a new taxonomy
	$labels = array(
		'name'              => _x( 'Tribes', 'taxonomy general name' ),
		'singular_name'     => _x( 'Tribe', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Tribe' ),
		'all_items'         => __( 'All Tribe' ),
		'parent_item'       => __( 'Parent Tribe' ),
		'parent_item_colon' => __( 'Parent Tribe:' ),
		'edit_item'         => __( 'Edit Tribe' ),
		'update_item'       => __( 'Update Tribe' ),
		'add_new_item'      => __( 'Add New Tribe' ),
		'new_item_name'     => __( 'New Tribe Name' ),
		'menu_name'         => __( 'Tribe' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( ),
	);

	register_taxonomy( 'wp-type-group', array( 'wp-type-contacts' ), $args );

	// create a new taxonomy
	$labels = array(
		'name'              => _x( 'Tags', 'taxonomy general name' ),
		'singular_name'     => _x( 'Tag', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Tag' ),
		'all_items'         => __( 'All Tag' ),
		'parent_item'       => __( 'Parent Tag' ),
		'parent_item_colon' => __( 'Parent Tag:' ),
		'edit_item'         => __( 'Edit Tag' ),
		'update_item'       => __( 'Update Tag' ),
		'add_new_item'      => __( 'Add New Tag' ),
		'new_item_name'     => __( 'New Tag Name' ),
		'menu_name'         => __( 'Tag' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( ),
	);

	register_taxonomy( 'wp-type-tags', array( 'wp-type-contacts' ), $args );

	// create a new taxonomy
	$labels = array(
		'name'              => _x( 'Contact Types', 'taxonomy general name' ),
		'singular_name'     => _x( 'Contact Type', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Contact Type' ),
		'all_items'         => __( 'All Contact Type' ),
		'parent_item'       => __( 'Parent Contact Type' ),
		'parent_item_colon' => __( 'Parent Contact Type:' ),
		'edit_item'         => __( 'Edit Contact Type' ),
		'update_item'       => __( 'Update Contact Type' ),
		'add_new_item'      => __( 'Add New Contact Type' ),
		'new_item_name'     => __( 'New Contact Type Name' ),
		'menu_name'         => __( 'Contact Type' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( ),
	);

	register_taxonomy( 'wp-type-contacts-subtype', array( 'wp-type-contacts' ), $args );

	// create a new taxonomy
	$labels = array(
		'name'              => _x( 'Touchpoint Types', 'taxonomy general name' ),
		'singular_name'     => _x( 'Touchpoint Type', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Touchpoint Type' ),
		'all_items'         => __( 'All Touchpoint Type' ),
		'parent_item'       => __( 'Parent Touchpoint Type' ),
		'parent_item_colon' => __( 'Parent Touchpoint Type:' ),
		'edit_item'         => __( 'Edit Touchpoint Type' ),
		'update_item'       => __( 'Update Touchpoint Type' ),
		'add_new_item'      => __( 'Add New Touchpoint Type' ),
		'new_item_name'     => __( 'New Touchpoint Type Name' ),
		'menu_name'         => __( 'Touchpoint Type' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( ),
	);

	register_taxonomy( 'wp-type-activity-types', array( 'wp-type-activity' ), $args );
  	delete_option( 'simple_fields_groups' );
}

/**
 * Define the metabox and field configurations.
 */
function ukuu_register_contact_metabox() {
  $prefix = 'wpcf-';
  $cmb_demo = new_cmb2_box( array(
                'id'            => $prefix . 'group-edit-contact-info',
                'title'         => __( 'Edit Contact Info', 'cmb2' ),
                'object_types'  => array( 'wp-type-contacts', ), // Post type
              ) );

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

  add_fields( $actual_fields , $cmb_demo);
}



/**
 * Define the metabox and field configurations.
 */
function ukuu_register_setting_metabox() {
  $prefix = 'wpcf-';
  $cmb_demo = new_cmb2_box( array(
                'id'            => $prefix . 'group-edit_contact_privacy_settings',
                'title'         => __( 'Edit Privacy Settings', 'cmb2' ),
                'object_types'  => array( 'wp-type-contacts', ), // Post type
              ) );

  $actual_fields = array(
    0 => 'privacy-settings',
    1 => 'bulk-mailings',
  );
  add_fields( $actual_fields , $cmb_demo);
}

function ukuu_register_related_org_metabox(){
  $prefix = 'wpcf-';
  $cmb_demo = new_cmb2_box( array(
                'id'            => $prefix . 'related-org-metabox',
                'title'         => __( 'Related Organization', 'cmb2' ),
                'object_types'  => array( 'wp-type-contacts', ), // Post type
              ) );

  $cmb_demo->add_field( array(
      'name'             => __( 'select organization', 'cmb2' ),
      'desc'             => __( '', 'cmb2' ),
      'id'               => $prefix . 'related-org',
      'type'             => 'select',
      'show_option_none' => true,
      'options'          => get_related_org_value(),
    ) );
}

/**
 * Define the metabox and field configurations.
 */
function ukuu_register_address_metabox() {
  $prefix = 'wpcf-';
  $cmb_demo = new_cmb2_box( array(
                'id'            => $prefix . 'group-edit_contact_address',
                'title'         => __( 'Edit Contact Address', 'cmb2' ),
                'object_types'  => array( 'wp-type-contacts', ), // Post type
              ) );

  $actual_fields = array(
    0 => 'streetaddress',
    1 => 'streetaddress2',
    2 => 'city',
    3 => 'state',
    4 => 'postalcode',
    5 => 'country',
  );
  add_fields( $actual_fields , $cmb_demo);
}

/**
 * Define the metabox and field configurations.
 */
function touchpoints_register_metabox() {
  $prefix = 'wpcf-';
  $activity_information = new_cmb2_box( array(
                            'id'            => $prefix . 'group-activity-information',
                            'title'         => __( 'Activity information', 'cmb2' ),
                            'object_types'  => array( 'wp-type-activity', ), // Post type
                          ) );

  $actual_fields = array(
    0 => 'startdate',
    1 => 'enddate',
    2 => 'status',
    3 => 'details',
    4 => 'attachments',
  );
  add_fields( $actual_fields , $activity_information);
}

function touchpoints_register_contacts_metabox() {
  $prefix = 'wpcf-';

  $touchpoint_contact = new_cmb2_box( array(
                          'id'            => $prefix . 'post-relationship',
                          'title'         => __( 'Touchpoint Contact', 'cmb2' ),
                          'object_types'  => array( 'wp-type-activity' ,), // Post type
                          'context'    => 'normal',
                          'priority'   => 'high',
                          'closed'     => true, // true to keep the metabox closed by default
                        ) );

  $touchpoint_contact->add_field( array(
      'name'             => __( 'Human', 'cmb2' ),
      'desc'             => __( 'This Touchpoint belongs to:', 'cmb2' ),
      'id'               => $prefix . 'pr-belongs',
      'type'             => 'select',
      'show_option_none' => true,
      'options'          => array(),
    ) );

}

function touchpoints_register_assigned_to_metabox() {
  $prefix = 'wpcf_';

  $touchpoint_assigned = new_cmb2_box( array(
                           'id'            => $prefix . 'touchpoint_assigned_metabox',
                           'title'         => __( 'Assigned To', 'cmb2' ),
                           'object_types'  => array( 'wp-type-activity' ,), // Post type
                           'context'    => 'normal',
                           'priority'   => 'high',
                           'closed'     => true, // true to keep the metabox closed by default
                         ) );

  $touchpoint_assigned->add_field( array(
      'name'             => __( 'Assigned to', 'cmb2' ),
      'desc'             => __( '', 'cmb2' ),
      'id'               => $prefix . 'assigned_to',
      'type'             => 'select',
      'show_option_none' => true,
      'options'          => get_id_and_displayname(),
      'repeatable'      => true,
    ) );
}

function get_id_and_displayname() {
  $args = array(
    'fields' => 'ids',
    'numberposts' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_status' => array( 'publish', 'private' ),
    'post_type' => 'wp-type-contacts',
    'suppress_filters' => 0,
  );
  $items = (array) get_posts($args);
  $display_names = array();
  foreach( $items as $item ) {
    $display_name = get_post_meta($item, 'wpcf-display-name', true);
    $display_names[$item] = $display_name;
  }
  $array_values = "";
  foreach ( $display_names as $key => $values ) {
    $array_values[$key] = __( $values, 'cmb2' );
  }
  if ( !empty( $array_values ) ) return $array_values;
}

function get_related_org_value() {
  $args = array(
    'fields' => 'ids',
    'numberposts' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_status' => array( 'publish', 'private' ),
    'post_type' => 'wp-type-contacts',
    'suppress_filters' => 0,
    'tax_query' => array(
      array(
        'taxonomy' => 'wp-type-contacts-subtype',
        'field' => 'slug',
        'terms' => 'wp-type-org-contact'
      )
    )
  );
  $items = (array) get_posts($args);
  $display_names = array();
  foreach( $items as $item ) {
    $display_name = get_post_meta($item, 'wpcf-display-name', true);
    $display_names[$item] = $display_name;
  }
  $array_values = "";
  foreach ( $display_names as $key => $values ) {
    $array_values[$key] = __( $values, 'cmb2' );
  }
  if ( !empty( $array_values ) ) return $array_values;
}

function add_fields( $actual_fields , $cmb_demo) {
  global $customterm;
  foreach ( $customterm['fields'] as $key => $value ){
    if ( in_array($key , $actual_fields) )
      $cmb_demo->add_field( $value );
  }
}

function update_date_picker_defaults( $l10n ) {
  $l10n['defaults']['date_picker']['yearRange'] = '1900:+10';
  return $l10n;
}
