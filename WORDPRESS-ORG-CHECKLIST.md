# WordPress.org Submission Checklist

Complete guide to submit **Promotional Footer Bar** to WordPress.org plugin directory.

## Pre-Submission Checklist

### 1. Code & Files
- [x] Plugin headers complete with all required fields
- [x] GPL-2.0-or-later license specified
- [x] License URI included in headers
- [x] Version number updated (1.1.0)
- [x] Text domain matches plugin slug (`promotional-footer-bar`)
- [x] All code follows WordPress Coding Standards
- [x] Proper sanitization and escaping
- [x] Nonce verification for forms
- [x] No hardcoded database table prefixes (uses $wpdb->prefix)
- [x] No external API calls (all self-contained)
- [x] No phone-home or tracking without user consent

### 2. Documentation
- [x] readme.txt created in WordPress.org format
- [x] Short description under 150 characters
- [x] Detailed description with features
- [x] Installation instructions
- [x] FAQ section
- [x] Changelog with version history
- [x] "Tested up to" WordPress version specified (6.8)
- [x] "Requires at least" WordPress version specified (5.0)
- [x] "Requires PHP" version specified (7.0)
- [x] Screenshots described (captions)
- [ ] Screenshots created (see SCREENSHOTS.md)
- [x] Upgrade notices included

### 3. Security
- [x] No eval() or base64_decode()
- [x] No exec() or system() calls
- [x] No file operations without proper checks
- [x] SQL queries use $wpdb->prepare()
- [x] All user inputs sanitized
- [x] All outputs escaped
- [x] CSRF protection (nonces)
- [x] Capability checks (manage_options)
- [x] No hardcoded secrets or API keys

### 4. Functionality
- [x] No fatal errors
- [x] No PHP warnings or notices
- [x] Works with WordPress debug mode enabled
- [x] Compatible with latest WordPress version
- [x] Tested on PHP 7.0+
- [x] No conflicts with popular plugins
- [x] Uninstall process cleans up data
- [x] No external dependencies required

### 5. Assets & Branding
- [ ] Plugin icon created (256x256 and 128x128)
- [ ] Plugin banner created (772x250 and 1544x500 - optional)
- [x] Plugin name doesn't include "WordPress" or "Plugin"
- [x] No trademark violations
- [x] All template URLs can be customized (not hardcoded)

## Submission Process

### Step 1: Create Screenshots
Follow instructions in `SCREENSHOTS.md` to create 6 screenshots and place them in `/assets/` folder.

### Step 2: Create Plugin Icon (Optional but Recommended)
1. Design a 256x256px icon (PNG with transparency)
2. Save as `icon-256x256.png`
3. Create a 128x128px version: `icon-128x128.png`
4. Place in `/assets/` folder

Suggested icon: Megaphone or notification bell with modern design

### Step 3: Create Plugin Banner (Optional)
1. Design a 772x250px banner for plugin directory
2. Save as `banner-772x250.png`
3. Optional: Create retina version 1544x500px as `banner-1544x500.png`
4. Place in `/assets/` folder

### Step 4: Prepare Plugin ZIP
```bash
# Navigate to plugins directory
cd wp-content/plugins

# Create clean plugin folder (remove .git, node_modules, etc.)
# Create zip file
zip -r promotional-footer-bar.zip promotional-footer-bar/ \
  -x "*/.*" \
  -x "*/.git/*" \
  -x "*/node_modules/*" \
  -x "*/WORDPRESS-ORG-CHECKLIST.md" \
  -x "*/SCREENSHOTS.md"
```

### Step 5: Submit to WordPress.org

1. **Create WordPress.org Account**
   - Go to https://wordpress.org/support/register.php
   - Register with valid email

2. **Submit Plugin**
   - Go to https://wordpress.org/plugins/developers/add/
   - Upload your plugin ZIP file
   - Fill out the submission form:
     - Plugin Name: Promotional Footer Bar
     - Plugin URL: (your repository URL or leave blank)
     - Description: Brief description of the plugin
   - Submit and wait for review

3. **Review Process**
   - Usually takes 2-14 days
   - Check email for WordPress.org communications
   - Respond promptly to any questions
   - Make requested changes if needed

4. **After Approval**
   - You'll receive SVN repository access
   - Upload your plugin using SVN
   - Add screenshots to /assets/ folder in SVN
   - Tag your first release

## SVN Upload Instructions (After Approval)

You'll receive an email with SVN repository URL like:
`https://plugins.svn.wordpress.org/promotional-footer-bar/`

```bash
# Checkout SVN repository
svn co https://plugins.svn.wordpress.org/promotional-footer-bar/

# Add your files to trunk
cd promotional-footer-bar
cp -r /path/to/your/plugin/* trunk/

# Add assets (screenshots, icons, banners)
mkdir assets
cp screenshot-*.png assets/
cp icon-*.png assets/  # if you have icons
cp banner-*.png assets/  # if you have banners

# Add files to SVN
svn add trunk/*
svn add assets/*

# Commit to trunk
svn ci -m "Initial commit of Promotional Footer Bar v1.1.0"

# Create tag for version 1.1.0
svn cp trunk tags/1.1.0
svn ci -m "Tagging version 1.1.0"
```

## Post-Submission

- [ ] Monitor WordPress.org support forum
- [ ] Respond to user questions
- [ ] Fix reported bugs promptly
- [ ] Release updates via SVN
- [ ] Maintain changelog
- [ ] Keep "Tested up to" version current

## Common Rejection Reasons (Avoid These)

1. ❌ Including plugin/theme frameworks without attribution
2. ❌ Obfuscated code or base64 encoding
3. ❌ Phone-home without user consent
4. ❌ Including unrelated functionality (like ads)
5. ❌ Poor security practices
6. ❌ Using someone else's trademark
7. ❌ Incomplete readme.txt
8. ❌ Non-GPL compatible code
9. ❌ Calling external APIs without notice
10. ❌ Poorly documented or confusing code

## WordPress.org Plugin Guidelines

Read the full guidelines:
https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

Key points:
- ✅ GPL-compatible license
- ✅ No spam or affiliate links in plugin pages
- ✅ Respect trademarks
- ✅ Include complete readme.txt
- ✅ Follow WordPress Coding Standards
- ✅ Properly escape and sanitize
- ✅ Don't hijack admin menus
- ✅ Don't track users without consent

## Support Resources

- **Plugin Handbook:** https://developer.wordpress.org/plugins/
- **Plugin Developer FAQ:** https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/
- **Readme.txt Validator:** https://wordpress.org/plugins/developers/readme-validator/
- **WordPress Coding Standards:** https://developer.wordpress.org/coding-standards/wordpress-coding-standards/

## Version Control Best Practices

For ongoing development:
1. Keep development in Git
2. Use semantic versioning (MAJOR.MINOR.PATCH)
3. Maintain changelog
4. Test before each release
5. Update "Tested up to" with each WordPress release
6. Sync Git tags with SVN tags

---

**Ready to Submit?**

1. Complete all checklists above
2. Create screenshots
3. Create plugin icon (recommended)
4. Test thoroughly
5. Submit to WordPress.org
6. Wait for approval
7. Upload via SVN

Good luck! 🚀
