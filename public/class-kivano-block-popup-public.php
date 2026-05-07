<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/keyvanmirani
 * @since      1.0.0
 *
 * @package    Kivano_Block_Popup
 * @subpackage Kivano_Block_Popup/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Kivano_Block_Popup
 * @subpackage Kivano_Block_Popup/public
 * @author     k1mirani <keyvan.mirani4419@gmail.com>
 */
class Kivano_Block_Popup_Public {

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
	 * Cached active popup data for the current request.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array|null    $active_popup    Active popup data.
	 */
	private $active_popup = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		if ( $this->get_active_popup() ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kivano-block-popup-public.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		$popup = $this->get_active_popup();

		if ( ! $popup ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kivano-block-popup-public.js', array(), $this->version, true );

		wp_localize_script(
			$this->plugin_name,
			'kivanoBlockPopupSettings',
			array(
				'delayEnabled'    => $popup['delay_enabled'],
				'delay'           => $popup['delay'],
				'repeatEnabled'   => $popup['repeat_enabled'],
				'repeatInterval'  => $popup['repeat_interval'],
				'maxWidth'        => $popup['max_width'],
				'showCloseButton' => $popup['show_close_button'],
				'oncePerSession'  => $popup['once_per_session'],
				'sessionKey'      => 'kivano_block_popup_closed_' . $popup['post']->ID,
				'debug'           => defined( 'WP_DEBUG' ) && WP_DEBUG,
			)
		);

	}

	/**
	 * Render the active popup shell and block content.
	 *
	 * @since    1.0.0
	 */
	public function render_popup() {

		$popup = $this->get_active_popup();

		if ( ! $popup ) {
			echo "\n<!-- Kivano Block Popup: no active popup -->\n";
			return;
		}

		global $post;

		$previous_post = $post;
		$post          = $popup['post'];
		setup_postdata( $post );

		$content = apply_filters( 'the_content', $post->post_content );

		wp_reset_postdata();
		$post = $previous_post;
		?>
		<!-- Kivano Block Popup rendered -->
		<div id="kivano-block-popup-overlay" class="kivano-block-popup-overlay" hidden>
			<div class="kivano-block-popup-modal" role="dialog" aria-modal="true" style="<?php echo esc_attr( '--kivano-block-popup-max-width: ' . $popup['max_width'] . 'px;' ); ?>">
				<?php if ( $popup['show_close_button'] ) : ?>
					<button type="button" class="kivano-block-popup-close" aria-label="<?php esc_attr_e( 'Close popup', 'kivano-block-popup' ); ?>">&times;</button>
				<?php endif; ?>
				<div class="kivano-block-popup-content">
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Find the first enabled published popup.
	 *
	 * @since    1.0.0
	 * @return   array|false
	 */
	private function get_active_popup() {

		if ( null !== $this->active_popup ) {
			return $this->active_popup;
		}

		if ( is_admin() || is_feed() || wp_doing_ajax() ) {
			$this->active_popup = false;
			return $this->active_popup;
		}

		$posts = get_posts(
			array(
				'post_type'              => 'popup_builder',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => 'date',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
				'meta_query'             => array(
					'relation' => 'OR',
					array(
						'key'   => '_kivano_block_popup_enabled',
						'value' => '1',
					),
					array(
						'key'   => '_popup_builder_enabled',
						'value' => '1',
					),
				),
			)
		);

		$post = $this->find_matching_popup( $posts );

		if ( ! $post ) {
			$this->active_popup = false;
			return $this->active_popup;
		}

		$show_close_button      = $this->get_compat_meta( $post->ID, '_kivano_block_popup_show_close_button', '_popup_builder_show_close_button', true );
		$once_per_session       = (bool) $this->get_compat_meta( $post->ID, '_kivano_block_popup_once_per_session', '_popup_builder_once_per_session', '0' );
		$repeat_enabled         = (bool) $this->get_compat_meta( $post->ID, '_kivano_block_popup_repeat_enabled', '', '0' );
		$this->active_popup     = array(
			'post'              => $post,
			'delay_enabled'     => (bool) $this->get_compat_meta( $post->ID, '_kivano_block_popup_delay_enabled', '', '1' ),
			'delay'             => $this->get_timing_meta_ms( $post->ID, '_kivano_block_popup_delay', 4000, '_popup_builder_delay' ),
			'repeat_enabled'    => $once_per_session ? false : $repeat_enabled,
			'repeat_interval'   => $this->get_timing_meta_ms( $post->ID, '_kivano_block_popup_repeat_interval', 0, '_popup_builder_repeat_interval' ),
			'max_width'         => max( 240, $this->get_number_meta( $post->ID, '_kivano_block_popup_max_width', 520, '_popup_builder_max_width' ) ),
			'show_close_button' => '' === $show_close_button ? true : (bool) $show_close_button,
			'once_per_session'  => $once_per_session,
		);

		return $this->active_popup;

	}

	/**
	 * Find the first enabled popup that matches this page.
	 *
	 * @since    1.0.0
	 * @param    WP_Post[] $posts Popup posts.
	 * @return   WP_Post|false
	 */
	private function find_matching_popup( $posts ) {

		foreach ( $posts as $post ) {
			$show_on = $this->get_compat_meta( $post->ID, '_kivano_block_popup_show_on', '_popup_builder_show_on', 'entire_site' );

			if ( '' === $show_on ) {
				$show_on = 'entire_site';
			}

			if ( $this->matches_targeting_rule( $show_on ) ) {
				return $post;
			}
		}

		return false;

	}

	/**
	 * Check whether the current request matches a targeting rule.
	 *
	 * @since    1.0.0
	 * @param    string $show_on Targeting rule.
	 * @return   bool
	 */
	private function matches_targeting_rule( $show_on ) {

		switch ( $show_on ) {
			case 'homepage_only':
				return is_front_page() || is_home();

			case 'posts_only':
				return is_singular( 'post' );

			case 'pages_only':
				return is_page();

			case 'entire_site':
				return true;

			default:
				return true;
		}

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
	 * Get timing meta normalized to milliseconds.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id    The post ID.
	 * @param    string $key        Meta key.
	 * @param    int    $default    Default value in milliseconds.
	 * @param    string $legacy_key Legacy meta key.
	 * @return   int
	 */
	private function get_timing_meta_ms( $post_id, $key, $default, $legacy_key = '' ) {

		$value = $this->get_compat_meta( $post_id, $key, $legacy_key, '' );

		if ( '' === $value ) {
			return absint( $default );
		}

		$value    = absint( $value );
		$migrated = (bool) get_post_meta( $post_id, '_kivano_block_popup_timing_migrated_to_ms', true );

		if ( ! $migrated && $value > 0 && $value < 1000 ) {
			return $value * 1000;
		}

		return $value;

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

}
