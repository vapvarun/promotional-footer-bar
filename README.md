# Promotional Footer Bar

**Version:** 1.2.0
**Author:** Wbcom Designs
**License:** GPL-2.0-or-later
**WordPress.org Ready:** Yes

## Description

A powerful, user-friendly WordPress plugin that displays random promotional notifications in a sticky top or bottom bar, plus inject custom header/footer tracking codes. Perfect for promoting products, announcements, or special offers with advanced targeting and scheduling, along with Google Analytics, Facebook Pixel, and other tracking scripts.

## Features

### Promotional Notifications

- ✅ **Position Control** - Display at top or bottom of page
- ✅ **Advanced Display Rules** - Show on specific page types, hide for logged-in users
- ✅ **Template Library** - 14+ proven marketing templates across 5 categories
- ✅ **Simple Interface** - No complex settings, just add your notifications
- ✅ **Random Display** - Shows one random notification per page load
- ✅ **Up to 10 Notifications** - Manage multiple notifications easily
- ✅ **Enable/Disable** - Toggle notifications on/off without deleting
- ✅ **Smart Scheduling** - Set start and end dates for time-limited offers
- ✅ **Dismissible** - Users can close notifications (reappear after 24 hours)
- ✅ **Color Customization** - Match your brand with custom colors
- ✅ **Mobile Responsive** - Different text for desktop and mobile
- ✅ **Dual CTAs** - Primary button + optional secondary link
- ✅ **UTM Tracking** - Automatic UTM parameters for analytics

### Scripts & Tracking (NEW in v1.2.0)

- ✅ **Header Code Injection** - Add tracking codes to `<head>` via wp_head
- ✅ **Footer Code Injection** - Add tracking codes before `</body>` via wp_footer
- ✅ **Code Editor** - Syntax highlighting with WordPress CodeMirror
- ✅ **Independent Controls** - Enable/disable header and footer code separately
- ✅ **Priority Control** - Choose when footer code runs (before/after notifications)
- ✅ **Security Features** - Requires `unfiltered_html` capability, code sanitization
- ✅ **Perfect For**:
  - Google Analytics & Google Tag Manager
  - Facebook Pixel tracking
  - Meta & Google Search Console verification
  - Custom CSS styles
  - Third-party chat widgets
  - Any custom HTML/JavaScript/CSS

### Performance & Architecture

- ✅ **High Performance** - Dual-layer caching, zero DB queries after first load
- ✅ **Modular Design** - Clean separation of features (v1.2.0)
- ✅ **No External Assets** - Minimal JavaScript, inline CSS only
- ✅ **WordPress.org Ready** - Proper readme.txt, GPL license, security hardened

## Installation

1. Upload the `promotional-footer-bar` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Footer Notices' in the admin menu to configure notifications

## Usage

### Quick Start with Templates

The fastest way to get started is using the built-in template library:

1. Go to **Footer Notices** in the WordPress admin menu
2. Browse the **Notification Templates** section at the top
3. Choose from 5 categories:
   - **WordPress Themes & Plugins** - Feature Reign Theme, launch plugins, promote bundles
   - **Free Monthly Goodies** - Free plugins, themes, and resource packs
   - **Events & Community Meetups** - WordCamps, webinars, WordPress meetups
   - **Special Offers & Deals** - Black Friday, flash sales, renewal discounts
   - **Community & Support** - Facebook group invites, support links
4. Click **Use Template** on any template
5. Customize the content and URLs for your site
6. Click **Save All Notifications**

**Templates Include:**
- Wbcom Designs-specific products and services
- Proven marketing copy with social proof
- Pre-configured brand colors
- Mobile-optimized text
- Professional CTAs

### Adding Custom Notifications

1. Go to **Footer Notices** in the WordPress admin menu
2. Click **Add Notification** to create a new notification
3. Fill in the fields:
   - **Desktop Text**: Full promotional text for desktop screens
   - **Mobile Text**: Shorter text for mobile (optional)
   - **Primary Button**: Button text and URL (e.g., "Get It Free")
   - **Secondary Link**: Optional additional link (e.g., "Learn More")
   - **Schedule**: Set start and end dates for time-limited offers (optional)
   - **Dismissible**: Allow users to close the notification
   - **Colors**: Customize background, text, and button colors
4. Click **Save All Notifications**

### Managing Notifications

- **Enable/Disable**: Use the checkbox at the top of each notification
- **Remove**: Click the trash icon to delete a notification
- **Reorder**: Add/remove notifications as needed

### How Random Selection Works

- Only **enabled** notifications are shown
- Only **scheduled** notifications (within date range) are shown
- One random notification is selected **per page load**
- Different users may see different notifications
- Same user will see different notifications on page refresh

### Scheduling Notifications

Set start and end dates for time-limited offers:

- **Start Date**: Notification will only show on or after this date
- **End Date**: Notification will only show until this date (inclusive)
- Leave both empty to show the notification always
- Perfect for:
  - Flash sales
  - Event announcements
  - Seasonal promotions
  - Limited-time offers

### Dismissible Notifications

Allow users to close notifications they don't want to see:

- **Enabled by default** - Each notification can be dismissed
- **24-hour cookie** - Dismissed notifications return after 24 hours
- **Per-notification** - Each notification tracks dismissals separately
- Users see a close button (X) in the top-right corner

### Color Customization

Match your brand with custom colors:

- **Background Color** - Main bar background
- **Text Color** - Notification text color
- **Primary Button** - CTA button background
- **Secondary Button** - Optional link button background
- Uses WordPress color picker for easy selection
- Defaults match the original design

### Using Scripts & Tracking (NEW in v1.2.0)

Add custom tracking codes and scripts to your site:

1. Go to **Footer Notices → Scripts & Tracking** in WordPress admin
2. **Header Code Section**:
   - Enable header code injection
   - Paste your code (Google Analytics, verification tags, etc.)
   - Code appears in `<head>` section via `wp_head` hook
3. **Footer Code Section**:
   - Enable footer code injection
   - Paste your code (Facebook Pixel, chat widgets, etc.)
   - Choose priority: before notifications (5) or after (15 - recommended)
   - Code appears before `</body>` via `wp_footer` hook
4. Click **Save Scripts Settings**

**Common Use Cases:**

```html
<!-- Google Analytics (Header or Footer) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>

<!-- Meta Verification (Header) -->
<meta name="facebook-domain-verification" content="your-verification-code" />
<meta name="google-site-verification" content="your-verification-code" />

<!-- Facebook Pixel (Footer) -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', 'YOUR_PIXEL_ID');
  fbq('track', 'PageView');
</script>
```

**Security Notes:**
- Requires administrator account with `unfiltered_html` capability
- Code is sanitized for non-admins
- Only add code from trusted sources
- Test on staging site first

## Configuration

### Customization

All styling is inline in the plugin, but you can override with custom CSS if needed (though color customization is now built-in):

```css
/* Override background (if not using built-in colors) */
#pfb-sticky-footer {
    background: #your-color !important;
}

/* Override button color (if not using built-in colors) */
#pfb-sticky-footer .pfb-footer-cta {
    background: #your-color !important;
}
```

**Note:** Use the built-in color customization options instead of custom CSS for easier management.

### UTM Parameters

The plugin automatically adds these UTM parameters to all notification links:
- `utm_source=sitewide-notice`
- `utm_medium=sticky-footer`
- `utm_campaign=footer-notification`

## Example Notification

**Desktop Text:**
```
FREE Product Roadmap Plugin — Plan & visualize your product strategy with ease. A must-have for teams!
```

**Mobile Text:**
```
FREE Product Roadmap Plugin — Must-have for planning!
```

**Primary Button:**
- Text: `Get It Free`
- URL: `https://example.com/product/`

**Secondary Link:**
- Text: `Learn More`
- URL: `https://example.com/learn-more/`

## Technical Details

### Performance
- **Zero database queries** after first load (dual-layer caching)
- **Autoload disabled** - No performance impact on page loads
- **Minimal frontend footprint** - Inline CSS, ~1KB JavaScript (only when dismissible)
- **Cache duration** - 24 hours (transient) + request-level (object cache)
- **Modular architecture** - Separate class files for maintainability

### Architecture (v1.2.0)
```
/promotional-footer-bar/
├── promotional-footer-bar.php (1,264 lines - Notifications)
├── includes/
│   └── class-scripts-tracking.php (517 lines - Scripts & Tracking)
├── assets/
│   ├── admin-script.js
│   ├── admin-style.css
│   └── dismiss.js
└── uninstall.php
```

### Security
- ✅ Proper nonce verification for all forms
- ✅ All inputs sanitized (`sanitize_text_field`, `esc_url_raw`, custom hex color validation)
- ✅ All outputs escaped (`esc_html`, `esc_attr`, `esc_url`)
- ✅ Capability checks (`manage_options`, `unfiltered_html` for scripts)
- ✅ Direct access protection on all files
- ✅ Scripts feature requires administrator with `unfiltered_html` capability

### Compatibility
- **PHP:** 7.0+
- **WordPress:** 5.0+
- **Multisite:** Fully supported
- **CDN:** Compatible
- **Caching Plugins:** Works with WP Rocket, W3 Total Cache, etc.

## Support

For support and feature requests, contact:
- **Website**: https://wbcomdesigns.com
- **Email**: admin@wbcomdesigns.com

## Changelog

### 1.2.0 - 2025-11-24
- **NEW:** Scripts & Tracking feature - Inject custom header and footer code
- **NEW:** Header code injection via `wp_head` hook (priority 10)
- **NEW:** Footer code injection via `wp_footer` hook (priority 15)
- **NEW:** WordPress CodeMirror code editor with syntax highlighting
- **NEW:** Independent enable/disable controls for header and footer code
- **NEW:** Footer priority selection (before/after notifications)
- **NEW:** Security - Requires `unfiltered_html` capability for unrestricted code
- **NEW:** Modular architecture - Scripts feature in separate class file
- **NEW:** Help tabs for Scripts & Tracking with common use cases
- **IMPROVED:** Code organization - Main plugin file reduced from 1,700+ to 1,264 lines
- **IMPROVED:** Separated Scripts & Tracking into `/includes/class-scripts-tracking.php` (517 lines)
- **IMPROVED:** Caching system for scripts settings (1-hour object cache)
- **IMPROVED:** Admin menu structure with submenu organization
- **IMPROVED:** Uninstall cleanup includes scripts settings
- **IMPROVED:** Better file structure for future scalability

### 1.1.0 - 2025-11-21
- **NEW:** Top/Bottom positioning option for each notification
- **NEW:** Advanced display rules - show on specific page types (all pages, homepage, posts, pages)
- **NEW:** Hide for logged-in users option
- **NEW:** WordPress.org package - readme.txt with proper formatting
- **IMPROVED:** Updated plugin headers with GPL-2.0-or-later license
- **IMPROVED:** Better accessibility and security
- **IMPROVED:** Enhanced documentation
- **FIXED:** Minor CSS improvements for top positioning shadow

### 1.0.0 - 2025-10-30
- Initial release
- **Template Library** - 14 Wbcom Designs-specific templates across 5 categories
  - WordPress Themes & Plugins (3 templates)
  - Free Monthly Goodies (3 templates)
  - Events & Community Meetups (3 templates)
  - Special Offers & Deals (3 templates)
  - Community & Support (2 templates)
- Random notification display
- Up to 10 notifications
- Enable/disable functionality
- **Scheduling system** - Set start/end dates for time-limited offers
- **Dismissible notifications** - Users can close notifications (24-hour cookie)
- **Color customization** - Full brand color control
- Mobile responsive design
- Automatic UTM tracking
- **High-performance dual-layer caching** - Zero DB queries after first load
- **Optimized for large sites** - Autoload disabled, minimal frontend footprint
- **Security hardened** - Proper nonces, sanitization, and escaping
- **Translation ready** - i18n support with text domain loaded
- **Clean uninstall** - Removes all data including multisite support
- **Production ready** - Audited and approved for high-traffic sites
