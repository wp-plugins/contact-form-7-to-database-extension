=== Community Yard Sale ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3FF6MP948QPW
Tags: yardsale,yard sale,community yard sale
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.0

Community yard sale with filterable table and Google Map.

== Description ==

Add an input form to collect yard sale entries, and put a Google Map linked to a filterable table to view/filter/search the listings.

== Installation ==

1. Your WordPress site must be running PHP5 or better. This plugin will fail to activate if your site is running PHP4.

Notes:

* Installing this plugin creates its own table. If you uninstall it, the table and its data remain.
* Tested using PHP 5.2.13, MySQL 5.0 (Using 1and1 for hosting)

== Frequently Asked Questions ==

= How do I use it? =

The plugin gives you two short codes. One that places a form on a page for people to add a listing. A second to display
the listing. Go to the Settings page and it will walk through creating the short codes.

= How do I edit my yard sale entry? =

After you add your entry initially, you will be emailed a link that you can use to to edit that entry.

= How do I delete my yard sale entry? =

After you add your entry initially, you will be emailed a link that you can use to to delete that entry.

= Yard sale entries are not appearing on the listing page =

Ensure that the "event" value used in the [yardsale-listing] short code matches that of the [yardsale-form] input form short code

= Can we make only registered users be allowed to make and entry or can we require a login =

The plugin does not directly support this. But you can use the WordPress option to require a password on the
page on which the entry form is located.

= I want to do a new yard sale event, how do I clear the old entries =

You don't remove old entries, you edit both of your short codes' "event" value to give them a new unique value.
Be sure that the "event" value matches on the short code for the input form [yardsale-form] and the short code for
the listing [yardsale-listing].


== Screenshots ==

1. Map view of listings
2. Table view of listing

== Changelog ==

= 0.1 =
Initial Revision
