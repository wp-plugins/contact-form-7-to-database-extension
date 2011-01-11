=== Contact Form 7 to Database Extension ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=NEVDJ792HKGFN&lc=US&item_name=Wordpress%20Plugin&item_number=cf7%2dto%2ddb%2dextension&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: contact form,database
Requires at least: 2.9
Tested up to: 3.0.4
Stable tag: 1.4.5

Extension to the Contact Form 7 plugin that saves submitted form data to the database.

== Description ==

Takes submissions from Contact Form 7 plugin, saves them in the database and allows you to export the data.

First a disclaimer: I am not the maker of Contact Form 7 or associated with it's author.

That being said, I think Contact Form 7 is great...except for one thing. It does not save its information to the database. And if you get a lot of form submissions, then you end up sorting through a lot of email.

Fortunately, the author of CF7 created a hook. So I wrote a plugin that hooks into CF7 and saves all of its form submissions to the database. You need to have both CF7 and this plugin installed and activated.

This plugin also puts a menu item in the Administration Plugins menu where you can see the data in the database.
You can also use the [cf7db-table] shortcode to display the data on a non-admin page on your site.

== Installation ==

1. Be sure that Contact Form 7 is installed and activated (this is an extension to it)
1. Import contact-form-7-db.zip via the 'Plugins' menu in WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Adds an Admin menu item for seeing the stored data under "Contact" -> "Database"

Notes:

* Installing this plugin creates its own table. If you uninstall it, it will delete its table and any data you have in it. (But you can deactivate it without loosing any data).
* Tested on WP 3.0.1, PHP 5.2.13, MySQL 5.0 (Using 1and1 for hosting)

== Frequently Asked Questions ==

=  Plugin Fails when activated with fatal error =

This indicates that you have your WordPress site configured to run using PHP4 whereas this plugin requires PHP5.
When using Apache as the web server, you can edit the __.htaccess__ file at the root of your WordPress installation and add these two lines:

    AddType x-mapp-php5 .php
    AddHandler x-mapp-php5 .php

= Where do I see the data? =

In the admin page, under CF7's top level "Contact" admin menu. Look for "Contact" -> "Database"

= What is the Excel Internet Query Export Option? =

This option exports a file that you can open in MS Excel. Unlike other exports, it is does not contain the data (initially), but creates an internet connection to the plugin page to extract the data.
The data can be refreshed from directly within Excel so there is no need to do an export every time there is new data.

= What are the differences among Excel CSV (UTF8-BOM), Excel TSV (UTF16LE-BOM) and Plain CSV (UTF8) Export files? =

* For any non-Excel spreadsheet application, use 'Plain CSV (UTF8)'.
* For Excel, first try 'Excel CSV (UTF8-BOM)' and if that does not work property, try 'Excel TSV (UTF16LE-BOM)'.
* Excel CSV (UTF8-BOM) is generally good for most Microsoft Excel installations. But in some cases Excel will not show non-western latin characters properly from UTF8-BOM file.

    In that case, you can use Excel TSV (UTF16LE-BOM) but this format has a downside. The UTF16LE-BOM format cannot handle new lines inside of entries. Therefore the plugin converts new lines to spaces.

    In other words, if you have a form with a text area where people enter multi-line input, the returns are converted to spaces in UTF16LE-BOM but are preserved in UTF8-BOM.

= Can I display form data on a non-admin web page or in a post? =

Yes, use `[cf7db-table]` shortcode to incorporate form data on regular posts and pages.

BUT: First be sure to give users access.

* In the __Admin panel, Contact -> Database Options__, set "__Can See Submission data__" to the appropriate choice.

Use `[cf7db-table form="your-form"]` with optional `show` and `hide`:

* `[cf7db-table form="your-form" show="field1,field2,field3"]` (optionally show selected fields),
* `[cf7db-table form="your-form" hide="field1,field2,field3"]` (optionally hide selected fields)

## Basic Shortcode ##
* `[cf7db-table form="your-form"]`                             (shows the whole table with CSS provided by the plugin)

## Shortcode: Controlling the Display ##
Apply your CSS to the table; set the table's 'class' or 'id' attribute:

* `[cf7db-table form="your-form" class="css_class"]`           (outputs `<table class="css_class">` (default: class="cf7-db-table")
* `[cf7db-table form="your-form" id="css_id"]`                 (outputs `<table id="css_id">` (no default id)
* `[cf7db-table form="your-form" id="css_id" class="css_class"]` (outputs `<table id="css_id" class="css_class">`)

## Shortcode: Filtering In and Out Columns: ##
* `[cf7db-table form="your-form" show="field1,field2,field3"]` (optionally show selected fields)
* `[cf7db-table form="your-form" hide="field1,field2,field3"]` (optionally hide selected fields)
* `[cf7db-table form="your-form" show="f1,f2,f3" hide="f1"]`   (hide trumps show, f1 will be hidden)

## Shortcode: Filtering In Rows: ##
* `[cf7db-table form="your-form" filter="field1=value1"]`      (show only rows where field1=value1)
* `[cf7db-table form="your-form" filter="field1=null"]`        (SPECIAL CASE: 'null' is interpreted as null-value (field does has no value)
* `[cf7db-table form="your-form" filter="field1!=value1"]`     (show only rows where field1!=value1)
* `[cf7db-table form="your-form" filter="field1=value1&&field2!=value2"]` (Logical AND the filters using '&&')
* `[cf7db-table form="your-form" filter="field1=value1||field2!=value2"]` (Logical OR the filters using '||')
* `[cf7db-table form="your-form" filter="field1=value1&&field2!=value2||field3=value3&&field4=value4"]` (Mixed &&, ||. Standard Boolean operator precedence applies (ANDs are evaluated, then ORs)
* `[cf7db-table form="your-form" filter="field1~~/^a/"]`       (Regular expression; shows rows where field1 starts with 'a')

## Shortcode: Supported Filter Operators ##
* `=` and `==` are the same
* `!=`, `<>` are the same
* `>`, `<`, `<=`, `>=`
* `===` and `!==`
* `~~` for regular expressions

## Shortcode: Filter by Regular Expressions ##
* Use the ~~ operator
* `[cf7db-table form="your-form" filter="field1~~/^a/"]`   (shows rows where field1 starts with 'a')
* FYI: uses preg_match() to evaluate the regex

## Shortcode: Filter Limitations ##
* Does not support parentheses to control the order of boolean evaluation

## Shortcode: Filter Variable Substitution ##
If the user is logged in when viewing the page with the shortcode, you can try to match a filter value against
some user information. If the user was logged in when he submitted the form, then 'Submitted Login' will be captured (since version 1.4.4)
So if the user is also logged in to view a page with this shortcode, you could have the table filter to show him only
his submissions using:

* `[cf7db-table form="your-form" filter="Submitted Login=$user_login"]`

Similarly, if the user entered his email in a form field, (say "email"), and perhaps was not logged in but entered
the same email address as is associated with his WordPress account, then later came back to view a page when logged in,
you could show him his entry using:

* `[cf7db-table form="your-form" filter="email=$user_email"]`

All of the following variables are supported

* `$user_login`
* `$user_email`
* `$first_name` or `$user_firstname`
* `$last_name` or `$user_lastname`
* `$user_nicename`
* `$id` or `$ID`

## Shortcode: Debugging Filter Expressions ##
If you have a complicated filter expression that may not be working right, you can get a printout of the parse tree.
To do this, you add debug="true", e.g. `[cf7db-table form="your-form" debug="true"]`

For example, if you had `[cf7db-table form="your-form" debug="true" filter="aaa=bbb||ccc=ddd&&eee=fff"]` then you
would get a dump like the following, where

* Tree Level 1 elements are ORed
* Tree Level 2 elements are ANDed
* Tree Level 3 elements are comparison expressions

`aaa=bbb||ccc=ddd&&eee=fff` parses to:

    Array
    (
        [0] => Array
            (
                [0] => Array
                    (
                        [0] => aaa
                        [1] => =
                        [2] => bbb
                    )
            )
        [1] => Array
            (
                [0] => Array
                    (
                        [0] => ccc
                        [1] => =
                        [2] => ddd
                    )
                [1] => Array
                    (
                        [0] => eee
                        [1] => =
                        [2] => fff
                    )
            )
    )

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

= 1.4.5 =
* Added a PHP version check. This Plugin Requires PHP5 or later. Often default configurations are PHP4. Now a more informative error is given when the user tries to activate the plugin with PHP4.

= 1.4.4 =
* If user is logged in when submitting a form, 'Submitted Login' is captured 
* [cf7db-table] shortcode options for filtering rows including using user variables (see FAQ)
* [cf7db-table] shortcode options for CSS
* Can exclude forms from being saved to DB by name

= 1.4.2 =
* Added "cf7db-table" shortcode to incorporate form data on regular posts and pages. Use [cf7db-table form="your-form"] with optional "show" and "hide: [cf7db-table form="your-form" show="field1,field2,field3"] (optionally show selected fields), [cf7db-table form="your-form" hide="field1,field2,field3"] (optionally hide selected fields)

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

