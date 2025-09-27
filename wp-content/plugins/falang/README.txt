=== Falang multilanguage for WordPress ===
Contributors: sbouey
Donate link: www.faboba.com/falangw/
Tags: multilingual, translation, translate, bilingual, localization
Requires at least: 4.7
Tested up to: 6.8
Requires PHP: 5.6
Stable tag: 1.3.67
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Falang is the easiest multilanguage plugin you can use to translate a WordPress site.

== Description ==

Falang is a multilanguage plugin for WordPress. It allows you to translate an existing WordPress site to other languages. Falang natively supports WooCommerce (product, variation, category, tag, attribute, etc.)

= Free vs Pro =

Pro version:
- Enabled popup translation for (menu/post,product...)
- DeepL translation service
- Translate/configure WooCommerce email
- has on-site support and language filtering - [documentation](https://www.faboba.com/en/wordpress/falang-for-wordpress/documentation/134-how-to-use-lang-filtering.html)
- WP User Manager
- YITH WooCommerce Compare
- WC Product addons
- CookieYes

= Concept =

* Easy setup
* Supports all languages supported by WordPress (RTL and LTR)
* Translate additional plugins like WooCommerce, Yoast SEO, etc.
* You can use Google, Azure, Lingvanex to help you with the translation (DeepL services available in Pro version)
* Translation are set in the meta, the original content is not modified.
* When you add a language in Falang, WP language packages are automatically downloaded and updated
* Easy to use: Translate Posts, Pages, Menus, Categories from the plugin or linked from the WP interface
* Translate Posts and Terms permalinks
* Displays the default language if the content is not yet translated
* The Language Switcher widget is configurable to display flags and/or language names
* Language Switcher can be put in Menu, Header, Footer, Sidebars
* Image captions, alt text and other media text translation without duplicating the media files
* Language Code directly in the URL
* No extra database tables created, no content duplication
* Very good website speed performance (low impact)
* Contains translations for IT, FR, DE, ES, NL
* Falang is not meant for WordPress multisite installations!

= Falang's goal is to let you translate everything on your page =

* Taxonomies
* Menu items
* Theme and plugin strings
* Custom fields
* Page builder content
* Widgets
* Shortcode outputs
* URL slugs
* WooCommerce products
* Page title and description
* Image alt text and captions

= Quick start video =


Falang and Falang for Elementor Lite
_(English version)_
[youtube https://youtu.be/ZEgIMY5mock]

_(French version with english subtitles)_
[youtube https://youtu.be/BibeMgPEgME]

= Also available =

* Falang WPML Importer [Falang WPML Importer](https://wordpress.org/plugins/falang-wpml-importer/)
* Falang Q-Importer [qTranslateX to Falang](https://wordpress.org/plugins/falang-q-importer/)
* Falang for Divi [Falang for Divi](https://wordpress.org/plugins/falang-for-divi-lite/)
* Falang for Elementor [Falang for Elementor](https://wordpress.org/plugins/falang-for-elementor-lite/)
* Falang for WPBakery [Falang for WPBakery](https://wordpress.org/plugins/falang-for-wpbakery-lite/)
* Falang for YooTheme [Falang for YOOtheme](https://wordpress.org/plugins/falang-for-yootheme-lite/)

= Falang integration =

* My Agile Privacy

== Installation ==

1. Upload the entire Falang folder to the /wp-content/plugins/ directory or install from WP Plugins
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add languages
4. Set the default language (main language of your site)
5. Add the  Language Switcher to your site
6. Translate your post / pages / menus etc. to the other languages

== Frequently Asked Questions ==

* Is Falang Translate free?
Yes, but without on-site support.
Purchased licenses come with 1 year on-site support.

* Where to find help?
  First time users should read the [Falang - Documentation](https://www.faboba.com/falangw/), which explains the basics (with screenshots - in English).

== Screenshots ==

1. The Post Listing translation panel
2. The Post translation panel
3. The Language Listing panel

== Changelog ==

* 1.3.67 (2025/09/16)
* fix WooCommerce – Dashboard menu active class missing (Alexandre Froger fix)
* fix CF7 bug with _locale filtring since CF7 6.1.1
* fix RankMath title translation for archive page
* fix meta (ACF) translation (regression from 1.3.66 version)
* fix for woocommerce Checkout link (jurifc)

* 1.3.66 (2025/09/01)
* fix theme editor save with slug
* fix bug with permalink since woocommerce 10.0.0
* fix object injection vulnerability's

* 1.3.65 (2025/08/02)
* fix _load_textdomain_just_in_time (thanks to Alexandre Froger)
* fix yoast title translation
* fix yoast category description (change in wpml-config.xml necessary)
* fix yoast graph slug write 2 times
* load custom wpml-config.xml in falang root directory

* 1.3.64 (2025/07/01)
* fix yoast title translation (no variable in the title)

* 1.3.63 (2025/05/14)
* fix error on WC checkout

* 1.3.62 (2025/05/12)
* add yootheme live search support
* fix yootheme search bug
* add WC term&condition translation when it's embeded in the page
* fix security (CSRF)
* WordPress 6.8 compatible

* 1.3.61 (2025/04/14)
* shortcode falang switcher fix the echo
* add password protection support for translated content
* fix yoast canonical on page
* fix title display
* support for My Agile Privacy
* fix deprecated Falang\Core\Language::$term_order
* add falang_languages_list (like pll_languages_list)
* fix duplicator pro popup bug with Falang [data-tooltip]
* add WPML ICL_LANGUAGE_CODE and ICL_LANGUAGE_NAME define (fix bug with some theme)

* 1.3.60 (2025/03/05)
* update term translation in the list status/content on save modal
* fix term translation service translation
* fix string translation service translation
* fix options translation service translation
* notice are now displayed every 2 weeks not 1
* add english/french canadian
* temporary fix load string translation put back in plugin plugins_loaded filter
* fix new icon copy on acf translation

* 1.3.59 (2025/01/30)
* fix bug in falangw shortcode use to display the switcher (thanks to Frank Sundgaard Nielsen)
* fix copy/translate button with supported builder (elementor/divi...)
* fix suoni language translation with translate service
* fix deepl translation on elementor text widget
* fix hreflang when they are overrided by the filter falang_hreflang
* remove font awesome / use fontello font only
* fix load_textdomain warning

* 1.3.58 (2025/01/15)
* change message when the post translation is done in a builder
* don't display original content with extra builder (fix element pack bug with simple form)
* add during install the Navigation Menu item option : _menu_item_url checked

== Known issues ==
* The WooCommerce attribute slug doesn't have to be translated

== Upgrade Notice ==
* No notice yet
