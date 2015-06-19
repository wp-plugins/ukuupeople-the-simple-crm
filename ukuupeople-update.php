<?php
defined( 'ABSPATH' ) OR exit;
define( 'UKUUPEOPLE_ABSPATH', dirname( __FILE__ ) );

/**
 * Check for Update process for new version
 **/

class ukuupeople_update {

  /**
   * @var array(revisionNumber) sorted numerically
   */
  private $revisions;
  /**
   * Class contructor
   **/
  function __construct( $old_version, $new_version ) {
    $this->latest_version = $new_version;
    $this->previous_version = $old_version;
  }

  function onUpdate() {
    //Set up incremental update function call
    $revisions = $this->getRevisions();
    if ( $this->hasPendingRevisions() ) {
      $currentRevision = $this->getCurrentRevision();
      foreach ( $this->getRevisions() as $revision ) {
        if ( $revision > $currentRevision ) {
          call_user_func( array($this,'ukuu_update_'.$revision) );
          update_option( 'ukuupeople_version_update_function', $revision );

        }
      }
    }
  }
  // ******** Revision-tracking helpers ********

  /**
   * Determine if there are any pending revisions
   *
   * @return bool
   */
  public function hasPendingRevisions() {
    $revisions = $this->getRevisions();
    $currentRevision = $this->getCurrentRevision();

    if (empty($revisions)) {
      return FALSE;
    }
    if (empty($currentRevision)) {
      return TRUE;
    }

    return ($currentRevision <= max($revisions));
  }

  /**
   * Get a list of revisions
   *
   * @return array(revisionNumbers) sorted numerically
   */
  public function getRevisions() {
    if (! is_array($this->revisions)) {
      $this->revisions = array();

      $clazz = new ReflectionClass(get_class($this));
      $methods = $clazz->getMethods();
      foreach ($methods as $method) {
        if (preg_match('/^ukuu_update_(.*)/', $method->name, $matches)) {
          $this->revisions[] = $matches[1];
        }
      }
      sort($this->revisions, SORT_NUMERIC);
    }

    return $this->revisions;
  }

  public function getCurrentRevision() {
    $key = get_option( 'ukuupeople_version_update_function' );
    if (!$key) {
      $key = 1000;
    }
    return $key;
  }

  // ******** Updater functions ********
  public function ukuu_update_1001() {
    global $ukuupeople_db_version, $wpdb;
    require_once( UKUUPEOPLE_ABSPATH. '/ukuupeople-config.php' );
    update_option("simple_fields_groups", $customterm['field_groups']);

    $table_name = $wpdb->prefix . 'ukuu_favorites';
    $retrieve = $wpdb->get_results( $wpdb->prepare(
               "
               SELECT *
               FROM $table_name
               "
    ) , OBJECT );
    $wpdb->delete( $table_name );

    if ( !empty( $retrieve ) ) {
      foreach ($retrieve as $key => $value ) {
        $retrieve_data = explode(",",$value->user_favs);
        foreach( $retrieve_data as $k => $v ) {
          $post_id = $v;
          $wpdb->insert(
            $table_name,
            array(
              'user_id' => $value->user_id,
              'user_favs' => $post_id,
            )
          );
        }
      }
    }
    $post_table = $wpdb->prefix . 'posts';
    $author_table = $wpdb->prefix . 'users';
    $wpdb->query("ALTER TABLE $table_name
      MODIFY user_id BIGINT( 20 ) UNSIGNED NOT NULL,
      MODIFY user_favs BIGINT( 20 ) UNSIGNED NOT NULL,
      ADD INDEX (user_favs),
      ADD CONSTRAINT Favourite FOREIGN KEY (user_favs) REFERENCES $post_table (ID) ON UPDATE CASCADE ON DELETE CASCADE,
      ADD CONSTRAINT Author FOREIGN KEY (user_id) REFERENCES $author_table (ID) ON UPDATE CASCADE ON DELETE CASCADE");
    $data = get_option('wpcf-fields');
    $data['phone']['type'] = 'phone';
    $data['phone']['data']['validate'] = array();
    $data['privacy-settings']['data']['options'] = array('wpcf-fields-checkboxes-option-1' => array('title' => 'Phone' , 'set_value' => 'do_not_phone' , 'display' => 'value', 'display_value_not_selected' => '', 'display_value_selected' => 'Phone') ,'wpcf-fields-checkboxes-option-2' => array('title' => 'Email' , 'set_value' => 'do_not_email' , 'display' => 'value', 'display_value_not_selected' => '', 'display_value_selected' => 'Email'),'wpcf-fields-checkboxes-option-3' => array('title' => 'Mail' , 'set_value' => 'do_not_mail' , 'display' => 'db'),'wpcf-fields-checkboxes-option-3' => array('title' => 'SMS' , 'set_value' => 'do_not_sms' , 'display' => 'value', 'display_value_not_selected' => '', 'display_value_selected' => 'SMS') );

    $data['bulk-mailings']['data']['options'] = array('wpcf-fields-checkboxes-option-1' => array('title' => 'Opt Out' , 'set_value' => 'opt_out' , 'display' => 'value', 'display_value_not_selected' => '', 'display_value_selected' => 'Opt Out') );
    $simple_field_data = get_option('simple_fields_groups');
    $simple_field_data[1]['fields'][1]['type'] = 'post';
    unset($simple_field_data[1]['fields'][1]['type_user_options']);
    $simple_field_data[1]['fields'][1]['type_post_options'] = array(
      "enabled_post_types" => array(
        "0" => 'wp-type-contacts',
      ),
      "additional_arguments" => 'post_status=publish,private',
      "enable_extended_return_values" => 1,
    );
    $simple_field_data[1]['fields_by_slug'] =  array(
      "assigned to" => array(
        "slug" => 'assigned',
        "name" => 'Assigned to',
        "description" => '',
        "type" => 'post',
        "options" => array(
          "post" => array(
            "enabled_post_types" => array(
              "0" => 'wp-type-contacts',
            ),
            "values" => array( ),
            "additional_arguments" => '',
            "enable_extended_return_values" => 1,
          ),
        ),
        "id" => 0,
        "type_post_options" => array(
          "enabled_post_types" => array(
            "0" => 'wp-type-contacts',
          ),
          "additional_arguments" => '',
          "enable_extended_return_values" => 1,
        ),
        "type_taxonomyterm_options" => array(
          "additional_arguments" => '',
        ),
        "deleted" => 0,
      ),
    );
    $simple_field_data[1]['added_with_code'] = 1;
    update_option('simple_fields_groups', $simple_field_data);
    update_option('wpcf-fields', $data);
    update_option( "ukuupeople_db_version", $ukuupeople_db_version );
    return true;
  }

 // ******** Updater functions ********
  public function ukuu_update_1002() {
    global $ukuupeople_db_version, $wpdb;
    require_once( dirpath. 'ukuupeople-config.php' );
    update_option("simple_fields_groups", $customterm['field_groups']);
    $data = get_option('wpcf-fields');
    $data['postalcode']['type'] = 'textfield';
    $data['postalcode']['data']['validate'] = array();
    update_option('wpcf-fields', $data);
    $newdata['ukuu-job-title'] = array(
      'id' => 'ukuu-job-title',
      'name' => 'Job Title',
      'slug' => 'ukuu-job-title',
      'type' => 'textfield',
      'description' => '',
      'data' => array(
        'repetitive' => 0,
        'validate' => array(),
        'conditional_display' => array(),
        'disabled_by_type' => 0,
      ),
      'meta_key' => 'wpcf-ukuu-job-title',
      'meta_type' => 'postmeta',
    );
    $newdata['ukuu-twitter-handle'] = array(
      'id' => 'ukuu-twitter-handle',
      'name' => 'Twitter Handle',
      'slug' => 'ukuu-twitter-handle',
      'type' => 'textfield',
      'description' => '',
      'data' => array(
        'repetitive' => 0,
        'validate' => array(),
        'conditional_display' => array(),
        'disabled_by_type' => 0,
      ),
      'meta_key' => 'wpcf-ukuu-twitter-handle',
      'meta_type' => 'postmeta',
    );
    $newdata['ukuu-facebook-url'] = array(
      'id' => 'ukuu-facebook-url',
      'name' => 'Facebook URL',
      'slug' => 'ukuu-facebook-url',
      'type' => 'url',
      'description' => '',
      'data' => array(
        'repetitive' => 0,
        'validate' => array(
          'url' => array(
            'active' => 1,
            'value' => true,
            'message' => 'Please enter a valid URL address',
          ),
        ),
        'disabled_by_type' => 0,
        'conditional_display' => array(),
      ),
      'meta_key' => 'wpcf-ukuu-facebook-url',
      'meta_type' => 'postmeta',
    );
    $newdata['ukuu-date-of-birth'] = array(
      'id' => 'ukuu-date-of-birth',
      'name' => 'Date Of Birth',
      'slug' => 'ukuu-date-of-birth',
      'type' => 'date',
      'description' => '',
      'data' => array(
        'date_and_time' => 'date',
        'repetitive' => 0,
        'validate' => array(
          'date' => array(
            'active' => 1 ,
            'format' => 'mdy',
            'pattern' => 'check.format',
            'message' => 'please enter a valid date',
          ),
        ),
        'conditional_display' => array(),
        'disabled_by_type' => 0,
      ),
      'meta_key' => 'wpcf-ukuu-date-of-birth',
      'meta_type' => 'postmeta',
    );
    self::ukuu_new_fields($newdata);
    $group_id = get_page_by_title( 'Edit Contact Info', OBJECT, 'wp-types-group' );
    $prev_fields = get_post_meta( $group_id->ID, '_wp_types_group_fields' );
    $fields = $prev_fields[0] ."ukuu-job-title,ukuu-twitter-handle,ukuu-facebook-url,ukuu-date-of-birth,";
    update_post_meta( $group_id->ID, '_wp_types_group_fields', $fields );
    $prev_fields = get_post_meta( $group_id->ID, '_wp_types_group_fields' );
    update_option( "ukuupeople_db_version", $ukuupeople_db_version );
    return true;
  }

  public function ukuu_new_fields($newFields) {
    $args['fields'] = $newFields;
    foreach ( $newFields as $field_id => $field_val) {
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
  }
}
