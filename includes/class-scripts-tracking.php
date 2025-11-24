<?php
/**
 * Scripts & Tracking Feature
 *
 * Handles custom header and footer code injection for tracking scripts,
 * verification codes, and analytics.
 *
 * @package Promotional_Footer_Bar
 * @since 1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scripts Tracking Class
 */
class PFB_Scripts_Tracking {

	/**
	 * Option name for storing scripts settings
	 */
	const OPTION_NAME = 'pfb_scripts_settings';

	/**
	 * Cache group name
	 */
	const CACHE_GROUP = 'promotional_footer_bar';

	/**
	 * Plugin version
	 */
	private $version;

	/**
	 * Constructor
	 *
	 * @param string $version Plugin version.
	 */
	public function __construct( $version ) {
		$this->version = $version;
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );

		// Admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Save settings
		add_action( 'admin_post_pfb_save_scripts', array( $this, 'save_scripts_settings' ) );

		// Inject custom header/footer code
		add_action( 'wp_head', array( $this, 'inject_header_code' ), 10 );
		add_action( 'wp_footer', array( $this, 'inject_footer_code' ), 15 );
	}

	/**
	 * Add submenu page
	 */
	public function add_admin_menu() {
		$hook = add_submenu_page(
			'footer-notifications',
			__( 'Scripts & Tracking', 'promotional-footer-bar' ),
			__( 'Scripts & Tracking', 'promotional-footer-bar' ),
			'manage_options',
			'footer-notifications-scripts',
			array( $this, 'scripts_settings_page' )
		);

		// Add help tab
		add_action( "load-{$hook}", array( $this, 'add_help_tab' ) );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function admin_scripts( $hook ) {
		if ( 'footer-notices_page_footer-notifications-scripts' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'pfb-admin-style',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin-style.css',
			array(),
			$this->version
		);

		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_enqueue_script( 'wp-theme-plugin-editor' );
	}

	/**
	 * Add help tab to scripts settings page
	 */
	public function add_help_tab() {
		$screen = get_current_screen();

		$screen->add_help_tab(
			array(
				'id'      => 'pfb-scripts-overview',
				'title'   => __( 'Overview', 'promotional-footer-bar' ),
				'content' => '<p>' . __( '<strong>Scripts & Tracking</strong> allows you to inject custom HTML, JavaScript, or CSS code into your site\'s header or footer.', 'promotional-footer-bar' ) . '</p>' .
							'<p>' . __( 'This is commonly used for adding tracking codes, verification tags, or analytics scripts.', 'promotional-footer-bar' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'pfb-scripts-usage',
				'title'   => __( 'Common Use Cases', 'promotional-footer-bar' ),
				'content' => '<p><strong>' . __( 'Header Code (wp_head):', 'promotional-footer-bar' ) . '</strong></p>' .
							'<ul>' .
							'<li>' . __( 'Meta verification tags (Facebook, Google Search Console)', 'promotional-footer-bar' ) . '</li>' .
							'<li>' . __( 'Google Analytics tracking code', 'promotional-footer-bar' ) . '</li>' .
							'<li>' . __( 'Google Tag Manager container', 'promotional-footer-bar' ) . '</li>' .
							'<li>' . __( 'Custom CSS styles', 'promotional-footer-bar' ) . '</li>' .
							'</ul>' .
							'<p><strong>' . __( 'Footer Code (wp_footer):', 'promotional-footer-bar' ) . '</strong></p>' .
							'<ul>' .
							'<li>' . __( 'Facebook Pixel tracking', 'promotional-footer-bar' ) . '</li>' .
							'<li>' . __( 'Third-party chat widgets', 'promotional-footer-bar' ) . '</li>' .
							'<li>' . __( 'Deferred JavaScript for better performance', 'promotional-footer-bar' ) . '</li>' .
							'</ul>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'pfb-scripts-security',
				'title'   => __( 'Security', 'promotional-footer-bar' ),
				'content' => '<p>' . __( '<strong>Important:</strong> Only add code from trusted sources.', 'promotional-footer-bar' ) . '</p>' .
							'<p>' . __( 'Code entered here will be executed on your website. Malicious code can compromise your site security.', 'promotional-footer-bar' ) . '</p>' .
							'<p>' . __( 'This feature requires the "unfiltered_html" capability, which is typically only available to administrators.', 'promotional-footer-bar' ) . '</p>',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'promotional-footer-bar' ) . '</strong></p>' .
			'<p><a href="https://docs.wbcomdesigns.com" target="_blank">' . __( 'Documentation', 'promotional-footer-bar' ) . '</a></p>' .
			'<p><a href="https://wbcomdesigns.com/support/" target="_blank">' . __( 'Support', 'promotional-footer-bar' ) . '</a></p>'
		);
	}

	/**
	 * Get scripts settings
	 *
	 * @return array Scripts settings with header and footer code.
	 */
	public function get_scripts_settings() {
		// Try cache first
		$cache_key = 'pfb_scripts_settings';
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		// Get from options
		$settings = get_option( self::OPTION_NAME, array() );

		// Set defaults if empty
		if ( empty( $settings ) ) {
			$settings = array(
				'header_code' => array(
					'enabled' => false,
					'code'    => '',
				),
				'footer_code' => array(
					'enabled'  => false,
					'code'     => '',
					'priority' => 15,
				),
			);
		}

		// Cache for 1 hour
		wp_cache_set( $cache_key, $settings, self::CACHE_GROUP, 3600 );

		return $settings;
	}

	/**
	 * Clear scripts cache
	 */
	private function clear_scripts_cache() {
		$cache_key = 'pfb_scripts_settings';
		wp_cache_delete( $cache_key, self::CACHE_GROUP );
	}

	/**
	 * Sanitize tracking code
	 *
	 * @param string $code Raw code input.
	 * @return string Sanitized code.
	 */
	private function sanitize_tracking_code( $code ) {
		// Allow administrators with unfiltered_html capability to save any code
		if ( current_user_can( 'unfiltered_html' ) ) {
			// Just remove slashes added by WordPress
			return wp_unslash( $code );
		}

		// For other users, use wp_kses_post for safer HTML
		return wp_kses_post( wp_unslash( $code ) );
	}

	/**
	 * Scripts settings page
	 */
	public function scripts_settings_page() {
		$settings = $this->get_scripts_settings();

		// Show admin notices
		if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) {
			add_settings_error(
				'pfb_scripts_messages',
				'pfb_scripts_message',
				__( 'Scripts settings saved successfully!', 'promotional-footer-bar' ),
				'updated'
			);
		}

		if ( isset( $_GET['error'] ) && 'permission' === $_GET['error'] ) {
			add_settings_error(
				'pfb_scripts_messages',
				'pfb_scripts_error',
				__( 'You do not have sufficient permissions to save code. Only administrators with "unfiltered_html" capability can save custom scripts.', 'promotional-footer-bar' ),
				'error'
			);
		}
		?>
		<div class="wrap pfb-admin-wrap">
			<h1><?php esc_html_e( 'Scripts & Tracking Settings', 'promotional-footer-bar' ); ?></h1>

			<?php settings_errors( 'pfb_scripts_messages' ); ?>

			<p class="description">
				<?php esc_html_e( 'Add custom HTML, JavaScript, or CSS code to your site\'s header or footer. Commonly used for analytics, tracking pixels, and verification codes.', 'promotional-footer-bar' ); ?>
			</p>

			<?php if ( ! current_user_can( 'unfiltered_html' ) ) : ?>
				<div class="notice notice-warning">
					<p>
						<strong><?php esc_html_e( 'Warning:', 'promotional-footer-bar' ); ?></strong>
						<?php esc_html_e( 'You do not have the "unfiltered_html" capability. Your code will be filtered for security. Contact an administrator if you need to save unrestricted code.', 'promotional-footer-bar' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="pfb-scripts-form">
				<?php wp_nonce_field( 'pfb_save_scripts', 'pfb_scripts_nonce' ); ?>
				<input type="hidden" name="action" value="pfb_save_scripts">

				<div class="pfb-scripts-container">
					<!-- Header Code Section -->
					<div class="pfb-notification-row">
						<div class="pfb-notification-header">
							<h2><?php esc_html_e( 'Header Code', 'promotional-footer-bar' ); ?></h2>
						</div>

						<div class="pfb-notification-content">
							<table class="form-table">
								<tr>
									<th scope="row">
										<?php esc_html_e( 'Enable Header Code', 'promotional-footer-bar' ); ?>
									</th>
									<td>
										<label>
											<input type="checkbox"
												name="header_enabled"
												value="1"
												<?php checked( ! empty( $settings['header_code']['enabled'] ) ); ?>>
											<?php esc_html_e( 'Inject code in <head> section (wp_head hook)', 'promotional-footer-bar' ); ?>
										</label>
										<p class="description">
											<?php esc_html_e( 'Code will be inserted before the closing </head> tag on all pages.', 'promotional-footer-bar' ); ?>
										</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="pfb-header-code">
											<?php esc_html_e( 'Header Code', 'promotional-footer-bar' ); ?>
										</label>
									</th>
									<td>
										<textarea
											id="pfb-header-code"
											name="header_code"
											rows="10"
											class="large-text code"
											placeholder="<?php esc_attr_e( 'e.g., <script>/* Your tracking code */</script>', 'promotional-footer-bar' ); ?>"
											spellcheck="false"><?php echo esc_textarea( $settings['header_code']['code'] ?? '' ); ?></textarea>
										<p class="description">
											<strong><?php esc_html_e( 'Common uses:', 'promotional-footer-bar' ); ?></strong>
											<?php esc_html_e( 'Google Analytics, Meta verification tags, Google Tag Manager, custom CSS', 'promotional-footer-bar' ); ?>
										</p>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- Footer Code Section -->
					<div class="pfb-notification-row">
						<div class="pfb-notification-header">
							<h2><?php esc_html_e( 'Footer Code', 'promotional-footer-bar' ); ?></h2>
						</div>

						<div class="pfb-notification-content">
							<table class="form-table">
								<tr>
									<th scope="row">
										<?php esc_html_e( 'Enable Footer Code', 'promotional-footer-bar' ); ?>
									</th>
									<td>
										<label>
											<input type="checkbox"
												name="footer_enabled"
												value="1"
												<?php checked( ! empty( $settings['footer_code']['enabled'] ) ); ?>>
											<?php esc_html_e( 'Inject code before </body> (wp_footer hook)', 'promotional-footer-bar' ); ?>
										</label>
										<p class="description">
											<?php esc_html_e( 'Code will be inserted before the closing </body> tag on all pages. Better for performance.', 'promotional-footer-bar' ); ?>
										</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="pfb-footer-code">
											<?php esc_html_e( 'Footer Code', 'promotional-footer-bar' ); ?>
										</label>
									</th>
									<td>
										<textarea
											id="pfb-footer-code"
											name="footer_code"
											rows="10"
											class="large-text code"
											placeholder="<?php esc_attr_e( 'e.g., <script>/* Your tracking code */</script>', 'promotional-footer-bar' ); ?>"
											spellcheck="false"><?php echo esc_textarea( $settings['footer_code']['code'] ?? '' ); ?></textarea>
										<p class="description">
											<strong><?php esc_html_e( 'Common uses:', 'promotional-footer-bar' ); ?></strong>
											<?php esc_html_e( 'Facebook Pixel, chat widgets, deferred scripts', 'promotional-footer-bar' ); ?>
										</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<?php esc_html_e( 'Footer Priority', 'promotional-footer-bar' ); ?>
									</th>
									<td>
										<select name="footer_priority" class="regular-text">
											<option value="5" <?php selected( ! empty( $settings['footer_code']['priority'] ) ? $settings['footer_code']['priority'] : 15, 5 ); ?>>
												<?php esc_html_e( 'Before notifications (priority 5)', 'promotional-footer-bar' ); ?>
											</option>
											<option value="15" <?php selected( ! empty( $settings['footer_code']['priority'] ) ? $settings['footer_code']['priority'] : 15, 15 ); ?>>
												<?php esc_html_e( 'After notifications (priority 15) - Recommended', 'promotional-footer-bar' ); ?>
											</option>
										</select>
										<p class="description">
											<?php esc_html_e( 'Choose whether footer code runs before or after the notification bar. Most tracking scripts should run after (priority 15).', 'promotional-footer-bar' ); ?>
										</p>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<div class="notice notice-info inline" style="margin: 20px 0; padding: 12px;">
						<p>
							<strong><?php esc_html_e( 'Security Notice:', 'promotional-footer-bar' ); ?></strong>
							<?php esc_html_e( 'Only add code from trusted sources. Malicious code can compromise your site. Always test changes on a staging site first.', 'promotional-footer-bar' ); ?>
						</p>
					</div>
				</div>

				<p class="submit">
					<?php submit_button( __( 'Save Scripts Settings', 'promotional-footer-bar' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div>

		<script>
			jQuery(document).ready(function($) {
				// Initialize code editor for header code
				if (wp.codeEditor && $('#pfb-header-code').length) {
					wp.codeEditor.initialize('pfb-header-code', {
						codemirror: {
							mode: 'htmlmixed',
							lineNumbers: true,
							lineWrapping: true,
							indentUnit: 2,
							tabSize: 2
						}
					});
				}

				// Initialize code editor for footer code
				if (wp.codeEditor && $('#pfb-footer-code').length) {
					wp.codeEditor.initialize('pfb-footer-code', {
						codemirror: {
							mode: 'htmlmixed',
							lineNumbers: true,
							lineWrapping: true,
							indentUnit: 2,
							tabSize: 2
						}
					});
				}
			});
		</script>
		<?php
	}

	/**
	 * Save scripts settings
	 */
	public function save_scripts_settings() {
		// Check nonce
		if ( ! isset( $_POST['pfb_scripts_nonce'] ) || ! wp_verify_nonce( $_POST['pfb_scripts_nonce'], 'pfb_save_scripts' ) ) {
			wp_die( __( 'Security check failed', 'promotional-footer-bar' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to perform this action', 'promotional-footer-bar' ) );
		}

		// Prepare settings array
		$settings = array(
			'header_code' => array(
				'enabled' => isset( $_POST['header_enabled'] ) ? true : false,
				'code'    => $this->sanitize_tracking_code( $_POST['header_code'] ?? '' ),
			),
			'footer_code' => array(
				'enabled'  => isset( $_POST['footer_enabled'] ) ? true : false,
				'code'     => $this->sanitize_tracking_code( $_POST['footer_code'] ?? '' ),
				'priority' => in_array( $_POST['footer_priority'] ?? 15, array( 5, 15 ), true ) ? (int) $_POST['footer_priority'] : 15,
			),
		);

		// Update option
		update_option( self::OPTION_NAME, $settings, false );

		// Clear cache
		$this->clear_scripts_cache();

		// Redirect back with success message
		wp_redirect(
			add_query_arg(
				array(
					'page'    => 'footer-notifications-scripts',
					'updated' => 'true',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Inject header code
	 */
	public function inject_header_code() {
		// Don't inject in admin
		if ( is_admin() ) {
			return;
		}

		$settings = $this->get_scripts_settings();

		if ( empty( $settings['header_code']['enabled'] ) || empty( $settings['header_code']['code'] ) ) {
			return;
		}

		// Output the code (already sanitized when saved)
		echo "\n<!-- Promotional Footer Bar - Header Code -->\n";
		echo wp_unslash( $settings['header_code']['code'] );
		echo "\n<!-- / Promotional Footer Bar - Header Code -->\n";
	}

	/**
	 * Inject footer code
	 */
	public function inject_footer_code() {
		// Don't inject in admin
		if ( is_admin() ) {
			return;
		}

		$settings = $this->get_scripts_settings();

		if ( empty( $settings['footer_code']['enabled'] ) || empty( $settings['footer_code']['code'] ) ) {
			return;
		}

		// Output the code (already sanitized when saved)
		echo "\n<!-- Promotional Footer Bar - Footer Code -->\n";
		echo wp_unslash( $settings['footer_code']['code'] );
		echo "\n<!-- / Promotional Footer Bar - Footer Code -->\n";
	}
}
