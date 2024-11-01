=== The Word Widget ===

Author: Helmut Steeb
Author URI: https://jsteeb.de
Tags: bible, bibel, losung, devotional, verse of the day, votd, sidebar, widget, An Bíobla Naofa, Bibelen, Bibel für Schwoba, Biblia Tysiąclecia, Bybel in Afrikaans, Chinese Union Version, English Standard Version, Hoffnung für Alle, Jubiläums-Bibel, Karoli, Kutsal Kitap, Leonberger Bibel, Neue Evangelistische Übersetzung, Modern Hebrew, New Arabic Version, Ketab el Hayat, Neue Evangelistische Übersetzung, Nuova Riveduta, O‘zbek tilidagi Muqaddas Kitob, Reina-Valera, Schlachter, Segond, Thai Holy Bible, Portuguese Bíblia Livre, Bible Thianghlim, Vietnamese Bible, Zimbrisch, Südsaarländisch, Cornilescu, Urdu Revision Version
Stable tag: trunk
Version: 0.9
Tested up to: 6.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows two Bible verses per day: "The Word" by project Bible 2.0, available in more than 20 languages, got remotely for each day

== Description ==

The plugin provides a widget which you can place into one of the widget areas provided by your theme (in the WordPress admin area: Appearance | Widgets).

The widget configuration retrieves the list of available Bible editions of the current year remotely from <https://bible2.net> and lets you select one Bible edition. On each day, the widget retrieves the two verses for the day from bible2.net and displays them.

### Project Bible 2.0

Project [Bible 2.0](https://bible2.net/) collects cross-references between selected Bible verses, formats the verses nicely and publishes two cross-referenced verses for each day of the year in more than 20 languages, available for free download and as a remote service.

### Bible Editions Available

Dated 2024-07-17 – for latest information, see <https://bible2.net/download/bible-editions-available/>.

For **2024**, The Word is available in the following Bible editions and languages:

* af Bybel in Afrikaans (1983-vertaling)
* ar كتاب الحياة : الترجمة العربية الجديدة (Holy Bible, New Arabic Version, Ketab El Hayat)
* cim Zimbrisch (German Cimbrian dialect), see <https://bible2.net/cim>
* da Bibelen (Danish)
* de Hoffnung für Alle (German)
* de Leonberger Bibel (German)
* de Neue Evangelistische Übersetzung (German)
* de Schlachter 2000 (German)
* en English Standard Version
* en Free Bible Version (new 2023!)
* es Reina-Valera 1995 (Spanish)
* fr Segond 21 (French)
* ga An Bíobla Naofa 1981 (Gaelic)
* gr Νέα Μετάφραση Βάμβα (Greek)
* he ספר הבריתות 2004 (Hebrew)
* hu Karoli 1990 (Hungarian)
* it Nuova Riveduta 1994 (Italian)
* pfl-x-suedsaar Südsaarländisch (Southern Saarlandian, a kind of Pfälzisch = Palatinate), see <https://bible2.net/pfl-x-suedsaar>
* pl Biblia Tysiąclecia (Polski Polish)
* pt Bíblia Livre em português (Portuguese)
* ro Cornilescu 2014 (Romanian), see <https://bible2.net/ro>
* ru Юбилейная Библия Jubiläums-Bibel (русский Russian)
* swg Bibel für Schwoba (Schwäbisch Swabian swg), see <https://bible2.net/swg>
* th สมาคมพระคริสตธรรมไทย 1971 (Thai Holy Bible 1971)
* tr Kutsal Kitap 2001
* ur کِتابِ مُقادّس (Urdu Revised Version)
* uz-Cyrl Ўзбек тилидаги Муқаддас Китоб 2012
* uz-Latn O‘zbek tilidagi Muqaddas Kitob 2012
- vi Vietnamese Bible (1934)
* zh-Hans 中文标准译本(简化字) (Simplified Chinese)
* zh-Hant 中文标准译本(繁體字) (Traditional Chinese)

### System Requirements

* PHP: tested with 7.4.
* SimpleXML: the widget needs the "SimpleXML" PHP library. If it is not available, the widget configuration (see "Appearance | Widgets" in the Installation chapter) will show an error message.

### Adapting the Layout 

The widget output uses the following CSS classes which you may adapt in your stylesheet (CSS):

* TheWord
* TL
* Parol
* IL
* L
* SL
	
An example CSS file is included in the plugin zip file. It includes CSS to either show the verses with line breaks (on a wider display) or without the line breaks (on a smaller display). You may need to adapt the rules, depending on the actual width of your widget area.

#### Workaround: Adapt your Theme Stylesheet

If you have no idea how to adapt your stylesheet, the following workaround may help:

- open your published WordPress page in a browser
- inspect the page sources (via right mouse, menu item like "View page source")
- search for `wp-content/themes`, you should find it in a `href` attribute of an HTML element `link`
- the `href` attribute value is an URL path to a stylesheet file of your WordPress theme
    * typically, the URL path ends like `.css?ver=1.2.3`
- on your web server, open the file located at that path (omit the trailing `?ver=1.2.3`)
    * typically, the file does not contain line-breaks
- just navigate to the end of the file, add a line-break, then enter the desired styles (you may start with the contents of the above mentioned example CSS file).
- save the file and reload the page in your browser.
    * Now you should see the effect of the modified stylesheet.

Note: keep a backup of the inserted styles - WordPress will likely overwrite the file when you update your WordPress version or the theme, so you'll have to apply the changes again.

#### Workaround: Adapt the Plugin Output

Example: you want the Bible 2.0 logo to open the bible2.net page not in the same but in a new browser Tab.

Precondition: you have access to the installed plugin files on your webserver. (If available, you better test this on a WordPress instance on a local computer first.)

Steps (based on plugin version 0.9):

- locate the file wp-content/plugins/the-word-widget/the-word-widget.php
- for backup, copy the file to another place
- locate the line
    * `# $theWord truthy`
- after that line, insert the following line (modifying the HTML link "target" to "_blank"):
    * `$theWord = str_replace('target="_top" class="b2-twd-start"', 'target="_blank" class="b2-twd-start"', $theWord);`
- save the file
- in the browser, refresh your web page that displays The Word
- click on the Bible 2.0 logo. This should now open the page in a new browser Tab.

By inspecting the page source in your browser, you may come up with other adaptations - like adding a specific CSS class or style.
Ensure you're using correct PHP syntax, e.g. don't use the outer single quotes unmasked within your replacement text.

Note: keep a backup of your change - WordPress will likely overwrite the file when you update your WordPress version or the theme, so you'll have to apply the changes again.

#### Further Examples

- Make the title line "The Word for..." a `H2` instead of a `P` element:
    * `$theWord = preg_replace("/<p\sclass='TL'>(.*?)<\/p>/s", "<h2 class='TL'>$1</h2>", $theWord);`
- Avoid the line-breaks in the verses (override CSS `white-space` to `normal`):
    * `$theWord = preg_replace("/(<p\sclass='L')>/", "$1 style='white-space: normal'>", $theWord);`
- Show the verse text in black color:
    * `$theWord = preg_replace("/(<p\sclass='L')>/", "$1 style='color: black'>", $theWord);`
- Show the Bible reference text in black color:
    * `$theWord = preg_replace("/(<p\sclass='SL')>/", "$1 style='color: black'>", $theWord);`

### For WordPress Developers

If you're expert in the hottest WordPress development stuff like the "Block editor", and you could imagine to **maintain this plugin** – you're welcome!

I don't follow new WordPress trends any more (I moved sites from WordPress to Symfony resp. Hugo).

So as of 2023-03, the WordPress admin dashboard supports configuring the plugin in "legacy" mode only. Maybe sometime they even obsolete this?

### Plugin License

The WordPress plugin is licensed under GPLv2 or later.

### License for Bible Texts (got remotely per day)

The license conditions for the Bible texts (verses) are defined by the publisher of the respective Bible edition. They are contained in the .twd XML files, and are also shown in <https://bible2.net/copyright/>.

### License for Related Bible References (got remotely per day)

The project Bible 2.0 provides pairs of related Bible references.

The Word associates one pair of Bible references with a certain day, respectively.

The association of Bible references into pairs and of such pairs to days of a certain year is subject to

    License “Creative Commons 4.0”
    <https://creativecommons.org/licenses/by-sa/4.0/>
    (Attribution, ShareAlike)

With each publication, the following statement with a link to <https://bible2.net> must be available for the user (e.g. by adding a link to the copyright page):

    Association of Bible references by project Bible 2.0


== Installation ==

1. unpack the zip file into your WordPress plugin folder `/wp-content/plugins/`;
2. in the WordPress admin area, in Plugins | Installed Plugins, activate the plugin;
3. in the WordPress admin area, in Appearance | Widgets, put the widget "The Word" into one of your widget areas;
4. within "The Word" widget, click into the box labelled "The Word", a combobox shows up
5. in the combobox, select the desired Bible edition
6. to see a preview, click outside the area labelled "The Word" (e.g. right to the top toolbar)
7. to apply the change to your site, click the "Update" button (previously: "Save" button) top-right in the "Widgets" window.

== Screenshots ==

1. The widget configuration
2. The widget in a wider widget area (line breaks preserved)
3. The widget in a small widget area (no line breaks)

== Changelog ==

= 0.9 =
* for use in a WP 5.8 Legacy Widget Block, moved the static method `html` from includes/admin-form.php into the-word-widget.php
* dropped the empty the-word-widget-admin.php
* tested with PHP 7.4.3 and WordPress 5.8 RC4

= 0.8 =
* use anonymous function (available since PHP 5.3.0) instead of create_function (deprecated in PHP 7.2)
* tested with PHP 7.2.15 and WordPress 5.1 RC2

= 0.7 =
* use https protocol to access bible2.net
* tested up to WordPress 4.8

= 0.6 =
* if the SimpleXML module is missing, the widget configuration form shows a detailed info.

= 0.5 =
* if the widget form (for admin) fails to retrieve the list of Bible editions from bible2.net, the form shows a detailed log of its actions, and some environment info (`allow_url_fopen`, `php_version()`, `$wp_version`, `$required_php_version`).

= 0.4 =
* on restricted server configurations,
* the plugin now falls back to a safer method to retrieve data from bible2.net
* (if PHP setting allow_url_fopen=Off, simplexml_load_file fails,
*  then the plugin uses the WordPress method wg_remote_get)

= 0.3 =
* the widget does not store a .twd file per year but uses the daily online service

= 0.2 =
* the widget form (for admin) retrieves the list of Bible editions from bible2.net,
* ... lets admin select the Bible edition
* ... and retrieves The Word .twd file for the selected Bible edition from bible2.net.
* the widget shows The Word from the Bible edition selected in the widget form.

= 0.1 =
* initial version

== Upgrade Notice ==
