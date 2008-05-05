=== Video Bracket Tag ===
Contributors: BobGneu
Donate link: http://example.com/
Tags: video, formatting, embed
Requires at least: 2.5.0
Tested up to: 2.5.1
Stable tag: N/A

Insert videos into posts using bracket method. Currently supported video formats include BrightCove, Google, LiveLeak, Vimeo, Veoh, Youtube and Youtube Custom Players

== Description ==

This plugin provides the ability to embed a number of video objects into your WP pages. The formatting is based off of the familiar BBCode tagging, so anyone who regulars forums these days will already be comfortable with their usage.

The current supported formats are:

* **BrightCove** [brightcove={ID}]
* **Google Video** [google={ID}]
* **LiveLeak** [liveleak={ID}]
* **Veoh** [veoh={ID}]
* **Vimeo** [vimeo={ID}]
* **Youtube** [youtube={ID}]
* **Youtube Custom Player** [youtubecp={ID}]

The tags accept a number of parameters. Justification, Width, Aspect Ratio and a text Blurb are all editable on a per tag basis. 

`[youtube=-GG7sj2APpc,LEFT,340,16:9,This is my test blurb]`

This will embed a youtube video left justified with a width of 340, aspect ratio of 16:9 and the blurb of "This is my test blurb" as its link.

Ordering of these parameters does not matter, and no, its not case sensative.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `WP Videos` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place a bracket tag into your post and give her a load

== Frequently Asked Questions ==

= Why isnt this working for me? =
The only thing that has come up that throws this off, at least thus far, is a failure to grab the entirety of the ID. Some of the id's include symbols, or characters, not expressly digits. Just confirm that and you will probably be surprised.

== Change Log ==

= Version 2.0 =

* Complete Revision of Plugin from previous state
* Added a number of parameters, consult description for further information
* Added a different mechanism for parsing the Excerpt v. the Content of the post. 
* Brought everything together in a class (serves as a namespace for now)
* Now is much easier to add further objects, including non video items.