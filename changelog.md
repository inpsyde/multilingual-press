# Changelog

## 2.0.0

- Code refactoring
- New Language Manager with editable languages
- Rename Widget to *Language Switcher*
- Improved storage of site relationships
- Set attributes `width` and `height` for flags
- Fixed error on plugin deactivation
- Implement `uninstall.php` to clean up on deletion properly
- Simplify user interface in site settings
- Better keyboard accessibility for form fields
- Convert text domain calls to static strings
- Better label texts
- Missing translation does not prevent translating a post again anymore
- Post authors of translations are not overwritten anymore.
- Show current site in widget works now
- Rework translation metaboxes, there is now one box for each language
- Rename *blog* to *site* in the user interface
- Update German translation, remove outdated other translations


## 1.1.1

- Fix incorrect URLs when front page is set as static page or custom post type.


## 1.1

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


## 1.0.3

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


## 1.0.2

 - Code: Fixed Auto Updater
 - Version: Hopping due to some Auto Update Issues


## 1.0.1

 - Code: Fixed Wrong Encoding in different files
 - Code: Fixed several warnings, notices and small bugs
 - Code: Fixed Auto Updater
 - Code: Fixed several Advanced Translator Bugs
 - Code: Fixed Post Relationships in Blog Duplicate


## 1.0

 - Feature: Advanced Translator for Posts and Pages
 - Feature: Support for Custom Post Types
 - Feature: Dashboard Widget
 - Feature: Duplicate Blogs
 - Feature: Quick Load of new language packs
 - Feature: Automatic browser redirection
 - Feature: Systemwide Trash
 - Feature: Individual backend language user settings


## 0.9.1

 - Using local logo


## 0.9

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


## 0.8.2

 - PHP 5.2 Fix


## 0.8.1

 - Adding Plugin Settingspage, Code Cleanup
 - added check that prevents the use of this plugin in a not-setted blog
 - Codexified several stuff
 - Fixed Missing Table from external Module
 - Added filter for the list, fixed Style
 - Fixed several notices
 - fixed language key output


## 0.8

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


## 0.7.5a

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


## 0.7.4a

 - Exported the basic UI and userinput handling functionality into "default-module" class
 - By default post types other than post and page are excluded
 - Incorrect flags for some languages [Issue 7](https://github.com/inpsyde/multilingual-press/issues/7)


## 0.7.3a

 - Exported helper functions into own class
 - Code documentation


## 0.7.2a

 - Updated language codes