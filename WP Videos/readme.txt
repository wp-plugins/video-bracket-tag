=== Video Bracket Tag ===
Contributors: BobGneu, Gneu.org
Donate link: http://blog.gneu.org/software-releases/
Tags: video, formatting, embed, youtube, youtubecp, google, vimeo, liveleak, veoh, brightcove, brightcovecp, bliptv, revver, dailymotion, myspace, hulu, yahoo, cnn, configurable, autoplay, 
Requires at least: 2.5.0
Tested up to: 2.9
Stable tag: 2.4.1

Insert videos into posts using bracket method. Currently supported video formats include Blip.tv, BrightCove, Google, LiveLeak, RevveR, Vimeo, Veoh, Youtube and Youtube Custom Players

== Description ==

This plugin provides the ability to embed a number of video objects into your WP pages. The formatting is based off of the familiar BBCode tagging, so anyone who regulars forums these days will already be comfortable with their usage.

The current supported formats are:

youtube, youtubecp, google, vimeo, liveleak,
veoh, brightcove, brightcovecp, bliptv, revver,
dailymotion, myspace, hulu, yahoo, cnn

The tags accept a number of parameters. Justification, Width, Aspect Ratio and a text Blurb are all editable on a per tag basis.

`[youtube=M5McvNTdEAE,LEFT,340,16:9,This is my test blurb,AUTOPLAY]`

This will embed a youtube video left justified with a width of 340, aspect ratio of 16:9 and the blurb of "This is my test blurb" as its link.

Ordering of these parameters does not matter, and no, its not case sensitive.

= New Features =

Options Menu has a new entry to control styling. Raw CSS is available to be tweaked on a site wide basis

Added two new providers - MySpace Video and Daily Motion.

Autoplay is now fully functional on Vimeo, Google and LiveLeak as well as Youtube Videos.

Corrected a linking issue for Veoh and Liveleak

**Deprecated Float Keyword**

= Currently Supported Parameters =

* **FLOAT** - Left Justification
* **LEFT** - Left Justification
* **RIGHT** - Right Justification
* **NOLINK** - Do not include video origin link
* **LINK** - Force Inclusion of video origin link
* **Ratio** - Accepted Ratios are - 16:9 16:10 1:1 221:100 5:4 - All other provided values are set to 4:3 (the most common video ratio)
* **Numerical Values** - If you provide any numerical values you are setting the width of your video.
* **Alphanumeric Values** - When you post your video you may want to change the text value from the default to something descriptive or to caption something in the video.


* Selected video players only:
* **AUTOPLAY** - Autoplay video when loaded
* **NOAUTOPLAY** - Don't autoplay video when loaded - if you have autoplay defaulted to on.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `WP Videos` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to the 'Settings' > 'Configure Videos' Menu to configure the defaults.
1. Place a bracket tag into your post and give her a load

== Frequently Asked Questions ==

= How do i configure my own styling? =
The CSS for the styling is on the same page as the other Video Configurations.

'Settings' > 'Configure Videos'

= Why isnt this working for me? =
The only thing that has come up that throws this off, at least thus far, is a failure to grab the entirety of the ID. Some of the id's include symbols, or characters, not expressly digits. Just confirm that and you will probably be surprised.

This plugin requires PHP5 in order to work. Since PHP4 is being phased out you should probably upgrade if you are having issues.

= What is in the future for this plugin? =
I plan to spend a good amount of time adding locale support, including server location but also making the options menu respect international language borders. If you are a translator or speak any languages you would like this plugin to support please send me an email and we can surely get started.

I am also thinking about adding the ability for just pasting the URL of the video into the text stream and replacing it with the appropriate embedded object. This is something a little more difficult because i would then be doing a more intrusive replacement. Maybe ill use a ! at the beginning to denote links you dont want to have parsed.

= Can you add {xyz} video player? =
I sure as hell can (probably...)! The process is quite simple and my turn around time is usually just a few hours. Just leave me a message and let me know which players are needed.

= What do you have planned for this? =

Ultimately i see this plugin moving towards being more abstract. I dont foresee the embedding of video to be a situation where we have to embed them expressly, although i do like the current number of tags and how they are all separated. 

Adding an abstract [object][/object] may be useful, as well as an [embed][/embed] tag, for those as yet unsupported tags that people aren't asking for. 

We'll see where the populous wants this plugin to go. =)

== Screenshots ==

1. A passing glance at the adminisration interface, note the supported players listing.

== Change Log ==

= Version 2.4.0 =
* Added Yahoo and CNN video embedding support
* Updated Screenshot
* Added Screenshot page to WP site listing

= Version 2.3.0 =
* Added Hulu to player listing (No Excerpt b/c links are too complex currently)
* Added BrightCove Channel Player to player listing (Requires Approval of channel owner)

= Version 2.2.1 =
* Corrected Video caption mixup.

= Version 2.2.0 =
* Add CSS Style div around embedded video.
* Add Autoplay to all video formats where applicable {Vimeo, Google, LiveLeak, Youtube}
* Add Myspace, DailyMotion video formats
* Corrected Linking Issue - Veoh & LiveLeak
* Deprecated Float Keyword

= Version 2.1.2 =
* Extended Keywords {NOLINK, LINK, NOAUTOPLAY, AUTOPLAY}
* The colon is now acceptable for use in the text blurb
* Updated the options menu to include autoplay as a sitewide option.

= Version 2.1.1 =
* Properly Added Options Menu
* Further reworking of the code
* moved all functions into class

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
