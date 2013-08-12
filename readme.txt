=== Multilingual Press ===
Contributors: Inpsyde, Bueltge, nullbyte, hughwillfayle, paddelboot, toscho
Tags: l10n, i18n, bilingual, international, internationalization, lang, language, localization,  multilanguage, multi language, multilingual, multi lingual, multisite, switcher, translation, website translation, wordpress translation, chinese, german, french, russian, widget

Requires at least: 3.5
Tested up to: 3.6
Stable tag: 1.1

Multilingual websites with WordPress Multisite

== Description ==

Connect multiple sites as language alternatives in a multisite. Use a customizable widget to link to all sites.


This plugin lets you connect an unlimited amount of sites with each other. Set a main language for 
each site, create relationships (connections), and start writing. You get a new field now to create 
a linked post on all the connected sites automatically.
The are accessible via the post/page editor screen - you can switch back and forth to translate them! 

In contrast to most other translation plugins there is **no lock-in effect**: When you disable our plugin, 
all sites will still work as separate sites without any data-loss or garbage output.

Note we cannot offer free ad hoc support.

= Free version =

- Set up unlimited blog relations in the site manager.
- View the translations for each post or page underneath the post editor.
- Show a list of links for all translations on each page in a flexible widget.
- No lock-in: After deactivation, all sites will still work.

= Pro Version =

Our [pro-version](http://marketpress.com/product/multilingual-press-pro/) offers many features to 
save your time and to improve your work flow and user experience:

- Support for custom post types.
- Automatic redirect to the user's preferred language version of a post.
- Edit all translations for a post from the original post editor without the need to switch sites.
- Duplicate blogs. Use one blog as template for new blogs, copy *everything:* Posts, attachments, 
  settings for plugins and themes, navigation menus, categories, tags and custom taxonomies.
- Synchronized trash: move all connected post to trash with one click.
- Quicklinks. Add links to language alternatives to a post automatically to the post content. This 
  is especially useful when you don't use widgets or a sidebar.
- User specific language settings for the back-end. Every user can choose a preferred language for 
  the user interface without affecting the output of the front-end.
- Show posts with incomplete translations in a dashboard widget.

== Installation ==

= Requirements =
* WordPress Multisite 3.3+
* PHP 5.2*, newer PHP versions will work faster.

= Installation =

 * Use the installer via back-end of your install or ...

1. Unpack the download-package
2. Upload the files to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Network/Plugins' menu in WordPress and hit 'Network Activate'
4. Go to 'All Sites' and then 'Edit' each Site and then select the tab 'Multilingual' to configure the
   settings for each Site

Help tab for further explanation can be found on the right top of the page.

== Screenshots ==
1. List of Sites in network with new column for interlinked sites
2. Settings for each site in network
3. Preview and linked posts in a post
4. Widget for switch language in Twenty Eleven theme


== Other Notes ==
= Acknowledgements =
**Thanks to** different customer for trust in our know how and suggestion and release of solutions in
this plugin.

* German language files by [ourselves](http://inpsyde.com) ;)
* Lithuanian translation files by [Vincent G](http://www.host1plus.com)

= Licence =
Good news, this plugin is free for everyone! Since it's released under the GPLv3, you can use it free
of charge on your personal or commercial blog.

= Translations =

The plugin comes with various translations, please refer to the
[WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress
in Your Language") for more information about activating the translation. If you want to help to translate
the plugin to your language, please have a look at the .pot file which contains all defintions and may
be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/)
(Windows) or plugin for WordPress
[Localization](http://wordpress.org/extend/plugins/codestyling-localization/).

== Changelog ==

= 1.1 =
- Better data handling during setup of post relationships.
- Fixed wrong links in widget on the front page.
- Added a Catalonian flag, the Canadian flag was used for `ca` accidentally.
- Removed need to reload settings after adding a new relationship.
- Copy post meta data and featured image for connected posts.
- Set preview of connected posts to `readonly`, not `disabled` to make copy and paste easier.
- Show languages in site list in native writing style (alphabet), not flags.
- Improved German translation.
- Made all text domain references static strings.
- Unify hook names. **Developers: we will change our API completely in version 1.2.** If you have 
  any questions, please [contact us](http://marketpress.com/contact/) before you write new code.
- Added a language list in `inc/language-list.php` to get languages in native and English writing by ISO codes.
- Added a helper class `Mlp_Db_Replace` to update multiple tables and columns at once.
- Many minor stability and performance improvements.

= 1.0.3 =
 - Code: Auto Updater Improvements
 - Code: Fixed Feature Loader
 - Removed link to blog posts on is_home()
 - Removed static ?noredirect parameter
 - Added modern greek as language
 - Added private posts for translations
 - Added parameter handling for mlp_show_linked_elements template function
 - Added parameter show_current_blog on mlp_show_linked_elements template function
 - Added show current blog at the MLP Widget
 - Added hook for checkbox "translate this post"
 - Added hook to change the default meta box
 - Added hook to change the link to the blog
 - Changed admin_url into network_admin_url
 - Redirect Feature: Added better check for session_start
 - Redirect Feature: Added redirect on is_home()
 - Redirect Feature: Added ?noredirect link with core plugin hook
 - Redirect Feature: Added english as browser language
 - Quicklink Feature: Added blog language to quicklink
 - Dashboard Widget: Fixed "This post is translated" checkbox
 - Advanced Translator: Removed default metabox when  feature is active
 - Added Feature: Default Actions
 - Autoupdate Feature: Removed autoupdate module from module list

= 1.0.2 =
 - Code: Fixed Auto Updater
 - Version: Hopping due to some Auto Update Issues

= 1.0.1 =
 - Code: Fixed Wrong Encoding in different files
 - Code: Fixed several warnings, notices and small bugs
 - Code: Fixed Auto Updater
 - Code: Fixed several Advanced Translator Bugs
 - Code: Fixed Post Relationships in Blog Duplicate

= 1.0 =
 - Feature: Advanced Translator for Posts and Pages
 - Feature: Support for Custom Post Types
 - Feature: Dashboard Widget
 - Feature: Duplicate Blogs
 - Feature: Quick Load of new language packs
 - Feature: Automatic browser redirection
 - Feature: Systemwide Trash
 - Feature: Individual backend language user settings

= 0.9.1 =
 - Using local logo

= 0.9 =
 - Feature: Added Demo Module
 - Feature: Added sort option to widget
 - Feature: Added is_home() to our queries
 - Feature: Added mlp_get_interlinked_permalinks
 - Feature: Added mlp_get_blog_language
 - Code: Fixed Widget
 - Code: Fixed several notices
 - Code: Fixed Buffer Bug in Settingspage
 - Code: Fixed Notices on refresh of the blog settings
 - Code: Fixed Column Content
 - Code: Fixed Relationship Error Notice
 - Code: Better Error Message for blog relationships
 - Code: Constant Language Strings
 - Code: Added Korean Language

= 0.8.2 =
 - PHP 5.2 Fix

= 0.8.1 =
 - Adding Plugin Settingspage, Code Cleanup
 - added check that prevents the use of this plugin in a not-setted blog
 - Codexified several stuff
 - Fixed Missing Table from external Module
 - Added filter for the list, fixed Style
 - Fixed several notices
 - fixed language key output

= 0.8 =
 - Codexified
 - Renamed the files
 - changed textdomain
 - fixed fi_FI language pack
 - fixed several widget bugs ( #10, #13, #18, #22 )
 - Documentation
 - Only load the Widget CSS when widget is used
 - added a check box to the editing view asking whether you want to create the drafts to other languages
 - Translation is availeable for drafts
 - Fixed up JS
 - Blog Checkup for invalid data

= 0.7.5a =
 - Display an admin notice if the plugin was not activated on multisite
 - Set the parent page if this page was also handled by the plugin [Issue 2](https://github.com/inpsyde/multilingual-press/issues/2)
 - Fix a problem that a new multisite cannot set related blogs
 - Change filter [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
 - Widget bugfix [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
 - Smaller source via use function selected() [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
 - Static value for register widget [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
 - Update Wiki for wrapper functions [Wiki on Repo](https://github.com/inpsyde/multilingual-press/wiki)
 - Add new pages on [Wiki on Repo](https://github.com/inpsyde/multilingual-press/wiki) for Filter- and Action Hooks inside the plugin
 - Fix bug, if you kill data on an blog for dont interlinked with other blogs

= 0.7.4a =
 - Exported the basic UI and userinput handling functionality into "default-module" class
 - By default post types other than post and page are excluded
 - Incorrect flags for some languages [Issue 7](https://github.com/inpsyde/multilingual-press/issues/7)

= 0.7.3a =
 - Exported helper functions into own class
 - Code documentation

= 0.7.2a =
 - Updated language codes
