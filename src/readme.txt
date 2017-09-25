=== MultilingualPress ===
Contributors: inpsyde, toscho, tfrommen, Bueltge, hughwillfayle, nullbyte, Biont, ChriCo, dnaber-de, paddelboot
Tags: bilingual, i18n, international, internationalization, l10n, lang, language, localization, multi, multilanguage, multilingual, multisite, network, translation
Requires at least: 4.2
Tested up to: 4.8
Stable tag: 2.9.1

Create a fast translation network on WordPress multisite.

== Description ==

Run each language in a separate site, and connect the content in a lightweight user interface. Use a customizable widget
to link to all sites.

This plugin lets you connect an unlimited amount of sites with each other.
Set a main language for each site, create relationships (connections), and start writing. You get a new field now to
create a linked post on all the connected sites automatically.
They are accessible via the post/page editor screen - you can switch back and forth to translate them.

In contrast to most other translation plugins there is **no lock-in effect**: When you disable our plugin, all sites
will still work as separate sites without any data-loss or garbage output.

Our **Language Manager** offers 174 languages, and you can edit them.

= Features =

- Set up unlimited site relationships in the site manager.
- Language Manager with 174 editable languages.
- Edit all translations for a post or page from the original post editor without the need to switch sites.
- Show a list of links for all translations on each page in a flexible widget.
- Translate posts, pages and taxonomy terms like categories or tags.
- Add translation links to any nav menu.
- No lock-in: After deactivation, all sites will still work.
- SEO-friendly URLs and permalinks.
- Support for top-level domains per language (via multisite domain mapping).
- Automatic `hreflang` support.
- Support for custom post types.
- Automatically redirect to the user's preferred language version of a post.
- Duplicate sites. Use one site as template for new site and copy *everything:* Posts, attachments, settings for plugins
and themes, navigation menus, categories, tags and custom taxonomies.
- Synchronized trash: move all connected post to trash with one click.
- Change relationships between translations or connect existing posts.
- Quicklinks. Add links to language alternatives to a post automatically to the post content. This is especially useful
when you don't use widgets or a sidebar.
- User specific language settings for the back-end. Every user can choose a preferred language for the user interface
without affecting the output of the front-end.
- Show posts with incomplete translations in a dashboard widget.

We cannot guarantee free ad hoc support. Please be patient, we are a small team.
You can follow our progress and development notices on our
[developer blog](http://make.multilingualpress.org).

= Premium Support =

We also offer [premium support](https://multilingualpress.org) to save your time.
You get direct help from the developers of the plugin-and support the development.

= WPML to MultilingualPress =

If you would like to switch from the WPML plugin to MultilingualPress, you can use the helping hands of
[WPML to MultilingualPress](https://wordpress.org/plugins/wpml-to-multilingualpress/). This plugin converts posts from
an existing WPML multilingual site via XLIFF export/import for MultilingualPress.

= Crafted by Inpsyde =
The team at [Inpsyde](http://inpsyde.com) is engineering the Web since 2006.

== Installation ==

= Requirements =
- WordPress Multisite 4.2+.
- PHP 5.2.4, newer PHP versions will work faster.

If you're new to WordPress multisite, you might find our [WordPress multisite installation
tutorial](http://make.multilingualpress.org/2014/02/how-to-install-multi-site/) helpful.

Use the installer via back-end of your install or ...

1. Unpack the download-package.
2. Upload the files to the `/wp-content/plugins/` directory.
3. Activate the plugin through the **Network/Plugins** menu in WordPress and click **Network Activate**.
4. Go to **All Sites**, **Edit** each site, then select the tab **MultilingualPress** to configure the settings. You
need at least two sites with an assigned language.

== Frequently Asked Questions ==

= Will MultilingualPress translate my content? =

No, it will not. It manages relationships between sites and translations, but it doesn't change the content.

= Can I use MultilingualPress on a single-site installation? =

That would require changes to the way WordPress stores post content. Other plugins do that; we think this is wrong,
because it creates a lock-in: you would lose access to your content after the plugin deactivation.

= I'm new to WordPress multisite. Are there any tutorials to get me started? =

Yes, just have a look at our [WordPress multisite installation
tutorial](http://make.multilingualpress.org/2014/02/how-to-install-multi-site/).

== Screenshots ==

1. New columns in the site list table for the _Relationships_ (i.e., connections with other sites) and the _Site Language_.
2. New settings tab on the _Edit Site_ page.
3. New settings tab on the _Add New Site_ page.
4. Plugin settings, including Custom Post Type translation.
5. The _Language Manager_.
6. Dashboard widget informing about currently untranslated posts.
7. Translate a post directly from the _Edit Post_ page, and set the translation status and _Trasher_ setting.
8. Translate a term directly from the _Add New Category_ page.
9. Edit term translations on the _Edit Category_ page.
10. New user settings for the sitewide _Backend Language_ and the _Language Redirect_.
11. New _Language Switcher_ widget.
12. Frontend view of a post showing both the _Quicklinks_ and the _Language Switcher_ widget.

== Changelog ==

= 2.9.1 =
- Translations: Restrict post type archive translations to active post types only.

= 2.9.0 =
- Activation: Initialize the plugin upon activation, see [issue #281](https://github.com/inpsyde/MultilingualPress/issues/281).
- Translations: Introduce a new filter, `multilingualpress.active_taxonomies`, to modify the allowed taxonomies, see [issue #282](https://github.com/inpsyde/MultilingualPress/issues/282).

= 2.8.1 =
- Content Relations: Fix always disconnecting the source post.

= 2.8.0 =
- Content Relations: Fix various (edge case) issue around adding a new post to an existing relationship, see [issue #280](https://github.com/inpsyde/MultilingualPress/issues/280).
- DB: Always (try to) delete a table before creating it anew, see [issue #240](https://github.com/inpsyde/MultilingualPress/issues/240).
- hreflang: Introduce a new filter, `multilingualpress.render_hreflang`, to force or prevent rendering.
- hreflang: Introduce a new filter, `multilingualpress.hreflang_post_status`, to restrict post translations, and only query published posts, see [issue #276](https://github.com/inpsyde/MultilingualPress/issues/276) and [issue #277](https://github.com/inpsyde/MultilingualPress/issues/277).
- Language API: Introduce a new argument, `post_status`, to restrict post translations.
- Redirect: Introduce a new filter, `multilingualpress.redirect_post_status`, to restrict post redirect targets, and only query published posts, see [pull request #278](https://github.com/inpsyde/MultilingualPress/pull/278), props @diedexx.

= 2.7.1 =
- Redirect: Add optional `$args` argument to `Mlp_Language_Negotiation::get_redirect_matches()` and pass it on to `Mlp_Language_Api::get_translations()`, see [pull request #271](https://github.com/inpsyde/MultilingualPress/pull/271), props @diedexx.
- Redirect: Add `content_id` to redirect matches, see [pull request #271](https://github.com/inpsyde/MultilingualPress/pull/271), props @diedexx.
- Relations: Delete all relationship data with the last relationship, see [issue #273](https://github.com/inpsyde/MultilingualPress/issues/273).

= 2.7.0 =
- Redirect: Introduce a new filter, `multilingualpress.redirect_targets`, to manipulate the found redirect targets, see [pull request #265](https://github.com/inpsyde/MultilingualPress/pull/265), props @diedexx.
- hreflang: Introduce new filters `multilingualpress.hreflang_type` and `multilingualpress.hreflang_translations`, see [pull request #267](https://github.com/inpsyde/MultilingualPress/pull/267), props @diedexx.
- hreflang: Bump priority for `hreflang` HTTP headers to `11`.
- Redirect: Bump default value for language-only priority factor.

= 2.6.0 =
- Types: Introduce null implementations for language and translation interfaces, `Mlp_Null_Language` and `Mlp_Null_Translation`.
- Nav Menu: Use `Mlp_Null_Translation` in favor of `null`.
- Language API: Don't cache translations anymore, see [issue #261](https://github.com/inpsyde/MultilingualPress/issues/261).
- Term Translator: Don't cache translations anymore.
- Table List: Don't cache table names anymore.
- Language Switcher: Make _Native name_ the default _Link text_ option value, see [issue #262](https://github.com/inpsyde/MultilingualPress/issues/262).

= 2.5.5 =
- Translations: Fix issue with unfilterable URLs in the `hreflang` HTTP header and HTML element output.
- Post Translator: Fix creation of (duplicate) draft translation posts on post preview, see
[issue #260](https://github.com/inpsyde/MultilingualPress/issues/260).

= 2.5.4 =
- Post Translator: Fix creation of duplicate draft translation posts, see
[issue #253](https://github.com/inpsyde/MultilingualPress/issues/253).

= 2.5.3 =
- Helpers: Fix `Mlp_Helpers::get_language_flag()` (and thus `mlp_get_language_flag()`) throwing a warning for non-custom flag images.

= 2.5.2 =
- Language Switcher: Fix widget not passing on the `strict` flag.

= 2.5.1 =
- Assets: Fix assets being provided on pages where they are not needed, see
[issue #249](https://github.com/inpsyde/multilingual-press/issues/249).

= 2.5.0 =
- Installation: Fix _Incorrect index name ''_ errors, see
[issue #246](https://github.com/inpsyde/multilingual-press/issues/246).
- Alternate Languages: Fix `hreflang` HTTP Headers and HTML `<link />` tags being provided on paged requests.
- Advanced Translator: Fix saving two posts due to (auto-)draft behavior.
- User Backend Language: Don't register module for WordPress 4.7+.
- Sites: Fix having two active nav tabs.
- General: Use `(get_)home_url()` for all front-end requests/URLs, see
[issue #247](https://github.com/inpsyde/multilingual-press/issues/247), props ptrckvzn.
- Nav Menu: Introduce CSS class for current language item, see
[issue #243](https://github.com/inpsyde/multilingual-press/issues/243), props ChriCo.
- Functions: Introduce filter for `Mlp_Helpers::show_linked_elements()` output, see
[issue #228](https://github.com/inpsyde/multilingual-press/issues/228), props dasllama.

= 2.4.8 =
- Install/Uninstall: Prevent potential PHP notice due to deprecated `wp_get_sites()` function, see
[issue #229](https://github.com/inpsyde/multilingual-press/issues/229) and
[issue #236](https://github.com/inpsyde/multilingual-press/issues/236).
- Language Manager: Fix HTTP code of _Chinese (Singapore)_, see
[issue #232](https://github.com/inpsyde/multilingual-press/issues/232).
- Language Manager: Fix HTTP code of _French (Switzerland)_, see
[issue #233](https://github.com/inpsyde/multilingual-press/issues/233).
- Redirect: Fix priority of language-only redirect matches, see
[issue #234](https://github.com/inpsyde/multilingual-press/issues/234).
- Post Translator: Prevent potential update of wrong posts, props 082net.

= 2.4.7 =
- CSS: Use font weight "600" instead of "bold", see
[Native Fonts in 4.6](https://make.wordpress.org/core/2016/07/07/native-fonts-in-4-6/).
- Redirect: Fix user setting not being respected, see
[issue #227](https://github.com/inpsyde/multilingual-press/issues/227).

= 2.4.6 =
- Post Translator: Fix translations not being saved, see
[issue #219](https://github.com/inpsyde/multilingual-press/issues/219).
- Advanced Translator: Fix just-in-place translation not working, see
[issue #220](https://github.com/inpsyde/multilingual-press/issues/220).

= 2.4.5 =
- Improve CopyPost script, see [issue #214](https://github.com/inpsyde/multilingual-press/issues/214).

= 2.4.4 =
- Fix HTTP redirection even though the user disabled it, see
[issue #218](https://github.com/inpsyde/multilingual-press/issues/218).
- JavaScript: Fix array check.

= 2.4.3 =
- Fix missing flag for the current site in the Language Switcher widget due to a regression introduced in
MultilingualPress 2.4.0, see [issue #212](https://github.com/inpsyde/multilingual-press/issues/212).
- Fix stylesheet not being enqueued when using the Language Switcher widget only (i.e., no Quicklinks) due to a
regression introduced in MultilingualPress 2.4.0, see
[issue #213](https://github.com/inpsyde/multilingual-press/issues/213).

= 2.4.2 =
- Fix incorrect styling due to a regression introduced in MultilingualPress 2.4.0, see
[issue #208](https://github.com/inpsyde/multilingual-press/issues/208).
- Fix incorrect Language Switcher form markup due to a regression introduced in MultilingualPress 2.4.0, see
[issue #209](https://github.com/inpsyde/multilingual-press/issues/209), props MBD.berlin.

= 2.4.1 =
- Fix potentially incorrect type hint, see [issue #204](https://github.com/inpsyde/multilingual-press/issues/204), props
tyrann0us.
- Fix MultilinguaPress JavaScript settings not being available when using minified JavaScript files, see
[issue #205](https://github.com/inpsyde/multilingual-press/issues/205), props kraftner.

= 2.4.0 =
- Overall improvement of nonce usage.
- Rename plugin text domain, and adapt gettext calls and translations files.
- When creating a new site, the language is set to the default site language.
- When the site language is changed, the MultilingualPress language select adapts to this.
- Improve _clearfix_ usage, props tiagoschenkel.
- Complete JavaScript refactor, see [issue #168](https://github.com/inpsyde/multilingual-press/issues/168).
- Refactor and improve the post translator's "Copy source post" functionality, see
[issue #140](https://github.com/inpsyde/multilingual-press/issues/140).
- Indicate if "Copy source post" button was used, see
[issue #169](https://github.com/inpsyde/multilingual-press/issues/169).
- Fire the `switch_theme` action when a site has been duplicated.
- Fix term relation not being deleted when term is deleted.
- Fix dynamic CPT permalinks (due to regression during merge).
- Add filter for remote post search minimum input length, see
[issue #193](https://github.com/inpsyde/multilingual-press/issues/193).
- Sort remote post search results by relevance.
- Improve CPT translator: allow translation for all editable post types, see
[issue #184](https://github.com/inpsyde/multilingual-press/issues/184), props kraftner.
- Use the full slug when copying post data, see [issue #195](https://github.com/inpsyde/multilingual-press/issues/195),
props luisarn.
- Improve (i.e., prepare/escape) several MySQL queries, props vaurdan.
- Introduce `get_term_by_term_taxonomy_id` cache for term translator, props vaurdan.
- Replace an uncached, direct MySQL query with a `get_posts()` call, props vaurdan.
- Lots of
[late](https://vip.wordpress.com/documentation/best-practices/security/validating-sanitizing-escaping/#always-escape-late)
[escaping](https://vip.wordpress.com/2014/06/20/the-importance-of-escaping-all-the-things/).
- [Implement](https://make.wordpress.org/core/?p=17066) [selective refresh](https://make.wordpress.org/core/?p=16546)
support for the Language Switcher widget.
- Adapt the Term Translator to the new Edit Tag admin page introduced in WordPress 4.5.0.
- Use the new `network_site_new_form` action hook (where available) instead of injecting markup with jQuery. Yay!
- Delete the according Language nav menu items when a site is deleted.
- Improve site language(s) on network settings pages.
- Full JavaScript unit test coverage, see [pull request #201](https://github.com/inpsyde/multilingual-press/pull/201).
- Add the MultilingualPress settings page link to the plugin list in the WordPress Network Admin.
- Fix `hreflang` links and headers, see [issue #202](https://github.com/inpsyde/multilingual-press/issues/202), props
jasuja.

= 2.3.2 =
- Fix leftover entry from site option included in languages data, see
[issue #183](https://github.com/inpsyde/multilingual-press/issues/183), props kraftner.
- Fix potentially invisibe plugin activation row on Add New Site page.
- Run `post_name` through `urldecode` to account for non-ASCII characters, see
[issue #186](https://github.com/inpsyde/multilingual-press/issues/186), props luisarn.
- Fix incorrect `ml_type` value of duplicated custom post type posts, see
[issue #185](https://github.com/inpsyde/multilingual-press/issues/185), props kraftner.
- **Developers:** As we would like to use [the official WordPress.org GlotPress for translating
MultilingualPress](https://translate.wordpress.org/projects/wp-plugins/multilingual-press), we will (have to) change the
plugin text domain from `multilingualpress` to `multilingual-press` with the next (major) release. So, in case you are
doing _crazy_ things with our translations (which you basically should really not), please be informed.

= 2.3.1 =
- Fix potentially invalid semi-hard-coded paths.

= 2.3.0 =
- Adapt potentially deprecated settings of Language Switcher widget, see
[issue #170](https://github.com/inpsyde/multilingual-press/issues/170).
- Delete `state_modules` site option on uninstall, props tiagoschenkel.
- Adapt Site Settings tab code for WordPress 4.4, props patricia70.
- Change settings page headings from h2 to h1.
- Integrate WordPress multisite installation tutorial into readme, see
[issue #178](https://github.com/inpsyde/multilingual-press/issues/178).
- Hide Redirect UI if the Redirect feature is disabled, see
[issue #177](https://github.com/inpsyde/multilingual-press/issues/177).
- Fix missing noredirect query var for all URLs of linked elements, see
[issue #174](https://github.com/inpsyde/multilingual-press/issues/174).
- New setting: Fire plugin activation hooks for active plugins when a site has been duplicated.
- Feature: Show sites with their alternative language title in the admin bar, see
[issue #110](https://github.com/inpsyde/multilingual-press/issues/110).

= 2.2.3 =
- Bugfix Translation meta box not visible, see [issue #166](https://github.com/inpsyde/multilingual-press/issues/166),
props gabsoftware.

= 2.2.2 =
- Bugfix term auto-selecting, again.
- Use `realpath()` for plugin file in requirements check to allow for symlinked plugin folder, see
[issue #162](https://github.com/inpsyde/multilingual-press/issues/162), props jackblackCH.

= 2.2.1 =
- Handle deletion of post relations no matter from what site, see
[issue #156](https://github.com/inpsyde/multilingual-press/issues/156), props kraftner.
- Bugfix auto-selecting the first remote term without relationships.
- Improve validity check for table names (don't be more restrictive than WP core).

= 2.2.0 =
- **Merge MultilingualPress Free and Pro.**
- Pass `$wpdb` object to `inpsyde_mlp_init` and `inpsyde_mlp_loaded` hooks.
- Remove `checkup_blog_message()` and `checkup_blog()`.
- Pass original content from TinyMCE to translation editor.
- Add hooks before and after the term translation boxes.
- Do not show a translation box title when there are no linked sites.
- Add Russian translation.
- Cache some Language API queries internally to avoid duplication.
- Cache query in `get_relations()`.
- If specified, always use the Custom Name in `get_name()`.
- Fallback to site URL when translation query is not strict.
- Introduce `Mlp_Db_Table_List_Interface` interface and `Mlp_Db_Table_List` class.
- Introduce `replace_string()` in `Mlp_Db_Replace`.
- Add access to invalid column names in `Mlp_Db_Replace` class.
- Introduce `mlp_hreflang_html` and `mlp_hreflang_http_header` filters in `Mlp_Hreflang_Header_Output` class.
- Better table name query.
- Introduce `mlp_translations` filter in `Mlp_Language_Api` class.
- Do not exclude non-public sites for relations anymore.
- Better check for separate home page (page for posts).
- Introduce `mlp_dashboard_widget_access` filter in `Mlp_Dashboard_Widget` class.
- Add `Mlp_Term_Translation` class.
- Introduce `mlp_show_translation_completed_checkbox` filter in `Mlp_Dashboard_Widget` class.
- Prevent lost site relations and duplicated languages, see
[issue #78](https://github.com/inpsyde/multilingual-press/issues/78).
- Add `README.md` file, see [issue #86](https://github.com/inpsyde/multilingual-press/issues/86).
- Move all feature directories one level up, see [issue #84](https://github.com/inpsyde/multilingual-press/issues/84).
- Improve User Backend Language, see [issue #89](https://github.com/inpsyde/multilingual-press/issues/89).
- Add visibility checkbox to site duplication screen, see
[issue #93](https://github.com/inpsyde/multilingual-press/issues/93).
- Pass translation object to `mlp_linked_element_link` filter, see
[issue #98](https://github.com/inpsyde/multilingual-press/issues/98).
- Fix order when sorting by priority, see [issue #99](https://github.com/inpsyde/multilingual-press/issues/99).
- Add French translation, props fxbenard.
- Add important features to readme files, see [issue #106](https://github.com/inpsyde/multilingual-press/issues/106).
- Add Language column to the network site table, see
[issue #92](https://github.com/inpsyde/multilingual-press/issues/92).
- Bugfix all relationships being removed on blog deletion, see
[issue #97](https://github.com/inpsyde/multilingual-press/issues/97).
- Add missing `hreflang` attribute to quicklinks, see
[issue #120](https://github.com/inpsyde/multilingual-press/issues/120).
- Use current blog language for `html` tag, see [issue #118](https://github.com/inpsyde/multilingual-press/issues/118).
- Do not redirect while doing AJAX, no matter if `admin-ajax.php` or not, see
[issue #121](https://github.com/inpsyde/multilingual-press/issues/121).
- Prevent PHP Notice in `Mlp_Language_Api` class, props iamntz.
- Save translations as long as title or content is given, see
[issue #123](https://github.com/inpsyde/multilingual-press/issues/123).
- Use grunt, and refactor and improve assets.
- Add missing `hfreflang` attribute for content's own language, see
[issue #114](https://github.com/inpsyde/multilingual-press/issues/114).
- Improve Language Switcher widget, see [issue #112](https://github.com/inpsyde/multilingual-press/issues/112), props
dboune.
- Remove long deprecated filters `mlp_pre_save_postdata` and `mlp_pre_update_post`.
- Improve DB functions.
- Deprecate `get_blog_language()` in favor of `mlp_get_blog_language()`.
- Update language information.
- Add post slug to Advanced Translator, see [issue #119](https://github.com/inpsyde/multilingual-press/issues/119).
- Show Translation meta box only for users who have the required capability, see
[issue #116](https://github.com/inpsyde/multilingual-press/issues/116).
- Add post excerpt to Advanced Translator, see [issue #102](https://github.com/inpsyde/multilingual-press/issues/102).
- Update German translation.
- Add Dutch and Greek translation.
- Remove upgrade notice, see [issue #147](https://github.com/inpsyde/multilingual-press/issues/147), props
kraftner.
- Add confirmation to post saving when relationships were changed, see
[issue #131](https://github.com/inpsyde/multilingual-press/issues/131).
- Get rid of an Undefined index PHP notice, see [issue #132](https://github.com/inpsyde/multilingual-press/issues/132),
props deantomasevic.
- Bugfix no-redirect links don't properly set session to not redirect, see
[issue #138](https://github.com/inpsyde/multilingual-press/issues/138), props dboune.
- Get rid of possibly mixed content relations, see
[issue #155](https://github.com/inpsyde/multilingual-press/issues/155), props kraftner.
- Allow removing all terms of a remote post in the advanced translator, see
[issue #154](https://github.com/inpsyde/multilingual-press/issues/154), props kraftner.

= 2.1.2 =
- Combine all scripts and stylesheets, separated for frontend and backend.
- Minify scripts and stylesheets when `SCRIPT_DEBUG` and `MULTILINGUALPRESS_DEBUG` are not set.
- Make the icon/flag for the current site available in nav menus.
- Sites with custom name are now returned in `Mlp_Language_Api::get_translations()`.
- Better updates: Make sure that site relations are not lost and languages are not duplicated.

= 2.1.1 =
- Fixed autoloading with `glob()` on Solaris systems.
- Fixed database error when upgrading from a preview version of the 2.1 branch.
- Custom flags are now fetched from the correct site.
- Built-in flag icons are checked on the file system before we return an URL for them.
- Language switcher widget is now visible for all users.
- Improved description of the widget options.
- Search pages are translated correctly.
- Pro: Table duplicator works with custom tables now.

= 2.1.0 =
- Added links to translations to the `head` element.
- Relations between sites are now stored in a separate table `mlp_site_relations`. This is faster than the previous
option call, and it is less error prone, because we don’t have to synchronize these relations between sites. The old
options will be imported into the table automatically during the upgrade.
- Pro: Post meta fields in poorly written plugins will not be overwritten anymore. We had many reports about plugins
without a check for the current site when they write meta fields. Now we remove all global post data before we
synchronize the posts, and we restore them when we are done.
- Installation and uninstallation are heavily improved now. We catch many more edge cases and switches from Free to Pro.
- Languages are now synchronized between MultilingualPress and WordPress. When you assign a language in
MultilingualPress to a site the first time and the language files are available, we set the site language in the
WordPress option to that value.
- You can add language links to regular navigation menus in the backend now. These links are adjusted automatically on
each site: if there is a dedicated translation, the link will be changed to that page. It will point to the other site’s
front page otherwise.
- Users who are not logged in will not get permalinks for non-public sites anymore. You can work on a new site now
safely, test all the links while being logged in, and your visitors will never see that until you set the site to
public.
- You can link existing terms (tags, categories, whatever) now. We will add support for term creation on that page
later.
- There are hundreds of other, minor improvements, too many to list them all.

= 2.0.0 =
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

= 1.1.1 =
- Fix incorrect URLs when front page is set as static page or custom post type.

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
  any questions, please [contact us](http://inpsyde.com/#contact) before you write new code.
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
- Set the parent page if this page was also handled by the plugin
[Issue 2](https://github.com/inpsyde/multilingual-press/issues/2)
- Fix a problem that a new multisite cannot set related blogs
- Change filter [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
- Widget bugfix [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
- Smaller source via use function selected() [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
- Static value for register widget [Issue 12](https://github.com/inpsyde/multilingual-press/issues/12)
- Update Wiki for wrapper functions [Wiki on Repo](https://github.com/inpsyde/multilingual-press/wiki)
- Add new pages on [Wiki on Repo](https://github.com/inpsyde/multilingual-press/wiki) for Filter- and Action Hooks
inside the plugin
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
