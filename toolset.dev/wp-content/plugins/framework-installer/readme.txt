=== Framework Installer Downloader ===
Contributors: codex-m, brucepearson, jozik, AmirHelzer
Donate link: http://wp-types.com/documentation/views-demos-downloader/
Tags: CMS, Views, Demos, Download
License: GPLv2
Requires at least: 3.8.1
Tested up to: 3.9.1
Stable tag: 1.6.0

Download complete reference sites for Types and Views.

== Description ==

This plugin lets you download complete, working reference designs for Types and Views. You'll be able to experiment with everything locally and follow our online tutorials.

If you also have Views plugins, install it and you'll see the source for all the Views and View Templates in the demo site.

Documentation:
http://wp-types.com/documentation/views-demos-downloader/

= Requirements =

* A fresh WordPress site
* Write access to the theme and uploads directories
* Some additional plugins (each demo will tell you which other plugins it needs and where to get them)


== Installation ==

1. Upload 'types' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= Can I use this on a live site? =

You can, but it highly not recommended. You should use this on local test sites.

== Changelog ==

= 0.1.2 =
* Added this readme file

= 1.1.3 =
Sync with Views 1.1.3

= 1.1.3.1 =
Fix for embedded Types

= 1.2 =
Correcting some bugs and adding code for inline documentation plugin, compatibility with new reference sites and with Types 1.2 and Views 1.2

= 1.2.1 =
Corrected some issues on Framework Installer plugin, increasing compatibility with version 1.2.1 of Views and Types

= 1.2.2 =
Added features on importing modules and Bootmag site. Increasing compatibility with Views 1.2.2. and Types 1.3. Corrected some bugs during import.

= 1.2.3 =
Added features on importing Classifieds site. Increasing compatibility with Views 1.2.3. and Types 1.3.1. Corrected some bugs during import.

= 1.2.4 =
Added some new features on importing Classifieds site. Increasing compatibility with Views and Types. Corrected some bugs during import.

= 1.3 =
Sync with Views 1.3
Improve downloading stability for slower connections when downloading demo sites in standalone localhost.
Improve method of downloading images for Framework Installer specially for slower connections.
Added support for new Toolset Bootstrap reference sites.
Added new Framework Installer site reset feature to easily reset database if users wants to download another demo site.

= 1.3.0.2 =
Sync with Views 1.3.0.2

= 1.3.1 =
Sync with Views 1.3.1

= 1.4 =
Sync with Views 1.4 and adding support for Bootstrap Toolset Classifieds and BootCommerce Multilingual.

= 1.5 =
Sync with Views 1.5

= 1.5.1 =
Fix compatibility issues with Types plugin on image custom field handling.

= 1.5.2 =
Fix compatibility issues with Multilingual Classifieds Site.

= 1.5.3 =
Added support for Multilingual Classifieds site Ad package feature.

= 1.5.4 =
Sync with Views 1.5.1 and Types 1.5.4
Added feature to dismiss Framework Installer notice after import.
Fixed PHP notices occurring after WordPress reset.

= 1.5.5 =
Added feature to allow downloading of multilingual reference sites without WPML plugins.
Fixed incompatibility issue of Framework Installer with WooCommerce versions 2.1+.
Deleted _callback_views-commerce.php since reference sites does not anymore include Views Base Commerce.
Added capability of Framework Installer to suggest the tested and compatible plugin versions of the site being imported.

= 1.5.6 =
Compatibility with WooCommerce 2.1.4.
Allow to import a newly generated WooCommerce export file for BootCommerce site.
Fixes a couple of importing issues relating to Classifieds and BootCommerce site relating to WooCommerce 2.1.4 update.

= 1.5.7 =
Compatibility with WooCommerce 2.1.5.
Added hook to fix the fatal error when importing multilingual websites with WPML 3.1+.
Changed plugin name to Framework Installer.
Fix some importing issues in Classifieds site using the latest Toolset plugins.

= 1.5.9 =
 Fixed some importing bugs on WPML string translations for sites with multilingual implementation. 
 Compatibility with the latest version of Classifieds Multilingual reference site.
 Updated embedded Types to use the latest version 1.5.5 of Types.
 Added WooCommerce shop page ID to WooCommerce settings import.
 Conditionally output errors when debugging is set to true.
 Removed some deprecated import code belonging to old/unsupported reference sites.
 Revised module manager import procedure to put it after theme import so modules can be imported using embedded module manager mode in Toolset Bootstrap theme.
 Revised module manager import function to use new automatic import function for modules in module manager version 1.3.
 Allowed Types and Views plugin full version to be activated automatically after import for sites with modules import.
 Added methods to flush permalinks for sites imported with WooCommerce plugin enabled.
 Added controls for Framework installer to check if wp-content is writable. As a requirement for automatic modules import from reference sites.
 Compatibility with the latest Types and Views release.
 
 ==1.6.0==
 Completed the adding import support of Bootstrap Real Estate. 
 Compatibility with Views 1.6.1 plugin version.
 Compatibility with Types 1.5.7 plugin version.
 Added new feature to highlight WPML plugins as optional plugins for importing sites with multilingual support.