<?php
/**
 * Plugin Name: Lynt leads
 * Plugin URI: https://github.com/lynt-smitka/lynt-leads
 * Description: Simple contact form processing & tracking
 * Version: 1.0
 * Author: Vladimir Smitka
 * Author URI: https://lynt.cz
 * License: GPL2
 */

// If this file is called directly, abort.
if (! defined("WPINC")) {
  die;
   } 

if (!class_exists("Lynt_Leads")) {
  class Lynt_Leads
   {
    // statuses map
    private $statuses = array(
      "lynt_new" => array("name" => "Nový", "next" => array("lynt_good", "lynt_bad")),
       "lynt_good" => array("name" => "Dobrý", "next" => array("lynt_win", "lynt_wait", "lynt_lost")),
       "lynt_bad" => array("name" => "Špatný", "next" => array("lynt_new")),
       "lynt_wait" => array("name" => "Čekající", "next" => array("lynt_win", "lynt_bad")),
       "lynt_win" => array("name" => "Úspěšný", "next" => array()),
       "lynt_lost" => array("name" => "Neúspěšný", "next" => array()),
      );
    
     private $ga_account;
    
     function __construct()
    
    {
      
       $this -> ga_account = get_option('lynt_leads_ga_account');
       // Leads custom post type
      add_action('init', array($this, 'register_post_type'));
      
       // leads statuses
      add_action('init', array($this, 'register_post_statuses'));
      
       // Leads metaboxes
      add_action('add_meta_boxes', array($this, 'register_post_metaboxes'));
      
       // Leads setting page
      add_action('admin_init', array($this, 'register_settings'));
       add_action('admin_menu', array($this, 'register_settings_page'));
      
       // Leads status column
      add_filter('manage_lynt-leads_posts_columns', array($this, 'add_leads_columns'));
      
       // Show lead status in column
      add_action('manage_lynt-leads_posts_custom_column' , array($this, 'render_leads_columns'), 10, 2);
      
       // remove Publish metabox
      add_action('admin_menu', function ()
        
        {
           remove_meta_box('submitdiv', 'lynt-leads', 'side'); } 
        );
      
       // save lead + send info to GA
      add_action('save_post_lynt-leads', array($this, 'save_lead'), 10 , 2);
      
       // process data from CF7
      add_action('wpcf7_mail_sent', array($this, 'wpcf7_mail_sent_function'));
      
       // test if GA Account defined
      if (!$this -> ga_account) add_action('admin_notices', array($this, 'admin_notice'));
      
       } 
    
    private function get_real_client_ip()
    
    {
       $ipaddress = '';
       if (isset($_SERVER['HTTP_CLIENT_IP']))
         $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
       else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
         $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
       else if (isset($_SERVER['HTTP_X_FORWARDED']))
         $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
       else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
         $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
       else if (isset($_SERVER['HTTP_FORWARDED']))
         $ipaddress = $_SERVER['HTTP_FORWARDED'];
       else if (isset($_SERVER['REMOTE_ADDR']))
         $ipaddress = $_SERVER['REMOTE_ADDR'];
       else
         $ipaddress = 'Unknown';
       return $ipaddress;
       } 
    
    public function register_post_type()
    
    {
      
       $labels = array(
        'name' => _x('Leady', 'Post Type General Name', 'lynt-leads'),
         'singular_name' => _x('Lead', 'Post Type Singular Name', 'lynt-leads'),
         'menu_name' => __('Leady', 'lynt-leads'),
         'name_admin_bar' => __('Lead', 'lynt-leads'),
         'archives' => __('Item Archives', 'lynt-leads'),
         'attributes' => __('Item Attributes', 'lynt-leads'),
         'parent_item_colon' => __('Parent Item:', 'lynt-leads'),
         'all_items' => __('Všechny leady', 'lynt-leads'),
         'add_new_item' => __('Přidat nový lead', 'lynt-leads'),
         'add_new' => __('Nový lead', 'lynt-leads'),
         'new_item' => __('Nová lead', 'lynt-leads'),
         'edit_item' => __('Upravit lead', 'lynt-leads'),
         'update_item' => __('Aktualizovat lead', 'lynt-leads'),
         'view_item' => __('View Item', 'lynt-leads'),
         'view_items' => __('View Items', 'lynt-leads'),
         'search_items' => __('Search Item', 'lynt-leads'),
         'not_found' => __('Not found', 'lynt-leads'),
         'not_found_in_trash' => __('Not found in Trash', 'lynt-leads'),
         'featured_image' => __('Featured Image', 'lynt-leads'),
         'set_featured_image' => __('Set featured image', 'lynt-leads'),
         'remove_featured_image' => __('Remove featured image', 'lynt-leads'),
         'use_featured_image' => __('Use as featured image', 'lynt-leads'),
         'insert_into_item' => __('Insert into item', 'lynt-leads'),
         'uploaded_to_this_item' => __('Uploaded to this item', 'lynt-leads'),
         'items_list' => __('Items list', 'lynt-leads'),
         'items_list_navigation' => __('Items list navigation', 'lynt-leads'),
         'filter_items_list' => __('Filter items list', 'lynt-leads'),
        );
       $args = array(
        'label' => __('Leady', 'lynt-leads'),
         'description' => __('Leady', 'lynt-leads'),
         'labels' => $labels,
         'supports' => array('title', 'custom-fields'),
         'taxonomies' => array(),
         'hierarchical' => false,
         'public' => false,
         'show_ui' => true,
         'show_in_menu' => true,
         'menu_position' => 5,
         'menu_icon' => 'dashicons-money',
         'show_in_admin_bar' => true,
         'show_in_nav_menus' => true,
         'can_export' => true,
         'has_archive' => false,
         'exclude_from_search' => true,
         'publicly_queryable' => false,
         'rewrite' => false,
         'capability_type' => 'post',
         'show_in_rest' => false,
         'capabilities' => array(
          'create_posts' => 'do_not_allow',
          ),
         'map_meta_cap' => true,
        );
      
       register_post_type('lynt-leads', $args);
      
       } 
    
    public function register_post_statuses()
    
    {
      
       foreach ($this -> statuses as $key => $value) {
        
        $args = array(
          'label' => _x($value['name'], 'Status Name', 'lynt-leads'),
           'label_count' => _n_noop($value['name'] . ' (%s)', $value['name'] . ' (%s)', 'lynt-leads'),
           'exclude_from_search' => true,
           'post_type' => array('lynt-leads'),
           'show_in_admin_status_list' => true,
           'public' => true,
          );
         register_post_status($key, $args);
        
         } 
      
      } 
    
    public function register_post_metaboxes()
    
    {
      
       add_meta_box('lynt_status', // ID
        esc_html__('Stav leadu', 'lynt-leads'), // Title
        array($this, 'status_metabox'), // Callback function
        'lynt-leads', // Admin page (or post type)
        'side', // Context
        'high' // Priority
        );
      
       add_meta_box('lynt_info', // ID
        esc_html__('Informace o leadu', 'lynt-leads'), // Title
        array($this, 'info_metabox'), // Callback function
        'lynt-leads', // Admin page (or post type)
        'normal', // Context
        'high' // Priority
        );
      
       } 
    
    public function status_metabox()
    
    {
      
       global $post;
      
       $post_status = get_post_status($post -> ID);
      
       $status = $this -> statuses[$post_status];
       $class = 'button-primary';
       $available = false;
       foreach ($status['next'] as $key => $value) {
        $available = true;
         echo "<button type=\"submit\" class=\"button {$class}\" name=\"post_status\" value=\"{$value}\">{$this->statuses[$value]['name']} lead</button>";
         if ($value === 'lynt_win') {
          echo "<br>Zisk:<br><input type=\"number\" name=\"lynt_revnue\">";
          
           } 
        echo "<hr>";
         $class = '';
        
         } 
      if (!$available) echo "Toto je konečný stav";
       } 
    
    public function info_metabox()
    
    {
       global $post;
      
       ?>
 <div class='report-leads'>

<?php
       $revenue = get_post_meta($post -> ID, 'lynt_revnue', true);
       if ($revenue) echo "<h3 style=\"color:green\">Zisk: $revenue</h3>";
       echo "<h3>Lead #" . $post -> ID . "</h3><h4>Status: " . $this -> statuses[get_post_status($post -> ID)]['name'] . "</h4><table class=\"wp-list-table widefat striped\">";
      
       $data = json_decode(get_the_content(null, false, $post -> ID), true);
      
       foreach ($data as $key => $value) {
        
        if (is_email($value)) $value = sprintf("<a href=\"mailto:%s\">%s</a>", esc_html($value), esc_html($value));
         else $value = esc_html($value);
        
         ?>
  
		<tr>
			<th scope='row'><?php echo esc_html($key);
        ?></th><td><?php echo $value;
        ?></td>
		</tr>
	
<?php
        
         } 
      
      echo '</table>';
      
       ?>
                </div>
 <?php
       } 
    
    public function add_leads_columns($columns)
    
    {
       $columns['status'] = __('Status', 'lynt-leads');
      
       return $columns;
       } 
    
    public function render_leads_columns($column, $post_id)
    
    {
      
       switch ($column) {
      case 'status' : echo $this -> statuses[get_post_status($post_id)]['name'];
         break;
        
         } 
      } 
    
    public function save_lead($post_id, $post, $update = false)
    
    {
       $value = 0;
       if (isset($_POST['lynt_revnue'])) {
        $value = intval($_POST['lynt_revnue']);
         update_post_meta($post_id, 'lynt_revnue', $value);
         } 
      
      $hostname = parse_url(home_url())['host'];
      
       file_put_contents('debug4-' . time() . '.txt', $post_id . '\n' . $post -> post_status . '\n' . $meta . '\n' . $hostname);
      
       $this -> sent_to_ga($post -> post_content, $post -> post_status, $post_id, $value);
       } 
    
    private function sent_to_ga($data, $status, $lead_id, $value)
    {
      
       $cid = json_decode($data, true)['ga_id'];
      
       $hostname = parse_url(home_url())['host'];
       $status = str_replace('lynt_', '', $status);
      
       $post = array(
        'timeout' => 15,
         'body' => array(
          'v' => 1,
           't' => 'event',
           'ec' => 'Lynt Lead',
           'tid' => $this -> ga_account,
           'cid' => $cid,
           'ea' => $status,
           'el' => $lead_id,
           'ev' => $value,
           'dh' => $hostname,
          ),
        );
      
       $response = wp_remote_post('https://www.google-analytics.com/collect', $post);
      
       } 
    
    public function register_settings_page()
    {
       add_options_page('Nastavení Lynt Leads', 'Lynt Leads', 'manage_options', 'lynt-leads', array($this, 'render_settings_page'));
       } 
    
    public function register_settings()
    {
      
       add_option('lynt_leads_ga_account', '');
       register_setting('lynt_leads_group', 'lynt_leads_ga_account');
       } 
    
    public function render_settings_page()
    
    {
       ?>
  <div class="wrap">
  <h1>Lynt Leads</h1>
  <form method="post" action="options.php">
  <?php settings_fields('lynt_leads_group');
      ?>
  <table class="form-table">
  <tr>
  <th scope="row"><label for="lynt_leads_ga_account">GA Account</label></th>
  <td><input type="text" id="lynt_leads_ga_account" name="lynt_leads_ga_account" placeholder="UA-xxxxxxxx-x" value="<?php echo get_option('lynt_leads_ga_account');
      ?>" /></td>
  </tr>
  </table>
  <?php submit_button();
      ?>
  </form>
  </div>
<?php
      } 
    
    public function admin_notice()
    {
       ?>
    <div class="notice notice-error is-dismissible">
      <p><?php _e('Není nastaven <a href="' . admin_url('options-general.php?page=lynt-leads') . '">GA Account</a>!', 'lynt-leads');
      ?></p>
    </div>
    <?php
      } 
    
    public function wpcf7_mail_sent_function($contact_form)
    
    {
      
       $submission = WPCF7_Submission :: get_instance();
      
       if ($submission) {
        $data = $submission -> get_posted_data();
         if (!empty($data['lynt_tag'])) {
          
          $data['post_id'] = intval($data['_wpcf7_container_post']);
           $data['ip_address'] = $this -> get_real_client_ip();
           $data['ga_id'] = empty($_COOKIE['_ga'])?'':preg_replace('/^GA\d+\.\d+\.(\d+\.\d+)$/', '\\1', $_COOKIE['_ga']);
          
           $tag = sanitize_text_field($data['lynt_tag']);
           $date = date('Y-m-d H:i');
           $name = '';
           $mail = '';
          
           if (!empty($data['lynt_name']) && !empty($data[$data['lynt_name']])) $name = sanitize_text_field($data[$data['lynt_name']]);
           if (!empty($data['lynt_mail']) && !empty($data[$data['lynt_mail']])) $mail = sanitize_text_field($data[$data['lynt_mail']]);
          
           $title = join(' / ', array($tag, $date, $name, $mail));
          
           foreach ($data as $key => $value) {
            if (substr($key, 0, 5) === '_wpcf' || substr($key, 0, 5) === 'lynt_') unset($data[$key]);
             else $data[$key] = sanitize_text_field($data[$key]);
             } 
          
          $id = wp_insert_post(
            array(
              
              'post_title' => $title,
               'post_status' => 'lynt_new',
               'post_type' => 'lynt-leads',
               'post_content' => wp_json_encode($data, JSON_UNESCAPED_UNICODE),
              
              )
            );
          
           } 
        } 
      
      } 
    
    } 
  
  $lynt_leads = new Lynt_Leads;
  
   } 
