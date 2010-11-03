=== Contact Form 7 to Database Extension ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=NEVDJ792HKGFN&lc=US&item_name=Wordpress%20Plugin&item_number=cf7%2dto%2ddb%2dextension&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: contact form,database
Requires at least: 2.9
Tested up to: 3.0.1
Stable tag: 1.3

Extension to the Contact Form 7 plugin that saves submitted form data to the database.

== Description ==

Takes submissions from Contact Form 7 plugin, saves them in the database and allows you to export the data.

First a disclaimer: I am not the maker of Contact Form 7 or associated with it's author.

That being said, I think Contact Form 7 is great...except for one thing. It does not save its information to the database. And if you get a lot of form submissions, then you end up sorting through a lot of email.

Fortunately, the author of CF7 created a hook. So I wrote a plugin that hooks into CF7 and saves all of its form submissions to the database. You need to have both CF7 and this plugin installed and activated.

This plugin also puts a menu item in the Administration Plugins menu where you can see the data in the database. It's pretty rudimentary but does the job.

== Installation ==

1. Be sure that Contact Form 7 is installed and activated (this is an extension to it)
1. Import contact-form-7-db.zip via the 'Plugins' menu in WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Adds an Admin menu item for seeing the stored data under "Contact" -> "Database"

Notes:

* Installing this plugin creates its own table. If you uninstall it, it will delete its table and any data you have in it. (But you can deactivate it without loosing any data).
* Tested on WP 3.0.1, PHP 5.2.13, MySQL 5.0 (Using 1and1 for hosting)

== Frequently Asked Questions ==

= Where do I see the data? =

In the admin page, under CF7's top level "Contact" admin menu. Look for "Contact" -> "Database"

= What is the Excel Internet Query Export Option? =

This option exports a file that you can open in MS Excel. Unlike other exports, it is does contain the data, but creates an internet connection to the plugin page to extract the data.
The data can be refreshed from directly withing Excel so there is no need to do an export every time there is new data.

= What is the difference between UTF-8 and UTF-16LE Export files? =

Try UTF-8 first and only use UTF-16LE if it does not work.
UTF-8 is generally good for most Microsoft Excel installation and all non-Excel spreadsheets that import CSV. But in some cases Excel will not show non-western latin characters properly from UTF-8.
In that case, you can use UTF-16LE (actually a TSV - tab separated values) but there is a downside. The UTF-16LE format cannot handle new lines inside of entries. Therefore the plugin converts new lines to spaces.
In other words, if you have a form with a text area where people enter multi-line input, the returns are converted to spaces in UTF-16LE but are preserved in UTF-8.

= What is the name of the table where the data is stored? =

wp_CF7DBPlugin_SUBMITS 
Note: if you changed your WordPress MySql table prefix from the default "wp_" to something else, then this table will also have that prefix insted of "wp_" ($wpdb->prefix)

= If I uninstall the plugin, what happens to its data in the database? =

The table and all its data are deleted when you uninstall. You can deactivate the plugin without loosing data.

= There used to be (pre-version 1.2) a top-level menu item in the Admin panel to see the data. Where did it go? =

It now under CF7's top level "Contact" admin menu. Look for "Contact" -> "Database". This is to take up less menu space and keep this extension's pages with those of CF7


== Screenshots ==

1. Admin Panel view of submitted form data

== Changelog ==

= 1.3.1 =
* Now saves files uploaded via a CF7 form. When defining a file upload in CF7, be sure to set a file size limit. Example: [file upload limit:10mb]
* Made date-time format configurable.
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
