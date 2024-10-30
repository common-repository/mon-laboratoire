=== Mon Laboratoire ===
Contributors: suaudeau
Donate link: https://monlabo.org/
Tags: user, science, monlabo, HAL, open science
Requires at least: 5.6
Tested up to: 6.6
Stable tag: 4.8.3
Requires PHP: 5.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easy pages for research labs (manage people and publications)

== Description ==
This WordPress plugin allows you to simply update the members, teams, themes, publications (from third party HAL or third party database Descartes Publi) of a laboratory's web pages without any knowledge of web programming.


== Installation ==
1. Go to your admin area and select Plugins -> Add new from the menu.
2. Search for "mon-laboratoire".
3. Click install.
4. Click activate.
5. Configure Mon Laboratoire's features you need at page admin.php?page=MonLabo_config
6. Place shortcodes in your pages (shortcode list is given at page "wp-admin/admin.php?page=MonLabo")

== Frequently Asked Questions ==

= Does MonLabo is functionnal in multisite? =

Yes, and it's getting better. Unfortunately there are few functionalites that do not yet work as attended. Future versions will fix that.

= If I want to display publications, what publication third party service should I configure ? =

Answer: By default you have to use HAL which is opened to all french-speaking scientific community. If you are in Paris Descartes University, you can use Descartes Publi (please contact author if you have no access to this service).

== Screenshots ==

1. Buit-in documentation
2. Features configuration page
3. Persons and structures edition page

== Changelog ==

= 4.8.3 =
*Release Date - 25 July 2024*

* BUG : CSS was not updated because of minification. Some bad display.
* TRANSLATION : Corrects a lot of minor formal translation errors (vocabulary for "plugin", typos, uppercases...)

= 4.8 =
*Release Date - 15 July 2024*

* Evolutions of functionalities :
    - NEW tab "Privacy" in admin interface :
        - NEW : Add a privacy option to hide emails on website
    - NEW tab "pages" in admin interface :
        - EVOL : Move configuration of attachment page for the personal pages to this new tab "Pages"
        - EVOL : Can also define a parent page for pages of teams, thematics and units
        - NEW : Adapt warnings about unconfigured parent pages: Inline menu in creation page button and in advanced tools.
    - IMPROVE "Advanced tools" in admin interface :
        - EVOL : the part "Manage pages" is redesigned and more beautiful
        - NEW : new buttons for correct parent page of persons / teams / groups / units
    - MISC : Update to the last version of the logo of CNRS
* Reliability :
    - CODE: ready and tested for WordPress 6.6
    - JS/CODE : Modernize obsolete jQuery calls
    - CSS/CODE : Optimise code. Normaly do not change display.
    - CODE : Minify js and css (for test, not yet appliyed)
    - Internationalization :
        - BUG : Bad parent page for translated pages
        - BUG : Pages with no hidden title had a translated page with hidden title
        - BUG : Restore display of all pages that was masked by Polylang on some select menus (solution : add '&lang=all' as pages parameters).
    - Import interface :
        - IMPROVE : Security, better check imported files.
        - BUG : Some special characters in fields were altered during an export then import operation
        - BUG : The algorithm for updating persons and teams via the import interface can potentialy confuse ids (bug never seen in real-life tests).
        - BUG : If an URL is given as an image of person imported, it was replaced by 'DEFAULT' instead of keeping URL.

= 4.7.2 =
*Release Date - 27 March 2024*

* BUG : For new installation option "Persons and teams" was not activated by default
* BUG : Syntax error on class_html.php for old version of PHP

= 4.7.1 =
*Release Date - 25 March 2024*

* Evolutions of functionalities :
    - NEW option to configure database table prefix for the plugin :
        - In multisite installation, different sites can now share the same Mon-Laboratoire data.
        - Add the option to manualy change wordpress table prefix
    - NEW / IMPROVE admin advanced tools for persons and structures :
        - NEW : The user can export data to CSV file (persons, team_members, teams, thematics, units)
        - NEW : The user can import persons, teams and their relations from CSV files.
        - IMPROVE: Reorganize, makes it clearer and color buttons for admin advanced tools interface.
        - IMPROVE: Delete option "advanced tools for persons and structures". The advanced tools are now always shown.
* Reliability :
    - CODE : Ready and tested for WordPress 6.5 (test OK - nothing need to be changed)
    - BUG/IMPROVE: In page admin -> Page edit members -> table view : display all pages links and no URL error on external address.
    - CODE: Improve privacy and avoid tabnabbing phishing for external URL (add rel="noopener noreferrer" after each target="_blank")
    - BUG: Documentation of shortcode [publications_list] was not inactivated when no publication server was selected in wp-admin/admin.php?page=MonLabo_config
    - CODE: Centralize use of dashicons
    - CODE: class Option can now adapt to any new option
    - CODE: Plugin can now run if php-mbstring librairy is not installed (in degraded mode for accentuated chars).
    - CODE: Plugin do not need php-curl librairy anymore

= 4.6 =
*Release Date - 6 November 2023*

* Evolutions of functionalities :
    - IMPROVE admin interface for large numbers of staff : 
        - Add a search field when selecting into large list (people, teams...)
        - Persons members can be driectly added from a team edit interface
    - Other IMPROVE of admin interface : 
        - Better (and colored) submit buttons
        - Add an advanced tool to toggle to draft all alumni pages
        - generate WordPress pages (persons, teams...) with the block editor format (and no more "classic" editor format).
        - Open external links of admin interface in new windows (suggestion of user)
        - clear publication cache when publication configuration is updated
    - IMPROVE Help : 
        - Add a link to a video presentation (in french) of functionalites.
        - Update URL of Aurehal
* Reliability :
    - IMPROVE : Signal an error if php curl plugin is missing.
    - BUG : Remove PHP warnings when "Persons and teams" configuration is uncheked
    - CODE : Ready and tested for WordPress 6.4 (test OK - nothing need to be changed)
    - CODE : Test with PHP 7.2.33
    - CODE : Improve WordPress coding standards to new CS 3.0.0
    - CODE : Add an autoloader
    - CODE : replace ( isset(a) && !empty(a) )  by !empty(a)
* Prepare monlabo version 5.0 :
    - Rewrite all publications management step 1 : beta v1 shortcode [publications_list2]

= 4.5.2 =
*Release Date - 14 August 2023*

* CODE : Ready and tested for WordPress 6.3 (test OK - nothing need to be changed)

= 4.5.1 =
*Release Date - 5 june 2023*

* CODE : Detect first configuration of plugin in order to a future installation task that will be proposed tu user.
* BUG : ERRORS with old versions of PHP (7.0.33)
* BUG : Impossible update of external URL of a person

= 4.4 =
*Release Date - 16 March 2023*

* IMPROVE : Add a much more visible button for emptying publications cache (add also a submenu to direct access this button)
* NEW : In tab "Advanced tool" :
            - Buttons for create missing pages of persons / teams / groups / units
            - Buttons for create missing translations of page of persons / teams / groups / units
            - Buttons for suppress invalid pages ID of persons / teams / groups / units
* BUG : The parent page of a translated page whas not the translated parent page of the page. Create this translated page if necessary.
* BUG : Uninstall was not functionnal (again). Retrofit V4.3.1
* CODE : Ready and tested for WordPress 6.2 (remove use of obsolete function get_page_by_title)
Minor or inconspicuous developments:
* EVOL : change URL hal.archives-ouvertes.fr to hal.science
* CODE : Set english as the default languages in code in order to prepare translation by external contributors
* CODE : Separate advanced features in specific files.

= 4.3.1 =
*Release Date - 10 January 2023*

* BUG : Uninstall was not functionnal (again).

= 4.3 =
*Release Date - 26 October 2022*

* NEW : Improve customization of texts and gendering for shortcodes :
    - Add inclusive french vocabulary (can be edited or suppressed with custom text config page)
    - Can customing texts about "supervised students" and "mentors".
    - Beautify custom text config page and add embeded graphical help
* BUG : Supress \ (backslash) in excess while converting ' (apostrophe) into ’ (right single quotation mark) in person names.
* BUG/EVOL: Authorize empty unit code (UMR XXXX => '')
* CODE : Ready and tested for WordPress 6.1
Minor or inconspicuous developments:
* EVOL : Add an official debugging option to shortcode [publications_list]

= 4.2 =
*Release Date - 27 September 2022*

* EVOL : Persons titles (ex : Pr. or Dr.) can be edited.
* IMPROVE : Add cache of HAL data in order to fasten rendering pages
* IMPROVE : In the configuration interface, clarify some legends of HAL fields
* CODE : Ready and tested for PHP 8.1
* BUG : HAL pages were empty when HAL server took more than 5s to generate
* BUG : Few text fields were not displayed in configuration menu
Minor or inconspicuous developments:
* HELP : Simplify default help : do not display help on Descates Publi if this database is not activated.
* UNIT TEST BUG : Increase page number of default teams in order not to get it randomnly in unit test

= 4.1.1 =
*Release Date - 29 August 2022*

* BUG : Uninstall was not functionnal.

= 4.1 =
*Release Date - 6 Jully 2022*

* EVOL : Add support of plugin PolyLang-pro
* EVOL : Update HAL logo and Universite Paris Cité logo
* BUG : In some rare cases, bad symetry in json encoding of URL in field wp_pos_ids (can have no \ before each / in database).
* CODE : refactoring class Edit_Members.
* CODE : Reduce static analyse warnings (Class_Page, Class_shortcode)
* CODE : Reduce PHPMD warnings. Create class Shortcode_static, a static interface to all non static code of shorcodes.

= 4.0.1 =
*Release Date - 24 May 2022*

* BUG : Bad PolyLang translated links in some shortcodes (take into account current page language before get link of a translated page)

= 4.0 =
*Release Date - 23 May 2022*

* Evolutions of functionalities :
	- IMPROVE : New config interface for managing pages of Persons, Teams, Thematics and Units
		- Add a radio-buttons-group Create/Choose/Edit/None for pages
		- Unification of interface for managing pages (same human friedly menus instead of sometime directly type IDs list)
		- 'External URL' is no more needed for Teams, Thematics and Units (fusion with this new functionality)
		- Create page if asked for Teams, Thematics and Units
		- Create multilingual pages if PolyLang activated
		- Signal pages attributed to other persons and structure
	- EVOL/IMPROVE : Translated pages with PolyLang are now well managed
		- translated pages are automatically used by the shortcodes
		- translated pages are displayed in config interface of person and structures
		- Create or delete translated page automatically
	- IMPORVE : add nice icons in admin menu
	- EVOL (minor) : Name of the config tabs URL are more expressive
	- EVOL : Persons picture is now managed more simply, i.e. separatly than featured image of the main page of person (drop also 'alternate image' field).
* Correct bugs :
	- BUG : in [members_chart] remove persons that are marked as not visible.
	- BUG : correct bad HTML in "custom texts" config page
* Improve reliability:
	- Ready and tested for WordPress 6.0
	- CODE : Deep refactoring : Simplify/reorganize a lot of class, properties and variables

= 3.6 =
*Release Date - 25 January 2022*

* Ready and tested for WordPress 5.9
* CODE: Strong refactor of code (access to Person and structures...) :
 - add classes Main_Struct, Persons_Group, Teams_Group
 - reducing warnings with static analizer PhpStan
 - better isolation between Person and structure models and controlers
 - reduce complexity of class AccessData (to be contunued)
* CODE (Minor) : clean CSS ways to prevent words to be cuted in panels

= 3.5.1 =
*Release Date - 6 October 2021*

* BUG : Remove debug print.

= 3.5 =
*Release Date - 1 October 2021*

* Evolutions of functionalities :
  - BUG/EVOL : Better integration of Polylang, links are pointing on translated pages
  - NEW (admin interface): We can now add several mentors and students that are from outside the laboratory
  - NEW (admin interface): Mentors and students selection interfaces are hidden by default. Can be shown with a button.
* Improve reliability:
  - CODE: Deep refactoring in process (new classes...)
  - BUG: New line character was not kept in several text area
  - CODE: upgrade licence version from GPL-2.0-or-later to GPL-3.0-or-later

= 3.4 =
*Release Date - 15 Jully 2021*

* Evolutions of functionalities :
    - EVOL: Improve [teams_list] - directors names are no more simple texts but links to their pages
    - CODE/EVOL : Transformation to uppercase of persons names is now done by CSS (thus can be reversed)
* Improve reliability:
    - CODE: ready and tested for WordPress 5.8
    - BUG: bad link to person that have multiple pages
    - BUG: Edition form of an alumni person do no more forget the state of alumni
    - BUG (minor):  Correct bad alphabetic order in editing form (table view)
    - BUG (minor): Correct some bad display of default picture of persons in editing form
    - CODE: Correct hundreds of warnings given by static analizer PHPMD  (PHAN static analyser is alredy used).

= 3.3 =
*Release Date - 15 March 2021*

* NEW : new language admin menu that permits:
    - force language in french or english
    - or, translate page in the language of user's browser
    - or, be able to translate pages of persons, teams, units in two languages
    - or, use a translation plugin as Polylang
* NEW : accept either singular of plural form of parameters for most shortcodes.
    Parameters: year(s), categor(y/ies), team(s), unit(s), person(s), unit(s), group(s), thematic(s)
    Shortcodes: [members_list] [members_table] [members_chart] [former_members_list] [former_members_table] [former_members_chart] [teams_list] [publications_list]
* EVOL: Rename several shortcode with more explicit names (old names are still functionnal)
    - Rename [alumni_list] into [former_members_list]
    - Rename [alumni_table] into [former_members_table]
    - Rename [alumni_chart] into [former_members_chart]
Minor or inconspicuous developments:
    * FIXES/CHANGES : display order of teams list for a person no more by team_id but alpabetically.
    * CODE : reduce complexity of several functions
    * CODE : Apply WordPress coding standards


= 3.2 =
*Release Date - 25 February 2021*

* Evolutions of functionalities :
    - FEAT: shortcode [publications_list] : Permit to chose the type of HAL publications to display => Add option hal_typepub
    - FEAT: shortcode [teams_list] : Add options 'unit' and 'team'
    - EVOL: shortcode [teams_list] : Small rearange in design (badly arranged margins, limits and sizes. Limit logo of thematics in size).
    - EVOL: shortcode [team_panel] : Add links to thematics pages. Add logo and color block
* Correct bugs :
    - BUG: shortcode [teams_list] : Do not display groups if groups option is not enabled
    - BUG: shortcode [teams_list] : Cannot link internal team pages
    - BUG: Admin menu : New lines in "contact phone" of main structure was not taken into account
* Improve reliability :
    - Code ready and tested for WordPress 5.7
    - CODE: Correct hundreds of warnings given by static analizer Phan.
    - CODE: Declare type of most function parameters
    - CODE: Make code more independant with the use of namespace. Rename all class and their files.
    - CODE: Self document all code with PHPDOC
    - CODE: create a new class MonLabo_teams

= 3.1.3 =
*Release Date - 27 January 2021*

* Ready and tested for WordPress 5.6
* BUG : Repair broken link "Configuration" in extensions list
* BUG : On new installations, some default activated-options were not activated (at least MonLabo_uses_members_and_groups)
* BUG : Remove some warnings (function image_from_id_or_url())

= 3.1.2 =
*Release Date - 21 December 2020*

* BUG : On new installations, some default activated-options were not activated (at least MonLabo_uses_members_and_groups)

= 3.1.1 =
*Release Date - 7 December 2020*

* Ready and tested for WordPress 5.6
* BUG : Repair broken link "Configuration" in extensions list

= 3.1 =
*Release Date - 19 October 2020*

* Main evolutions of functionalities :
    - EVOL : A person can own several pages
    - EVOL : A person can own several emails (separated by coma)
    - EVOL : Add the option "person=" to shortcode [alumni_list] (as it already exists for [members_list])
* Main improvements or updates of interface:
    - DESIGN : Improve ergonomy of "Custom texts" config tab.
    - EVOL : Much easier configuration form for members WordPress address
    - EVOL : Correction of poor display of radio buttons in config interface.
* Main improvements about security and reliability:
    - CODE/BUG : Rewrite all code for chosing an image as a logo for Thematic, team or unit. Sometimes it was non functionnal.
    - CODE : Improve unit test coverage (6026 tests) and tested in WordPress multisite
* Minors :
    - EVOL : Allows user to customize text "Room" in team_panel.
    - DESIGN : Always displays "Custom texts" config tab. Lighten "Features" config tab.
    - EVOL : Add logo U-Paris. Suppress UPEC (non contributive since V2.0)
    - CODE : Detect obsolete database
    - CODE : Secure ajax code with a nonce to prevent unauthorized access
    - CODE : Secure all actions that accept POST with a nonce to prevent unauthorized access
    - BUG : Suppress php warnings that occur when creating new person, team, thematic or unit.
    - BUG : Suppress php warnings that occur when apparence fields in are empty in configuration interface.
    - BUG : Correct bad redirection of buttons for modifying teams, thematics or units in the admin tab "table view"
    - BUG : Bad alphabetic order of [members_list] with people that have accents in names. Order shoud be : E < É < F
    - BUG : Suppress warnings if database is empty
    - CODE : Correct few HTML warnings from W3C standards
    - BUG : Suppress a PHP warning in MonLabo_doc
    - BUG : In some server configuration, URL of default image for a person was wrong.

= 3.0.5 =
*Release Date - 28 Jully 2020*

* BUG : PHP error when activate multiple units mode and define no unit

= 3.0.4 =
*Release Date - 27 Jully 2020*

* BUG : Bad PHP warning of function error_MonLabo_perso_page_parent()

= 3.0.3 =
*Release Date - 26 Jully 2020*

* CODE: Convert text-domain and langage slug 'MonLabo' into 'mon-laboratoire' in order to be compatible with translate.wordpress.org
* BUG : Simplify some complex translation calls beause it was badly interpretated sometimes
* BUG: Solve potential errors of translations when blog language is different that page language (for instance if a translation plugin is installed).

= 3.0.2 =
*Release Date - 18 Jully 2020*

* BUG : Suppress warnings with PHP 7.4
* BUG : Sometimes "Room" was badly translated in shortcodes for members
* BUG : Sometimes language english was not taken into account : add en_GB translation
* BUG : Wordpress official repository do not recognize that main language is french : add fr_FR translation

= 3.0.1 =
*Release Date - 8 June 2020*

* BUG : Suppress warnings in admin page

= 3.0 =
*Release Date - 20 February 2020*

* EVOL : The plugin is changing its name from "MonLabo" to "Mon Laboratoire"
* EVOL : The plugin is now in WordPress plugin repository
* EVOL : Remove backward compatibility for following obsolete shortcodes :
    - [members_list_automatic], [get_members_list]
    - [custom_publications_list], [publications_automatic]
    - [perso_panel_automatic]
    - [alumni]
* CODE : Suppress dead codes.

See [changelog.txt](https://plugins.svn.wordpress.org/mon-laboratoire/trunk/changelog.txt) for older, minor or inconspicuous changelog

== Upgrade notice ==
