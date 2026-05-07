<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/keyvanmirani
 * @since      1.0.0
 *
 * @package    Kivano_Block_Popup
 * @subpackage Kivano_Block_Popup/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kivano_Block_Popup
 * @subpackage Kivano_Block_Popup/admin
 * @author     k1mirani <keyvan.mirani4419@gmail.com>
 */
class Kivano_Block_Popup_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook_suffix ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Kivano_Block_Popup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Kivano_Block_Popup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( ! $this->is_popup_admin_screen( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kivano-block-popup-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Kivano_Block_Popup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Kivano_Block_Popup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( ! $this->is_popup_admin_screen( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kivano-block-popup-admin.js', array( 'wp-a11y' ), $this->version, true );
		wp_localize_script(
			$this->plugin_name,
			'kivanoBlockPopupAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'kivano_block_popup_toggle_enabled' ),
				'i18n'    => array(
					'enable'  => __( 'Enable popup', 'kivano-block-popup' ),
					'disable' => __( 'Disable popup', 'kivano-block-popup' ),
					'enabled' => __( 'Enabled', 'kivano-block-popup' ),
					'disabled' => __( 'Disabled', 'kivano-block-popup' ),
					'saving'  => __( 'Saving...', 'kivano-block-popup' ),
					'error'   => __( 'Could not update popup status. Please try again.', 'kivano-block-popup' ),
				),
			)
		);

	}

	/**
	 * Register the popup post type.
	 *
	 * @since    1.0.0
	 */
	public function register_popup_post_type() {

		$labels = array(
			'name'               => _x( 'Popups', 'post type general name', 'kivano-block-popup' ),
			'singular_name'      => _x( 'Popup', 'post type singular name', 'kivano-block-popup' ),
			'menu_name'          => __( 'Popups', 'kivano-block-popup' ),
			'name_admin_bar'     => __( 'Popup', 'kivano-block-popup' ),
			'add_new'            => __( 'Add New', 'kivano-block-popup' ),
			'add_new_item'       => __( 'Add New Popup', 'kivano-block-popup' ),
			'new_item'           => __( 'New Popup', 'kivano-block-popup' ),
			'edit_item'          => __( 'Edit Popup', 'kivano-block-popup' ),
			'view_item'          => __( 'View Popup', 'kivano-block-popup' ),
			'all_items'          => __( 'Popups', 'kivano-block-popup' ),
			'search_items'       => __( 'Search Popups', 'kivano-block-popup' ),
			'not_found'          => __( 'No popups found.', 'kivano-block-popup' ),
			'not_found_in_trash' => __( 'No popups found in Trash.', 'kivano-block-popup' ),
		);

		$args = array(
			'labels'       => $labels,
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'revisions' ),
			'menu_icon'    => 'dashicons-welcome-widgets-menus',
		);

		register_post_type( 'popup_builder', $args );

	}

	/**
	 * Add popup settings meta box.
	 *
	 * @since    1.0.0
	 */
	public function add_popup_meta_boxes() {

		add_meta_box(
			'kivano_block_popup_settings',
			__( 'Popup Settings', 'kivano-block-popup' ),
			array( $this, 'render_popup_meta_box' ),
			'popup_builder',
			'side',
			'default'
		);

	}

	/**
	 * Render popup settings fields.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post The current popup post.
	 */
	public function render_popup_meta_box( $post ) {

		wp_nonce_field( 'kivano_block_popup_save_meta', 'kivano_block_popup_meta_nonce' );

		$enabled           = (bool) $this->get_compat_meta( $post->ID, '_kivano_block_popup_enabled', '_popup_builder_enabled', '0' );
		$delay             = $this->get_number_meta( $post->ID, '_kivano_block_popup_delay', 4, '_popup_builder_delay' );
		$repeat_interval   = $this->get_number_meta( $post->ID, '_kivano_block_popup_repeat_interval', 4, '_popup_builder_repeat_interval' );
		$max_width         = $this->get_number_meta( $post->ID, '_kivano_block_popup_max_width', 520, '_popup_builder_max_width' );
		$show_close_button = $this->get_compat_meta( $post->ID, '_kivano_block_popup_show_close_button', '_popup_builder_show_close_button', true );
		$once_per_session  = (bool) $this->get_compat_meta( $post->ID, '_kivano_block_popup_once_per_session', '_popup_builder_once_per_session', '0' );
		$show_on           = $this->get_compat_meta( $post->ID, '_kivano_block_popup_show_on', '_popup_builder_show_on', 'entire_site' );
		$show_on_options   = $this->get_show_on_options();

		if ( '' === $show_close_button ) {
			$show_close_button = true;
		}

		if ( ! array_key_exists( $show_on, $show_on_options ) ) {
			$show_on = 'entire_site';
		}
		?>
		<p>
			<label>
				<input type="checkbox" name="kivano_block_popup_enabled" value="1" <?php checked( $enabled ); ?>>
				<?php esc_html_e( 'Enable popup', 'kivano-block-popup' ); ?>
			</label>
		</p>
		<p>
			<label for="kivano_block_popup_delay"><?php esc_html_e( 'Display delay in seconds', 'kivano-block-popup' ); ?></label>
			<input class="widefat" type="number" id="kivano_block_popup_delay" name="kivano_block_popup_delay" value="<?php echo esc_attr( $delay ); ?>" min="0" step="1">
		</p>
		<p>
			<label for="kivano_block_popup_repeat_interval"><?php esc_html_e( 'Repeat interval in seconds', 'kivano-block-popup' ); ?></label>
			<input class="widefat" type="number" id="kivano_block_popup_repeat_interval" name="kivano_block_popup_repeat_interval" value="<?php echo esc_attr( $repeat_interval ); ?>" min="0" step="1">
		</p>
		<p>
			<label for="kivano_block_popup_max_width"><?php esc_html_e( 'Max width in px', 'kivano-block-popup' ); ?></label>
			<input class="widefat" type="number" id="kivano_block_popup_max_width" name="kivano_block_popup_max_width" value="<?php echo esc_attr( $max_width ); ?>" min="240" step="1">
		</p>
		<p>
			<label>
				<input type="checkbox" name="kivano_block_popup_show_close_button" value="1" <?php checked( (bool) $show_close_button ); ?>>
				<?php esc_html_e( 'Show close button', 'kivano-block-popup' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="kivano_block_popup_once_per_session" value="1" <?php checked( $once_per_session ); ?>>
				<?php esc_html_e( 'Show once per session', 'kivano-block-popup' ); ?>
			</label>
		</p>
		<p>
			<label for="kivano_block_popup_show_on"><?php esc_html_e( 'Show on', 'kivano-block-popup' ); ?></label>
			<select class="widefat" id="kivano_block_popup_show_on" name="kivano_block_popup_show_on">
				<?php foreach ( $show_on_options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $show_on, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php

	}

	/**
	 * Save popup settings.
	 *
	 * @since    1.0.0
	 * @param    int $post_id The current popup post ID.
	 */
	public function save_popup_meta( $post_id ) {

		if ( ! isset( $_POST['kivano_block_popup_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kivano_block_popup_meta_nonce'] ) ), 'kivano_block_popup_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$enabled           = isset( $_POST['kivano_block_popup_enabled'] ) ? '1' : '0';
		$delay             = isset( $_POST['kivano_block_popup_delay'] ) ? absint( wp_unslash( $_POST['kivano_block_popup_delay'] ) ) : 4;
		$repeat_interval   = isset( $_POST['kivano_block_popup_repeat_interval'] ) ? absint( wp_unslash( $_POST['kivano_block_popup_repeat_interval'] ) ) : 4;
		$max_width         = isset( $_POST['kivano_block_popup_max_width'] ) ? absint( wp_unslash( $_POST['kivano_block_popup_max_width'] ) ) : 520;
		$show_close_button = isset( $_POST['kivano_block_popup_show_close_button'] ) ? '1' : '0';
		$once_per_session  = isset( $_POST['kivano_block_popup_once_per_session'] ) ? '1' : '0';
		$show_on           = isset( $_POST['kivano_block_popup_show_on'] ) ? sanitize_key( wp_unslash( $_POST['kivano_block_popup_show_on'] ) ) : 'entire_site';

		if ( ! array_key_exists( $show_on, $this->get_show_on_options() ) ) {
			$show_on = 'entire_site';
		}

		update_post_meta( $post_id, '_kivano_block_popup_enabled', $enabled );
		update_post_meta( $post_id, '_kivano_block_popup_delay', $delay );
		update_post_meta( $post_id, '_kivano_block_popup_repeat_interval', $repeat_interval );
		update_post_meta( $post_id, '_kivano_block_popup_max_width', max( 240, $max_width ) );
		update_post_meta( $post_id, '_kivano_block_popup_show_close_button', $show_close_button );
		update_post_meta( $post_id, '_kivano_block_popup_once_per_session', $once_per_session );
		update_post_meta( $post_id, '_kivano_block_popup_show_on', $show_on );

	}

	/**
	 * Add useful popup columns.
	 *
	 * @since    1.0.0
	 * @param    array $columns Existing columns.
	 * @return   array
	 */
	public function add_popup_columns( $columns ) {

		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns['kivano_block_popup_enabled']         = __( 'Enabled', 'kivano-block-popup' );
				$new_columns['kivano_block_popup_delay']           = __( 'Delay', 'kivano-block-popup' );
				$new_columns['kivano_block_popup_repeat_interval'] = __( 'Repeat Interval', 'kivano-block-popup' );
			}
		}

		return $new_columns;

	}

	/**
	 * Render popup columns.
	 *
	 * @since    1.0.0
	 * @param    string $column  Column name.
	 * @param    int    $post_id Current post ID.
	 */
	public function render_popup_columns( $column, $post_id ) {

		if ( 'kivano_block_popup_enabled' === $column ) {
			$enabled = (bool) $this->get_compat_meta( $post_id, '_kivano_block_popup_enabled', '_popup_builder_enabled', '0' );
			$label   = $enabled ? __( 'Disable popup', 'kivano-block-popup' ) : __( 'Enable popup', 'kivano-block-popup' );
			$status  = $enabled ? __( 'Enabled', 'kivano-block-popup' ) : __( 'Disabled', 'kivano-block-popup' );

			printf(
				'<button type="button" class="kivano-block-popup-enabled-toggle%s" role="switch" aria-checked="%s" aria-label="%s" data-post-id="%d" data-enabled="%d"><span class="kivano-block-popup-toggle-track" aria-hidden="true"><span class="kivano-block-popup-toggle-thumb"></span></span><span class="kivano-block-popup-toggle-status">%s</span></button>',
				$enabled ? ' is-enabled' : '',
				$enabled ? 'true' : 'false',
				esc_attr( $label ),
				absint( $post_id ),
				$enabled ? 1 : 0,
				esc_html( $status )
			);
		}

		if ( 'kivano_block_popup_delay' === $column ) {
			printf(
				/* translators: %d: delay in seconds. */
				esc_html__( '%d sec', 'kivano-block-popup' ),
				absint( $this->get_number_meta( $post_id, '_kivano_block_popup_delay', 4, '_popup_builder_delay' ) )
			);
		}

		if ( 'kivano_block_popup_repeat_interval' === $column ) {
			printf(
				/* translators: %d: repeat interval in seconds. */
				esc_html__( '%d sec', 'kivano-block-popup' ),
				absint( $this->get_number_meta( $post_id, '_kivano_block_popup_repeat_interval', 4, '_popup_builder_repeat_interval' ) )
			);
		}

	}

	/**
	 * Toggle popup enabled status from the admin list table.
	 *
	 * @since    1.0.0
	 */
	public function ajax_toggle_popup_enabled() {

		check_ajax_referer( 'kivano_block_popup_toggle_enabled', 'kivano_block_popup_toggle_enabled_nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		$enabled = isset( $_POST['enabled'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) ? '1' : '0';

		if ( ! $post_id || 'popup_builder' !== get_post_type( $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid popup.', 'kivano-block-popup' ),
				),
				400
			);
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to edit this popup.', 'kivano-block-popup' ),
				),
				403
			);
		}

		update_post_meta( $post_id, '_kivano_block_popup_enabled', $enabled );

		wp_send_json_success(
			array(
				'enabled' => '1' === $enabled,
			)
		);

	}

	/**
	 * Get numeric meta with a default for unset values.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id The post ID.
	 * @param    string $key     Meta key.
	 * @param    int    $default Default value.
	 * @return   int
	 */
	private function get_number_meta( $post_id, $key, $default, $legacy_key = '' ) {

		$value = $this->get_compat_meta( $post_id, $key, $legacy_key, '' );

		if ( '' === $value ) {
			return absint( $default );
		}

		return absint( $value );

	}

	/**
	 * Get new meta, falling back to legacy keys for existing popups.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id    The post ID.
	 * @param    string $key        New meta key.
	 * @param    string $legacy_key Legacy meta key.
	 * @param    mixed  $default    Default value.
	 * @return   mixed
	 */
	private function get_compat_meta( $post_id, $key, $legacy_key = '', $default = '' ) {

		$value = get_post_meta( $post_id, $key, true );

		if ( '' !== $value || '' === $legacy_key ) {
			return '' === $value ? $default : $value;
		}

		$value = get_post_meta( $post_id, $legacy_key, true );

		return '' === $value ? $default : $value;

	}

	/**
	 * Check whether an admin asset request belongs to the popup post type.
	 *
	 * @since    1.0.0
	 * @param    string $hook_suffix Current admin page hook.
	 * @return   bool
	 */
	private function is_popup_admin_screen( $hook_suffix ) {

		$screen = get_current_screen();

		if ( $screen && 'popup_builder' === $screen->post_type ) {
			return true;
		}

		return in_array( $hook_suffix, array( 'edit.php', 'post.php', 'post-new.php' ), true ) && isset( $_GET['post_type'] ) && 'popup_builder' === sanitize_key( wp_unslash( $_GET['post_type'] ) );

	}

	/**
	 * Get targeting options.
	 *
	 * @since    1.0.0
	 * @return   array
	 */
	private function get_show_on_options() {

		return array(
			'entire_site'   => __( 'Entire site', 'kivano-block-popup' ),
			'homepage_only' => __( 'Homepage only', 'kivano-block-popup' ),
			'posts_only'    => __( 'Posts only', 'kivano-block-popup' ),
			'pages_only'    => __( 'Pages only', 'kivano-block-popup' ),
		);

	}

}
