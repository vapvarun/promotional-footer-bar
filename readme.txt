=== Promotional Footer Bar ===
Contributors: wbcomdesigns, vapvarun
Tags: notification bar, promotional bar, announcement, sticky bar, marketing
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display promotional notifications in a sticky bar + inject custom header/footer tracking codes with advanced scheduling and display rules.

== Description ==

**Promotional Footer Bar** is a powerful yet simple WordPress plugin that helps you display promotional notifications, announcements, or special offers in a sticky notification bar. Perfect for promoting products, announcements, events, or any important messages to your visitors.

### Key Features

#### Promotional Notifications

* **Top or Bottom Positioning** - Choose to display your notification bar at the top or bottom of the page
* **Template Library** - 14+ proven marketing templates across 5 categories to get started instantly
* **Random Display** - Shows one random notification per page load for variety
* **Smart Scheduling** - Set start and end dates for time-limited offers
* **Full Color Customization** - Match your brand with custom colors for background, text, and buttons
* **Mobile Responsive** - Different text for desktop and mobile devices
* **Advanced Display Rules** - Control where notifications appear:
  * Show on all pages, homepage only, blog posts, or pages
  * Hide for logged-in users
  * Perfect targeting for your audience
* **Dismissible Notifications** - Users can close notifications (reappear after 24 hours)
* **Dual CTAs** - Primary button + optional secondary link
* **UTM Tracking** - Automatic UTM parameters for analytics

#### Scripts & Tracking (NEW in 1.2.0)

* **Header Code Injection** - Add custom code to wp_head for tracking, verification, and analytics
* **Footer Code Injection** - Add custom code to wp_footer for performance-optimized tracking
* **Code Editor** - Syntax highlighting with WordPress CodeMirror integration
* **Enable/Disable Controls** - Toggle header and footer code independently
* **Priority Control** - Choose whether footer code runs before or after notifications
* **Security Features** - Requires `unfiltered_html` capability, code sanitization
* **Common Use Cases**:
  * Google Analytics & Tag Manager
  * Facebook Pixel tracking
  * Meta verification tags
  * Google Search Console verification
  * Custom CSS styles
  * Third-party chat widgets

#### Performance & Security

* **High Performance** - Dual-layer caching, zero DB queries after first load
* **Security First** - Proper sanitization, escaping, and nonce verification
* **Modular Architecture** - Clean code separation for easy maintenance

### Perfect For

* **Product Launches** - Announce new products or features
* **Flash Sales** - Time-limited offers with scheduling
* **Event Promotion** - WordCamps, webinars, meetups
* **Free Resources** - Promote free plugins, themes, or downloads
* **Community Building** - Invite users to Facebook groups or forums
* **Support & Documentation** - Link to help resources

### Template Categories

1. **WordPress Themes & Plugins** - Feature products, launch plugins, promote bundles
2. **Free Monthly Goodies** - Free plugins, themes, and resource packs
3. **Events & Community Meetups** - WordCamps, webinars, WordPress meetups
4. **Special Offers & Deals** - Black Friday, flash sales, renewal discounts
5. **Community & Support** - Facebook group invites, support links

### Easy to Use

#### Setting Up Notifications

1. Install and activate the plugin
2. Go to **Footer Notices → Notifications** in WordPress admin
3. Choose a template or create your own notification
4. Customize colors, text, and buttons
5. Set display rules and scheduling
6. Save and watch it appear on your site!

#### Adding Tracking Codes (NEW in 1.2.0)

1. Go to **Footer Notices → Scripts & Tracking** in WordPress admin
2. Enable header code and/or footer code
3. Paste your tracking scripts (Google Analytics, Facebook Pixel, etc.)
4. Choose footer code priority if needed
5. Save and your codes are live!

### Performance Optimized

* **Dual-layer caching** - Transient cache (24h) + object cache (request-level)
* **Zero database queries** after first load
* **Minimal frontend footprint** - Inline CSS, ~1KB JavaScript (only when dismissible)
* **Autoload disabled** - No performance impact on page loads

### Privacy & Security

* No external API calls
* No data collection
* Cookie-based dismissal (24-hour expiration)
* GDPR friendly
* Security hardened with proper sanitization and escaping

== Installation ==

### Automatic Installation

1. Log in to your WordPress admin panel
2. Navigate to **Plugins > Add New**
3. Search for "Promotional Footer Bar"
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New > Upload Plugin**
4. Choose the downloaded zip file and click **Install Now**
5. Activate the plugin

### After Activation

1. Go to **Footer Notices** in the WordPress admin menu
2. Browse the template library or create a custom notification
3. Fill in your notification details
4. Choose position (top or bottom)
5. Set display rules (where to show, who can see)
6. Customize colors to match your brand
7. Click **Save All Notifications**

== Frequently Asked Questions ==

= How many notifications can I create? =

You can create and manage up to 10 notifications. The plugin will randomly select one enabled notification to display per page load.

= Can I show the bar at the top instead of the bottom? =

Yes! Version 1.1.0 added position control. Each notification can be set to appear at the top or bottom of the page.

= Can I target specific pages? =

Yes! You can choose to show notifications on:
* All pages
* Homepage only
* Blog posts only
* Pages only
* Hide for logged-in users

= How does scheduling work? =

Set start and end dates for each notification. The notification will only appear between those dates. Leave empty to show always.

= Are notifications dismissible? =

Yes! Users can close notifications using the X button. Dismissed notifications reappear after 24 hours via cookie storage.

= Does it work with caching plugins? =

Yes! The plugin is fully compatible with WP Rocket, W3 Total Cache, and other caching plugins thanks to dual-layer caching.

= Can I use custom colors? =

Absolutely! Each notification has full color customization for:
* Background color
* Text color
* Primary button color
* Secondary button color

= Does it track clicks? =

The plugin automatically adds UTM parameters to all links for Google Analytics tracking:
* utm_source=sitewide-notice
* utm_medium=sticky-footer
* utm_campaign=footer-notification

= Is it mobile responsive? =

Yes! The plugin is fully responsive and includes separate text options for mobile and desktop.

= Does it affect page load speed? =

No! The plugin uses dual-layer caching and has minimal frontend footprint. Zero database queries after the first page load.

= What is Scripts & Tracking? (NEW in 1.2.0) =

Scripts & Tracking allows you to inject custom HTML, JavaScript, or CSS code into your site's header (wp_head) or footer (wp_footer). This is perfect for adding:
* Google Analytics or Google Tag Manager
* Facebook Pixel tracking
* Meta/Google verification tags
* Custom CSS styles
* Chat widgets and other third-party scripts

= Is the Scripts & Tracking feature secure? =

Yes! The feature requires administrator-level permissions with the `unfiltered_html` capability. Code is sanitized based on user permissions, and only trusted users can save unrestricted code.

= Can I add Google Analytics with this plugin? =

Yes! Go to Footer Notices → Scripts & Tracking, enable header code or footer code, paste your Google Analytics tracking code, and save. It will appear on all frontend pages.

= Where should I add my tracking codes - header or footer? =

* **Header** - For verification tags, critical CSS, or scripts that must load early
* **Footer** - For analytics (Google Analytics, Facebook Pixel), chat widgets, or non-critical scripts (better for performance)

Most tracking scripts work best in the footer for optimal page load speed.

== Screenshots ==

1. Admin interface with template library
2. Notification settings with color picker
3. Position and display rules options
4. Bottom notification bar example
5. Top notification bar example
6. Mobile responsive view

== Changelog ==

= 1.2.0 - 2025-11-24 =
* **NEW:** Scripts & Tracking feature - Inject custom header and footer code
* **NEW:** Header code injection via wp_head hook (priority 10)
* **NEW:** Footer code injection via wp_footer hook (priority 15)
* **NEW:** WordPress CodeMirror code editor with syntax highlighting
* **NEW:** Independent enable/disable controls for header and footer code
* **NEW:** Footer priority selection (before/after notifications)
* **NEW:** Security - Requires `unfiltered_html` capability for unrestricted code
* **NEW:** Modular architecture - Scripts feature in separate class file
* **IMPROVED:** Code organization - Main plugin file reduced from 1,700+ to 1,264 lines
* **IMPROVED:** Separated Scripts & Tracking into `/includes/class-scripts-tracking.php`
* **IMPROVED:** Caching system for scripts settings (1-hour object cache)
* **IMPROVED:** Admin menu structure with submenu organization
* **IMPROVED:** Help tabs for Scripts & Tracking with common use cases
* **IMPROVED:** Uninstall cleanup includes scripts settings

= 1.1.0 - 2025-11-21 =
* **NEW:** Top/Bottom positioning option for each notification
* **NEW:** Advanced display rules - show on specific page types
* **NEW:** Hide for logged-in users option
* **NEW:** Proper WordPress.org package with readme.txt
* **IMPROVED:** Updated plugin headers with GPL license
* **IMPROVED:** Better accessibility and security
* **FIXED:** Minor CSS improvements for top positioning

= 1.0.0 - 2025-10-30 =
* Initial release
* Template library with 14 pre-built templates
* Random notification display
* Up to 10 notifications
* Enable/disable functionality
* Scheduling system with start/end dates
* Dismissible notifications with 24-hour cookie
* Full color customization
* Mobile responsive design
* Automatic UTM tracking
* High-performance dual-layer caching
* Security hardened
* Translation ready

== Upgrade Notice ==

= 1.2.0 =
Major feature update! Adds Scripts & Tracking for custom header/footer code injection. Perfect for Google Analytics, Facebook Pixel, verification tags, and more. Modular architecture for better maintainability.

= 1.1.0 =
Major update! Adds top/bottom positioning, advanced display rules, and hide for logged-in users. Includes WordPress.org packaging.

= 1.0.0 =
Initial release with full feature set including template library, scheduling, and color customization.

== Support ==

For support and feature requests:

* **Documentation:** [View Documentation](https://docs.wbcomdesigns.com)
* **Support Portal:** [Get Support](https://wbcomdesigns.com/support/)
* **Facebook Community:** [Join Our Community](https://www.facebook.com/groups/wwbprofessionals)

== Credits ==

* Developed by [Wbcom Designs](https://wbcomdesigns.com)
* Lead Developer: [Varun Dubey](https://vapvarun.in)
