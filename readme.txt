=== Aviberry WordPress Video Conversion Plugin ===
Contributors: Aviberry
Tags: aviberry, Encoding, media, media library, plugin, transcoding, video, video blog, video gallery, video responses
Requires at least: 2.9.2
Tested up to: 3.5.1
Stable tag: 2.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: http://www.movavi.com

Get videos for your site converted fast and easy.

== Description ==

**Attention! This plugin is no longer supported and Aviberry Cloud Online Video Converter is closed.**

The Aviberry WordPress Video Plugin software enables you to connect your WordPress panel with Aviberry Server Video Converter to convert videos for publishing on your blog.

The plugin transfers videos uploaded to your WP Media Library to the Aviberry сonversion service. There, your videos are rapidly converted to web-compatible formats and returned to you in ideal formats for publishing on your WordPress blog.
 
The WordPress Video Plugin consistently and reliably ensures that your videos will play on any platform or web browser, desktop or mobile.

= Key Features: =

- WordPress-optimized default settings, simple customization
- Easy conversion right after upload or from the Media Library
- Fast and efficient video encoding
- Wide choice of online video presets
- Automatic conversion when creating a new post
- FTP / FTPS / Amazon S3 file storage options
- Variable video player size
- Inbuilt flash media player

= Getting Started: =

1. Install Aviberry Server Video Converter
2. Install Aviberry WordPress Video Plugin 
3. Copy the API key and API password from your Aviberry account. Go to "Aviberry Plugin" in your WordPress account and paste them into the corresponding fields 

= How it works: =

**Variant 1. Converting from Media Library**

1. Add a video to your Media Library
2. Click "Convert" 
3. Select "Add New Post " and choose the file from Media Library. Click "Insert into Post". 

**Variant 2. Automatic conversion when creating a new post**

Select "Add New Post", choose a file then click "Convert with Aviberry" and "Insert into Post". Publish or save the post. The plugin will start conversion and embed the encoded video to your post when done.

= Input and Output Formats =

See the complete list at <http://www.aviberry.com/supported_formats.html>.

== Installation ==

= Getting Started: =

1. Install Aviberry Server Video Converter
2. Install Aviberry WordPress Video Plugin 
3. Copy the API key and API password from your Aviberry account. Go to "Aviberry Plugin" in your WordPress account and paste them into the corresponding fields. 

= How it works: =

**Variant 1. Converting from Media Library**

1. Add a video to your Media Library
2. Click "Convert" 
3. Select "Add New Post " and choose the file from Media Library. Click "Insert into Post". 

**Variant 2. Automatic conversion when creating a new post**

Select "Add New Post", choose a file then click "Convert with Aviberry" and "Insert into Post". Publish or save the post. The plugin will start conversion and embed the encoded video to your post when done.

== How to use plugin? ==

= Variant 1. Converting from Media Library =
1.	Configure the WordPress plugin options, setting access parameters for Media Library. You can choose any other storage for the converted files but in that case they will not appear in Media Library.
2.	Switch to Media Library and chose convert options for your files.
3.	Wait until the conversion ends.
4.	The converted files will be delivered to the \wp-content\uploads folder and, if the option chosen, appear in Media Library right after you refresh the web page.

= Variant 2. Automatic conversion when creating a new post =
1.	In the Automatic Conversion section of the plugin options, choose a preset for automatic conversion.
2.	Configuring the Plugin options, choose the Media Library storage type and set the access options.
3.	Create a new post or start editing an existing one.
4.	Embed video (upload from a hard drive, insert from the Web or Media Library).
5.	Open extra options of a video file by clicking the Show link.
6.	Click the Convert with Aviberry button in the Link URL section.
7.	Click the Insert into Post button. The following shortcode will be added to the post: [aviberry_conversion source_attachment_id='<attachment id>' conversion_id='']. Place it wherever you like in the post.
8.	Save and post.
9.	Wait until the conversion ends.
10.	After the conversion is done, converted videos will be automatically delivered to Media Library and appear in the post instead of the shortcode. If you have turned on the player from the Plugin options, the video starts playing automatically through Aviberry player. 

= Variant 3. Using the Aviberry player =
1.	Configure the player parameters from the Plugin options.
2.	Create new or start editing an existing post.
3.	Paste a softcode [aviberry_player title="My video" href="http://my_wordpress_domain/wp-content/uploads/2012/06/video.mp4" width="400" height="300" id="2"].
4.	Post. There will be video instead of the shortcode.

== Screenshots ==
1. Enter your Aviberry account information to sign in to the plugin settings page. 
2. Specify the parameters to configure Aviberry plugin.
3. Add file to Media Library and click "Convert".
4. Select "Add New Post", choose the file from Media Library, click "Insert into Post" and publish.
5. Or click "Convert with Aviberry", when add the video for your new post. Once the post is saved or published, the plugin will start encoding.
6. Enjoy your high quality video!

== Changelog ==

= 2.4.1 =
* Aviberry SaaS is no longer supported

= 2.4 =
* Wordpress 3.5 is now supported
* Support for width/height in shortcode [aviberry_conversion] is added
* Support of using conversion for QuickPress is added
* Autocompletion of admin email in registration form is added

= 2.3 =
* The Aviberry account registration from plugin support

= 2.2 =
* GUI of the plugin settings page is updated
* Automatic connection of Aviberry plugin to aviberry.com by e-mail is added
* The possibility of purchasing of extended Aviberry account from the plugin is simplified

= 2.1 =
* The plugin's description for the catalogue has been changed. The detailed usage instruction and clear screenshots have been added. 
* The plugin has become even easier to configure. We've added all necessary tooltips and explanations. 
* We've added the links that help you to sign up for the service and remove the trial version restrictions. 
* We've started to gather the statistics about your current Plugin and WordPress version. 
* The Settings Panel opening button has been added.

= 2.0.1 =
* The 'File storage URL' information field has been added to the plugin settings page.
* The Storage URL check has been added when saving plugin settings. (for WordPress versions 3.x and above)

= 2.0 =
* Significantly improved security
* Automatic conversion when creating/editing a post added
* Migrated to Aviberry API v1.1.1. 
* WordPress 3.4.1 support added
* New presets added
* RPC libraries updated
* Minor bugs fixed

= 1.0.6 =
* Migrated to Aviberry API v1.1. 
* The preset list updated.
* Added field to edit the host API Aviberry.
* Fixed GUI bug when using ftp/S3 storages.
* Minor interface improvements.

= 1.0.5 =
* WebM, WMV formats support added.

= 1.0.4 =
* Ported to WordPress 3.1. WordPress media library now may be used to store converted files.
* Thumbnails added to media library for converted files.
* Minor interface bugs fixed.

= 1.0.3 =
* Video settings are updated.

= 1.0.2 =
* Context help for URL fields added.

= 1.0.1 =
* Minor progress bar fix.

= 1.0 =
* New release

== Upgrade Notice ==

= 2.4.1 =
Aviberry SaaS is no longer supported

= 2.4 =
Wordpress 3.5 is now supported. Support for width/height in shortcode [aviberry_conversion] is added. Support of using conversion for QuickPress is added.

= 2.0 =
Significantly improved security. Automatic conversion when creating/editing a post added. New presets added.

== Upgrade Notes ==

= 2.0 =

If you use shortcode [aviberry-player], make the following changes when migrating to 2.0:

1. "aviberry-player" -> "aviberry_player";
2. "wdth" -> "width";
3. "hight" -> "height".

*Examples:*

1.0.6: [aviberry-player title="title" href="http://my_wordpress_domain/wp-content/uploads/2012/06/video.mp4" wdth="400" hight="300" id="2"] 

2.0: [aviberry_player title="title" href="http://my_wordpress_domain/wp-content/uploads/2012/06/video.mp4" width="400" height="300" id="2"] 
