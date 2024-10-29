=== B-Rad Rotator ===
Contributors: bferrara
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WCUDY5JCSCMHE
Tags: images, rotator, image rotator, thumbnail, thumbnail rotator, carousel, image carousel, thumbnail carousel 
Requires at least: 3.1
Tested up to: 3.7
Stable tag: 1.0.1	

The B-Rad Rotator plugin allows the editing user to create dynamic rotators.

== Description ==

--Basic Way--

Rotator ID
Use shortcode [bRadRotator id='RotatorID' ] to have B-Rad Rotator retrieve all posts with the given RotatorID that have both a Thumbnail URL and an Action URL and display them in a rotator.

Adding Rotator Items
Installing B-Rad Rotator enables the user to add rotator Thumbnail URLs and the Action URLs they go to, and then group these thumbnails into separate rotators using a Rotator ID. After you have installed this plugin, you will notice a new option for your posts in the admin section of your site.

Each post can have its own Thumbnail and Action URL. 
By using the same Rotator Id in several posts, you can add more items to that rotator. 

--Advanced Ways--

Ajax Url
use shortcode [bRadRotator ajaxUrl='http://someURL' ] to have B-Rad Rotator retrieve the URL response in the form of a json object (json_encode).

JSON
Use shortcode [bRadRotator json='{"json":"test json"}' ] to have B-Rad Rotator parse the passed JSON to populate itself.

JSON FORMAT{
 
   "item0" : { "thumbUrl" : "UrlToImage", "actionUrl" : "UrlToGoToWhenClicked"},

  "item1" : { "thumbUrl" : "UrlToImage", "actionUrl" : "UrlToGoToWhenClicked"}

}


B-Rad Rotator is available in:

* English


== Installation ==

The plugin is simple to install:

1. Download `b-rad-rotator.zip`
2. Unzip
3. Upload `b-rad-rotator` directory to your `/wp-content/plugins` directory
4. Go to the plugin management page and enable the plugin
5. Chose a method of use as outlined above.


== Screenshots ==

1. Shortcode
2. Meta Box

== Documentation ==

Additional documentation can be found on the [B-Rad Rotator](http://bferrara.ca/b-rad-rotator/) page.

== Changelog ==

= 1.0.1 =
* Fixed scaling, implemened cleaner rendering of thumbnails.

= 1.0 =
* Initial release



