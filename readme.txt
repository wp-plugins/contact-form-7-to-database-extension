=== Community Yard Sale ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3FF6MP948QPW
Tags: yardsale,yard sale,community yard sale
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.0

Community yard sale with filterable table and Google Map.

== Description ==

A community yard sale involves multiple yard sales within a community, usually at the same time. This event attracts
more buyers. But buyers need to know where each yard sale is located and want to know what items are for sale at each
yard sale. This plugin helps address these issues by (1) providing sellers an input form for people to list their
address and items being sold and (2) providing buyers a Google Map and searchable/filterable table of yard sale listings.

The administrative control panel leads you through creating short codes that generate an input form for sellers and
Google Map + table of listings for buyers.

== Installation ==

1. Your WordPress site must be running PHP5 or better. This plugin will fail to activate if your site is running PHP4.

Notes:

* Installing this plugin creates its own table. If you uninstall it, the table and its data remain.
* Tested using PHP 5.2.13, MySQL 5.0 (Using 1and1 for hosting)

== Frequently Asked Questions ==

= How do I use it? =

The plugin gives you two short codes: one that places a form on a page for people to add a listing, a second to display
the listing. Go to the Settings page and it will walk through creating the short codes.

= How do I edit/delete my yard sale entry? =

After you add your entry initially, you will be emailed links that you can use to to edit or delete that entry.

= Yard sale entries are not appearing on the listing page =

Ensure that the "event" value used in the [yardsale-listing] short code matches that of the [yardsale-form] input form short code

= Can we make only registered users be allowed to make and entry or can we require a login =

The plugin does not directly support this. But you can use the WordPress option to require a password on the
page on which the entry form is located.

= I want to do a new yard sale event, how do I clear the old entries =

You have two choices: (1) delete existing entries, (2) use a new event tag. For (1), go to the admin setting panel,
"Delete Entry" tab and delete the event tag that you used in your short code. For (2), edit your [yardsale-form] and
[yardsale-listing] short codes by changing the "event" value to some new value. Be sure you use the same value for
both tags so that they reference the same data.


== Screenshots ==

1. Map view of listings
2. Table view of listings

== Changelog ==

= 1.0.1 =
Minor tweaks

= 1.0 =
Now available via WordPress site

= 0.1 =
Initial Revision
