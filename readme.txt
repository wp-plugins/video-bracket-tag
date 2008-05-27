=== Video Bracket Tag ===
Contributors: BobGneu
Donate link: http://blog.gneu.org/software-releases/
Tags: video, formatting, embed
Requires at least: 2.5.0
Tested up to: 2.5.1
Stable tag: 2.1

Insert videos into posts using bracket method. Currently supported video formats include Blip.tv, BrightCove, Google, LiveLeak, RevveR, Vimeo, Veoh, Youtube and Youtube Custom Players

== Description ==

This plugin provides the ability to embed a number of video objects into your WP pages. The formatting is based off of the familiar BBCode tagging, so anyone who regulars forums these days will already be comfortable with their usage.

The current supported formats are:

* **Blip.tv** [bliptv={ID}]
* **BrightCove** [brightcove={ID}]
* **Google Video** [google={ID}]
* **LiveLeak** [liveleak={ID}]
* **RevveR** [revver={ID}]
* **Veoh** [veoh={ID}]
* **Vimeo** [vimeo={ID}]
* **Youtube** [youtube={ID}]
* **Youtube Custom Player** [youtubecp={ID}]

The tags accept a number of parameters. Justification, Width, Aspect Ratio and a text Blurb are all editable on a per tag basis.

`[youtube=-GG7sj2APpc,LEFT,340,16:9,This is my test blurb]`

This will embed a youtube video left justified with a width of 340, aspect ratio of 16:9 and the blurb of "This is my test blurb" as its link.

Ordering of these parameters does not matter, and no, its not case sensitive.

= New Features =

Now includes an options menu, allowing site wide defaults to be included when using the plugin.

Currently configurable items :

* Show Link by default **(When turned off, links are turned off site wide)**
* Maximum Width
* Default Aspect ratio

Also - I have corrected the file layout in the SVN to be able to allow the auto update feature to work without issues. =)

= Currently Supported Parameters =

* **FLOAT** - this is how you handle the left or right float. Defaults to float left. This is most useful when you are trying to embed the video into a body of text.
* **LEFT** - Left Justification
* **RIGHT** - Right Justification
* **NOLINK** - Do not include link
* **Ratio** - Accepted Ratios are : 16:9 16:10 1:1 221:100 5:4 - All other provided values are set to 4:3 (the most common video ratio)
* **Numerical Values** - If you provide any numerical values you are setting the width of your video.
* **Alphanumeric Values** - When you post your video you may want to change the text value from the default to something descriptive or to caption something in the video.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `WP Videos` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to the 'Settings' > 'Configure Videos' Menu to configure the defaults.
1. Place a bracket tag into your post and give her a load

== Frequently Asked Questions ==

= Why isnt this working for me? =
The only thing that has come up that throws this off, at least thus far, is a failure to grab the entirety of the ID. Some of the id's include symbols, or characters, not expressly digits. Just confirm that and you will probably be surprised.

= Can you add {xyz} video player? =
I sure as hell can (probably...)! The process is quite simple and my turn around time is usually just a few hours. Just leave me a message and let me know which players are needed.

= What do you have planned for this? =

Ultimately i see this plugin moving towards being more abstract. I dont foresee the embedding of video to be a situation where we have to embed them expressly, although i do like the current number of tags and how they are all separated. 

Adding an abstract [object][/object] may be useful, as well as an [embed][/embed] tag, for those as yet unsupported tags that people aren't asking for.

We'll see where the populous wants this plugin to go. =)

== Screenshots ==

1. A look at the options interface.

== Change Log ==

= Version 2.1 =
* Further reworking of the code
* Added Options Menu

= Version 2.0.2 =

* Added RevveR
* Corrected some code (simplification)

= Version 2.0.1 =

* Added Blip.tv
* Expanded on description to include information about the parameters
* Added NOLINK parameter to be a per item option

= Version 2.0 =

* Complete Revision of Plugin from previous state
* Added a number of parameters, consult description for further information
* Added a different mechanism for parsing the Excerpt v. the Content of the post. 
* Brought everything together in a class (serves as a namespace for now)
* Now is much easier to add further objects, including non video items.
