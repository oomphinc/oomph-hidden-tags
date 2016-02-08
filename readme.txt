=== Oomph Hidden Tags ===
Tags: hide, hidden, secret, tags
Requires at least: 3.8
Tested up to: 3.8.1
Stable tag: 0.1

Hide certain tags from tag lists and tag clouds. Allow capable users to see
hidden tags with the *see_hidden_tags* capability.

== Description ==

Specify a list of tags to keep hidden: These tags will be invisible to users,
such as in tag clouds or in post tags lists.  This is useful when using tags to
control behavior of your blog, or when you wish to maintain groupings of posts
out of the public eye.

This does not prevent tag archives for hidden tags from being accessible, only
hides tag links from tag lists and tag clouds.

Capable users (with the *see_hidden_tags* capability,) can see hidden tags in
tag lists (but NOT tag clouds.) Hidden tags will receive the .hidden-tag class
and are grayed out by default. 

== Installation ==

1. Unzip the package, and upload `hidden-tags` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit 'Settings > Reading' to add tags

== Changelog ==

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.1 =
* Initial release
