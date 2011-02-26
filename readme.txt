=== Contact Form 7 to Database Extension ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=NEVDJ792HKGFN&lc=US&item_name=Wordpress%20Plugin&item_number=cf7%2dto%2ddb%2dextension&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: contact form,database
Requires at least: 2.9
Tested up to: 3.1
Stable tag: 1.6.5

Extension to the Contact Form 7 plugin that saves submitted form data to the database.

== Description ==

Saves form submissions to the database that come from Contact Form 7 (CF7) plugin and/or Fast Secure Contact Form (FSCF) plugin.

CF7 and FSCF are great plugins but but were lacking one thing...the ability to save the form data to the database.
And if you get a lot of form submissions, then you end up sorting through a lot of email.
This plugin-to-a-plugin provides that functionality.

You will need to have CF7 and/or FSCF installed along with this plugin.
This plugin also puts a menu item in the Administration Plugins menu where you can see the data in the database.
You can also use the [cfdb-table], [cfdb-datatable], [cfdb-value] and [cfdb-json] shortcodes to display the data on a non-admin page on your site.

Disclaimer: I am not the maker of Contact Form 7 nor Fast Secure Contact Form and am not associated with the development of those plugins.

== Installation ==

1. Your WordPress site must be running PHP5 or better. This plugin will fail to activate if your site is running PHP4.
1. Be sure that Contact Form 7 and/or Fast Secure Contact Form is installed and activated (this is an extension to them)
1. Fast Secure Contact Form should be at least version 2.9.7

Notes:

* Installing this plugin creates its own table. If you uninstall it, it will delete its table and any data you have in it. (But you can deactivate it without loosing any data).
* Tested on WP 3.0.1, PHP 5.2.13, MySQL 5.0 (Using 1and1 for hosting)

== Frequently Asked Questions ==

= Where can I find documentation on the plugin? =
Refer the <a href="http://cfdbplugin.com/">Plugin Site</a>


= Where do I see the data? =

* Contact Form 7 Users: In the admin page, under CF7's top level "Contact" admin menu. Look for "Contact" -> "Database"
* Fast Secure Contact Form Users: In the admin page, Plugins -> FS Contact Form Option, There is a "Database" link at the top of the page
* For a direct link, use http://<your-wordpress-site>/wp-admin/admin.php?page=CF7DBPluginSubmissions

= Can I display form data on a non-admin web page or in a post? =

Yes, <a href="http://cfdbplugin.com/?page_id=89">documentation on shortcodes</a> `[cfdb-datatable]`, `[cfdb-table]`, `[cfdb-json]` and `[cfdb-value]`

= What is the name of the table where the data is stored? =

`wp_CF7DBPlugin_SUBMITS`
Note: if you changed your WordPress MySql table prefix from the default `wp_` to something else, then this table will also have that prefix instead of `wp_` (`$wpdb->prefix`)

= If I uninstall the plugin, what happens to its data in the database? =

The table and all its data are deleted when you uninstall. You can deactivate the plugin without loosing data.


== Screenshots ==

1. Admin Panel view of submitted form data

== Changelog ==

= 1.7 =
* Creating an export from the admin panel now filters rows based on text in the DataTable "Search" field.
* [cfdb-json] now has "format" option.
* Fixed bug where "Submitted" column would sometimes appear twice in shortcodes
* Now can filter on "Submitted" column.
* Admin Database page is now blank by default and you have to select a form to display.

= 1.6.5 =
* Now fully supports internationalization (i18n) but we need people to contribute more translation files.
* DataTables (including those created by shortcodes) will automatically i18n based on translations available from DataTables.net
* Italian translation courtesy of Gianni Diurno
* Turkish translation courtesy of Oya Simpson
* Admin page DataTable: removed horizontal scrolling because headers do not scroll with columns properly
* Updated license to GPL3 from GPL2

= 1.6.4 =
* Bug fix: Fixed bug causing FireFox to not display DataTables correctly.

= 1.6.3 =
* Bug fix: Handling problem where user is unable to export from Admin page because jQuery fails to be loaded.

= 1.6.2 =
* Bug fix: avoiding inclusion of DataTables CSS in global admin because of style conflicts & efficiency

= 1.6.1 =
* Bug fix in CSV Exports where Submitted time format had a comma in it, the comma was being interpreted as a
field delimiter.
* Accounting for local timezone offset in display of dates

= 1.6 =
* Admin page for viewing data is not sortable and filterable
* New shortcode: [cfdb-datatable] to putting sortable & filterable tables on posts and pages.
    This incorporates http://www.datatables.net
* Option for display of localized date-time format for Submitted field based on WP site configuration in
"Database Options" -> "Use Custom Date-Time Display Format"
* Option to save Cookie data along with the form data. "Field names" of cookies will be "Cookie <cookie-name>"
See "Database Options" -> "Save Cookie Data with Form Submissions" and "Save only cookies in DB named"

= 1.5 =
* Now works with Fast Secure Contact Form (FSCF)
* New shortcode `[cfdb-value]`
* New shortcode `[cfdb-json]`
* Renamed shortcode `[cf7db-table]` to `[cfdb-table]` (dropped the "7") but old one still works.
* Added option to set roles that can see data when using `[cfdb-table]` shortcode
* Can now specify per-column CSS for `[cfdb-table]` shortcode table (see FAQ)
* Fixed bug with `[cfdb-table]` shortcode where the table aways appeared at the top of a post instead of embedded with the rest of the post text.

= 1.4.5 =
* Added a PHP version check. This Plugin Requires PHP5 or later. Often default configurations are PHP4. Now a more informative error is given when the user tries to activate the plugin with PHP4.

= 1.4.4 =
* If user is logged in when submitting a form, 'Submitted Login' is captured
* `[cfdb-table]` shortcode options for filtering rows including using user variables (see FAQ)
* `[cfdb-table]` shortcode options for CSS
* Can exclude forms from being saved to DB by name

= 1.4.2 =
* Added `[cf7db-table]` shortcode to incorporate form data on regular posts and pages. Use `[cf7db-table form="your-form"]` with optional "show" and "hide: [cf7db-table form="your-form" show="field1,field2,field3"] (optionally show selected fields), [cf7db-table form="your-form" hide="field1,field2,field3"] (optionally hide selected fields)

= 1.4 =
* Added export to Google spreadsheet
* Now saves files uploaded via a CF7 form. When defining a file upload in CF7, be sure to set a file size limit. Example: [file upload limit:10mb]
* Made date-time format configurable.
* Can specify field names to be excluded from being saved to the DB.
* In Database page, the order of columns in the table follows the order of fields from the last form submitted.

= 1.3 =
* Added export to Excel Internet Query
* "Submitted" now shows time with timezone instead of just the date.
* The height of cells in the data display are limited to avoid really tall rows. Overflow cells will get a scroll bar.
* Protection against HTML-injection
* Option to show line breaks in multi-line form submissions
* Added POT file for i18n

= 1.2.1 =
* Option for UTF-8 or UTF-16LE export. UTF-16LE works better for MS Excel for some people but does it not preserve line breaks inside a form entry.

= 1.2 =
* Admin menu now appears under CF7's "Contact" top level menu
* Includes an Options page to configure who can see and delete submitted data in the database
* Saves data in DB table as UTF-8 to support non-latin character encodings.
* CSV Export now in a more Excel-friendly encoding so it can properly display characters from different languages

= 1.1 =
* Added Export to CSV file
* Now can delete a row

= 1.0 =
* Initial Revision.

== Upgrade Notice ==

= 1.6 =
New cool DataTable

= 1.5 =
Now works with <a href="http://wordpress.org/extend/plugins/si-contact-form/">Fast Secure Contact Form</a>. Plus more and better shortcodes.
