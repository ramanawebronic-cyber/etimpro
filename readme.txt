=== ETIM for WooCommerce ===
Contributors: webronic
Tags: woocommerce, etim, classification, product attributes, technical specifications
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate ETIM international classification standard with WooCommerce products. Map products with ETIM Groups, Classes, Features, and Values.

== Description ==

ETIM for WooCommerce allows you to classify your products using the ETIM (European Technical Information Model) international standard. This plugin connects directly to the official ETIM API to provide accurate and up-to-date classification data.

= Features =

* **Admin Settings Page** - Easily configure your ETIM API credentials (Client ID, Client Secret, Scope)
* **Secure Token Management** - Automatic OAuth2 token generation and caching
* **Full ETIM API Integration** - Access Groups, Classes, Features, and Values
* **WooCommerce Product Integration** - Custom meta box on product edit pages
* **AJAX-Based Selection** - Smooth cascading selection (Group → Class → Features → Values)
* **Frontend Display** - Technical specifications tab on product pages
* **Multi-language Support** - Configure default language for ETIM data
* **Clean Code Architecture** - Separated files for settings, API, AJAX, and product meta

= How It Works =

1. Enter your ETIM API credentials in the settings page
2. Edit any WooCommerce product
3. Use the ETIM Classification meta box to:
   - Select an ETIM Group
   - Choose a Class within that Group
   - Add and configure Features
   - Assign Values to Features
4. Save the product - ETIM data is stored and displayed on the frontend

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher
* ETIM API credentials (obtain from [ETIM International](https://www.etim-international.com/))

== Installation ==

1. Upload the `etim-for-woocommerce` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'ETIM Settings' in the admin menu
4. Enter your ETIM API credentials
5. Test the connection to verify everything is working
6. Start classifying your products!

== Frequently Asked Questions ==

= Where do I get ETIM API credentials? =

You can obtain API credentials from [ETIM International](https://www.etim-international.com/). You'll need to register and request API access.

= Can I use multiple ETIM classes on one product? =

Yes! You can add multiple ETIM classes to a single product and configure features for each class independently.

= Does this plugin support multiple languages? =

Yes, you can select your preferred language in the settings. The plugin supports all languages available in the ETIM API.

= Is the ETIM data cached? =

The access token is cached to minimize API calls. Class and feature data is fetched fresh to ensure accuracy.

= Can I display ETIM features on the product page? =

Yes, the plugin automatically adds a "Technical Specifications" tab to product pages showing all assigned ETIM features with values.

== Screenshots ==

1. Admin Settings Page - Configure your ETIM API credentials
2. Product Edit Page - ETIM Classification meta box
3. Frontend Product Tab - Technical Specifications display

== Changelog ==

= 2.0.0 =
* Complete rewrite with clean architecture
* Removed VVS integration (now ETIM-only)
* New AJAX-based cascading selection
* Improved admin UI with Select2
* Better error handling and validation
* Added connection testing
* Added multi-language support
* WooCommerce HPOS compatibility
* Code separated into logical classes

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 2.0.0 =
Major update with complete code rewrite. Please backup your database before upgrading. VVS integration has been removed.

== Developer Notes ==

The plugin is built with a clean, modular architecture:

* `class-etim-api.php` - Handles OAuth authentication and API requests
* `class-etim-admin-settings.php` - Admin settings page and configuration
* `class-etim-ajax-handler.php` - AJAX endpoints for data fetching
* `class-etim-product-meta.php` - WooCommerce product meta box and frontend display

= Hooks =

The plugin provides several filters and actions for customization:

* `etim_before_save_product_data` - Modify data before saving
* `etim_after_save_product_data` - Perform actions after saving
* `etim_feature_value_display` - Customize feature value display

= API Endpoints =

AJAX endpoints available:

* `etim_fetch_groups` - Search ETIM groups
* `etim_fetch_classes` - Search ETIM classes
* `etim_fetch_class_details` - Get class with features
* `etim_save_product_data` - Save product ETIM data
* `etim_load_product_data` - Load product ETIM data
