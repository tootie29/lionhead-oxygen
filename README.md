# Lionhead Digital Custom Functionality

A comprehensive WordPress plugin that adds security, performance optimization, custom functionality, and Oxygen Builder integration features to your website.

## What This Plugin Does

This plugin is like a toolbox that adds many useful features to your WordPress website. It helps make your site:

-   **More Secure** - Protects against hackers and malware
-   **Faster** - Optimizes how your site loads
-   **More Powerful** - Adds custom features for Oxygen Builder
-   **Better Organized** - Adds custom content types and tools

---

## Features Overview

### 🔒 Security Features

**What it does:** Protects your website from hackers and attacks.

**Key protections:**

-   **File Editing Protection** - Prevents hackers from editing your theme/plugin files through the admin panel
-   **File Modification Protection** - Prevents installation and updates of plugins/themes through admin (DISALLOW_FILE_MODS)
-   **Automatic Updater Disabled** - Disables automatic WordPress updates for manual control (AUTOMATIC_UPDATER_DISABLED)
-   **Automatic wp-config.php Security** - Automatically adds security constants to wp-config.php on plugin activation
-   **Version Hiding** - Hides WordPress version number from attackers (they can't see what version you're using)
-   **Login Security** - Hides login error messages and limits login attempts to prevent brute force attacks
-   **XML-RPC Protection** - Disables XML-RPC to prevent attacks through that method
-   **Security Headers** - Adds security headers to protect against common attacks
-   **Directory Browsing** - Prevents people from browsing your file directories
-   **Suspicious Query Protection** - Blocks dangerous queries that could be SQL injection or XSS attacks
-   **Author Scanning Protection** - Prevents attackers from finding usernames by scanning author pages
-   **File Upload Security** - Restricts dangerous file types from being uploaded
-   **Comment Security** - Sanitizes comment data to prevent malicious code
-   **.htaccess Security Rules** - Automatically adds security rules to .htaccess file

**How it works:** All these protections run automatically in the background. Security constants are automatically added to wp-config.php when the plugin is activated (if the file is writable). You can also manage security settings through the Security Config page under Tools.

---

### ⚡ Performance Optimization

**What it does:** Makes your website load faster and perform better.

**Key optimizations:**

-   **LCP Image Preloading** - Preloads the main image on pages so it appears faster (improves mobile performance)
-   **Resource Hints** - Tells the browser to connect to external services early (like Google Fonts, CDNs)
-   **Font Display Optimization** - Uses `font-display: swap` so text appears immediately while fonts load

**How it works:** The plugin automatically optimizes how images and fonts load. On mobile devices, it uses smaller image sizes for faster loading while keeping full quality on desktop.

---

### 🖼️ Image Optimization

**What it does:** Makes images load faster and use less bandwidth.

**Key features:**

-   **Responsive Image Sizes** - Automatically serves the right image size for mobile, tablet, and desktop
-   **Retina Support** - Provides high-quality images for high-resolution screens
-   **Image Size Management** - Limits maximum image dimensions to prevent huge files

**How it works:** When you upload an image, WordPress creates multiple sizes. This plugin ensures the correct size is served based on the user's device, making pages load faster.

---

### 📝 Custom Post Types & Taxonomies

**What it does:** Adds special content types for organizing your content.

**Custom Post Types:**

-   **Case Results** - A special content type for showcasing legal case results with its own menu in WordPress admin
-   **Testimonials** - A content type for displaying customer testimonials with its own menu in WordPress admin

**Custom Taxonomies:**

-   **Case Result Category** - Categories for organizing case results
-   **Case Type** - Types of cases (e.g., Personal Injury, Criminal Defense, etc.)
-   **Testimonial Category** - Categories for organizing testimonials

**How it works:** These appear in your WordPress admin menu. You can create and organize case results and testimonials just like you would with regular posts, but they're kept separate and organized.

---

### 🎨 Custom Fonts

**What it does:** Allows you to use custom fonts in Oxygen Builder.

**Key features:**

-   **Automatic Font Detection** - Finds all font files in `wp-content/uploads/fonts/` folder
-   **Multiple Format Support** - Supports EOT, OTF, SVG, TTF, WOFF, and WOFF2 formats
-   **Oxygen Builder Integration** - Custom fonts appear in Oxygen Builder's font selector
-   **Optimized Loading** - Uses `font-display: swap` for better performance

**How to use:**

1. Upload your font files to `wp-content/uploads/fonts/` folder
2. Fonts will automatically appear in Oxygen Builder
3. The plugin creates optimized CSS for the fonts

---

### 🧩 Oxygen Builder Integration

**What it does:** Adds custom conditions and features for Oxygen Builder.

**Custom Conditions:**

-   **Previous Post URL Empty** - Check if there's no previous post (useful for navigation)
-   **Next Post URL Empty** - Check if there's no next post (useful for navigation)
-   **Current Post ID** - Compare the current page/post ID with a specific ID (supports ==, !=, >=, <=, >, < operators)

**How to use:**

1. In Oxygen Builder, go to any element's conditions
2. You'll see these new conditions in the list
3. Use them to show/hide elements based on post navigation or specific post IDs

**Example:** Hide "Next Post" button when there's no next post, or show special content only on a specific post ID.

---

### 🚀 Lazy Content Loading

**What it does:** Loads sections of your page only when they're about to be visible, making pages load faster.

**Key features:**

-   **Automatic Detection** - Looks for sections with `lazy-section` class
-   **Fallback Support** - If no `lazy-section` found, automatically uses `.section` class
-   **Smooth Loading** - Sections fade in smoothly when they load
-   **Mobile & Desktop** - Works on all devices (configurable)

**How to use:**

1. In Oxygen Builder, add the class `lazy-section` to any section you want to lazy load
2. If you don't add the class, all sections with `.section` class will be lazy loaded automatically
3. Sections will load when they're about 50px away from entering the viewport

**Benefits:** Reduces initial page load time by only loading content that's visible or about to be visible.

---

### 🛠️ Helper Functions

**What it does:** Provides useful functions that can be used throughout your site.

**Available Functions:**

-   `lhd_get_post_id()` - Gets the current post ID (or a specific post ID)
-   `lhd_get_permalink()` - Gets the permalink (URL) for a post
-   `lhd_get_case_result_category_name()` - Gets the category name for a case result
-   `lhd_get_case_type_name()` - Gets the case type name for a case result

**How to use:** These functions can be used in templates, shortcodes, or other PHP code. For example, you can use `lhd_get_post_id()` to get the current page ID in Oxygen Builder conditions.

---

### 📜 Scripts & Styles Optimization

**What it does:** Optimizes how CSS and JavaScript files load.

**Key features:**

-   **Resource Hints** - Adds preconnect and dns-prefetch hints for faster external resource loading
-   **Core Script Detection** - Identifies WordPress core scripts that need to load early
-   **Smart Loading** - Ensures critical scripts load first, non-critical ones can wait

**How it works:** Automatically adds hints to the HTML that tell browsers to connect to external services (like Google Fonts, CDNs) early, reducing loading time.

---

### 🎯 Query Modifications

**What it does:** Customizes how WordPress queries and displays content.

**Features:**

-   Custom query modifications for case results and other content types
-   Optimized queries for better performance

---

### ⚙️ Admin Customizations

**What it does:** Customizes the WordPress admin area.

**Features:**

-   Admin interface improvements
-   Custom admin functionality
-   Page ID column in pages list

---

### 📦 Recommended Plugins

**What it does:** Provides an easy way to install and activate optional/recommended plugins.

**Key features:**

-   **One-Click Installation** - Install plugins directly from the WordPress repository
-   **ZIP File Support** - Install plugins from uploaded ZIP files
-   **One-Click Activation** - Activate installed plugins with a single click
-   **Status Detection** - Automatically detects if plugins are installed or active
-   **Customizable List** - Easily add or remove recommended plugins via filter

**How to use:**

1. Go to **Plugins > Recommended** in WordPress admin
2. Browse the list of recommended plugins
3. Click **Install** to install a plugin from the WordPress repository
4. Click **Activate** to activate an installed plugin
5. Plugins marked as "Active" are already active and working

**Adding Custom Recommended Plugins:**

You can add your own recommended plugins using the `lhd_recommended_plugins` filter:

```php
add_filter( 'lhd_recommended_plugins', function( $plugins ) {
    $plugins[] = array(
        'name'        => 'Your Plugin Name',
        'slug'        => 'your-plugin-slug',
        'required'    => false,
        'description' => 'Description of your plugin.',
        'source'      => 'wordpress', // or 'upload' for ZIP files
        'zip_url'     => '', // URL to ZIP file if source is 'upload'
    );
    return $plugins;
} );
```

---

### 📦 Shortcodes

**What it does:** Adds custom shortcodes you can use in your content.

**How to use:** Shortcodes can be added to posts, pages, or Oxygen Builder text elements. Check the shortcodes file for available shortcodes.

---

### 🔧 Utility Functions

**What it does:** Provides additional utility functions for common tasks.

**Features:**

-   Various helper functions for site functionality
-   Reusable code snippets

---

## Installation

1. Download the plugin ZIP file
2. Go to **Plugins > Add New** in your WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Activate**

---

## Configuration

Most features work automatically without any configuration needed. However, some features can be customized:

### Custom Fonts

-   Upload font files to `wp-content/uploads/fonts/` folder
-   Fonts will automatically appear in Oxygen Builder

### Lazy Content Loading

-   Add `lazy-section` class to sections you want to lazy load
-   Or let it automatically lazy load all `.section` elements

### Security

-   All security features are enabled by default
-   Security constants are automatically added to wp-config.php on plugin activation
-   **Security Configuration Page** - Go to **Tools > Security Config** to:
    -   Add security rules to .htaccess file
    -   Add security constants to wp-config.php
    -   Fix commented-out security constants (if they were accidentally added inside comment blocks)
    -   View recommended security settings
-   If wp-config.php is not writable, you'll see instructions for manual configuration

---

## Requirements

-   WordPress 5.0 or higher
-   PHP 7.4 or higher
-   Oxygen Builder (for Oxygen-specific features)

---

## Disabled Features

-   **Critical CSS** - Currently disabled as it was causing website issues. This feature would have generated and inlined critical CSS for faster page loads.

---

## Technical Details

### File Structure

```
lionhead-oxygen/
├── plugin.php (Main plugin file)
├── includes/
│   ├── security.php (Security features)
│   ├── security-config.php (Security configuration and wp-config.php management)
│   ├── oxygen-fonts.php (Custom fonts)
│   ├── performance-optimization.php (Performance features)
│   ├── image-optimization.php (Image optimization)
│   ├── lazy-content.php (Lazy loading)
│   ├── oxygen-integration.php (Oxygen Builder features)
│   ├── post-types.php (Custom post types)
│   ├── taxonomies.php (Custom taxonomies)
│   ├── helper-functions.php (Helper functions)
│   ├── scripts-styles.php (Script/style optimization)
│   ├── query-modifications.php (Query modifications)
│   ├── utility-functions.php (Utility functions)
│   ├── admin-customizations.php (Admin features)
│   ├── shortcodes.php (Shortcodes)
│   └── recommended-plugins.php (Recommended plugins installer)
└── assets/
    ├── css/ (Stylesheets)
    └── js/ (JavaScript files)
```

---

## Support

For issues or questions, contact the plugin developer.

---

## Changelog

### Version 1.0.0

-   Initial release with all features
-   Security protections
-   Performance optimizations
-   Custom fonts support
-   Oxygen Builder integration
-   Lazy content loading
-   Custom post types and taxonomies (Case Results, Testimonials)
-   Helper and utility functions
-   Automatic wp-config.php security constants addition
-   Security Configuration admin page
-   Automatic detection and fixing of commented-out security constants
-   Recommended plugins installer with one-click installation and activation

---

## Notes

-   This plugin is designed specifically for sites using Oxygen Builder
-   Some features require Oxygen Builder to be active
-   Security features work on all WordPress sites
-   Performance optimizations are automatic and don't require configuration
