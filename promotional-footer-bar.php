<?php
/**
 * Plugin Name: Promotional Footer Bar
 * Plugin URI: https://wbcomdesigns.com/downloads/promotional-footer-bar/
 * Description: Display random promotional notifications in a sticky top or bottom bar. Simple interface to manage up to 10 notifications with advanced display rules and scheduling.
 * Version: 1.1.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Wbcom Designs
 * Author URI: https://wbcomdesigns.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: promotional-footer-bar
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
class Promotional_Footer_Bar {

	/**
	 * Option name for storing notifications
	 */
	const OPTION_NAME = 'pfb_notifications';

	/**
	 * Plugin version
	 */
	const VERSION = '1.1.0';

	/**
	 * Cache group name
	 */
	const CACHE_GROUP = 'promotional_footer_bar';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Load text domain for translations
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Save settings
		add_action( 'admin_post_pfb_save_notifications', array( $this, 'save_notifications' ) );

		// Display notification in footer
		add_action( 'wp_footer', array( $this, 'display_footer_notification' ) );
	}

	/**
	 * Load plugin text domain for translations
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'promotional-footer-bar', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Sanitize hex color with fallback
	 *
	 * @param string $color   Color to sanitize.
	 * @param string $default Default color if invalid.
	 * @return string Sanitized hex color.
	 */
	private function sanitize_hex_color( $color, $default = '#ffffff' ) {
		// Remove any whitespace
		$color = trim( $color );

		// Return default if empty
		if ( empty( $color ) ) {
			return $default;
		}

		// Ensure color starts with #
		if ( '#' !== substr( $color, 0, 1 ) ) {
			$color = '#' . $color;
		}

		// Check if valid hex color (3 or 6 characters after #)
		if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
			return strtolower( $color );
		}

		return $default;
	}

	/**
	 * Add admin menu page
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Footer Notifications', 'promotional-footer-bar' ),
			__( 'Footer Notices', 'promotional-footer-bar' ),
			'manage_options',
			'footer-notifications',
			array( $this, 'admin_page' ),
			'dashicons-megaphone',
			80
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function admin_scripts( $hook ) {
		if ( 'toplevel_page_footer-notifications' !== $hook ) {
			return;
		}

		// WordPress color picker.
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'pfb-admin-style',
			plugin_dir_url( __FILE__ ) . 'assets/admin-style.css',
			array(),
			self::VERSION
		);

		wp_enqueue_script(
			'pfb-admin-script',
			plugin_dir_url( __FILE__ ) . 'assets/admin-script.js',
			array( 'jquery', 'wp-color-picker' ),
			self::VERSION,
			true
		);

		wp_localize_script(
			'pfb-admin-script',
			'pfbAdmin',
			array(
				'maxNotifications' => 10,
				'minNotifications' => 1,
			)
		);
	}

	/**
	 * Get all notifications
	 */
	public function get_notifications() {
		$notifications = get_option( self::OPTION_NAME, array() );

		// Ensure we have at least one empty notification for the form
		if ( empty( $notifications ) ) {
			$notifications = array( $this->get_default_notification() );
		}

		return $notifications;
	}

	/**
	 * Get enabled notifications with caching for performance
	 *
	 * Uses WordPress transients for 24-hour caching to avoid querying
	 * and filtering notifications on every page load.
	 *
	 * @return array Enabled notifications
	 */
	public function get_enabled_notifications() {
		// Try object cache first (if available)
		$cache_key = 'pfb_enabled_notifications';
		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		// Try transient cache (24 hour cache)
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			// Also set in object cache for this request
			wp_cache_set( $cache_key, $cached, self::CACHE_GROUP, 3600 );
			return $cached;
		}

		// Not cached, load and filter
		$all_notifications = get_option( self::OPTION_NAME, array() );

		// Early return if no notifications
		if ( empty( $all_notifications ) ) {
			return array();
		}

		$enabled = array_filter(
			$all_notifications,
			function( $notification ) {
				// Must be enabled and have a title.
				if ( empty( $notification['enabled'] ) || empty( $notification['title'] ) ) {
					return false;
				}

				// Check scheduling.
				$now        = current_time( 'timestamp' );
				$start_date = ! empty( $notification['start_date'] ) ? strtotime( $notification['start_date'] ) : false;
				$end_date   = ! empty( $notification['end_date'] ) ? strtotime( $notification['end_date'] . ' 23:59:59' ) : false;

				// If start date is set and we haven't reached it yet.
				if ( $start_date && $now < $start_date ) {
					return false;
				}

				// If end date is set and we've passed it.
				if ( $end_date && $now > $end_date ) {
					return false;
				}

				return true;
			}
		);

		// Re-index array for consistent array_rand() behavior
		$enabled = array_values( $enabled );

		// Cache in both transient (persistent) and object cache (request-level)
		set_transient( $cache_key, $enabled, DAY_IN_SECONDS );
		wp_cache_set( $cache_key, $enabled, self::CACHE_GROUP, 3600 );

		return $enabled;
	}

	/**
	 * Clear notification cache (both transient and object cache)
	 */
	private function clear_cache() {
		$cache_key = 'pfb_enabled_notifications';
		delete_transient( $cache_key );
		wp_cache_delete( $cache_key, self::CACHE_GROUP );
	}

	/**
	 * Filter notifications by display rules
	 *
	 * @param array $notifications Array of notifications to filter.
	 * @return array Filtered notifications.
	 */
	private function filter_by_display_rules( $notifications ) {
		return array_filter(
			$notifications,
			function( $notification ) {
				// Check if hidden for logged-in users.
				if ( ! empty( $notification['hide_for_logged_in'] ) && is_user_logged_in() ) {
					return false;
				}

				// Check show_on rules.
				$show_on = ! empty( $notification['show_on'] ) ? $notification['show_on'] : 'all';

				switch ( $show_on ) {
					case 'homepage':
						return is_front_page();
					case 'posts':
						return is_single() && get_post_type() === 'post';
					case 'pages':
						return is_page();
					case 'all':
					default:
						return true;
				}
			}
		);
	}

	/**
	 * Get default notification structure
	 */
	private function get_default_notification() {
		return array(
			'enabled'            => true,
			'title'              => '',
			'mobile_title'       => '',
			'cta_text'           => '',
			'cta_url'            => '',
			'secondary_text'     => '',
			'secondary_url'      => '',
			'start_date'         => '',
			'end_date'           => '',
			'dismissible'        => true,
			'position'           => 'bottom',
			'show_on'            => 'all',
			'hide_for_logged_in' => false,
			'bg_color'           => '#0f172a',
			'text_color'         => '#ffffff',
			'cta_bg_color'       => '#10b981',
			'secondary_bg_color' => '#1f2937',
		);
	}

	/**
	 * Get notification templates
	 *
	 * @return array Predefined notification templates organized by category.
	 */
	private function get_notification_templates() {
		return array(
			'products' => array(
				'label' => __( 'WordPress Themes & Plugins', 'promotional-footer-bar' ),
				'templates' => array(
					array(
						'name'           => __( 'New Plugin Launch', 'promotional-footer-bar' ),
						'title'          => __( 'NEW: BuddyPress Check-ins Pro — Let Your Community Share Locations! Perfect for Social Sites', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'NEW Plugin: Check-ins Pro!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Get It Now', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/downloads/',
						'secondary_text' => __( 'View Demo', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#6366f1',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#10b981',
						'secondary_bg_color' => '#4f46e5',
					),
					array(
						'name'           => __( 'Theme Feature', 'promotional-footer-bar' ),
						'title'          => __( 'Reign Theme: Build Powerful BuddyPress Communities — Used by 10,000+ Sites Worldwide!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'Reign Theme — 10K+ Sites!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Explore Reign', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/downloads/reign-buddypress-theme/',
						'secondary_text' => __( 'See Demos', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#0f172a',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#10b981',
						'secondary_bg_color' => '#1f2937',
					),
					array(
						'name'           => __( 'Plugin Bundle', 'promotional-footer-bar' ),
						'title'          => __( 'Complete BuddyPress Bundle: 15+ Premium Plugins — Save 60% Today Only!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'BP Bundle — Save 60%!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Get Bundle', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/downloads/',
						'secondary_text' => __( 'See What\'s Included', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#dc2626',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#fbbf24',
						'secondary_bg_color' => '#991b1b',
					),
				),
			),
			'freebies' => array(
				'label' => __( 'Free Monthly Goodies', 'promotional-footer-bar' ),
				'templates' => array(
					array(
						'name'           => __( 'Free Plugin of Month', 'promotional-footer-bar' ),
						'title'          => __( 'FREE Plugin This Month: BuddyPress Activity Social Share — Share Activities on Social Media!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'FREE Plugin — Grab Now!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Download Free', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/downloads/',
						'secondary_text' => __( 'Learn More', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#059669',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#fbbf24',
						'secondary_bg_color' => '#047857',
					),
					array(
						'name'           => __( 'Free Theme', 'promotional-footer-bar' ),
						'title'          => __( 'FREE WordPress Theme: Community Starter — Perfect for Building Social Networks from Scratch!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'FREE Theme Available!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Get Free Theme', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/downloads/',
						'secondary_text' => '',
						'secondary_url'  => '',
						'bg_color'       => '#0f172a',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#10b981',
						'secondary_bg_color' => '#1f2937',
					),
					array(
						'name'           => __( 'Free Resource Pack', 'promotional-footer-bar' ),
						'title'          => __( 'FREE Resource: 50 BuddyPress Customization Snippets — Customize Your Community Site Easily!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'FREE 50 Code Snippets!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Download Now', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/',
						'secondary_text' => '',
						'secondary_url'  => '',
						'bg_color'       => '#7c3aed',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#10b981',
						'secondary_bg_color' => '#5b21b6',
					),
				),
			),
			'events' => array(
				'label' => __( 'Events & Community Meetups', 'promotional-footer-bar' ),
				'templates' => array(
					array(
						'name'           => __( 'WordCamp Meetup', 'promotional-footer-bar' ),
						'title'          => __( 'Meet Wbcom at WordCamp US 2025 — Visit Our Booth & Get Exclusive Discounts!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'Meet Us at WordCamp US!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Get Booth Pass', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/',
						'secondary_text' => __( 'Event Details', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#0f172a',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#f59e0b',
						'secondary_bg_color' => '#1f2937',
					),
					array(
						'name'           => __( 'Free Webinar', 'promotional-footer-bar' ),
						'title'          => __( 'FREE Webinar: Building Profitable BuddyPress Communities — Join 500+ Site Owners Live!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'FREE Webinar — Join Live!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Register Free', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/',
						'secondary_text' => __( 'View Agenda', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#1e40af',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#10b981',
						'secondary_bg_color' => '#1e3a8a',
					),
					array(
						'name'           => __( 'Community Meetup', 'promotional-footer-bar' ),
						'title'          => __( 'WordPress Meetup: Building Social Networks — Share Your Experience & Learn from Others!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'WP Meetup — Join Us!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'RSVP Now', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/',
						'secondary_text' => '',
						'secondary_url'  => '',
						'bg_color'       => '#0f172a',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#3b82f6',
						'secondary_bg_color' => '#1f2937',
					),
				),
			),
			'offers' => array(
				'label' => __( 'Special Offers & Deals', 'promotional-footer-bar' ),
				'templates' => array(
					array(
						'name'           => __( 'Black Friday Sale', 'promotional-footer-bar' ),
						'title'          => __( 'BLACK FRIDAY: 50% OFF All WordPress Themes & Plugins — Biggest Sale of the Year!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'BLACK FRIDAY: 50% OFF!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Shop Now', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/downloads/',
						'secondary_text' => __( 'View Deals', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#000000',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#dc2626',
						'secondary_bg_color' => '#991b1b',
					),
					array(
						'name'           => __( 'Limited Time Offer', 'promotional-footer-bar' ),
						'title'          => __( 'FLASH SALE: 30% OFF BuddyPress Plugins — Ends in 24 Hours! Don\'t Miss Out', 'promotional-footer-bar' ),
						'mobile_title'   => __( '30% OFF — 24 Hours Only!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Grab Deal', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/downloads/',
						'secondary_text' => '',
						'secondary_url'  => '',
						'bg_color'       => '#dc2626',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#fbbf24',
						'secondary_bg_color' => '#991b1b',
					),
					array(
						'name'           => __( 'Renewal Discount', 'promotional-footer-bar' ),
						'title'          => __( 'Renew Your License & Save 25% — Keep Your Plugins Updated with Premium Support!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'Renewal: Save 25%!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Renew Now', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/my-account/',
						'secondary_text' => __( 'Learn More', 'promotional-footer-bar' ),
						'secondary_url'  => '',
						'bg_color'       => '#0f172a',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#10b981',
						'secondary_bg_color' => '#1f2937',
					),
				),
			),
			'community' => array(
				'label' => __( 'Community & Support', 'promotional-footer-bar' ),
				'templates' => array(
					array(
						'name'           => __( 'Join Facebook Group', 'promotional-footer-bar' ),
						'title'          => __( 'Join 15,000+ WordPress Developers in Our Community — Get Help, Share Tips & Network!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'Join 15K+ Developers!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Join Group', 'promotional-footer-bar' ),
						'cta_url'        => 'https://www.facebook.com/groups/wwbprofessionals',
						'secondary_text' => '',
						'secondary_url'  => '',
						'bg_color'       => '#1877f2',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#ffffff',
						'secondary_bg_color' => '#0c63d4',
					),
					array(
						'name'           => __( 'Get Support', 'promotional-footer-bar' ),
						'title'          => __( 'Need Help? Our Expert Support Team is Ready — Get Quick Answers to Your Questions!', 'promotional-footer-bar' ),
						'mobile_title'   => __( 'Need Help? Contact Us!', 'promotional-footer-bar' ),
						'cta_text'       => __( 'Get Support', 'promotional-footer-bar' ),
						'cta_url'        => 'https://wbcomdesigns.com/support/',
						'secondary_text' => __( 'View Docs', 'promotional-footer-bar' ),
						'secondary_url'  => 'https://docs.wbcomdesigns.com/',
						'bg_color'       => '#0f172a',
						'text_color'     => '#ffffff',
						'cta_bg_color'   => '#10b981',
						'secondary_bg_color' => '#1f2937',
					),
				),
			),
		);
	}

	/**
	 * Admin page HTML
	 */
	public function admin_page() {
		$notifications = $this->get_notifications();
		$templates     = $this->get_notification_templates();
		?>
		<div class="wrap pfb-admin-wrap">
			<h1><?php esc_html_e( 'Footer Notifications', 'promotional-footer-bar' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Add up to 10 notifications. One will be randomly selected and displayed in the sticky footer on each page load.', 'promotional-footer-bar' ); ?>
			</p>

			<!-- Template Library -->
			<div class="pfb-template-library">
				<h2>
					<span class="dashicons dashicons-book"></span>
					<?php esc_html_e( 'Notification Templates', 'promotional-footer-bar' ); ?>
				</h2>
				<p class="description">
					<?php esc_html_e( 'Quick-start with proven marketing templates. Click "Use Template" to populate a new notification.', 'promotional-footer-bar' ); ?>
				</p>

				<div class="pfb-template-categories">
					<?php foreach ( $templates as $category_key => $category ) : ?>
						<div class="pfb-template-category">
							<h3><?php echo esc_html( $category['label'] ); ?></h3>
							<div class="pfb-template-grid">
								<?php foreach ( $category['templates'] as $template ) : ?>
									<div class="pfb-template-card">
										<h4><?php echo esc_html( $template['name'] ); ?></h4>
										<p class="pfb-template-preview"><?php echo esc_html( wp_trim_words( $template['title'], 12 ) ); ?></p>
										<button type="button" class="button button-secondary pfb-use-template" data-template="<?php echo esc_attr( wp_json_encode( $template ) ); ?>">
											<span class="dashicons dashicons-plus-alt"></span>
											<?php esc_html_e( 'Use Template', 'promotional-footer-bar' ); ?>
										</button>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="pfb-notifications-form">
				<?php wp_nonce_field( 'pfb_save_notifications', 'pfb_nonce' ); ?>
				<input type="hidden" name="action" value="pfb_save_notifications">

				<div id="pfb-notifications-container">
					<?php
					$index = 0;
					foreach ( $notifications as $notification ) :
						$this->render_notification_row( $notification, $index );
						$index++;
					endforeach;
					?>
				</div>

				<div class="pfb-actions">
					<button type="button" class="button button-secondary" id="pfb-add-notification">
						<span class="dashicons dashicons-plus-alt"></span>
						<?php esc_html_e( 'Add Notification', 'promotional-footer-bar' ); ?>
					</button>

					<div class="pfb-primary-actions">
						<?php submit_button( __( 'Save All Notifications', 'promotional-footer-bar' ), 'primary', 'submit', false ); ?>
					</div>
				</div>
			</form>

			<!-- Template for new notification rows -->
			<script type="text/template" id="pfb-notification-template">
				<?php $this->render_notification_row( $this->get_default_notification(), '__INDEX__' ); ?>
			</script>
		</div>
		<?php
	}

	/**
	 * Render a single notification row
	 */
	private function render_notification_row( $notification, $index ) {
		$enabled = isset( $notification['enabled'] ) ? (bool) $notification['enabled'] : true;
		?>
		<div class="pfb-notification-row" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="pfb-notification-header">
				<div class="pfb-notification-toggle">
					<label>
						<input type="checkbox"
							name="notifications[<?php echo esc_attr( $index ); ?>][enabled]"
							value="1"
							<?php checked( $enabled ); ?>
							class="pfb-enable-toggle">
						<span class="pfb-toggle-label">
							<?php esc_html_e( 'Enabled', 'promotional-footer-bar' ); ?>
						</span>
					</label>
				</div>
				<button type="button" class="button button-link pfb-remove-notification">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Remove', 'promotional-footer-bar' ); ?>
				</button>
			</div>

			<div class="pfb-notification-content">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="pfb-title-<?php echo esc_attr( $index ); ?>">
								<?php esc_html_e( 'Desktop Text', 'promotional-footer-bar' ); ?>
							</label>
						</th>
						<td>
							<input type="text"
								id="pfb-title-<?php echo esc_attr( $index ); ?>"
								name="notifications[<?php echo esc_attr( $index ); ?>][title]"
								value="<?php echo esc_attr( $notification['title'] ?? '' ); ?>"
								class="large-text"
								placeholder="<?php esc_attr_e( 'e.g., FREE Product Roadmap Plugin — Plan & visualize your product strategy', 'promotional-footer-bar' ); ?>">
							<p class="description">
								<?php esc_html_e( 'Full text displayed on desktop screens', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="pfb-mobile-title-<?php echo esc_attr( $index ); ?>">
								<?php esc_html_e( 'Mobile Text', 'promotional-footer-bar' ); ?>
							</label>
						</th>
						<td>
							<input type="text"
								id="pfb-mobile-title-<?php echo esc_attr( $index ); ?>"
								name="notifications[<?php echo esc_attr( $index ); ?>][mobile_title]"
								value="<?php echo esc_attr( $notification['mobile_title'] ?? '' ); ?>"
								class="large-text"
								placeholder="<?php esc_attr_e( 'e.g., FREE Product Roadmap — Must-have for planning!', 'promotional-footer-bar' ); ?>">
							<p class="description">
								<?php esc_html_e( 'Shorter text for mobile devices (optional - uses desktop text if empty)', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="pfb-cta-text-<?php echo esc_attr( $index ); ?>">
								<?php esc_html_e( 'Primary Button', 'promotional-footer-bar' ); ?>
							</label>
						</th>
						<td>
							<div class="pfb-button-group">
								<input type="text"
									id="pfb-cta-text-<?php echo esc_attr( $index ); ?>"
									name="notifications[<?php echo esc_attr( $index ); ?>][cta_text]"
									value="<?php echo esc_attr( $notification['cta_text'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Button Text (e.g., Get It Free)', 'promotional-footer-bar' ); ?>"
									class="regular-text">

								<input type="url"
									id="pfb-cta-url-<?php echo esc_attr( $index ); ?>"
									name="notifications[<?php echo esc_attr( $index ); ?>][cta_url]"
									value="<?php echo esc_attr( $notification['cta_url'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Button URL', 'promotional-footer-bar' ); ?>"
									class="regular-text">
							</div>
							<p class="description">
								<?php esc_html_e( 'Primary call-to-action button (green)', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="pfb-secondary-text-<?php echo esc_attr( $index ); ?>">
								<?php esc_html_e( 'Secondary Link', 'promotional-footer-bar' ); ?>
							</label>
						</th>
						<td>
							<div class="pfb-button-group">
								<input type="text"
									id="pfb-secondary-text-<?php echo esc_attr( $index ); ?>"
									name="notifications[<?php echo esc_attr( $index ); ?>][secondary_text]"
									value="<?php echo esc_attr( $notification['secondary_text'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Link Text (e.g., Learn More)', 'promotional-footer-bar' ); ?>"
									class="regular-text">

								<input type="url"
									id="pfb-secondary-url-<?php echo esc_attr( $index ); ?>"
									name="notifications[<?php echo esc_attr( $index ); ?>][secondary_url]"
									value="<?php echo esc_attr( $notification['secondary_url'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Link URL', 'promotional-footer-bar' ); ?>"
									class="regular-text">
							</div>
							<p class="description">
								<?php esc_html_e( 'Optional secondary link (gray button)', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Schedule', 'promotional-footer-bar' ); ?>
						</th>
						<td>
							<div class="pfb-button-group">
								<input type="date"
									id="pfb-start-date-<?php echo esc_attr( $index ); ?>"
									name="notifications[<?php echo esc_attr( $index ); ?>][start_date]"
									value="<?php echo esc_attr( $notification['start_date'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'Start Date', 'promotional-footer-bar' ); ?>"
									class="regular-text">

								<input type="date"
									id="pfb-end-date-<?php echo esc_attr( $index ); ?>"
									name="notifications[<?php echo esc_attr( $index ); ?>][end_date]"
									value="<?php echo esc_attr( $notification['end_date'] ?? '' ); ?>"
									placeholder="<?php esc_attr_e( 'End Date', 'promotional-footer-bar' ); ?>"
									class="regular-text">
							</div>
							<p class="description">
								<?php esc_html_e( 'Optional: Set start and end dates for this notification. Leave empty to show always.', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Position', 'promotional-footer-bar' ); ?>
						</th>
						<td>
							<label style="margin-right: 20px;">
								<input type="radio"
									name="notifications[<?php echo esc_attr( $index ); ?>][position]"
									value="top"
									<?php checked( ! empty( $notification['position'] ) ? $notification['position'] : 'bottom', 'top' ); ?>>
								<?php esc_html_e( 'Top of page', 'promotional-footer-bar' ); ?>
							</label>
							<label>
								<input type="radio"
									name="notifications[<?php echo esc_attr( $index ); ?>][position]"
									value="bottom"
									<?php checked( ! empty( $notification['position'] ) ? $notification['position'] : 'bottom', 'bottom' ); ?>>
								<?php esc_html_e( 'Bottom of page', 'promotional-footer-bar' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Choose where the notification bar appears on the page.', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Display Rules', 'promotional-footer-bar' ); ?>
						</th>
						<td>
							<div style="margin-bottom: 12px;">
								<label for="pfb-show-on-<?php echo esc_attr( $index ); ?>" style="font-weight: 500; display: block; margin-bottom: 5px;">
									<?php esc_html_e( 'Show On', 'promotional-footer-bar' ); ?>
								</label>
								<select id="pfb-show-on-<?php echo esc_attr( $index ); ?>" name="notifications[<?php echo esc_attr( $index ); ?>][show_on]" class="regular-text">
									<option value="all" <?php selected( ! empty( $notification['show_on'] ) ? $notification['show_on'] : 'all', 'all' ); ?>>
										<?php esc_html_e( 'All pages', 'promotional-footer-bar' ); ?>
									</option>
									<option value="homepage" <?php selected( ! empty( $notification['show_on'] ) ? $notification['show_on'] : 'all', 'homepage' ); ?>>
										<?php esc_html_e( 'Homepage only', 'promotional-footer-bar' ); ?>
									</option>
									<option value="posts" <?php selected( ! empty( $notification['show_on'] ) ? $notification['show_on'] : 'all', 'posts' ); ?>>
										<?php esc_html_e( 'Blog posts only', 'promotional-footer-bar' ); ?>
									</option>
									<option value="pages" <?php selected( ! empty( $notification['show_on'] ) ? $notification['show_on'] : 'all', 'pages' ); ?>>
										<?php esc_html_e( 'Pages only', 'promotional-footer-bar' ); ?>
									</option>
								</select>
							</div>
							<div>
								<label>
									<input type="checkbox"
										name="notifications[<?php echo esc_attr( $index ); ?>][hide_for_logged_in]"
										value="1"
										<?php checked( ! empty( $notification['hide_for_logged_in'] ) ); ?>>
									<?php esc_html_e( 'Hide for logged-in users', 'promotional-footer-bar' ); ?>
								</label>
							</div>
							<p class="description">
								<?php esc_html_e( 'Control where and to whom this notification appears.', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Options', 'promotional-footer-bar' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox"
									name="notifications[<?php echo esc_attr( $index ); ?>][dismissible]"
									value="1"
									<?php checked( ! empty( $notification['dismissible'] ) ? $notification['dismissible'] : true ); ?>>
								<?php esc_html_e( 'Allow users to dismiss this notification', 'promotional-footer-bar' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'If checked, users can close the notification. It will reappear after 24 hours.', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Colors', 'promotional-footer-bar' ); ?>
						</th>
						<td>
							<div class="pfb-color-group">
								<div class="pfb-color-field">
									<label>
										<?php esc_html_e( 'Select Color for Background', 'promotional-footer-bar' ); ?>
										<input type="text"
											name="notifications[<?php echo esc_attr( $index ); ?>][bg_color]"
											value="<?php echo esc_attr( $notification['bg_color'] ?? '#0f172a' ); ?>"
											class="pfb-color-picker"
											data-default-color="#0f172a">
									</label>
								</div>

								<div class="pfb-color-field">
									<label>
										<?php esc_html_e( 'Select Color for Text', 'promotional-footer-bar' ); ?>
										<input type="text"
											name="notifications[<?php echo esc_attr( $index ); ?>][text_color]"
											value="<?php echo esc_attr( $notification['text_color'] ?? '#ffffff' ); ?>"
											class="pfb-color-picker"
											data-default-color="#ffffff">
									</label>
								</div>

								<div class="pfb-color-field">
									<label>
										<?php esc_html_e( 'Select Color for Primary Button', 'promotional-footer-bar' ); ?>
										<input type="text"
											name="notifications[<?php echo esc_attr( $index ); ?>][cta_bg_color]"
											value="<?php echo esc_attr( $notification['cta_bg_color'] ?? '#10b981' ); ?>"
											class="pfb-color-picker"
											data-default-color="#10b981">
									</label>
								</div>

								<div class="pfb-color-field">
									<label>
										<?php esc_html_e( 'Select Color for Secondary Link', 'promotional-footer-bar' ); ?>
										<input type="text"
											name="notifications[<?php echo esc_attr( $index ); ?>][secondary_bg_color]"
											value="<?php echo esc_attr( $notification['secondary_bg_color'] ?? '#1f2937' ); ?>"
											class="pfb-color-picker"
											data-default-color="#1f2937">
									</label>
								</div>
							</div>
							<p class="description">
								<?php esc_html_e( 'Customize colors to match your brand. Each color controls a specific part of the notification bar.', 'promotional-footer-bar' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Save notifications
	 */
	public function save_notifications() {
		// Check nonce
		if ( ! isset( $_POST['pfb_nonce'] ) || ! wp_verify_nonce( $_POST['pfb_nonce'], 'pfb_save_notifications' ) ) {
			wp_die( __( 'Security check failed', 'promotional-footer-bar' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to perform this action', 'promotional-footer-bar' ) );
		}

		$notifications = array();

		if ( isset( $_POST['notifications'] ) && is_array( $_POST['notifications'] ) ) {
			foreach ( $_POST['notifications'] as $notification ) {
				// Sanitize and validate.
				$clean_notification = array(
					'enabled'            => isset( $notification['enabled'] ) ? true : false,
					'title'              => sanitize_text_field( $notification['title'] ?? '' ),
					'mobile_title'       => sanitize_text_field( $notification['mobile_title'] ?? '' ),
					'cta_text'           => sanitize_text_field( $notification['cta_text'] ?? '' ),
					'cta_url'            => esc_url_raw( $notification['cta_url'] ?? '' ),
					'secondary_text'     => sanitize_text_field( $notification['secondary_text'] ?? '' ),
					'secondary_url'      => esc_url_raw( $notification['secondary_url'] ?? '' ),
					'start_date'         => sanitize_text_field( $notification['start_date'] ?? '' ),
					'end_date'           => sanitize_text_field( $notification['end_date'] ?? '' ),
					'dismissible'        => isset( $notification['dismissible'] ) ? true : false,
					'position'           => in_array( $notification['position'] ?? 'bottom', array( 'top', 'bottom' ), true ) ? $notification['position'] : 'bottom',
					'show_on'            => in_array( $notification['show_on'] ?? 'all', array( 'all', 'homepage', 'posts', 'pages' ), true ) ? $notification['show_on'] : 'all',
					'hide_for_logged_in' => isset( $notification['hide_for_logged_in'] ) ? true : false,
					'bg_color'           => $this->sanitize_hex_color( $notification['bg_color'] ?? '', '#0f172a' ),
					'text_color'         => $this->sanitize_hex_color( $notification['text_color'] ?? '', '#ffffff' ),
					'cta_bg_color'       => $this->sanitize_hex_color( $notification['cta_bg_color'] ?? '', '#10b981' ),
					'secondary_bg_color' => $this->sanitize_hex_color( $notification['secondary_bg_color'] ?? '', '#1f2937' ),
				);

				// Only save if it has at least a title.
				if ( ! empty( $clean_notification['title'] ) ) {
					$notifications[] = $clean_notification;
				}
			}
		}

		// Update with autoload=false to prevent loading on every page (performance optimization for large sites)
		update_option( self::OPTION_NAME, $notifications, false );

		// Clear cache when saving
		$this->clear_cache();

		// Redirect back with success message
		wp_redirect( add_query_arg(
			array(
				'page'    => 'footer-notifications',
				'updated' => 'true',
			),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Display random notification in footer
	 */
	public function display_footer_notification() {
		// Don't show in admin.
		if ( is_admin() ) {
			return;
		}

		// Get enabled notifications (cached for performance).
		$enabled_notifications = $this->get_enabled_notifications();

		if ( empty( $enabled_notifications ) ) {
			return;
		}

		// Filter notifications based on display rules.
		$enabled_notifications = $this->filter_by_display_rules( $enabled_notifications );

		if ( empty( $enabled_notifications ) ) {
			return;
		}

		// Allow filtering which pages show notifications (for performance).
		if ( ! apply_filters( 'pfb_show_notification', true ) ) {
			return;
		}

		// Pick a random notification.
		$notification = $enabled_notifications[ array_rand( $enabled_notifications ) ];

		// Check if user has dismissed this notification.
		$cookie_name = 'pfb_dismissed_' . md5( wp_json_encode( $notification ) );
		if ( ! empty( $notification['dismissible'] ) && isset( $_COOKIE[ $cookie_name ] ) ) {
			return;
		}

		// Get colors and position.
		$bg_color         = ! empty( $notification['bg_color'] ) ? $notification['bg_color'] : '#0f172a';
		$text_color       = ! empty( $notification['text_color'] ) ? $notification['text_color'] : '#ffffff';
		$cta_bg_color     = ! empty( $notification['cta_bg_color'] ) ? $notification['cta_bg_color'] : '#10b981';
		$secondary_bg     = ! empty( $notification['secondary_bg_color'] ) ? $notification['secondary_bg_color'] : '#1f2937';
		$is_dismissible   = ! empty( $notification['dismissible'] );
		$position         = ! empty( $notification['position'] ) ? $notification['position'] : 'bottom';

		// Add UTM parameters to URLs.
		$cta_url       = $this->add_utm_params( $notification['cta_url'] );
		$secondary_url = ! empty( $notification['secondary_url'] ) ? $this->add_utm_params( $notification['secondary_url'] ) : '';

		// Enqueue dismiss script if needed.
		if ( $is_dismissible ) {
			wp_enqueue_script(
				'pfb-dismiss',
				plugin_dir_url( __FILE__ ) . 'assets/dismiss.js',
				array(),
				self::VERSION,
				true
			);
		}

		?>
		<!-- Promotional Footer Bar -->
		<div id="pfb-sticky-footer" role="region" aria-label="<?php esc_attr_e( 'Promotional notification', 'promotional-footer-bar' ); ?>" data-cookie="<?php echo esc_attr( $cookie_name ); ?>">
			<span class="pfb-promo-text pfb-desktop-text">
				<?php echo wp_kses_post( $notification['title'] ); ?>
			</span>

			<?php if ( ! empty( $notification['mobile_title'] ) ) : ?>
				<span class="pfb-promo-text pfb-mobile-text">
					<?php echo wp_kses_post( $notification['mobile_title'] ); ?>
				</span>
			<?php endif; ?>

			<div class="pfb-footer-actions">
				<?php if ( ! empty( $notification['cta_text'] ) && ! empty( $notification['cta_url'] ) ) : ?>
					<a class="pfb-footer-cta" href="<?php echo esc_url( $cta_url ); ?>">
						<?php echo esc_html( $notification['cta_text'] ); ?>
					</a>
				<?php endif; ?>

				<?php if ( ! empty( $notification['secondary_text'] ) && ! empty( $secondary_url ) ) : ?>
					<a class="pfb-footer-link" href="<?php echo esc_url( $secondary_url ); ?>">
						<?php echo esc_html( $notification['secondary_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $is_dismissible ) : ?>
				<button class="pfb-close" aria-label="<?php esc_attr_e( 'Close notification', 'promotional-footer-bar' ); ?>" title="<?php esc_attr_e( 'Close', 'promotional-footer-bar' ); ?>">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M12.854 3.646a.5.5 0 0 0-.708-.708L8 7.293 3.854 3.146a.5.5 0 1 0-.708.708L7.293 8l-4.147 4.146a.5.5 0 0 0 .708.708L8 8.707l4.146 4.147a.5.5 0 0 0 .708-.708L8.707 8l4.147-4.146z" fill="currentColor"/></svg>
				</button>
			<?php endif; ?>
		</div>

		<style>
			#pfb-sticky-footer {
				position: fixed; <?php echo ( 'top' === $position ) ? 'top: 0;' : 'bottom: 0;'; ?> left: 0; right: 0;
				background: <?php echo esc_attr( $bg_color ); ?>;
				color: <?php echo esc_attr( $text_color ); ?>;
				padding: .7rem 1rem;
				font: 500 15px/1.4 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
				z-index: 99999;
				display: flex; flex-wrap: wrap;
				justify-content: center; align-items: center; gap: .75rem 1rem;
				text-align: center;
				box-shadow: <?php echo ( 'top' === $position ) ? '0 2px 8px rgba(0,0,0,.15)' : '0 -2px 8px rgba(0,0,0,.15)'; ?>;
			}
			.pfb-promo-text { margin: 0; }
			.pfb-desktop-text { display: inline; }
			.pfb-mobile-text  { display: none; }

			.pfb-footer-actions {
				display: inline-flex; gap: .6rem; align-items: center;
				flex: 0 0 auto;
			}

			/* Close button */
			.pfb-close {
				background: transparent;
				border: none;
				color: <?php echo esc_attr( $text_color ); ?>;
				cursor: pointer;
				padding: .25rem;
				margin-left: .5rem;
				opacity: .7;
				transition: opacity .2s ease;
				display: inline-flex;
				align-items: center;
			}
			.pfb-close:hover {
				opacity: 1;
			}

			/* Primary CTA button */
			#pfb-sticky-footer .pfb-footer-cta {
				display: inline-flex; align-items: center; justify-content: center;
				padding: .48rem 1.2rem; border-radius: 8px;
				font-weight: 700; line-height: 1;
				text-decoration: none !important;
				border: none; cursor: pointer;
				background: <?php echo esc_attr( $cta_bg_color ); ?>;
				color: #ffffff !important;
				transition: transform .15s ease, opacity .2s ease;
			}
			#pfb-sticky-footer .pfb-footer-cta:hover {
				opacity: .9;
				transform: translateY(-1px);
			}
			#pfb-sticky-footer .pfb-footer-cta:focus {
				outline: 2px solid <?php echo esc_attr( $text_color ); ?>;
				outline-offset: 2px;
			}

			/* Secondary link */
			#pfb-sticky-footer a.pfb-footer-link {
				display: inline-flex; align-items: center; justify-content: center;
				padding: .48rem 1rem; border-radius: 8px;
				font-weight: 600; line-height: 1;
				text-decoration: none !important;
				background: <?php echo esc_attr( $secondary_bg ); ?>;
				color: #ffffff !important;
				border: 1px solid rgba(255,255,255,.12);
				transition: opacity .2s ease;
			}
			#pfb-sticky-footer a.pfb-footer-link:hover {
				opacity: .9;
			}
			#pfb-sticky-footer a.pfb-footer-link:focus {
				outline: 2px solid <?php echo esc_attr( $text_color ); ?>;
				outline-offset: 2px;
			}

			/* Mobile responsive */
			@media (max-width: 600px) {
				#pfb-sticky-footer {
					font-size: 14px;
					flex-direction: column;
					padding: .6rem 1rem;
				}
				.pfb-desktop-text { display: none; }
				.pfb-mobile-text  { display: inline; }
				.pfb-footer-actions { width: 100%; }
				#pfb-sticky-footer .pfb-footer-cta,
				#pfb-sticky-footer a.pfb-footer-link {
					flex: 1 1 auto;
					text-align: center;
				}
			}
		</style>
		<?php
	}

	/**
	 * Add UTM parameters to URL
	 */
	private function add_utm_params( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		return add_query_arg(
			array(
				'utm_source'   => 'sitewide-notice',
				'utm_medium'   => 'sticky-footer',
				'utm_campaign' => 'footer-notification',
			),
			$url
		);
	}
}

// Initialize the plugin
new Promotional_Footer_Bar();
