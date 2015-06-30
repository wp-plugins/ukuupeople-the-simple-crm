<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Users Contact Page.
 *
 * @package UKUUPEOPLE
 * @subpackage UKUU CRM Contacts
 */

class UkuuPeople{

	public function __construct( $args = array() ) {
    /*
     * Insert Contact Type and Activity Type taxonomy terms on plugin activation
     */
    add_action( 'admin_init', array( $this , 'insert_taxonomy_terms' ));
    add_action( "admin_init", array ( $this,"remove_box" ));
    add_action( 'wp_dashboard_setup', array( $this, 'ukuuCRM_dashboard_setup' ) );

    /* metabox for custom activity list */
    add_action( 'add_meta_boxes',  array( $this , 'ukuu_custom_touchpoint' ) );

    add_action( 'transition_post_status', array( $this, 'publish_post_privately' ), 999, 3 );

    /**
     * if submitted filter by post meta*/
    add_filter( 'parse_query', array( $this ,'posts_filter_touchpoint_contact') );

    /* Change public text to Create */
    add_filter( 'gettext', array( $this , 'change_publish_button' ) , 10, 2 );

    /* Change email to display name */
    add_filter( 'the_title', array( $this , 'change_related_org_value' ), 9999, 2 );

    // hide fields table meta box
    add_filter("simple_fields_add_post_edit_side_field_settings", array( $this , "hide_simple_side_field_settings" ), 10, 2);

    /*
     * Unset the All/Published/Trash menu filter from WP-Type-Activity and WP-Type-Contact list view
     */
    add_action( 'views_edit-wp-type-contacts',  array( $this , 'remove_views' ));
    add_action( 'views_edit-wp-type-activity',  array( $this , 'remove_views' ));

    /* Remove view link from activities post */
    add_filter( 'post_row_actions', array( $this , 'remove_row_actions'), 10, 1 );

    /* ajax call to get activity list*/
    add_action( 'wp_ajax_get_act_lists', array( $this , 'prepare_query' )); //get lists via ajax call
    add_action( 'wp_ajax_ukuu_add_to_fav', array( $this , 'ukuu_add_to_fav' )); //get lists via ajax call

    /*
     * Add custom filters for wp-type-contacts and wp-type-activity post listing
     * Add Contact type graph on wp-type-contacts post type listing
     */
    add_action( 'restrict_manage_posts', array( $this , 'ukuu_custom_filters_posts' ), 1);

    /*
     * Add custom field columns on wp-type-contacts and wp-type-activity post listing page
     */
    add_action( 'manage_wp-type-contacts_posts_custom_column', array( $this, 'ukuu_custom_field_list_text' ), 10, 2 );
    add_action( 'manage_wp-type-activity_posts_custom_column', array( $this, 'ukuu_custom_field_list_text' ), 10, 2 );
    add_filter( 'manage_wp-type-contacts_posts_columns', array( $this, 'ukku_contacts_custom_fields_list_view' ) );
    add_filter( 'manage_wp-type-activity_posts_columns', array( $this, 'ukku_activity_custom_fields_list_view' ) );
    add_action( 'admin_head', array( $this , 'ukku_contacts_custom_fields_hide_date_filter' ) );

    /*
     * Update related user on wp-type-contact updation
     */
    add_action( 'profile_update', array( $this , 'ukuu_custom_profile_update' ), 10, 2 );

    /*
     * Set contact/activity type on respective new post type saving
     */
    add_action( 'save_post', array( $this, 'ukuu_set_wp_custom_type' ), 99 , 2 );

    /*
     * Remove submenu pages for contact/Activity add and hide/remove title, types meta box
     */
    add_action( 'admin_menu', array( $this , 'ukuu_custom_remove_links_meta'), 9999 );
    add_action( 'admin_menu', array($this, 'ukuupeople_menu') );

    /*
     * Add Contact summary view on Edit link
     */
    add_action( 'edit_form_after_title', array( $this , 'ukuu_custom_summary_view' ));

    /*
     * Collapse the fields on page load
     */
    add_filter( 'get_user_option_closedpostboxes_wp-type-contacts', array( $this , 'closed_meta_boxes') );

    /*
     * Edit 'Title' label on quick edit for activity
     */
    add_filter('gettext',array( $this , 'custom_enter_title'));

    /*
     * Add wordpress user on wp-type-contacts post save
     */
    add_filter( 'wp_insert_post_data', array( $this , 'ukuu_custom_add_user'), 10, 2 );

    /*
     * Delate respecive contacts post on user deletion
     */
    add_action( 'delete_user', array( $this , 'ukuu_delete_user' ));

    add_action( 'wp-type-activity-types_add_form_fields', array ( $this, 'add_color_fields' ) );
    add_action( 'create_wp-type-activity-types', array ( $this, 'save_color_fields' ) );

  }

  function remove_box()
  {
    remove_post_type_support('wp-type-contacts', 'title');
    remove_post_type_support('wp-type-contacts', 'editor');
    remove_post_type_support('wp-type-activity', 'editor');
  }

  function ukuupeople_menu() {
    add_submenu_page( NULL, 'Add New Contact', 'Add New Contact', 'manage_options', 'add-new-contact', array( $this, 'add_new_contact_type'));
    add_submenu_page( 'edit.php?post_type=wp-type-contacts' , 'Touchpoint', 'Touchpoints', 'manage_options', 'edit.php?post_type=wp-type-activity', '');
    require_once(UKUUPEOPLE_ABSPATH.'/includes/add-ons.php');
    $ukuupeople_add_ons_page = add_submenu_page( 'edit.php?post_type=wp-type-contacts', __( 'UkuuPeople Add-ons', 'ukuupeople' ), __( 'Add-ons', 'UkuuPeople' ), 'install_plugins', 'ukuupeople-addons', 'ukuupeople_add_ons_page' );
    if ( current_user_can('manage_categories') ) {
      add_submenu_page('edit.php?post_type=wp-type-contacts', 'setting', 'Settings' , 'manage_options','settings' ,array( $this ,'settings'));
    }
  }

  function ukuuCRM_dashboard_setup() {
    wp_add_dashboard_widget (
      'ukuuCRM-dashboard-activities-widget',
      'My Activities',
      array( $this , 'ukuuCRM_dashboard_activities_content' ),
      $control_callback = null
    );
    wp_add_dashboard_widget (
      'ukuuCRM-dashboard-favorites-widget',
      'My Favorites',
      array( $this, 'ukuuCRM_dashboard_favorites_content' ),
      $control_callback = null
    );
	}

  // Function for adding color option for Touchpoints
  function add_color_fields($taxonomy_name) {
    ?>
    <div>
        <label>Choose color for Touchpoint</label>
         <input type="radio" name="category-radio" id="act-color-1" value="#666" style="display: inline-block; width: 25px;" />
        <label for="act-color-1" style="display: inline-block; width: auto; vertical-align: top;"><div style="width: 30px;height: 20px;border-width: 1px;background-color:#666;"></div></label>
        <input type="radio" name="category-radio" id="act-color-2" value="#FE4D39" style="display: inline-block; width: 25px;" />
        <label for="act-color-2" style="display: inline-block; width: auto; vertical-align: top;"><div style="width: 30px;height: 20px;border-width: 1px;background-color:#FE4D39;"></div></label>
        <input type="radio" name="category-radio" id="act-color-3" value="#E6397A" style="display: inline-block; width: 25px;" />
        <label for="act-color-3" style="display: inline-block; width: auto; vertical-align: top;"><div style="width: 30px;height: 20px;border-width: 1px;background-color:#E6397A;"></div></label>
    </div></br>
    <?php
  }

  /**
   * save our custom fields and all term related data.
   *
   * @param type $term_id
   */
  function save_color_fields($term_id) {
    //collect all term related data for this new taxonomy
    $term_item = get_term($term_id,'wp-type-activity-types');
    $term_slug = $term_item->slug;
    if (isset($_POST['category-radio'])) {
      //collect our custom fields
      $term_category_radio = sanitize_text_field($_POST['category-radio']);
      //save our custom fields as wp-options
      update_option('term_category_radio_' . $term_slug, $term_category_radio);
    }
  }

  function publish_post_privately( $new_status, $old_status, $post ) {
    if ( ($post->post_type == 'wp-type-contacts' || $post->post_type == 'wp-type-activity') && $new_status == 'publish' && $old_status  != $new_status ) {
      $post->post_status = 'private';
      wp_update_post( $post );
    }
  }

  /**
   * Unset the All/Published/Trash menu filter from WP-Type-Activity and WP-Type-Contact list view.
   *
   * @param type $views
   * @return type
   */
  function remove_views( $views ) {
    $views = array();
    return $views;
  }

  /**
   * Remove view link from activities post.
   *
   * @param type $actions
   * @return type
   */
  function remove_row_actions( $actions ) {
    if ( get_post_type() === 'wp-type-activity' ) {
      unset( $actions['view'] );
    }
    return $actions;
  }

  /**
   * hide screen option for simple-fields meta box.
   *
   * @param type $simple_field_setting
   * @return type
   */
  function hide_simple_side_field_settings($simple_field_setting, $post) {
    if ( $post->post_type == 'wp-type-activity' || $post->post_type == 'wp-type-contacts' ) {
      $simple_field_setting = false;
    }
    return $simple_field_setting;
  }

  /**
   * Returns filter text.
   *
   * @global type $wpdb
   * @param type $text
   * @return type
   */
  function change_publish_button( $translation, $text ) {
    global $post_type;
    if ( $post_type == 'wp-type-contacts' ||  $post_type == 'wp-type-activity' ) {
      if ( $text == 'Publish' )
        return 'Create';
    }
    return $translation;
  }

  /**
   * Returns display name for $id.
   *
   * @global type $wpdb
   * @param type $id
   * @return type
   */
  function change_related_org_value( $title, $id = NULL ) {
    if ( !empty($id) && 'wp-type-contacts' == get_post_type($id) ) {
      $display = get_post_meta( $id , 'wpcf-display-name', true );
      $title = $display;
    }
    return $title;
  }

  /**
   * parse query for custom search.
   *
   * @param type $query
   */
  function posts_filter_touchpoint_contact( $query ) {
    $qv = &$query->query_vars;//grab a reference to manipulate directly
    Global $pagenow;
    if( $pagenow=='edit.php' && isset($_GET['post_type']) && 'wp-type-activity' ==$_GET['post_type']  && $query->is_main_query() ) {
      if( !empty ( $_GET['touchpoint-contact'] ) )
        {
          $qv['meta_query'][] = array('value' => $_GET['touchpoint-contact']);
        }
    }
    if( $pagenow=='edit.php' && isset($_GET['post_type']) && 'wp-type-contacts' ==$_GET['post_type']  && $query->is_main_query() ) {
      /* If this drop-down has been affected, add a meta query to the query
       *
       */
      if( !empty ( $_GET['s'] ) ) {
        global $wpdb;
        // custom filter for display name //
        $meta_value = $_GET['s'];
        $terms = $wpdb->get_results( $wpdb->prepare(
               "
               SELECT ID
               FROM $wpdb->posts
               LEFT JOIN $wpdb->postmeta
               ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
               WHERE (post_status = 'publish' OR  post_status = 'private')
               AND post_type = 'wp-type-contacts'
               AND ((meta_key='wpcf-display-name' and meta_value like %s) OR (meta_key='wpcf-email' and meta_value = %s)
               OR (meta_key='wpcf-first-name' and meta_value like %s ) OR (meta_key='wpcf-last-name' and meta_value like %s))
               ",
               $meta_value,
               $meta_value,
	        $meta_value,
               $meta_value
        ) , OBJECT);

        if ( !empty( $terms ) ) {
          $postID = array_map ( function ( $v ){ return $v->ID; }, $terms );
          $qv['s']= '';
          $qv['post__in'] =$postID;
        } ?>
        <script src="<?php echo includes_url().'js/jquery/jquery.js'; ?>"></script>
        <script type="text/javascript">
           jQuery( function($){
               var str = document.getElementsByName('s')[0].value;
               $('.post-type-wp-type-contacts .wrap h2 .subtitle').html("Search results for '"+str+"' ");
             });
        </script><?php
            } else {
        // parse query to make default display sorted by first name and alphabetically //
        $qv['order'] = 'ASC';
        $qv['orderby'] = 'meta_value';
        $qv['meta_key'] = 'wpcf-display-name';
      }
    }
  }

  /**
   * Custom metabox for Touchpoint list.
   */
  function ukuu_custom_touchpoint() {
    //To hide screen options
    global $wp_meta_boxes;
    unset($wp_meta_boxes['wp-type-contacts']['side']['high']['wpcf-marketing']);
    unset($wp_meta_boxes['wp-type-activity']['side']['high']['wpcf-marketing']);
    unset($wp_meta_boxes['wp-type-contacts']['side']['core']['categorydiv']);
    unset($wp_meta_boxes['wp-type-contacts']['normal']['default']['wpcf-post-relationship']);
    unset($wp_meta_boxes['wp-type-contacts']['normal']['core']['slugdiv']);

    $screens = array( 'post', 'wp-type-activity' );
    foreach ( $screens as $screen ) {
      add_meta_box(
        'touchpoint-types',            // Unique ID
        __( 'Touchpoint List'),      // Box title
        array( $this ,'ukuu_custom_touchpoint_list') ,  // Content callback
        $screen
      );
    }
  }

  /**
   * Content callback for custom metabox Touchpoint list.
   */
  function ukuu_custom_touchpoint_list( $post ) {
    if ( isset($post->ID) && $post->post_type == 'wp-type-activity'  ) {
      // To change Dropdown email list to display name//
      global $wpdb;
      $opt = array(
         '0' => 'None',
      );
      $args = array(
        'fields' => 'ids',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => array( 'publish', 'private' ) ,
        'post_type' => 'wp-type-contacts',
        'suppress_filters' => 0,
      );
      $items = get_posts($args);
      if ( !empty ( $items ) ) {
        foreach ( $items as $temp_post => $val ) {
          $display = get_post_meta( $val , 'wpcf-display-name', true );
          $opt[$val] = $display;
        }
      }
      $postid = json_encode(get_post_meta( $post->ID , '_wpcf_belongs_wp-type-contacts_id', true ));
      $opt = json_encode($opt);?>
      <script type="text/javascript">
         var newOptions = <?php echo $opt; ?>;
         var postID = <?php echo $postid; ?>;
         var $el = jQuery('select[name="wpcf-pr-belongs"]');
         $el.empty(); // remove old options
         jQuery.each(newOptions, function(value,key) {
             if ( postID == value ) {
               $el.append(jQuery("<option selected></option>")
                          .attr("value", value).text(key));
             } else {
               $el.append(jQuery("<option></option>")
                          .attr("value", value).text(key));
             }
           });
      </script><?php
      //  To change Dropdown email list to display name //
      wp_enqueue_script( 'ukuucrm', UKUUPEOPLE_RELPATH.'/script/ukuucrm.js' , array() );
      $contact_id = '';
      if ( isset( $_GET['cid'] ) ) $contact_id = $_GET['cid'];

      echo "<input type='hidden' value='$contact_id' name='hidden_cid'>";
      $post_term = wp_get_post_terms( $post->ID, 'wp-type-activity-types', array("fields" => "names") );
      $acttype = get_terms( 'wp-type-activity-types' ,'hide_empty=0' );
      echo '<select name="touchpoint-list" id="touchpoint-list" class="postbox">';
      if( empty( $post_term )  ){
        echo "<option value='' selected>Select Touchpoint type..</option>";
      }
      foreach ($acttype as $key => $value ) {
        if( isset( $post_term[0] ) && $value->name == $post_term[0] ){
          echo "<option value=".$value->slug." selected>".$value->name."</option>";
        }
        else {
          echo "<option value=".$value->slug.">".$value->name."</option>";
        }
      }
      echo "</select>";
    }
  }

  /**
   * Touchpoint list for dashboard.
   *
   */
  public static function ukuuCRM_dashboard_activities_content() {
    $user_id= get_current_user_id();
    $args = array('post_type' => 'wp-type-activity' , 'author' =>$user_id , 'posts_per_page'=> 4, 'meta_query' => array(
      array(
        'key'     => 'wpcf-status',
        'value'   => 'scheduled',
      ),
    ));
    $loop = new WP_Query( $args );
    global $base_url;
    echo "<div class='activity-wrapper'>";
    while ( $loop->have_posts() ) : $loop->the_post();
    $iduser=get_the_author();
    $custom = get_post_custom( get_the_ID() );
    if (isset($custom['wpcf-startdate'][0]) && !empty($custom['wpcf-startdate'][0]) && isset($custom['wpcf-status'][0]) && $custom['wpcf-status'][0] == 'scheduled') {
      $URL = get_edit_post_link( get_the_ID() );
      echo "<div class='user_activity' style='overflow:auto;'><div class='activity_date' style='background-color:#0074a2;'><div class='month-act'>";
      echo date('F' ,$custom['wpcf-startdate'][0] );echo "</div><div class='day-act'>";
      echo date('d' ,$custom['wpcf-startdate'][0] );
      echo "</div></div>";
      echo "<div class='activity_info'>";
      if(!wp_is_mobile()) {
        echo "<div><a href='$URL'><strong>";
        the_title();echo "</a></strong></div>";
      } else {
        echo "<div><a href='$URL'><strong>";
        echo substr( get_the_title(), 0, 15).'...';echo "</a></strong></div>";
      }
      echo "created by ";
      echo "<span><a href='#'>$iduser</a></span>";
      $with_id = get_post_meta( get_the_ID() , '_wpcf_belongs_wp-type-contacts_id', true );
      if ( $with_id ) {
        $first =  get_post_meta( $with_id , 'wpcf-first-name', true );
        $last =  get_post_meta( $with_id , 'wpcf-last-name', true );
        $contact_view_url = get_edit_post_link ( $with_id );
        echo "<div>with <span><a href='$contact_view_url'>".$first." ".$last."</a></span></div>";
      }
      echo "</div><div class='activity_time' style='background-color:#0074a2;'><div class='dayName'>";
      echo date('l' ,$custom['wpcf-startdate'][0] );echo "</div><div class='time'>";
      echo date('g:i a' ,$custom['wpcf-startdate'][0] );
      echo "</div></div></div>";
    }
    endwhile;
    echo "</div>";
  }

  /**
   * Touchpoint favorites list for dashboard.
   */
  public static function ukuuCRM_dashboard_favorites_content() {
    $user_id= get_current_user_id();
    $posts = self::retrieve_favorites();
    global $base_url;
    echo "<div class='dashboard-favorites-wrapper'>";
    if (!empty($posts)) {
      foreach ($posts as $k => $postID) {
        $custom = get_post_custom( $postID );
        echo "<div class='dashboard-favorites-list'>";
        echo "<div class='contact-image'>";
        if ( ! empty( $custom['wpcf-contactimage'][0] ) ) {
          echo "<img src='".$custom['wpcf-contactimage'][0]."' width='69' height='69'>";
        } else {
          $avatar = get_avatar( $custom['wpcf-email'][0] ,69);
          echo $avatar;
        }
        echo "</div>";
        echo '<div class="display-name">';
        echo "<span class='contact-name'>";
        $URL = get_edit_post_link( $postID );
        echo "<a href='{$URL}'><b>";
        if( isset($custom['wpcf-display-name'][0]  )) echo $custom['wpcf-display-name'][0];
        echo "</b></a></span>";
        if( isset($custom['wpcf-email'][0]  )) echo "<br/><span class='dashboard-email'><a href='mailto:{$custom['wpcf-email'][0]}' title='email'><u>{$custom['wpcf-email'][0]}</u></a></span>";
        if( isset($custom['wpcf-phone'][0]  )) echo "<br/><span class='dashboard-email'>{$custom['wpcf-phone'][0]}</span>";

        echo '</div>';
        echo "<div class='phone'>";
        echo '<img src="' . plugins_url( '../images/phone-call.jpg', __FILE__ ) . '" />';
        echo "</div>";
        echo "<div class='clear'></div>";
        echo "</div>";
      }
    }
    echo "</div>";
  }

  /*
   * Retrive all the favorite contacts of logged in User.
   */
  public static function retrieve_favorites() {
    global $wpdb;

    $uid = $user_ID = get_current_user_id();
    $table_name = $wpdb->prefix . "ukuu_favorites";
    $retrieve_data = $wpdb->get_results( $wpdb->prepare(
               "
               SELECT *
               FROM $table_name
               where user_id = %s
               ",
               $uid
    ) , OBJECT_K );

    if ( !empty( $retrieve_data ) ) {
      $is_fav = array();
      foreach( $retrieve_data as $k => $v ) {
        $post_id = $v->user_favs;
        $postttt = get_post($post_id);
        if ( get_post_status( $post_id ) == 'trash' || (FALSE === get_post_status( $post_id )) ) {
          $wpdb->delete(
            $table_name,
            array(
              'user_id' => $uid,
              'user_favs' => $post_id,
            )
          );
        } else {
          $is_fav[$k] = $post_id;
        }
      }
      return $is_fav;
    }

    return NULL;
  }

  /**
   * Add favorite for post id.
   */
  function ukuu_add_to_fav() {
    check_ajax_referer( 'star-ajax-nonce', 'security', false );
    $post_id = $_REQUEST['post_id'];
    $entry = $_REQUEST['entry'];
    global $wpdb;
    $table_name = $wpdb->prefix . 'ukuu_favorites';
    $uid = $user_ID = get_current_user_id();
    if ($entry == "del") {
      $message = "Removed as favorite";
      $wpdb->delete(
        $table_name,
        array(
          'user_id' => $uid,
          'user_favs' => $post_id,
        )
      );
      wp_die($message);
    } elseif ( $entry == "add" ) {
      $wpdb->insert(
        $table_name,
        array(
          'user_id' => $uid,
          'user_favs' => $post_id,
        )
      );
      $message = "Added as favorite";
    }
    wp_die($message);
  }

  /* ajax call to get activity list
   *
   */
  function prepare_query( ) {
    $cid = $_REQUEST['cid'];
    $paged = isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
    $limit = isset( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : -1;
    $offset = ( $paged - 1 ) * $limit;
    $lists = $this->get_contact_activity_list( $limit , $paged , $cid );
    echo $this->activity_list( $cid , $lists );
    die();
  }

  /* $cid as contact id and $lists as activity list related to $cid
   *
   * @param type $cid as post id, $lists as touchpoint list
   */
  function activity_list( $cid , $lists ) {
    $count = 0;
    $listdata = '';
    foreach ( $lists as $key => $value ) {
      $custom = get_post_custom( $value->ID );
      $title = $value->post_title;
      $ids = $value->ID;
      if ( $count%2 == 0)  $listdata .= "<tr><th class='check-column' scope='row'><input type='checkbox'></th><td>"; else $listdata .= "<tr class='alternate'><th class='check-column' scope='row'><input type='checkbox'></th><td>";
      $listdata .= $this->activity_startdate ( $ids );
      $listdata .= "</td><td>";
      $listdata .= $this->activity_contact_type ( $ids );
      $listdata .=  '</td><td>';
      $URL = get_edit_post_link( $ids );
      $listdata .= $title;
      $listdata .= '<div class="row-actions"><span class="edit"><a title="Edit this item" href="'.$URL.'">Edit</a></span></div>';
      $listdata .=  '</td><td>';
      $listdata .= $this->activity_assigned ( $ids );
      $listdata .=  '</td><td>';
        if ( $custom['wpcf-status'][0] == 'scheduled' ) $listdata .= "<div class='ukuuclock'></div>";
        else if( $custom['wpcf-status'][0] == 'completed' ) $listdata .= "<div class='ukuucheck'></div>";

      $listdata .= "</td></tr>";
      $count++;
    }
    return $listdata;
  }

  /**
   * custom touchpoint list with pagination.
   */
  function ukuu_activity_list_meta_box() {
    // callback on custom touchpoint list for pagination //
    wp_enqueue_script( 'ukuu-function', UKUUPEOPLE_RELPATH. '/script/function.js' , array() );
    $lists = $this->get_contact_activity_list( 5 , 0 , get_the_ID() );
    $activity_listcount = sizeof( $lists );
    $total_list_value = $this->get_contact_activity_list( -1, 0 , get_the_ID() );
    $total_list_count = ceil(sizeof($total_list_value)/5);
    $button_disable = '';
    if( $total_list_count == 1 ) {
      $button_disable = 'disabled';
    }
    echo '<div id="activity"><div class="inside">';
    if ( $activity_listcount ) {
      $page_links[] = "<span style='color:#959595'>".sizeof($total_list_value)." items</span>";
      $page_links[] = "<input class='first-page' type='button' value='&laquo;' onclick='refreshActivityList( ".get_the_ID()." , $activity_listcount ,\"first-page\")' disabled>";
      $page_links[] = "<input class='pre-page' type='button' value='&lsaquo;' onclick='refreshActivityList( ".get_the_ID()." , $activity_listcount ,\"pre-page\")' disabled>";
      $page_links[] = '<span class="paging-input"><input id="current-value" class="current-page" type="text" size="1" value="1" name="paged" title="Current page" readonly> of <span id="total-value" class="total-pages">'.$total_list_count.'</span></span>';
      $page_links[] = "<input class='next-page' type='button' value='&rsaquo;' onclick='refreshActivityList( ".get_the_ID()." , $activity_listcount ,\"next-page\")' ". $button_disable.">";
      $page_links[] = "<input class='last-page' type='button' value='&raquo;' onclick='refreshActivityList( ".get_the_ID()." , $activity_listcount ,\"last-page\")' ". $button_disable.">";
      $pagination_links_class = 'pagination-links';
      $output = "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';
      echo "<div class='page-tablenav'>$output</div>";
      ?>
      <table class='widefat fixed posts'>
         <thead><tr>
         <th id="ab" class="manage-column column-ab check-column" style="" scope="col"><input type="checkbox" /></th>
         <th class="manage-column" style="" scope="col">Date and Time</th>
         <th class="manage-column" style="" scope="col">Contact Type</th>
         <th class="manage-column sortable desc" style="" scope="col">Subject</th>
         <th class="manage-column" style="" scope="col">Assigned to</th>
         <th class="manage-column" style="" scope="col">Status</th>
         </tr></thead>
         <tfoot><tr>
         <th class="manage-column column-ab check-column" style="" scope="col"><input type="checkbox" /></th>
         <th class="manage-column" style="" scope="col">Date and Time</th>
         <th class="manage-column" style="" scope="col">Contact Type</th>
         <th class="manage-column sortable desc" style="" scope="col">Subject</th>
         <th class="manage-column" style="" scope="col">Assigned to</th>
         <th class="manage-column" style="" scope="col">Status</th>
         </tr></tfoot>
         <tbody id='the-list'> <?php
         $main_table = self::activity_list(get_the_ID(), $lists);
         $main_table = apply_filters( 'display_touchpoints', $main_table, get_the_ID() , $lists );
         echo $main_table; ?>
         </tbody>
      </table>
      <div id="" class="form-item form-item-select wpcf-form-item wpcf-form-item-select">Show
       <select id="mySelect" class="wpcf-relationship-items-per-page wpcf-form-select form-select select" name="_wpcf_relationship_items_per_page" onchange="refreshActivityList(<?php echo get_the_ID(); ?>, <?php echo sizeof($total_list_value) ?> , '')">
         <option class="wpcf-form-option form-option option" value="">All</option>
         <option class="wpcf-form-option form-option option" selected="selected" value="5">5</option>
         <option class="wpcf-form-option form-option option" value="10">10</option>
         <option class="wpcf-form-option form-option option" value="15">15</option>
       </select>Touchpoints
      </div>
      <?php } echo "</div></div>";
      $contact_id = get_the_ID();
      echo "<a href='".admin_url()."post-new.php?post_type=wp-type-activity&cid=$contact_id' target='_blank' class='button button-primary'>Add Touchpoints</a>";
  }

  /*
   * returns the activity list related to contact id as $cid and $limit as posts per page and $paged as offset value
   */
  function get_contact_activity_list( $limit , $paged , $cid ) {
    $args = array(
      'post_type' => 'wp-type-activity',
      'posts_per_page' => $limit,
      'paged' => $paged,
      'meta_query' => array(
        array(
          'key'     => '_wpcf_belongs_wp-type-contacts_id',
          'value'   => $cid,
        ),
      )
    );
    $loop = new WP_Query( $args );
    $lists = array();
    return $loop->posts;
  }

  /*
   * Insert taxonomy terms for custom taxonomy on plugin activation.
   */
  function insert_taxonomy_terms() {
      // To add custom taxonomy terms
      $cTypes = array('ind' => array('name' => 'Individual', 'slug' => 'wp-type-ind-contact'),'org' => array('name' => 'Organization', 'slug' => 'wp-type-org-contact') ) ;
      self::add_taxonomies($cTypes, 'wp-type-contacts-subtype');
      $aTypes = array('meeting' => array('name' => 'Meeting', 'slug' => 'wp-type-activity-meeting'),'phone' => array('name' => 'Phone', 'slug' => 'wp-type-activity-phone') , 'note' => array('name' => 'Note', 'slug' => 'wp-type-activity-note') , 'contact-form' => array('name' => 'Contact Form', 'slug' => 'wp-type-contactform'));
      self::add_taxonomies($aTypes, 'wp-type-activity-types');
  }

  /*
   * Callback for Insert taxonomy terms.
   */
  public static function add_taxonomies ( $terms, $tax ) {
    foreach ( $terms as $termKey => $termValue ) {
      $term = get_term_by('name', $termValue['name'], $tax );
      if ( empty( $term ) ) {
        wp_insert_term(
          $termValue['name'], // the term
          $tax, // the taxonomy
          array(
            'description'=> '',
            'slug' => $termValue['slug'],
          )
        );
      }
    }
  }

  /*
   * Add custom filters for wp-type-contacts and wp-type-activity post listing
   * Add Contact type graph on wp-type-contacts post type listing
   */
  function ukuu_custom_filters_posts() {
    global $typenow;
    if( $typenow == "wp-type-contacts" ) {
      $indUrl = admin_url( 'post-new.php?post_type=wp-type-contacts&ctype=wp-type-ind-contact' );
      $orgUrl = admin_url( 'post-new.php?post_type=wp-type-contacts&ctype=wp-type-org-contact');
    ?>
    <div id="dialog" title="Add New">
       <input name="wp-contact-type-select" type="radio" value="wp-type-ind-contact" redirect="<?php echo $indUrl?>"  > <?php echo __('Human'); ?><br/>
       <input name="wp-contact-type-select" type="radio" value="wp-type-org-contact" redirect="<?php echo $orgUrl?>" > <?php echo __('Organization'); ?><br/>
    </div>
    <?php
    // Common scripts
    wp_enqueue_script('jquery-ui-dialog');
		// Common styles
    wp_enqueue_style("wp-jquery-ui-dialog");
    ?>
    <script>
    <!--Custom ukuupeople type dialog box-->
    jQuery(function($) {
       $( ".wrap h2 a" ).attr("href", "#");
       $( "#dialog" ).dialog({
                 modal : true,
               autoOpen: false,
                   show: {
                     effect: "blind",
                     duration: 1000
                   },
                   hide: {
                     effect: "explode",
                     duration: 1000
                   },
         });

       $( ".wrap h2 a" ).click(function() {
           $( "#dialog" ).dialog( "open" );
         });
     });
    jQuery("input[name='wp-contact-type-select']").on('click',function( event ) {
        window.location = jQuery(this).attr('redirect');
    });
    </script>
    <?php
      wp_enqueue_script( 'd3', UKUUPEOPLE_RELPATH.'/script/d3/d3.min.js' , array() );
      wp_enqueue_script( 'ukuucrm', UKUUPEOPLE_RELPATH.'/script/ukuucrm.js' , array() );
      $url = admin_url('edit.php');
      $string = $graph = array();
      $filters = array( 'wp-type-group', 'wp-type-tags', 'wp-type-contacts-subtype' );
      foreach ( $filters as $tax_slug ) {
        $tax_obj = get_taxonomy( $tax_slug );
        $tax_name = $tax_obj->labels->name;
        global $wpdb;
        $terms = $wpdb->get_results( $wpdb->prepare(
               "
               SELECT $wpdb->terms.term_id, $wpdb->terms.slug , $wpdb->terms.name, COALESCE(s.count ,0) AS count
               FROM $wpdb->terms
               LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
               LEFT JOIN (SELECT  COUNT($wpdb->posts.ID) as count, slug , name , $wpdb->terms.term_id FROM $wpdb->posts
               LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
               LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
               LEFT JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
               WHERE (post_status = 'publish' OR  post_status = 'private')
               AND post_type = 'wp-type-contacts'
               AND slug IS NOT NULL
               AND taxonomy= %s group by slug ) as s
               ON ($wpdb->term_taxonomy.term_id = s.term_id)
               WHERE taxonomy= %s ORDER BY $wpdb->terms.term_id ASC
               ",
               $tax_slug ,
               $tax_slug
        ) , OBJECT);
        echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
        echo "<option value=''>Show All $tax_name</option>";
        foreach ( $terms as $term ) {
          $selected = isset($_GET[$tax_slug]) ? $_GET[$tax_slug] : null;
          echo '<option value='. $term->slug, $selected == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
          if ($tax_slug == "wp-type-contacts-subtype" ) {
            $graph[] = $term->count;
            if ( isset( $_GET['wp-type-contacts-subtype'] ) && $term->slug == $_GET['wp-type-contacts-subtype'] ) {
              $class = ' class="current"';
            } else {
              $class = '';
            }
            $string[]  = "<li class='{$term->slug}'><a href=' ". add_query_arg( array('post_type' => 'wp-type-contacts', 'wp-type-contacts-subtype' => $term->slug) ,$url )."' $class'>{$term->name} </a>($term->count)</li>";
          }
        }
        echo "</select>";
      }
      $count_posts = (array) wp_count_posts($typenow, 'readable', false);
      unset($count_posts['auto-draft']);
      $counts = array_sum($count_posts);
      $selected = (count($_GET) == 1) ? 'current' : '';
      $string[]  = "<li class='all'><a href='". add_query_arg( array('post_type' => 'wp-type-contacts') ,$url )."' class='$selected'>All </a>($counts)</li>";
      $trashCount = $count_posts['trash'];
      $selected = isset($_GET['post_status']) && $_GET['post_status'] == 'trash' ? 'current' : '';
      $string[]  = "<li class='trash'><a href=' ". add_query_arg( array('post_type' => 'wp-type-contacts', 'post_status' => 'trash') ,$url)."' class='$selected'>Trash </a>($trashCount)</li>";
      if ( !empty($graph) ) {
        echo '<div class="graph-main-container" style="width:70% !important">';
        $str = implode($string, ' | ');
        echo '<div class="graph-container clear">';
        if(!wp_is_mobile()) {
          echo "<div id='graph'>
    		<div id='graph-container' style=''>";
          $color = array( 'Individual' => '#0072BB','Organization' => '#30A08B' );
          $contacts_count = $graph;
          echo "<div class='type-indicator'>";
          echo "<div class='ukuu-head'>";
          echo "<table><tr>";
          foreach ( $color as $keys => $values ) {
            echo "<td><svg height='10' width='10'>";
            echo "<circle cx='5' cy='5' r='4' stroke-width='3' fill='$values' />";
            echo "</svg></td>";
            echo "<td style='padding-right:15px;font-size:16px;color: #777777;'><span>".$keys."</span></td>";
          }
          echo "</tr></table>";
          echo "</div></div>";
          echo "<div  id='chart' type='".json_encode( $contacts_count )."' color='".json_encode( array_values($color) )."'></div></div></div>";
           echo "<ul class='subsubsub'>$str</ul>";
        }
        echo "</div></div>";
      }
    }
    if( $typenow == "wp-type-activity" ) {
      wp_enqueue_script( 'd3', UKUUPEOPLE_RELPATH.'/script/d3/d3.min.js' , array() );
      wp_enqueue_script( 'ukuucrm', UKUUPEOPLE_RELPATH.'/script/ukuucrm.js' , array() );
      $filters = array('wp-type-activity-types');
      $url = admin_url('edit.php');
      $string = $graph = array();
      $graphTouchpoint = array();
      $li_color = array( 'wp-type-activity-meeting' => '#377CB6' , 'wp-type-activity-phone' => '#771D78'  , 'wp-type-activity-note' => '#3DA999' , 'wp-type-contactform' => '#E6397A' , 'other' => '#d3d3d3');
      foreach ($filters as $tax_slug) {
        $tax_obj = get_taxonomy($tax_slug);
        $tax_name = $tax_obj->labels->name;
        global $wpdb;
        $terms = $wpdb->get_results( $wpdb->prepare(
               "
               SELECT $wpdb->terms.term_id, $wpdb->terms.slug , $wpdb->terms.name, COALESCE(s.count ,0) AS count
               FROM $wpdb->terms
               LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
               LEFT JOIN (SELECT  COUNT($wpdb->posts.ID) as count, slug , name , $wpdb->terms.term_id FROM $wpdb->posts
               LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
               LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
               LEFT JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
               WHERE (post_status = 'publish' OR  post_status = 'private')
               AND post_type = 'wp-type-activity'
               AND slug IS NOT NULL
               AND taxonomy= %s group by slug ) as s
               ON ($wpdb->term_taxonomy.term_id = s.term_id)
               WHERE taxonomy= %s ORDER BY $wpdb->terms.term_id ASC
               ",
               $tax_slug ,
               $tax_slug
        ) , OBJECT);
        echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
        echo "<option value=''>Show All $tax_name</option>";
        foreach ($terms as $term) {
          $selected = isset($_GET[$tax_slug]) ? $_GET[$tax_slug] : null;
          echo '<option value='. $term->slug, $selected == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
          if ($tax_slug == "wp-type-activity-types" ) {
            $graphTouchpoint[] = $term->count;
            $selectedColor[$term->slug] = get_option('term_category_radio_' . $term->slug);
            if ( isset( $_GET['wp-type-activity-types'] ) && $term->slug == $_GET['wp-type-activity-types'] ) {
              $class = ' class="current"';
            } else {
              $class = '';
            }
            $li_colors = array_merge($selectedColor, $li_color);
            if ( array_key_exists($term->slug, $li_colors) && !empty($li_colors[$term->slug]) ) {
              $style = $li_colors[$term->slug];
            }
            else {
              $selectedColor[$term->slug] = $style = '#d3d3d3';
            }
            $string[]  = "<li class='{$term->slug}''><svg height='10' width='10'><circle cx='5' cy='5' r='4' stroke-width='3' fill='$style' /></svg> <a href=' ". add_query_arg( array('post_type' => 'wp-type-activity', 'wp-type-activity-types' => $term->slug), $url )."' $class'>{$term->name} </a>($term->count)</li>";
          }
        }
        echo "</select>";
      }
      // Filter post by Touchpoint Contact
      wp_reset_query();  // Restore global post data stomped by the_post().
      $args = array(
        'post_type' => 'wp-type-contacts',
        //'post_status' => 'publish',
      );
      $loop = new WP_Query( $args );
      echo "<select name='touchpoint-contact' id='touchpoint-contact' class='postform'>";
      echo "<option value=''>Show All Touchpoint Contact</option>";
      foreach ( $loop->posts as $keys => $values ) {
        $selected = isset($_GET['touchpoint-contact']) ? $_GET['touchpoint-contact'] : null;
        $display = get_post_meta( $values->ID , 'wpcf-display-name', true);
        echo '<option value='. $values->ID , $selected == $values->ID ? ' selected="selected"' : '','>' . $display .'</option>';
      }
      echo "</select>";
      $count_posts = (array) wp_count_posts($typenow, 'readable', false);
      unset($count_posts['auto-draft']);
      $counts = array_sum($count_posts);
      $selected = (count($_GET) == 1) ? 'current' : '';
      $string[]  = "<li class='all'><a href=' ". add_query_arg( array('post_type' => 'wp-type-activity') ,$url )."' class='$selected'>All </a>($counts)</li>";
      $trashCount = $count_posts['trash'];
      $selected = isset($_GET['post_status']) && $_GET['post_status'] == 'trash' ? 'current' : '';
      $string[]  = "<li class='trash'><a href=' ". add_query_arg( array('post_type' => 'wp-type-activity', 'post_status' => 'trash') ,$url)."' class='$selected'>Trash </a>($trashCount)</li>";
      if (!empty($graphTouchpoint)) {
        echo '<div class="graph-main-container" style="width:70% !important">';
        $str = implode($string, ' | ');
        echo '<div class="graph-container clear">';
        if(!wp_is_mobile()) {
          echo "<div id='graph'><div id='graph-container' style=''>";
          $touchpoint_count = $graphTouchpoint;
          $colors = array_merge($selectedColor, $li_color);
          echo "<div  id='chart' type='".json_encode( $touchpoint_count )."' color='".json_encode( array_values($colors) )."'></div></div></div>";
          echo "<ul class='subsubsub'>$str</ul>";
        }
        echo "</div></div>";
      }
    }
  }

  /*
   * add submenu for custom post type
   */
  public static function add_new_contact_type() {
    echo '<h3>ADD NEW</h3>';
    $indUrl = admin_url( 'post-new.php?post_type=wp-type-contacts&ctype=wp-type-ind-contact' );
    $orgUrl = admin_url( 'post-new.php?post_type=wp-type-contacts&ctype=wp-type-org-contact');
    ?>

    <input name="wp-contact-type-select" type="radio" value="wp-type-ind-contact" redirect="<?php echo $indUrl?>"  > <?php echo __('Human'); ?><br/>
    <input name="wp-contact-type-select" type="radio" value="wp-type-org-contact" redirect="<?php echo $orgUrl?>" > <?php echo __('Organization'); ?><br/>
    <script type="text/javascript">
      jQuery("input[name='wp-contact-type-select']").on('click',function( event ) {
        window.location = jQuery(this).attr('redirect');
        return false;
      });
    </script>
    <?php
  }

  /*
   * Add custom field columns on wp-type-contacts and wp-type-activity post listing page
   *
   * @param type $column custom column name
   * @param type $post_id
   */
  function ukuu_custom_field_list_text( $column, $post_id ) {
    global $post;
    switch ( $column ) {
      case 'wp-fullname': //Contact Full Name
        $id = get_post_meta( get_the_ID() , '_wpcf_belongs_wp-type-contacts_id', true );
        $display =  get_post_meta( $id , 'wpcf-display-name', true );
        $URL = get_edit_post_link( $id );
        echo "<a href='$URL'>$display</a>";
        break;
      case 'wp-assigned': // Contacts activity is assigned to
        echo $this->activity_assigned( get_the_ID() );
        break;
      case 'wp-status': // Activity Status
        if (get_post_meta( get_the_ID(), 'wpcf-status', true) == 'scheduled' ) {
          echo "<div class='ukuuclock'></div>";
        }
        elseif (get_post_meta( get_the_ID(), 'wpcf-status', true) == 'completed' ) {
          echo "<div class='ukuucheck'></div>";
        }
        if ( get_post_meta( get_the_ID(), 'wpcf-attachments', true) ) {
          echo "<div class='paper-icon'></div>";
        }
        break;
      case 'wp-contact-type': // Contact type
        echo $this->activity_contact_type( get_the_ID() );
        break;
      case 'wp-startdate': // Activity start sate
        echo $this->activity_startdate( get_the_ID());
        break;
      case 'wp-contact-full-name': // Full name
        $ids = get_the_ID();
        $data = get_post( $ids );
        if ( $data->post_type == 'wp-type-contacts' ) {
          $display =  get_post_meta( $ids , 'wpcf-display-name', true );
          $URL = get_edit_post_link( $ids );
          $viewURL = get_permalink( $ids );
          $deleteURL = get_delete_post_link( $ids );
          $permDeleteURL = get_delete_post_link( $ids, null, true );
          echo "<a href=$URL>".$display."</a>";
          if ( get_post_status ( $ids ) == 'trash' ) {
            $_wpnonce = wp_create_nonce( 'untrash-post_' . $ids );
            $url = admin_url( 'post.php?post=' . $ids . '&action=untrash&_wpnonce=' . $_wpnonce ); ?>
            <div class="row-actions">
            <?php if ( current_user_can('edit_post', $ids) ) { ?>
            <span class="untrash"><a href="<?php echo $url; ?>" title="Restore this item from the Trash">Restore</a>
            <?php } ?>
            <?php if ( current_user_can('delete_post', $ids) ) { ?>
            |</span><span class="delete"><a class="submitdelete" href="<?php echo $permDeleteURL; ?>" title="Delete this item permanently">Delete Permanently</a></span>
            <?php } else echo "</span>"; ?>
            </div><?php
          }
          else { ?>
            <div class="row-actions">
            <?php if ( current_user_can('edit_post', $ids) ) { ?>
            <span class="edit"><a title="Edit this item" href="<?php echo $URL; ?>">View</a>|</span>
            <span class="inline hide-if-no-js"><a class="editinline" title="Edit this item inline" href="#">Quick Edit</a>
            <?php } ?>
            <?php if ( current_user_can('delete_post', $ids) ) { ?>
            |</span><span class="trash"><a class="submitdelete" href="<?php echo $deleteURL; ?>" title="Move this item to the Trash">Trash</a></span>
            <?php } else echo "</span>"; ?>
            </div><?php
          }
        }
        break;
      case 'wp-phone': // Contact's phone
        $phoneNumber = get_post_meta( get_the_ID(), 'wpcf-phone', true);
        if( $phoneNumber ) {
          if ( !preg_match('/[^0-9]/', $phoneNumber ) ) {
            $phoneNumber = substr($phoneNumber, 0, 3).'-'.substr($phoneNumber, 3, 3).'-'.substr($phoneNumber, 6);
            $phoneNumber = rtrim($phoneNumber , '-');
          }
          echo $phoneNumber;
        }
        break;
      case 'wp-email': // Email
        echo get_post_meta( get_the_ID(), 'wpcf-email', true);
        break;
      case 'wp-color-code': // Contact type

        $contact_image = get_post_meta( get_the_ID(), 'wpcf-contactimage', true );
        if ( ! empty( $contact_image ) ) {
          echo "<span class='contactimage'><img src='".$contact_image."' width='32' height='32'></span>";
        } else {
          $avatar = get_avatar( get_post_meta( get_the_ID() , 'wpcf-email', true ) ,32);
          echo "<span class='contactimage'>".$avatar."</span>";
        }

        $color = array( 'Individual' => '#0072BB','Organization' => '#30A08B' );
        $type = get_the_terms( get_the_ID(), 'wp-type-contacts-subtype');
        if ($type) {
          foreach ( $type as $key => $value ) {
            $contacttype = $value->name;
          }
          echo "<svg height='10' width='10'>";
          echo "<circle cx='5' cy='5' r='4' stroke-width='3' fill='$color[$contacttype]' />";
          echo "</svg>";
        }
        break;
      case 'wp-favorites':
        $action = 'add';
        $posts = self::retrieve_favorites();
        if ( $posts && in_array(get_the_ID(), $posts) ) {
          echo "<div class='add_to_fav_star' style='float:left;'><div id='fav-star' class='remove-star'></div></div>";
        }
        break;
      case 'wp-type-activity': // Activity chart
        $monthsArray[date('Y')][date('n')] =  date( 'n' );
        for ( $i = 4; $i >= 0; $i-- ) {
          $year =  date( 'Y', strtotime( "-$i month", strtotime( date( 'Y-m-1 H:i:s' ) ) ) );
          $month =  date( 'n', strtotime( "-$i month", strtotime( date( 'Y-m-1 H:i:s' ) ) ) );
          $monthsArray[$year][$month] = $month;
          ksort($monthsArray[$year]);
        }
        ksort( $monthsArray, true );
        $months = array();
        foreach ( $monthsArray as $mk => $mv ) {
          $months = array_merge($months, $mv);
        }
        $timeline = array();
        $args = array(
          'post_type' => 'wp-type-activity',
          'posts_per_page' => -1,
          'meta_query' => array(
            array(
              'key'     => '_wpcf_belongs_wp-type-contacts_id',
              'value'   => get_the_ID(),
            ),
          )
        );
        $loop = new WP_Query( $args );
        foreach ( $loop->posts as $child_post ) {
          $date = get_post_meta( $child_post->ID, 'wpcf-startdate', true);
          if ( !empty($date) ) {
            $activityMonth =  date( 'n', date($date) );
            if ( in_array($activityMonth,$months) ) {
              $monthKey = array_search($activityMonth, $months);
              $monthKey = $months[$monthKey];
              $monthsDate = date( "Y-m-d", mktime( 0, 0, 0, $monthKey  ,01 , date( "Y" ) ) );
              $activity_date_time =  date( "Y-m-d", $date );
              $monthsLastDate = date( "Y-m-d", mktime( 0, 0, 0, $monthKey, 31, date( "Y" ) ) );
              if ( ( strtotime ( $monthsDate ) <= strtotime( $activity_date_time ) ) && ( strtotime( $activity_date_time )  <= strtotime ( $monthsLastDate ) ) ) {
                if ( array_key_exists($monthKey, $timeline) ) {
                  $timeline[$monthKey] = $timeline[$monthKey] + 1;
                } else {
                  $timeline[$monthKey] = 1;
                }
              }
            }
          }
        }
        $co_ordinates = array();
        foreach ( $months as $key => $value ) {
          if ( ! isset( $timeline[$value] ) ) {
            $co_ordinates[] = array($key, 0);
          } else{
            $co_ordinates[] = array($key, $timeline[$value]);
          }
        }
        $combine = array( 'val' => $co_ordinates , 'colorlist' => '#344F8C' ,'contactid' => get_the_ID() );
        echo "<div class='tdata' ><svg id='visualisation_".get_the_ID()."' activityc='".json_encode( $combine )."' width='175' height='50'></svg></div>";
        break;
    }
  }

  /*
   * Returns custom field columns for wp-type-contacts post
   *
   * @param type $defaults
   * @return type
   */
  function ukku_contacts_custom_fields_list_view($defaults) {
    unset( $defaults['date'] );
    unset( $defaults['categories'] );
    $defaults['cb'] = __('input id="cb-select-all-1" type="checkbox"');
    $defaults['wp-color-code'] = __('');
    $defaults['wp-contact-full-name'] = __('Name');
    $defaults['wp-email'] = __('Email');
    $defaults['wp-phone'] = __('Phone');
    $defaults['wp-type-activity'] = __('TouchPoint');
    $defaults['wp-favorites'] = __('');
    $Order = array( 'cb' ,'wp-color-code' , 'wp-contact-full-name' , 'wp-email' , 'wp-phone' , 'wp-type-activity','wp-favorites');
    foreach ($Order as $colname){
      $new[$colname] = $defaults[$colname];
    }
    $defaults = $new;
    return $defaults;
  }

  function ukku_contacts_custom_fields_hide_date_filter() {
    $screen = get_current_screen();
    if ( 'wp-type-contacts' == $screen->post_type || 'wp-type-activity' == $screen->post_type ){
      add_filter('months_dropdown_results', '__return_empty_array');
    }
  }

  /*
   * Returns custom field columns for wp-type-activity post
   *
   * @param type $defaults
   * @return type
   */
  function ukku_activity_custom_fields_list_view($defaults) {
    unset( $defaults['title'] );
    unset( $defaults['date'] );
    $defaults['cb'] = __('input id="cb-select-all-1" type="checkbox"');
    $defaults['wp-startdate'] = __('Date and Time');
    $defaults['wp-contact-type'] = __('Contact Type');
    $defaults['wp-fullname'] = __('TouchPoint Contact');
    $defaults['title'] = __('Subject');
    $defaults['wp-assigned'] = __('Assigned to');
    $defaults['wp-status'] = __('Status');
    $Order = array('cb','wp-startdate', 'title', 'wp-contact-type', 'wp-fullname', 'wp-assigned','wp-status');
    foreach ($Order as $colname){
      $new[$colname] = $defaults[$colname];
    }
    $defaults = $new;
    return $defaults;
  }

  /*
   * returns the contacts assigned to $id
   *
   * @param type $id activity id
   */
  function activity_assigned ( $id ) {
    $repeatable_field_values = get_post_meta( $id , 'wpcf_assigned_to', true);
    $data = array();
    if ( $repeatable_field_values ) {
      foreach ( $repeatable_field_values as $key => $val ) {
        $data[] = get_post_meta( $val , 'wpcf-display-name', true );
      }
    }
    $data = implode (',', $data );
    return $data;
  }

  /*
   * returns the activity start date
   *
   * @param type $id activity id
   */
  function activity_startdate ( $id ) {
    $data ='';
    $time = $month = $datenum = '';
    $atype ='';
    $termType = $acttype = get_the_terms( $id , 'wp-type-activity-types');
    $termSlug = '';
    if (!empty($acttype)){
      $term = array_shift( $termType);
      $termSlug = $term->slug;
    }
    $selectedColor = get_option('term_category_radio_' . $termSlug);
    $color = array( 'Meeting' => '#377CB6' , 'Phone' => '#771D78'  , 'Note' => '#3DA999' , 'Contact Form' => '#E6397A' );
    if ( !empty ( $acttype ) ) {
      $atype = array_map( function($a) { return $a->name; }, $acttype );
      $atype = reset($atype);
    }
    $date = get_post_meta( $id, 'wpcf-startdate', true);
    if ( !empty ( $date ) ) {
      $time = date('h:i a', $date);
      $month = date('F', $date);
      $datenum = date('d', $date);
    }
    if (array_key_exists($atype, $color) ) {
      $data .= "<div style= 'background-color:$color[$atype]' class='contribution_amount activityColor'>$month <br><span class='date_number'>$datenum</span><br>$time</div>";
    }
    else if( $selectedColor && !empty($selectedColor) ) {
      $data .= "<div style= 'background-color:$selectedColor' class='contribution_amount activityColor'>$month <br><span class='date_number'>$datenum</span><br>$time</div>";
    } else {
      $data .= "<div style= 'background-color:#d3d3d3' class='contribution_amount activityColor'>$month <br><span class='date_number'>$datenum</span><br>$time</div>";
    }
    return $data;
  }

  /*
   * returns the ukuupeople type
   *
   * @param type $id activity id
   */
  function activity_contact_type ( $id ) {
    $type = array();
    $contacttype = '';
    $data = '';
    $id = get_post_meta( $id , '_wpcf_belongs_wp-type-contacts_id', true);
    $color = array( 'Individual' => '#0072BB','Organization' => '#30A08B' );
    $type = get_the_terms( $id , 'wp-type-contacts-subtype');
    if ( !empty($type) ) {
      foreach ( $type as $k => $v ) {
        $contacttype = $v->name;
      }
      $data .= "<svg height='10' width='10'>";
      $data .= "<circle cx='5' cy='5' r='4' stroke-width='3' fill='$color[$contacttype]' />";
      $data .= "</svg>";
    }
    $data .= $contacttype;
    return $data;
  }

  /*
   * Update related user on wp-type-contact updation
   *
   * @param type $user_id
   * @param type $old_user_data
   */
  function ukuu_custom_profile_update( $user_id, $old_user_data ) {
    $userdata = get_user_meta( $user_id );
    $user = get_userdata( $user_id );
    $post = get_page_by_title( $user->user_login, OBJECT, 'wp-type-contacts' );
    if ( !empty( $post ) ) {
      $postID = $post->ID;
      $meta_values = get_post_meta( $postID );
      foreach ( $meta_values as $keys => $id ){
        $k = str_replace('wpcf-', '', $keys);
        $key = str_replace('-', '_', $k);
        if ( isset($userdata[$key]) ) {
          update_post_meta( $postID, $keys, $userdata[$key][0] );
        }
        update_post_meta( $postID, 'wpcf-email', $user->user_email );
      }
    }
  }

  /*
   * Set contact/activity type on respective new post type saving
   *
   * @param type $post_ID
   */
  function ukuu_set_wp_custom_type( $post_ID , $post) {
    global $typenow;
    global $wp_meta_boxes;
    $url = admin_url();
    $types = array(
      'wp-type-ind-contact' => 'wp-type-ind-contact',
      'wp-type-org-contact' => 'wp-type-org-contact',
      'wp-type-activity-phone' => 'wp-type-activity-phone',
      'wp-type-activity-meeting' => 'wp-type-activity-meeting',
    );
    if ( !isset( $_GET['ctype'] ) && $typenow == "wp-type-contacts" && isset( $post ) && $post->filter != 'raw' && !isset( $_GET['action'] )) {
      wp_redirect($url.'admin.php?page=add-new-contact');
      exit();
    }

    if ( (isset($_GET['ctype']) || isset($_GET['atype']) ) && ($typenow == "wp-type-contacts" || $typenow == 'wp-type-activity') ) {
      $subtype = isset( $_GET['ctype'] ) ? $_GET['ctype'] : $_GET['atype'];
      if ( array_key_exists ( $subtype, $types ) ) {
        $mainType = ( $typenow == 'wp-type-contacts' ) ? 'wp-type-contacts-subtype' : 'wp-type-activity-types';
        wp_set_object_terms( $post_ID, $types[$subtype], $mainType, true );
      }
    }

    if ( !isset ( $_GET['ctype'] ) && $typenow == "wp-type-contacts" && !isset( $_REQUEST['action'] )) {
      wp_set_object_terms( $post_ID, 'wp-type-ind-contact', 'wp-type-contacts-subtype', true );
    }
    if ($typenow == "wp-type-contacts") {
      $type = get_the_terms( $post_ID, 'wp-type-contacts-subtype');
      $meta_data = get_post_meta($post_ID);
      if ( !empty( $type ) && $type[0]->slug == 'wp-type-ind-contact' ) {
        $first = isset( $meta_data['wpcf-first-name'][0] ) ? $meta_data['wpcf-first-name'][0] : '';
        $last = isset( $meta_data['wpcf-last-name'][0] ) ? $meta_data['wpcf-last-name'][0] : '';
        $display = $first." ".$last;
        if ( isset( $meta_data['wpcf-first-name'][0] ) && isset( $meta_data['wpcf-first-name'][0] ) ) {
          update_post_meta( $post_ID, 'wpcf-display-name', $display );
        }
      }
      $postVal = get_post($post_ID, ARRAY_A);
      $validEmail = get_post_meta( $post_ID, 'wpcf-email');
      $meta_data = get_post_meta($post_ID);
      if ( isset( $postVal['post_status'] ) && $postVal['post_status'] != 'auto-draft' && isset( $validEmail ) && !empty($meta_data)) {
        $title = $postVal['post_title'];
        $userdata = array(
          'first_name' => isset( $meta_data['wpcf-first-name'][0] ) ? $meta_data['wpcf-first-name'][0] : '',
          'last_name' => isset( $meta_data['wpcf-last-name'][0] ) ? $meta_data['wpcf-last-name'][0] : '',
          'display_name' => isset( $display) ? $display : $meta_data['wpcf-display-name'][0],
          'user_email' => $meta_data['wpcf-email'][0],
        );
        $userdata['user_login'] = $meta_data['wpcf-email'][0];
        $user = get_user_by( 'email', $userdata['user_login'] );

        if ( !$user ) {
          $user = get_user_by( 'login', $title );
        }
        if ( !$user ) {
          $title = $postVal['post_title'] = $meta_data['wpcf-email'][0];
          $userdata['user_pass'] = Null;
          $user_id = wp_insert_user( $userdata ) ;
          wp_new_user_notification( $user_id, '' );
        }
        else {
          $user = $user->data;
          $userdata['ID'] = $user->ID;
          $userdata['user_login'] = $user->user_login;
          $title = $postVal['post_title'] = $user->user_login;
          $user_id = wp_update_user( $userdata );
        }
        if ( ! wp_is_post_revision( $post_ID ) ) {
          remove_action( 'save_post', array( $this, 'ukuu_set_wp_custom_type' ), 99, 2 );
          $args = array();
          $args['ID'] = $post_ID;
          $args['post_title' ] = $title;
          wp_update_post( $args );
          if ( isset($display) ) {
            update_post_meta( $post_ID, 'wpcf-display-name', $display );
          }
          add_action( 'save_post', array( $this, 'ukuu_set_wp_custom_type' ), 99, 2 );
        }
      }
    }
    if ($typenow == "wp-type-activity") {
      $type = get_the_terms( $post_ID, 'wp-type-activity-types');
      $meta_data = get_post_meta($post_ID);
      if ( !empty( $type ) && $type[0]->slug == 'wp-type-activity-note' ) {
        update_post_meta( $post_ID, 'wpcf-startdate', strtotime(get_the_date('Y-m-d H:i:s')) );
        update_post_meta( $post_ID, 'wpcf-enddate', strtotime(get_the_date('Y-m-d H:i:s')));
        update_post_meta( $post_ID, 'wpcf-status', '' );
      }
      if ( isset( $_POST['wpcf-pr-belongs'] ) && empty( $_POST['hidden_cid'] ) )
      update_post_meta( $post_ID , "_wpcf_belongs_wp-type-contacts_id", $_POST['wpcf-pr-belongs'] );
    }
  }

  /*
   * Remove submenu pages for contact/Activity add and hide/remove title, types meta box
   */
  function ukuu_custom_remove_links_meta() {
    global $typenow;
    global $submenu;
    global $wp_post_types;

    if ( isset($_GET['ctype']) ) {
      //change title of human and Organization
      $postLabels = $wp_post_types['wp-type-contacts']->labels;
      if ( $_GET['ctype'] == 'wp-type-ind-contact' )
        $postLabels->add_new_item = 'Add New Human';
      if ( $_GET['ctype'] == 'wp-type-org-contact' )
        $postLabels->add_new_item = 'Add New Organization';
    }

    //Hide Contact post title
    remove_post_type_support('wp-type-contacts', 'title');
    //Hide Contact/Actiivty Types meta box
    remove_meta_box('wp-type-contacts-subtypediv', 'wp-type-contacts' , 'side' );
    remove_meta_box('wp-type-activity-typesdiv', 'wp-type-activity' , 'side' );
    remove_menu_page( 'edit.php?post_type=wp-type-activity' );
    remove_submenu_page( 'edit.php?post_type=wp-type-contacts', 'post-new.php?post_type=wp-type-contacts' );
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=category&amp;post_type=wp-type-contacts');
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=wp-type-group&amp;post_type=wp-type-contacts');
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=wp-type-tags&amp;post_type=wp-type-contacts');
    remove_submenu_page('edit.php?post_type=wp-type-contacts','edit-tags.php?taxonomy=wp-type-contacts-subtype&amp;post_type=wp-type-contacts');
  }

  /*
   * Collapse the fields on page load
   *
   * @param type $closed
   * @return type
   */
  function closed_meta_boxes( $closed ) {
    global $post;
    if ( isset($post->ID) && $post->filter == 'edit' ) {
      $closed = array( 'wpcf-group-edit-contact-info', 'wpcf-group-edit_contact_address', 'wpcf-group-edit_contact_privacy_settings' , 'wpcf-related-org-metabox' );
    } elseif ( isset($post->ID) && $post->filter == 'raw' ) {
      $closed = array();
    }
    return $closed;
  }

  /*
   * Add Contact summary view on Edit link
   *
   * @param type $edit post id
   */
  function ukuu_custom_summary_view($edit) {
    if ( isset($edit->ID) && $edit->post_type == 'wp-type-contacts' ) {
      wp_enqueue_script( 'ukuucrm', UKUUPEOPLE_RELPATH.'/script/ukuucrm.js' , array() );
      $type = get_the_terms( $edit->ID , 'wp-type-contacts-subtype');
      // Custom fields update for ukuupeople organization //
      if ( isset ( $type[0]->slug ) && $type[0]->slug =='wp-type-org-contact' ) { ?><script>
        jQuery( document ).ready(function() {
          jQuery('.post-type-wp-type-contacts #wpcf-related-org-metabox').hide();
          jQuery('.post-type-wp-type-contacts #cmb2-metabox-wpcf-group-edit-contact-info .cmb2-id-wpcf-first-name').hide();
          jQuery('.post-type-wp-type-contacts #cmb2-metabox-wpcf-group-edit-contact-info .cmb2-id-wpcf-last-name').hide();
          jQuery('.post-type-wp-type-contacts #cmb2-metabox-wpcf-group-edit-contact-info .cmb2-id-wpcf-ukuu-job-title').remove();
          jQuery('.post-type-wp-type-contacts #cmb2-metabox-wpcf-group-edit-contact-info .cmb2-id-wpcf-ukuu-date-of-birth').remove();
          jQuery('.post-type-wp-type-contacts #cmb2-metabox-wpcf-group-edit-contact-info .cmb2-id-wpcf-first-name').find("input").prop("disabled",true);
          jQuery('.post-type-wp-type-contacts #cmb2-metabox-wpcf-group-edit-contact-info .cmb2-id-wpcf-last-name').find("input").prop("disabled",true);
          // Validation for Display name field //
          jQuery('.post-type-wp-type-contacts .cmb2-id-wpcf-display-name label').append('*');
          jQuery('.post-type-wp-type-contacts .cmb2-id-wpcf-display-name input').prop('required',true);
        }); </script><?php
      }
      // Custom fields update for ukuupeople Individual //
      if ( isset ( $type[0]->slug ) && $type[0]->slug =='wp-type-ind-contact' ) { ?><script>
        jQuery( document ).ready(function() {
          jQuery('.post-type-wp-type-contacts #wpcf-related-org-metabox').show();
          jQuery('.post-type-wp-type-contacts #cmb2-metabox-wpcf-group-edit-contact-info .cmb2-id-wpcf-display-name').hide();
        }); </script><?php
      }

      if ( $edit->filter == 'edit' ) {
        $custom = get_post_custom( $edit->ID );
        $contactdetails_keys = array( 'wpcf-phone' => '' , 'wpcf-email' =>'', 'wpcf-mobile'  =>'' , 'wpcf-website'  =>'');
        if(isset($custom['wpcf-phone'][0]) && !preg_match('/[^0-9]/', $custom['wpcf-phone'][0]) ) {
          $phoneNumber = $custom['wpcf-phone'][0];
          $phoneNumber = substr($phoneNumber, 0, 3).'-'.substr($phoneNumber, 3, 3).'-'.substr($phoneNumber, 6);
          $phoneNumber = rtrim($phoneNumber , '-');
          $custom['wpcf-phone'][0] =  $phoneNumber;
        }

        $contactdetailsblock = array_intersect_key ( $custom, $contactdetails_keys ) ;
        $ctags = wp_get_post_terms($edit->ID, 'wp-type-tags', array("fields" => "names"));
        echo '<div id="first-sidebar-contact"> <div id="user-images">';
        if ( ! empty( $custom['wpcf-contactimage'][0] ) ) {
          echo "<img src='".$custom['wpcf-contactimage'][0]."' width='150' height='150'>";
        } else {
          $avatar = get_avatar( $custom['wpcf-email'][0] ,150);
          echo $avatar;
        }
        echo '</div>';

        $action = 'add';
        $posts = self::retrieve_favorites();
        echo '<input type="hidden" name="star-ajax-nonce" id="star-ajax-nonce" value="' . wp_create_nonce( 'star-ajax-nonce' ) . '" />';
        if ( $posts && in_array($edit->ID, $posts) ) {
          $action = "del";
          echo "<div class='remove_fav_star' style='float:left;'><div id='fav-star' class='remove-star' onclick=\"addToFav($edit->ID, '{$action}');\"></div></div>";
        } else {
          echo "<div class='add_to_fav_star' style='float:left;'><div id='fav-star' class='add-star' onclick=\"addToFav($edit->ID, '{$action}');\" /></div></div>";
        }

        echo '<div id="display-name">';
        if( isset($custom['wpcf-display-name'][0]  )) echo $custom['wpcf-display-name'][0];
        echo '</div><div id="contactdetailsblock"><table id="contactdetail-table">';
        foreach( $contactdetailsblock as $key => $value ) {
          echo "<tr><td><span class='contactdetailhead'>".ucfirst(substr($key, 5))."</span></td><td class='title-value'>".$value[0]."</td></tr>";
        }
        if ( isset( $custom['wpcf-ukuu-job-title'] ) ) {
          echo "<tr><td><span class='contactdetailhead'>Job Title</span></td><td class='title-value'>". $custom['wpcf-ukuu-job-title'][0]. "</td></tr>";
        }

        if ( isset( $custom['wpcf-ukuu-twitter-handle'] ) ) {
          echo "<tr><td><span class='contactdetailhead'>Twitter Handle</span></td><td class='title-value'>". $custom['wpcf-ukuu-twitter-handle'][0] ."</td></tr>";
        }
        if ( isset( $custom['wpcf-ukuu-facebook-url'] ) ) {
          echo "<tr><td><span class='contactdetailhead'>Facebook URL</span></td><td class='title-value'>". $custom['wpcf-ukuu-facebook-url'][0] ."</td></tr>";
        }
        if ( isset( $custom['wpcf-ukuu-date-of-birth'] ) && !empty( $custom['wpcf-ukuu-date-of-birth'][0] ) ) {
          echo "<tr><td><span class='contactdetailhead'>Date Of Birth</span></td><td class='title-value'>". $custom['wpcf-ukuu-date-of-birth'][0] ."</td></tr>";
        }

        echo '</table></div></div><div id="second-sidebar-contact"><div id="addressblock">';
        $html = "<table id='contactdetail-table'><tr><td><span class='contactdetailhead'>Address</span></td></tr>";
        $streetaddress = $city = $country = $postalcode = $state ='';
        if( isset ( $custom['wpcf-streetaddress'] ) ) {
          $html .= "<tr><td>".$custom['wpcf-streetaddress'][0]."</td></tr>";
        }
        if( isset ( $custom['wpcf-streetaddress2'] ) ) {
          $html .= "<tr><td>".$custom['wpcf-streetaddress2'][0]."</td></tr>";
        }

        $html .= "<tr><td></td></tr>";
        $city = isset($custom['wpcf-city']) ? $custom['wpcf-city'][0] : '';
        $state = isset($custom['wpcf-state']) ? $custom['wpcf-state'][0] : '';
        $postalcode = isset($custom['wpcf-postalcode']) ? $custom['wpcf-postalcode'][0] : '';

        $html .= "<tr><td>{$city}".(!empty($city) ? ', ' : '')."{$state} {$postalcode}</td></tr></table>";
        echo $html.'</div><div id="tagblock">';
        $html = "<table id='contactdetail-table'><tr><td><span class='contactdetailhead'>Tags</span></td></tr><tr>";
        $tags = '';
        if ( ! empty( $ctags ) ) {
          $tags = implode( ', ' , $ctags );
        }
        $html .= "<td><p class='title-value'>$tags</p></td></tr></table>";
        echo $html.'</div></div>';
        $this->ukuu_activity_list_meta_box();
      }
    }
  }

  /*
   * Edit 'Title' label on quick edit for activity
   *
   * @param type $input
   * @return type
   */
  function custom_enter_title( $input ) {
    global $post_type, $typenow;
    if( is_admin() && (('Title' == $input && 'wp-type-activity' == $post_type) ||
        ('Post Title' == $input && 'wp-type-contacts' == $typenow)) ) {
      return 'Subject';
    }
    return $input;
  }

  /*
   * Add wordpress user on wp-type-contacts post save
   *
   * @param type $data
   * @return type
   */
  function ukuu_custom_add_user( $data , $postarr ) {
    if ( $postarr['post_type'] == 'wp-type-activity'   ) {
      if ( array_key_exists('touchpoint-list', $_POST ) ) {
        $post_term = wp_get_post_terms($postarr['ID'], 'wp-type-activity-types', array("fields" => "all"));
        if ( !empty( $post_term ) ) {
          wp_delete_object_term_relationships( $postarr['ID'],  'wp-type-activity-types'  );
        }
        wp_set_object_terms( $postarr['ID'], $_POST['touchpoint-list'] , 'wp-type-activity-types' , true );
        if ( isset($_POST['hidden_cid'] ) && !empty( $_POST['hidden_cid'] ) ) {
          update_post_meta( $postarr['ID'], "_wpcf_belongs_wp-type-activity_id", $_POST['hidden_cid'] );
          update_post_meta( $postarr['ID'], "_wpcf_belongs_wp-type-contacts_id", $_POST['hidden_cid'] );
        }
      }
    }
    return $data;
  }

  /*
   * Delete respective contacts post on user deletion
   *
   * @param type $user_id
   */
  function ukuu_delete_user( $user_id ) {
    $user = get_userdata( $user_id );
    $post = get_page_by_title($user->user_login, OBJECT, 'wp-type-contacts');
    $postID = $post->ID;
    wp_delete_post($postID);
  }

  /*
   * Add submenu page for wp-type-contact post type
   */
  function settings() {
    //$Ukuu_Custom_List = new Ukuu_People_List();
    $activity_type = get_terms( 'wp-type-activity-types','hide_empty=0' );
    $tribes = get_terms( 'wp-type-group','hide_empty=0' );
    $tags = get_terms( 'wp-type-tags','hide_empty=0' );
    echo '<h2>Settings</h2>';
    echo '<div class="postbox main-type-list"><h3 class="hndle"><span>Touchpoint Types</span></h3>';
    $this->types_list( $activity_type , 'touchpoint' );
    echo '</div>';
    echo "<a href='".admin_url()."edit-tags.php?taxonomy=wp-type-activity-types&post_type=wp-type-contacts'><input type='button' class='button button-primary' value='Add New Touchpoint Type'></a>";
    echo '<div class="postbox main-type-list"><h3 class="hndle"><span>Tribes</span></h3>';
    $this->types_list( $tribes , '' );
    echo '</div>';
    echo "<a href='".admin_url()."edit-tags.php?taxonomy=wp-type-group&post_type=wp-type-contacts'><input type='button' class='button button-primary' value='Add New Tribe'></a>";
    echo '<div class="postbox main-type-list"><h3 class="hndle"><span>People Tags</span></h3>';
    $this->types_list( $tags , '' );
    echo '</div>';
    echo "<a href='".admin_url()."edit-tags.php?taxonomy=wp-type-tags&post_type=wp-type-contacts'><input type='button' class='button button-primary' value='Add New Tag'></a>";
  }

  /*
   * callback function
   *
   * @param type $types
   * @param type $type_name
   */
  function types_list( $types , $type_name ) {
    echo "<table class='type-list'><tbody>";
    $count = 0;
    $color = array(
      'wp-type-activity-meeting' => '#377CB6',
      'wp-type-activity-phone' => '#771D78',
      'wp-type-activity-note' => '#3DA999' ,
      'wp-type-contactform' => '#E6397A'
    );

    foreach ( $types as $key => $value ) {
      if ( $count%2 == 0) echo "<tr>"; else echo "<tr class='alternate'>";
      $URL = get_edit_term_link( $value->term_id ,$value->taxonomy );
      $selectedColor = get_option('term_category_radio_' . $value->slug);
      $deleteURL = get_delete_post_link( $value->term_id);
      $wpn = wp_create_nonce( 'delete-tag_' .$value->term_id );
      $dURL = 'edit-tags.php?action=delete&taxonomy=wp-type-activity-types&tag_ID='.$value->term_id.'&_wpnonce='.$wpn;
      echo "<td><a href='$URL'>$value->name</a>";?>
      <div class="row-actions">
        <span class="edit"><a title="Edit this item" href="<?php echo $URL ?>">Edit</a>|</span>
        <span class="trash"><a class="submitdelete" href="<?php echo $dURL ?>" title="Move this item to the Trash">Trash</a></span>
      </div>
      <?php if ( $type_name == 'touchpoint' ) {
        if (array_key_exists($value->slug, $color)) {
          echo "</td><td class='color' style='background-color:".$color[$value->slug]."'></td></tr>";
        }
        else {
          echo "</td><td class='color' style='background-color:".$selectedColor."'></td></tr>";
        }
      }
      else echo "</td></tr>";
      $count++;
    }
    echo "</tbody></table>";
  }

}
