=== Mention commment's Authors by Wabeo ===
Contributors: willybahuaud
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=A4P2WCN4TZK26&lc=FR&item_name=Wabeo&item_number=3
Tags: mention, twitter, facebook, poke, comments, authors, cite, quote, comment, response, answer, commentator, reply, mentions
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

"Mention comment's authors" is a plugin that improves the WordPress comments fonctionality, adding a response system between authors.
When adding a comment, your readers can directly mentioning the author of another comment, like facebook or twitter do,using the "@" symbol.

== Description ==

"Mention comment's authors" is a plugin that improves the WordPress comments fonctionality, adding a response system between authors.
When adding a comment, your readers can directly mentioning the author of another comment, like facebook or twitter do,using the "@" symbol.

This mention plugin add two features :

* In the comments field, when an user entered the "@" symbol, it allows, through an autocompletion system, to quote (or poke) a preceding commentator.
* Once comments validated, the mentioned names take the appearance of buttons. When the user clicks on it, window scrolls to the preceding comment from the person named. A class is added to it, for temporarily customize it in CSS.

== Installation ==

= For a non-ajax website =

1. Upload the plugin's folder into `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. All done !

= For an ajax-based website =

1. Upload the plugin's folder into `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the line `add_filter( 'mcaajaxenable', '__return_true' );` to your fonctions.php theme file
4. Call the function `mcaAjaxChange();` in your javascript after each succefull ajax refresh
5. Be sure to apply the filter "commment_text" each time you load comments in ajax 
3. All done ! ;-)

== Changelog ==

= 0.9 =
* Initial release
