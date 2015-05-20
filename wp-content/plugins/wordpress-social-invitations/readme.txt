=== Wordpress Social Invitations ===
Author: Damian Logghe
Website: http://www.timersys.com
Contributors: Timersys
Tags: Social Invitations, twitter, facebook, linkedin, hotmail, yahoo
License: http://codecanyon.net/licenses/regular
Stable Tag: 2.4
Tested on: 4.0

== Description ==

Allow your visitors to invite friends of their social networks such as Facebook, Twitter, Linkedin, Google, Yahoo, Hotmail and more.

= Translations Credits = 

* Spanish - Eruedados Colombia 
* Serbo/Croatian - Borisa Djuraskovic


== Installation ==

1. Upload the `wordpress-social-invitations` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

You're done!

== Changelog ==

= 2.4 - 23 Oct, 2014 =

* Added %%CUSTOMURL%% field 
* Improved session handling for better compability
* Fixed bug with facebook registration url
* Added Site url to facebook sharing option

= 2.3.4 - 23 Sept, 2014 =

* Fixed Facebook workflow
* Fixed bug with facebook and Mycred/Cubepoints
* Fixed Oauth redeclare bug
* Added Cubepoints multiple points support

= 2.3.3 - Jul 26, 2014 =

* Changed CSS of widget for better compatibility
* Added fallback for facebook when using a mobile device

= 2.3.2 - Jul 10, 2014 =

* Improved error page
* Updated hybrid auth
* Updated Google providers for new api
* Updated Facebook send dialog that was failing when other fb plugins were initializated earlier
* Fixed fb og:tags
* Update edd plugin updater

= 2.3.1 - May 22, 2014 =

* Fixed bug that was preventing to Facebook work with registration url
* Added the ability to change fb open graph tags in the registration page
* Emails and popups use site charset

= 2.3 - May 17, 2014 =

* Changed facebook to SEND dialog. Thanks API 2.0 :\
* Removed content filters
* Css bugfixes in front and backend
* Updated language files

= 2.2.2 - March 29, 2014 =

* Updated hybridauth providers Yahoo, Foursquare, and Facebook
* Fixed bug with ie 10
* Added filters to change bp slug
* Fixed fontawesome collision in some sites


= 2.2.1 - March 8, 2014 =

* Changed css styles to avoid problems with sites with font awesome already installed
* Added %%CURRENTTITLE%% shortcode
* Fixed chars left script error
* Added an option to disable html email templates

= 2.2 - March 4, 2014 =

* Improved Facebook invitations script
* Added manual cron functions to override Wordpress cron system
* Changed style and icons with fonts for better customization
* Added the ability to add emails manually(mail provider)
* Improved debug tab
* Templates updated
* Updated language files

= 2.1.3 - February 3, 2014 =

* Changed how cubepoints/My cred gives points in invitations
* Fixed bug in test.php file
* Added new filters to let users change messages programatically
* Fixed bug with popup in Internet Explorer
* Css fixes for Internet Explorer
* Updated spanish translation

= 2.1.2 - December 28, 2013 =

* Fixed bug in error template
* Fixed bug in cubepoints module
* Remove extra scripts and html from other plugins in popup

= 2.1.1 - November 30, 2013 =

* Added new placeholder %%CURENTURL%%
* Added fallback for Twitter DM fail - Read http://wp.timersys.com/twitter-playing-us/
* Fixed bug with facebook cypto error that was preventing the post to wall - Read http://wp.timersys.com/known-openssl-bug-affecting-facebook-chat-new-centos/
* Added an extra check for cron in case is not setted up properly
* Moved goo.gl function to Queue Class so it can be used globally now
* Fixed sidebar broken link
* Added new error message in error template

= 2.1 - November 30, 2013 =

* Fixed bug with js that was preventing popup to close and show thanks message in sidebar or widgets
* Fixed bug to remove extra html added by other plugins in the popup
* Fixed bug with editor height
* Better buddypress integration . "Send Social Invites" screen and menu options added

= 2.0 - November 18, 2013 = 

* Content locker. Now you can protect your posts and show content to only users that send invitations

* MyCreed Integration. Give points for invitations sent / accepted

* Cubepoints Integration. Give points for invitations sent / accepted ( I recommend MyCreed project, this is abandoned and I just added for backward compatibility)

* Automatic Updates. Now you will be able to update your plugin the same way you do with other plugins

* Small bugfixes and code improvement

* Added new action hooks for developers

* Changelog now includes date :)

= 1.4.0.4 =

* Changed Linkedin default message as it only support 200 characters
* Fixed small bug with redirect url field

= 1.4.0.3 =
* Fixed encoding problems 

= 1.4.0.2 =

* Fixed bug with facebook that was preventing the queue to continue when error
* Fixed bug with twitter that was preventing the queue to continue when error
* Fixed bug with Linkeding that was preventing the queue to continue when error
* Fixed bug with scopes on facebook

= 1.4.0.1 =

* Fixed bug with queue system on server with different time than WP

= 1.4 =

* Complete redesign of popup email collector 
* New template system for emails and visual aspects that let users change everything 
* Queue system to handle invitations and API limits using wp-cron 
* Gmail and SMTP Support 
* New default messages separate for each providers 
* Placeholders to use in messages 
* HTML Email Templates 
* Improved facebook chat system with fallback to wall post 
* Goo.gl for facebook and twitter to improve rates 
* New Online documentation 
* Improved PHP functions 
* CSS bugfixes

= 1.3.4.2 =

* Fixed js with Invite Anyone plugin in widget
* Updated language files
* Added debug section & common problems to Docs

= 1.3.4.1 =
* Fixed enquein problem with stylesheet and js
* Added check to see if mb_string PHP function is supported
* Added option to enable dev mode to debug errors

= 1.3.4 =
* Added DEBUG tab to help me out with support tickets and to check facebook chat status
* Updated Docs
* Fixed bug that was preventing widget to display inside Invite Anyone sidebar widget
* Removed all statics functions from Plugin

= 1.3.3 =
* Fixed bug when message edition was disabled
* Added new tab to check stats on the settings page
* Changed the way emails were sent with Bcc

= 1.3.2 =
* Added a sidebar widget
* Added background for logging invitations for future stats
* Css Fixes
* Improved coding batches

= 1.3 =
* Added foursquare
* Fixed documentation
* Minor fixes

= 1.2 =
* Fixed with Live provider
* Added Twitter
* Added feature to redirect users after they send invitations
* Improved documentation

= 1.1 =
* Added facebook
* Added ordering feature for widget providers
* Added custom CSS
* Minor fixes

= 1.0 =
* Plugin released, woohoo!