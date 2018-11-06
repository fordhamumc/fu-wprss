<?php

namespace FU_WPRSS\admin;

use FU_WPRSS as NS;
use WP_Post;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      0.0.1
 *
 * @author    Michael Foley
 */
class Admin {
  /**
   * Singleton
   *
   * @since   0.1.0
   * @access  protected
   * @var     Admin|null
   */
  protected static $instance = null;

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

    /**
     * The base name of this plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $plugin_basename    The base name of this plugin.
     */
    protected $plugin_basename;

    /**
     * The title of this plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $plugin_title    The title of this plugin.
     */
    protected $plugin_title;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;

    /**
     * The min version of WP RSS Aggregator plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $wprss_version    The min version of WP RSS Aggregator.
     */
    protected $wprss_version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $text_domain    The text domain of this plugin.
	 */
	protected $text_domain;


  /**
   * Singleton
   *
   * @since   0.1.0
   * @return  Admin|null
   */
  public static function instance() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
	 * Initialize the class and set its properties.
	 *
	 * @since       0.0.1
	 */
	public function __construct() {

        $this->plugin_name = NS\PLUGIN_NAME;
        $this->plugin_basename = NS\PLUGIN_BASENAME;
        $this->version = NS\PLUGIN_VERSION;
        $this->text_domain = NS\PLUGIN_TEXT_DOMAIN;
        $this->plugin_title = NS\PLUGIN_TITLE;
        $this->wprss_version = NS\WPRSS_MIN_VERSION;

	}

	/**
   * Action: admin_enqueue_scripts
	 * Register the stylesheets for the admin area.
	 *
	 * @since       0.0.1
	 */
	public function enqueue_styles() {
//		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fu-wprss-admin.css', array(), $this->version, 'all' );
	}

	/**
   * Action: admin_enqueue_scripts
	 * Register the JavaScript for the admin area.
	 *
	 * @since       0.0.1
	 */
	public function enqueue_scripts() {
//    wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fu-wprss-admin.min.js', array( 'media-editor', 'media-views', 'jquery' ), $this->version, false );
	}


    /**
     * Checks for the WP RSS Aggregator core plugin.
     *
     * @since    0.1.0
     */

    public function check_aggregator() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( !defined( 'WPRSS_VERSION' ) || version_compare( WPRSS_VERSION, $this->wprss_version, '<' ) ) {
            add_action( 'all_admin_notices', array($this, 'notify_dependency') );
            deactivate_plugins ( $this->plugin_basename );
            return false;
        }

        return true;
    }

    /**
     * Action: plugins_loaded
     * Exit if dependencies are missing
     *
     * @since    0.1.0
     */

    public function check_plugin_dependencies() {
        if (!$this->check_aggregator()) {
            wp_die(  $this->dependency_msg() , $this->plugin_title, array( 'back_link' => true ) );
        }
    }

    /**
     * Action: all_admin_notices
     * Add a Wordpress Admin Notice
     *
     * @since    0.1.0
     */

    public function notify_dependency() {
        ?>
        <div class="updated error">
        <p>
                <?php echo $this->dependency_msg() ?>
            </p>
        </div>
        <?php
    }

    /**
     * Returns the dependency is missing message
     *
     * @since    0.1.0
     * @return  string
     */

    public function dependency_msg() {
        return sprintf( __( 'The %1$s plugin has been <strong>deactivated</strong> due to unsatisfied critical dependencies.</br>'
            . 'Please install and activate %3$s, at version <strong>%2$s</strong> or higher, for the %1$s to work.' ), $this->plugin_title, $this->wprss_version, 'WP RSS Aggregator' );
    }


    /**
     * Action: wprss_ftp_converter_inserted_post
     * Register the JavaScript for the admin area.
     *
     * @param       int     $post_id    The ID of the converted post
     * @since       0.1.0
     */
    public function filter_image_urls($post_id) {
	    $content = get_post($post_id)->post_content;
        $content = preg_replace('/"(https?:\/\/fordhamsports\.com\/).*?image_path=\/?(.*?.(?:gif|jpe?g|png)).*?"/i','$1$2', $content);
	    wp_update_post(array(
	        'ID'            => $post_id,
	        'post_content'  => $content
        ));
    }


    /**
     * Action: wprss_ftp_converter_inserted_post
     * Alert user(s) when new post is added
     *
     * @param       int     $post_id        The ID of the converted post
     * @since       0.1.0
     */

    public function alert_users( $post_id ) {
        $feed_id = get_post_meta( $post_id, 'wprss_feed_id', true);
        if (!$feed_id)
            return;

        $emails = get_post_meta($feed_id, 'fu_wprss_new_post_alert', true);
        if ( empty($emails) )
            return;

        $title = get_the_title($post_id);
        $status = get_post_status($post_id);
        $subject = __('New post added', $this->text_domain) . ': ' . $title;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $body = sprintf(__('A new post has been added to %s from the %s RSS feed.', $this->text_domain), get_bloginfo('name'), get_the_title($feed_id)) . '<br><br>';
        $body .= __('Title', $this->text_domain) . ': ' . $title . '<br>';
        $body .= __('Status', $this->text_domain) . ': ' . (($status === 'publish') ? 'published' : $status) . '<br>';
        $body .= __('Link', $this->text_domain) . ': ' . get_permalink($post_id);
        foreach($emails as $email) {
            wp_mail($email, $subject, $body, $headers);
        }
    }


    /**
     * Action: add_meta_boxes
     * Add meta box to collect emails to alert when a new item is posted
     *
     * @since       0.1.0
     */

    public function add_new_post_alert_meta_box() {
        add_meta_box(
            'fu-wprss-new-post-alert-meta-box',
            __( 'Feed to Post - Alert', $this->text_domain ),
            array( $this, 'render_new_post_alert_meta_box' ),
            'wprss_feed',
            'normal',
            'default'
        );
    }


    /**
     * Render the textarea to collect email addresses
     *
     * @param       WP_Post     $post       Wordpress post object
     * @since       0.1.0
     */

    public function render_new_post_alert_meta_box( $post ) {
        $template = <<<'EOT'
        <p>%s</p>
        <table class="form-table wprss-form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="fu_wprss_new_post_alert">%s</label>
                    </th>
                    <td>
                        <textarea class="wprss-text-input" id="fu_wprss_new_post_alert" name="fu_wprss_new_post_alert">%s</textarea>
                    </td>
                </tr>
            </tbody>
        </table> 
EOT;
        printf($template,
            __( 'Comma-separated list of emails to alert when a new feed item is added.', $this->text_domain),
            __( 'Emails', $this->text_domain),
            esc_attr( implode(', ', get_post_meta( $post->ID, 'fu_wprss_new_post_alert', true ) ) )
        );
    }


    /**
     * Save the post meta
     *
     * @param       int     $post_id       Post ID
     * @since       0.1.0
     */

    public function save_post_meta( $post_id ) {
        if (get_post_type() !== 'wprss_feed')
            return;

        $field = 'fu_wprss_new_post_alert';
        $old = get_post_meta($post_id, $field, true);
        $new = array();
        $filtered = array();

        if (isset($_POST[$field]))
            $new = explode(',', $_POST[$field]);

        foreach($new as $email){
            $email = trim($email);
            if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                $filtered[] = $email;
            }
        }

        $filtered = array_values( array_unique( $filtered ) );

        if ($filtered && $filtered != $old) {
            update_post_meta($post_id, $field, $filtered);
        } elseif ($filtered === array() && $old) {
            delete_post_meta($post_id, $field, $old);
        }
    }
}