=== Contact Form 7 to Database Extension ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=NEVDJ792HKGFN&lc=US&item_name=Wordpress%20Plugin&item_number=cf7%2dto%2ddb%2dextension&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: contact form,database
Requires at least: 2.9
Tested up to: 3.1
Stable tag: 1.0

Extension to the Contact Form 7 plugin that saves submitted form data to the database.

== Description ==

First a disclaimer: I am not the maker of Contact Form 7 or associated with it’s author.

That being said, I think Contact Form 7 is great….except for one thing. It does not save its information to the database. And if you get a lot of form submissions, then you end up sorting through a lot of email.

Fortunately, the author of CF7 created a hook. So I wrote a plugin that hooks into CF7 and saves all of its form submissions to the database. You need to have both CF7 and this plugin installed and activated.

This plugin also puts a menu item in the Administration Plugins menu where you can see the data in the database. It’s pretty rudimentary but does the job.

== Installation ==

1. Be sure that Contact Form 7 is installed and activated (this is an extension to it)
1. Import contact-form-7-db.zip via the 'Plugins' menu in WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. The 'Plugins' menu will contain a menu item for seeing the stored data

Notes:
* Installing this plugin creates its own table. If you uninstall it, it will delete its table and any data you have in it. (But you can deactivate it without loosing any data).
* Tested on WP 3.0, PHP 5.2.13, MySQL 5.0 (Using 1and1 for hosting)

== Changelog ==

= 1.0 =
* Initial Revision.

