=== Contact Form 7 to Database Extension ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=NEVDJ792HKGFN&lc=US&item_name=Wordpress%20Plugin&item_number=cf7%2dto%2ddb%2dextension&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: contact form,database
Requires at least: 2.9
Tested up to: 3.0.4
Stable tag: 1.6.1

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

=  Plugin Fails when activated with fatal error =

<a id="php4error"></a>This indicates that you have your WordPress site configured to run using PHP4 whereas this plugin requires PHP5.
When using Apache as the web server, you can edit the __.htaccess__ file at the root of your WordPress installation and add these two lines:

    AddType x-mapp-php5 .php
    AddHandler x-mapp-php5 .php

= Where do I see the data? =

* Contact Form 7 Users: In the admin page, under CF7's top level "Contact" admin menu. Look for "Contact" -> "Database"
* Fast Secure Contact Form Users: In the admin page, Plugins -> FS Contact Form Option, There is a "Database" link at the top of the page
* For a direct link, use http://<your-wordpress-site>/wp-admin/admin.php?page=CF7DBPluginSubmissions

= What is the Excel Internet Query Export Option? =

<a id="iqy"></a>This option exports a file that you can open in MS Excel. Unlike other exports, it is does not contain the data (initially), but creates an internet connection to the plugin page to extract the data.
The data can be refreshed from directly within Excel so there is no need to do an export every time there is new data.

= What are the differences among Excel CSV (UTF8-BOM), Excel TSV (UTF16LE-BOM) and Plain CSV (UTF8) Export files? =
<a id="csv"></a>

* For any non-Excel spreadsheet application, use 'Plain CSV (UTF8)'.
* For Excel, first try 'Excel CSV (UTF8-BOM)' and if that does not work property, try 'Excel TSV (UTF16LE-BOM)'.
* Excel CSV (UTF8-BOM) is generally good for most Microsoft Excel installations. But in some cases Excel will not show non-western latin characters properly from UTF8-BOM file.

    In that case, you can use Excel TSV (UTF16LE-BOM) but this format has a downside. The UTF16LE-BOM format cannot handle new lines inside of entries. Therefore the plugin converts new lines to spaces.

    In other words, if you have a form with a text area where people enter multi-line input, the returns are converted to spaces in UTF16LE-BOM but are preserved in UTF8-BOM.

= Can I display form data on a non-admin web page or in a post? =

<a id="shortcodes"></a>Yes, see below about shortcodes `[cfdb-datatable]`, `[cfdb-table]`, `[cfdb-json]` and `[cfdb-value]`

= How to use [cfdb-datatable] shortcode to incorporate form data on posts and pages =
<a id="cfdb-datatable"></a>Use `[cfdb-datatable form="your-form"]`.
This shortcode provides a dynamically filterable and sortable table on your page.
This is different than the `filter` parameter on the shortcode.
The `filter` parameter (such as in `[cfdb-datatable form="your-form" filter="field1=value1"]` creates a table
with only the data selected by the filter. But once you have a table showing on a page, you can further dynamically
filter using the "search" field above the displayed Datatable.

Notes:
* All options that apply to `[cfdb-table]` also apply to `[cfdb-datatable]`. Refer to the `[cfdb-table]` section.
* `[cfdb-datatable]` relies on [DataTable](http://www.datatables.net "DataTable") Javascript.
* If you want to set [DataTable Features](http://www.datatables.net/usage/features "DataTable Features") on your table,
then you can pass them using the `dt_option` shortcode parameter.

    Example:

    `[cfdb-datatable form="your-form" id="mytable" dt_options="bJQueryUI:true, sScrollX:'100%', bScrollCollapse:true"]`

    Outputs the following. So be sure you are providing valid JavaScript syntax.

    `<script type="text/javascript" language="Javascript">
        jQuery(document).ready(function() {
            jQuery('#mytable').dataTable({
                bJQueryUI:true, sScrollX='100%', bScrollCollapse=true
            })
        });
    </script>`

= How to use [cfdb-table] shortcode to incorporate form data on posts and pages =

<a id="cfdb-table"></a>Use `[cfdb-table form="your-form"]` with optional `show` and `hide`:

* `[cfdb-table form="your-form" show="field1,field2,field3"]` (optionally show selected fields),
* `[cfdb-table form="your-form" hide="field1,field2,field3"]` (optionally hide selected fields)

## Basic Shortcode ##
* `[cfdb-table form="your-form"]`                             (shows the whole table with CSS provided by the plugin)

## Shortcode: Controlling the Display ##
<a id="shortcode-display"></a>Apply your CSS to the table; set the table's 'class' or 'id' attribute:

* `[cfdb-table form="your-form" class="css_class"]`           (outputs `<table class="css_class">` (default: class="cf7-db-table")
* `[cfdb-table form="your-form" id="css_id"]`                 (outputs `<table id="css_id">` (no default id)
* `[cfdb-table form="your-form" id="css_id" class="css_class"]` (outputs `<table id="css_id" class="css_class">`)

Applying different styles to different columns:
By default, all `<th>` and `<td>` tags are given `title=` the field (column) name.
For example, for `field1` you will see tags:

* `<th title="field1"><div>field1</div></th>`
* `<td title="field1"><div>{row value for field1}</div></td>`

(Notice the there is a `<div>` nested in the `<th>` and `<td>`)

Use this `title` attribute to style columns and their headers individually.
Example CSS selectors:

* Assuming shortcode: `[cfdb-table form="myform" class="myformclass"]` that includes form field/table column `field1`
* `table.myformclass th[title="field1"] {}` selects the `<th>` for column `field1`

    Use this to style just the `<th>` (like its height and width) but not the text in the cell.
    Remember: the text of the header is nested inside a div (`<th><div>`)
* `table.myformclass th[title="field1"] > div {}` selects the nested div (`<th><div>`) for column `field1`

    Use this to specifically format the column header text.
* `table.myformclass td[title="field1"] {}` selects each table cell `<td>` in the column `field1`

    Use this to style just the `<td>` (like its height and width) but not the text in the cell.
    Remember: the text of the field is nested inside a div (`<td><div>`)
* `table.myformclass td[title="field1"] > div {}` selects the nested div (`<th><div>`) for column `field1`

    Use this to specifically format the text in the table cells

## Shortcode: Filtering In and Out Columns: ##
<a id="shortcode-filter-columns"></a>

* `[cfdb-table form="your-form" show="field1,field2,field3"]` (optionally show selected fields)
* `[cfdb-table form="your-form" hide="field1,field2,field3"]` (optionally hide selected fields)
* `[cfdb-table form="your-form" show="f1,f2,f3" hide="f1"]`   (hide trumps show, f1 will be hidden)

## Shortcode: Filtering In Rows: ##
<a id="shortcode-filter-rows"></a>

* `[cfdb-table form="your-form" filter="field1=value1"]`      (show only rows where field1=value1)
* `[cfdb-table form="your-form" filter="field1=null"]`        (SPECIAL CASE: 'null' is interpreted as null-value (field does has no value)
* `[cfdb-table form="your-form" filter="field1!=value1"]`     (show only rows where field1!=value1)
* `[cfdb-table form="your-form" filter="field1=value1&&field2!=value2"]` (Logical AND the filters using '&&')
* `[cfdb-table form="your-form" filter="field1=value1||field2!=value2"]` (Logical OR the filters using '||')
* `[cfdb-table form="your-form" filter="field1=value1&&field2!=value2||field3=value3&&field4=value4"]` (Mixed &&, ||. Standard Boolean operator precedence applies (ANDs are evaluated, then ORs)
* `[cfdb-table form="your-form" filter="field1~~/^a/"]`       (Regular expression; shows rows where field1 starts with 'a')

## Shortcode: Supported Filter Operators ##
<a id="shortcode-filter-ops"></a>

* `=` and `==` are the same
* `!=`, `<>` are the same
* `>`, `<`, `<=`, `>=`
* `===` and `!==`
* `~~` for regular expressions

## Shortcode: Filter by Regular Expressions ##
<a id="shortcode-filter-regex"></a>

* Use the `~~` operator, and Perl-style delimiters around the pattern, such as `/`
* `[cfdb-table form="your-form" filter="field1~~/^a/"]`     (shows rows where field1 starts with 'a')
* `[cfdb-table form="your-form" filter="field1~~/.*@gmail.com/i"]`  (shows rows where field1 is a Gmail address, case-insensitive)
* FYI: uses [preg_match](http://php.net/manual/en/function.preg-match.php "preg_match") to evaluate the regex

## Shortcode: Filter Limitations ##
<a id="shortcode-filter-limitations"></a>

* Does not support parentheses to control the order of boolean evaluation

## Shortcode: Filter Variable Substitution: Identifying logged-in user ##
<a id="shortcode-user-vars"></a>

If the user is logged in when viewing the page with the shortcode, you can try to match a filter value against
some user information. If the user was logged in when he submitted the form, then 'Submitted Login' will be captured (since version 1.4.4)
So if the user is also logged in to view a page with this shortcode, you could have the table filter to show him only
his submissions using:

* `[cfdb-table form="your-form" filter="Submitted Login=$user_login"]`

Similarly, if the user entered his email in a form field, (say "email"), and perhaps was not logged in but entered
the same email address as is associated with his WordPress account, then later came back to view a page when logged in,
you could show him his entry using:

* `[cfdb-table form="your-form" filter="email=$user_email"]`

All of the following variables are supported

* `$user_login`
* `$user_email`
* `$first_name` or `$user_firstname`
* `$last_name` or `$user_lastname`
* `$user_nicename`
* `$id` or `$ID`

## Shortcode: Filter Variable Substitution: Using HTTP GET, POST and COOKIE variables ##
<a id="shortcode-filter-params"></a>

When viewing a page or post, you can add HTTP GET parameters on the URL, for example the URL to view post #85
might be:

* `http://mywordpress.com/?p=85`

to which you could add some arbitrary parameter:

* `http://mywordpress.com/?p=85&email=joe@nowhere.com`

in this case you might want to use that `email=joe@nowhere.com` in the shortcode filter. Assuming the table
you are querying has a field named 'contact_email', you could use the shortcode:

* `[cfdb-table form="your-form" filter="contact_email=$GET(email)"]` This looks for form submissions where the
submitted value for the form's contact_email field is equal to joe@nowhere.com.

This syntax can be used:

* `$GET(http_get_parameter)` for URL parameters as described above or forms posting to the page on which the
shortcode is located, where the form uses method=GET
* `$POST(http_post_parameter)` for forms posting to the page on which the shortcode is located,
where the form uses method=POST
* `$COOKIE(http_cookie_name)` to reference Cookies.

<a id="warning-on-name"></a>WARNING: Known issue: Avoid using `name` as a GET parameter. This example will not work:

* `http://mywordpress.com/?page_id=128&name=admin` __WILL NOT WORK!!__

this gives you a page with the error:

* `Apologies, but the page you requested could not be found. Perhaps searching will help.`

The problem is with using `name`. Use something else, like `name1` and use `$GET(name1)` in your filter.
In WordPress, your URL does not go directly to the page, it goes to `http://mywordpress.com/` in this
example and that takes the parameters and dispatches it to the appropriate page/post etc. So you
have to choose GET parameters names that do not conflict with those that WordPress uses.
`name` is such a conflict. I don't a list of all conflicts, but look for the above error.

WARNING: PHP programmers: note the syntax and don't get confused with similar PHP syntax:

* `$GET(value)` __not__ `$_GET['value']`
* `$POST(value)` __not__ `$_POST['value']`
* `$COOKIE(value)` __not__ `$_COOKIE['value']`

Summary of differences from PHP syntax:

* Parentheses are used instead of square brackets because the shortcode already has brackets and we can't nest them within it.
* Quoting 'value' is not necessary since you are already quoting the shortcode attribute,
and this would result in nested quotes.
* The leading underscore is dropped for brevity and to be consistent with other variable substitutions (like `$user_login`)

## Shortcode: Debugging Filter Expressions ##
<a id="shortcode-filter-debug"></a>

If you have a complicated filter expression that may not be working right, you can get a printout of the parse tree.
To do this, you add debug="true", e.g. `[cfdb-table form="your-form" debug="true"]`

For example, if you had `[cfdb-table form="your-form" debug="true" filter="aaa=bbb||ccc=ddd&&eee=fff"]` then you
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

= How to use [cfdb-json] shortcode to incorporate form data on posts and pages =
<a id="cfdb-json"></a>Use `[cfdb-table form="your-form"]` with optional `show`, `hide` and `filter` just like `[cfdb-table]`.

The `[cfdb-json]` works much the same as the `[cfdb-table]` tag (see above) but it outputs a `<script>` tag
in the HTML in which it set a Javascript variable equal to a JSON representation of the data.

* Use `var` to indicate the name of the javascript variable you would like.
For example `[cfdb-json var="mystuff"]` would result in Javascript `var mystuff = [the json data];`
* `show`, `hide`, `filter` options work just as they do for [cfdb-table]. Refer to the documentation on that tag.

The JSON data will be in the form of an array of rows. Each row is a submission entry. Each row is a map of
column-name -> value. Each "cell" is referenced by `jsonVar[row-integer][col-name-string]`.
For example, using `[cfdb-json var="mystuff"]` you would be able to reference an example value
in Javascript using: `mystuff[0]["Submitted"]` to refer to the first row, "Submitted" column.

= How to make an AJAX call to get JSON =
<a id="ajax-json"></a>The quick way to see what URL you need to make an AJAX, go to the Database admin page and export
to type JSON. Then look at the URL in the browser. It will be of the following form (in this example our form name
is "Form Name" so we have to use "Form+Name" in the URL:

`http://mywordpress.com/wp-content/plugins/contact-form-7-to-database-extension/export.php?form=Form+Name&enc=JSON`

Issues: you will run into problems if you not logged in when making this AJAX call because WordPress will redirect you
to the login form page. To get past that, you need to use the URL for the login page with a `redirect_to` parameter
that gives the URL that you would have wanted above. The problem is that you have to URL-encode the parameters in
that URL. In other words,

Example: (You will have to substitute `EncodedFormName` below for the URLEncoded name of your form)

`http://mywordpress.com/wp-login.php?redirect_to=/wp-content/plugins/contact-form-7-to-database-extension/export.php%3Fform%3DEncodedFormName`

NOTE: currently there is no `show`, `hide` or `filter` option for this kind of AJAX call.

= How to use [cfdb-value] shortcode to incorporate form data on posts and pages =
<a id="cfdb-value"></a>Don't want a table or JSON, just want to put a value in the page? Use the `[cfdb-value]` shortcode

Example: [cfdb-value form="your-form" show="field1" filter="Submitted Login=$user_login"]
would display the field1 form value for the currently viewing user (who would have needed to be logged in when he
submitted...see documentation on $user_login).

The intention is to specify one column/field in `show` and specify a `filter` that would select on submission.
(see how this works for `[cfdb-table]`)
But if you specify more columns or have more submissions (rows) resulting in the filter, then this shortcode will print
out a comma-delimited list of values. You can also use `hide`.

= What is the name of the table where the data is stored? =

`wp_CF7DBPlugin_SUBMITS`
Note: if you changed your WordPress MySql table prefix from the default `wp_` to something else, then this table will also have that prefix instead of `wp_` (`$wpdb->prefix`)

= If I uninstall the plugin, what happens to its data in the database? =

The table and all its data are deleted when you uninstall. You can deactivate the plugin without loosing data.

= There used to be (pre-version 1.2) a top-level menu item in the Admin panel to see the data. Where did it go? =

It now under CF7's top level "Contact" admin menu. Look for "Contact" -> "Database". This is to take up less menu space and keep this extension's pages with those of CF7


== Screenshots ==

1. Admin Panel view of submitted form data

== Changelog ==

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
