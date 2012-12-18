=== Multilingual Press ===
Contributors: Inpsyde, Bueltge, nullbyte, hughwillfayle
Tags: language, multilinguage, multisite
Requires at least: 3.3
Tested up to: 3.5-RC4
Stable tag: 1.0.2

Multilingual websites with WordPress Multisite

== Description ==
By using the powerful WordPress Multilingual-Press plugin it´s much easier to build multilingual sites and
run them with WordPress' multisite feature. To get going with this plugin, you need to setup a WordPress 
multisite installation first 
(check the Codex for more infos on this topic: http://codex.wordpress.org/Create_A_Network). 
Each site/blog can then be attributed to a different language. Simply write a post or page in one language 
and Multilingual-Press will automatically create a duplication of it in the other sites/blogs. These new 
posts and pages are interlinked and are easily accessible via the post/page editor screen - you can switch 
back and forth to translate them! Multilingual-Press is WordPress conform, easy to install and doesn't make 
any changes to the WordPress core. It doesn't harm your website's performance.  

= doodleinnovation =
We supporten plugin authors in their free time, the free plugins. Please have a little patience and ask friendly, businesslike.

Please give us feedback, contribute and file technical bugs on 
[GitHub Repo](https://github.com/inpsyde/multilingual-press).

= Pro Version =
We will also have a pro version of this plugin, which includes these additional features and many more:

- browser language detection and automatically forwarding to the correct language (can be 
  deactivated via settings)
- creating a draft is activated by default (can be deactivated globaly via settings or in 
  metabox for specific posts)
- possibility to publish posts just on selected sites/blogs
- create new language -> copy of a complete site/blog:
        posts, links, attachments, categories, tags
        reminder which posts you have to translate
        custom table column with language
- duplicate sites/blogs and create links    
- frontend: cookies store which languages were selected
- easily switching to another language from your editor while editing a post
- global media library for all sites/blogs
- Dashboard widget in root blog displaying all posts which still have to be translated

**Currently in development, but you can test it - write us**

== Installation ==
= Requirements =
* WordPress Multisite 3.3*
* PHP 5.2*

= Installation =
 * Use the installer via backend of your install or ...

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

= 1.0.2 =
* Code: Fixed Auto Updater
* Version: Hopping due to some Auto Update Issues

= 1.0.1 =
* Code: Fixed Wrong Encoding in different files
* Code: Fixed several warnings, notices and small bugs
* Pro Code: Fixed Auto Updater
* Pro Code: Fixed several Advanced Translator Bugs
* Pro Code: Fixed Post Relationships in Blog Duplicate

= 1.0 =
* Pro Feature: Advanced Translator for Posts and Pages
* Pro Feature: Support for Custom Post Types
* Pro Feature: Dashboard Widget
* Pro Feature: Duplicate Blogs
* Pro Feature: Quick Load of new language packs
* Pro Feature: Automatic browser redirection
* Pro Feature: Systemwide Trash
* Pro Feature: Individual backend language user settings

= 0.9 =
* Feature: Added Demo Module
* Feature: Added sort option to widget
* Feature: Added is_home() to our queries
* Feature: Added mlp_get_interlinked_permalinks
* Feature: Added mlp_get_blog_language
* Code: Fixed Widget
* Code: Fixed several notices
* Code: Fixed Buffer Bug in Settingspage
* Code: Fixed Notices on refresh of the blog settings
* Code: Fixed Column Content
* Code: Fixed Relationship Error Notice
* Code: Better Error Message for blog relationships
* Code: Constant Language Strings
* Code: Added Korean Language

= 0.8.2 =
* PHP 5.2 Fix

= 0.8.1 =
* Adding Plugin Settingspage, Code Cleanup
* Added check that prevents the use of this plugin in a not-setted blog
* Codexified several stuff
* Fixed Missing Table from external Module
* Added filter for the list, fixed Style
* Fixed several notices
* Fixed language key output

= 0.8.0 =
* Codexified
* Renamed the files
* changed textdomain
* fixed fi_FI language pack
* fixed several widget bugs ( #10, #13, #18, #22 )
* Documentation
* Only load the Widget CSS when widget is used
* added a check box to the editing view asking whether you want to create the drafts to other languages
* Translation is availeable for drafts
* Fixed up JS
* Blog Checkup for invalid data

= 0.7.5a =
* Display an admin notice if the plugin was not activated on multisite
* Set the parent page if this page was also handled by the plugin [Issue 2](https://github.com/inpsyde/multilingual-press/issues/2)
* Fix a problem that a new multisite cannot set related blogs
* Change filter [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
* Widget bugfix [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
* Smaller source via use function selected() [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
* Static value for register widget [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
* Update Wiki for wrapper functions [Wiki on Repo](https://github.com/inpsyde/multilingual-press/wiki)
* Add new pages on [Wiki on Repo](https://github.com/inpsyde/multilingual-press/wiki) for Filter- and Action Hooks inside the plugin
* Fix bug, if you kill data on an blog for dont interlinked with other blogs

= 0.7.4a =
* Exported the basic UI and userinput handling functionality into "default-module" class
* By default post types other than post and page are excluded
* Incorrect flags for some languages [Issue 7](https://github.com/inpsyde/multilingual-press/issues/7)

= 0.7.3a =
* Exported helper functions into own class
* Code documentation

= 0.7.2a =
* Updated language codes
