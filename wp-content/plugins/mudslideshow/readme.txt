=== MudSlideShow ===
Contributors: sebaxtian
Tags: picasa, images, image, photos, photo, gallery, flickr
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 0.12.8.6

An image gallery system using Picasa and/or Flickr.

== Description ==

MudSlideShow is free software, and you can use it in commercial and non-commercial sites, but this plugin uses __[Highslide](http://highslide.com/ "An Ajax image viewer")__, __[Lytebox](http://www.dolem.com/lytebox/ "An Ajax image viewer")__, __[Fancybox](http://www.fancybox.net/ "Fancy lightbox alternative")__ or __[prettyPhoto](http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/ "jQuery lightbox clone")__ to display images, and each one would require or not a special license to be used in commercial sites. Be a fair user with the developer of the library you choose if you are going to earn money with your site. In __Configuration > MudSlideShow__ you can define the viewer to use. 

I suggest you to use the button in the post editor to facilitate the process to add a mudslideshow tag to your page.

To show a gallery in a report or page, use [mudslide:flickr|picasa,conf,user\_name,album\_id], where user\_name and album\_id are the data from the photo service. You can use a word as the album\_id, and MudSlideShow would search for the photos with this tag.

The configuration number can be calculated as follows:

* Add 1 if you want to show the descriptio (first comment in Picasa, or the picture description in Flickr) as a caption in the image (only works with selected viewers).
* Add 2 if you want to show the gallery in reverse order.

To show a frame with just one photo at a time, use [mudslide:flickr|picasa,conf,user\_name,album\_id,size,(left|right)], where user\_name and album\_id are the data from the photo service. The size and the position values would tell the plugin this is a frame gallery. The size could be an allowed value from __[Picasa](http://code.google.com/intl/es-AR/apis/picasaweb/docs/2.0/reference.html#Parameters "Programmers reference por Picasa")__ or from Flickr (s, t, m or l). The frame would let you navigate between the images.

To show a picture in a report or page use [mudslide:flickr|picasa,conf,user\_name,album\_id,photo\_number,size,(left|center|right)] where user\_name and album\_id are the data from the photo service. The photo\_number is the photo's index in the album. Size is optional and could be an allowed value from Picasa API. The position is optional too.

There is also a Widget to show a random picture, the last photo from the last gallery, a random photo from the last gallery, or a random photo from a specified gallery.

The first time you add or see a gallery in a post or page, MudSlideShow would add it to the list to be used in the Widget. You can delete and update a gallery in __Tools / MudSlideShow__ from the list of galleries, but if a page or post uses it MudSlideShow would add the gallery to the list again.

MudSlideShow updates automatically each gallery once in a year. If you change a photo or an album in the photo service and the album is in your blog you have to update it manually. Use the link at the end of the gallery or click over the numeric information in a simple framed gallery. You can see those links only if you are the owner of the post, or if you can modify post from others. You can also update a gallery in __Tools / MudSlideShow__. 

There is a button in the RichText Editor to add __galleries__, __single frame galleries__, __images__ and __feature images__ to your posts. It would save the last user\_name used, and will fill the galleries list with the data retrieved from PicasaWeb. If the list doesn't show a gallery you just added, update it with the button at the right side of each list. In the single picture section you can update the contents of a gallery too.

This plugin have been developed to show photos and galleries. It doesn't allow a user to add commentaries to a photo or evaluate it, and those features aren't planned for a near future (See the FAQ).

The MudSlideShow plugin has been translated to french by __[Oyabi](http://www.oyabi.fr/ "Actualité informatique et multimédia")__, Belarusian by __[Marcis G](http://pc.de/)__, German by __[Rian](http://vca.examen.tel/)__, Dutch by __[Rene](http://wpwebshop.com/premium-wordpress-themes/ "Premium WordPress Themes")__, Romanian by __[Catalin](http://www.reviewguy.net "Technology, gadgets, software and reviews")__ and Basque by __[Unai](http://www.goikoetxeta.com "Unai's blog")__. Thanks for your time guys!

Screenshots are in spanish because it's my native language. As you should know yet 
I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Decompress mudslideshow.zip and upload `/mudslideshow/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the __Plugins__ menu in WordPress.
3. Activate the Widget if you want a random photo in the sidebar.
4. You can use mudslide.css, lytebox.css and highslide.css to create your own style (see FAQ).
5. You can use hs-config.js to set a special behavior to highslide (see FAQ).

== Frequently Asked Questions ==

= Whats is this thing about 'Show Description'?  =

MudSlideShow would displays a text in a frame near the image when you add 1 to the config 
number. If the gallery is in Picasa, MSS would use the first comment, if it is a Flickr 
gallery MSS would use the picture description.

This feature is enabled only if you use Highslide or Fancybox.

= Whats is this thing about 'Feature Image'? =

If your theme can manage __Feature Images__, you can enable MudSlideShow to automatically 
assign a thumbnail when you modify or save a post. This feature copy the image to your
media library, attaches it to the post and sets as the feature image.

MudSlideShow selects the __Feature Image__ applying these rules:

* The first image with the tag __[mudthumb]__.
* The first single image __[mudslide]__.
* The first image in a gallery __[mudslide]__.
* The first attached image __[media library]__.

If you want to change the __Feature Image__, use the tag __[mudthumb]__ (the editor would 
guide you with this) inside the post. Then delete the old __Feature Image__ and update the post.
An image in a __mudthumb__ would be invisible inside the post, this way you can have 
a post without images but with a custom thumbnail.

In case you want a post without a feture image, add the __[custom field](http://www.kriesi.at/archives/how-to-use-wordpress-custom-fields)__ __no_thumb__ to your post, and delete the feature image if the post 
has one picture asigned yet.

Remember that MSS only assign the __Feature Image__ when you save or update a post.
To enable the capability to create the thumb when displaying a post, add this line of
code to the file `function.php` in your theme:

define('MUDS_CREATE_THUMBS', true);

I suggest to use this capability only to create the thumbnails quickly while you navigate
throught your page, but I strongly suggest to disable it when you have finished.

If your theme doesn't mannage __Feature Images__, you can add the function __mud_thumb($size)__. 
This function returns an html code with a squared thumbnail with the `Featured Image`
selected by mudslideshow, even if you don't have a thumbnail enable theme.

= Is this plugin bug free? =

I don't think so. Feedbacks would be appreciated.

= I don't know my username in Flickr/Picasa =

When in doubt, use your email in the editor box. It will try to find your id.

= I updated a photo in my flickr/picasa gallery, but MSS didn't updated the gallery automatically =

You have to manually update any galley in MudSlideShow. This plugin has been designed
to add galleries to posts, but not to create a link with a photo service to update your
site with any change you do. 

= I just need the Widget, how can i add something to the list? =

Add galleries to the list with the 'Add Gallery' button in Tools/MudSlideShow. Add the widget to your sidebar and set the gallery you want to display.

= I have deleted a gallery from the list, but it appears again! =

Maybe the gallery is declared in a post or page, and when a user opens this page the plugin recreates and adds the galleries in the post to the list.

= What about commentaries and votes in a photo? =

I designed MudSlideShow to be part of a post, like a `part of the hole` and not a `section apart`. That's why this plugin doesn't allow to add commentaries to a photo or evaluate it, and those features aren't planned for a near future. Don't lose your time asking for this feature. But, if you are a programmer and you have an idea, I'll love to hear about it.

= Can I set my own CSS? =

Yes. Copy the files mudslide.css (how the images are shown in a page), highslide.css or lytebox.css to your theme folder. The plugin will check for them.

= Can I set my own scheme for Highslide? =

Yes. Copy the file hs-config.js to your theme folder. The plugin will check for it.

= What about licenses? =

MudSlideShow is free software, and you can use it in commercial and non-commercial sites, but this plugin uses __[Highslide](http://highslide.com/ "An Ajax image viewer")__, __[Lytebox](http://www.dolem.com/lytebox/ "An Ajax image viewer")__, __[Fancybox](http://www.fancybox.net/ "Fancy lightbox alternative")__ or __[prettyPhoto](http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/ "jQuery lightbox clone") to show the photos, and each one would require or not a license to be used in commercial sites. Be a fair user with the developer of the library you choose if you are going to earn money with your site.

= How can I change the language in Lytebox? =

This viewer use images instead of text for the buttons. To change the language you have to change the images.

= I would like to see MudSlideShow using the xyz viewer =

Contact me. I'll put the viewer in the list if I like it and it's free for non-commercial sites. And if it's free for commercial sites too, I'll put it as the default viewer for sure.

= Can I change the position of the simpleslide controls? =

Copy the simpleslide.css file in your theme and edit it.

== Screenshots ==

1. Configuration form
2. Galleries list in Tools / MudSlideShow
3. Form to add galleries.
4. Widget configuration
5. The widget in the sidebar
6. Icon in the RichText editor
7. Add a gallery
8. A gallery in a post - See the Actualizar (Update in spanish) link
9. Add a framed gallery
10. A framed gallery in a post
11. A framed gallery using Highslide as viewer.
12. Add a single photo
13. A single photo in a post 

== Changelog ==

= 0.12.8.6 =
* Uses WPBook filter to set feature image in Facebook stream.

= 0.12.8.5 =
* Now you can open set widgets to open posts where the photo is.

= 0.12.8.4 =
* Solved bug with 'imported' posts.

= 0.12.8.3 =
* Solved bug with special characters
* Solved bug with detection of RSS feed call.

= 0.12.8.2 =
* Solved bug with widgets when using new 'feed detection' system.

= 0.12.8.1 =
* Solved bug with brakets inside photo comments and names
* Added system to check if WP is displaying a feed and change the viewer

= 0.12.8 =
* Checked for WP 3.1
* Solved bug with special characters inside photo comments and names
* Solved bug with 'loading' image precharge.
* Solved problem with the excerpt.
* Preview images while selecting single image or fature image
* Don't display caption when the photo doesn't have a name.

= 0.12.7.99 =
* Modified TinyMCE call to solve bugs with wp-cache.

= 0.12.7.12 =
* Solved CSS bug with bottom-margin in some themes.
* Added new GUI to show description.
* Added new GUI to reverse order.

= 0.12.7.11 =
* Added PrettyPhoto as a new viewer.
* Solved situation with Flickr in full mode.

= 0.12.7.10 =
* Modified excerpt functions.

= 0.12.7.9 =
* Use none-viewer when in feed.

= 0.12.7.8 =
* Solved bug with pop-up image in special themes.
* Not show pop-up image when in feed.

= 0.12.7.7 =
* Solved bug with size in flickr.

= 0.12.7.6 =
* Solved bug with email recognition.
* Solved bug with size in flickr.

= 0.12.7.5 =
* First release with basque translation. Thanks __[Unai](http://www.goikoetxeta.com "Unai's blog")__.

= 0.12.7.4 =
* Solved bug with [simple gallery+highslide] initialization.

= 0.12.7.3 =
* Solved bug with loading image on [simple gallery+highslide]

= 0.12.7.2 =
* Solved bug with little images in Flickr.

= 0.12.7.1 =
* Solved bug with higshlide + simple gallery in script declaration order.
* Don't show the Loading image when using a simple gallery in highslide.
* Using a semaphore system to initialize simple galleries in Higslide viewer.
* Using fade instead expand in higslide.

= 0.12.7 =
* Solved configuration rights bug.

= 0.12.6.6 =
* Solved minor bugs.

= 0.12.6.5 =
* Solved bug with photos with spaces in the name.

= 0.12.6.4 =
* New tag to add featured images.
* New schemme to add featured images.
* Modified configuration page to allow set featured image system.

= 0.12.6.3 =
* Solved bug with simpleslide script.
* Solved bug with thumbnail title.

= 0.12.6.2 =
* Added feature to set the post thumb.

= 0.12.6.1 =
* Solved bug with single image in flickr.
* Added function to get first thumb (for templates).

= 0.12.6 =
* Using WP functions to add safely scripts and css.

= 0.12.5.4 =
* Solved bug with admin header.

= 0.12.5.3 =
* Solved bug with PHP tag.

= 0.12.5.2 =
* Solved bug with version comparison.

= 0.12.5.1 =
* Solved bug to not use size array in add-gallery manager.

= 0.12.5 =
* Solved bug with Fancybox and Internet Explorer (did I mention I hate IE?)
* Solved bug with CSS to put an image over other images.

= 0.12.4 =
* First release with romanian translation. Thanks __[Catalin](http://www.reviewguy.net "Technology, gadgets, software and reviews")__.
* Solved bug with size data in Flickr API.
* Solved bug to not use size array in add-gallery manager.

= 0.12.3.4 =Size data.
* Solving bug from using new scripts with multimedia library and higslide.

= 0.12.3.3 =
* Solving bug with single photo grouped in highslide.
* Solving CSS situation.

= 0.12.3.2 =
* Solving bug with galleries id.

= 0.12.3.1 =
* Solving simpleslide-higslide-CSS bugs.

= 0.12.3 =
* Solving simpleslide-higslide-CSS bugs.
* Added uninstall script.

= 0.12.2.3 =
* Solving simpleslide-higslide bugs.

= 0.12.2.2 =
* Solving simpleslide-higslide bugs.

= 0.12.2.1 =
* Solving simpleslide-higslide bugs.

= 0.12.2 =
* Simpleslide with Highslide library.

= 0.12.1.1 =
* Using local jquery when using wp 3.0

= 0.12.1 =
* Using new script functions.
* Cleaning code.

= 0.12 =
* First release that doesn't require Minimax.

= 0.11.6.2 =
* First release with german translation. Thanks __[Rian](http://vca.examen.tel/)__.
* First release with dutch translation. Thanks __[Rene](http://wpwebshop.com/premium-wordpress-themes/ "Premium WordPress Themes")__.

= 0.11.6.1 =
* Allow to chose the full size image, between 800px (approx) or original size in galleries. 

= 0.11.6 =
* Allow to chose the full size image, between 800px (approx) or original size.

= 0.11.5.5 =
* Fixing box size.

= 0.11.5.4 =
* Solved a bug with Picasa Squared single images.

= 0.11.5.3 =
* Solved another division by zero bug.

= 0.11.5.2 =
* Solved a division by zero bug.

= 0.11.5.1 =
* Controls in simpleslide are now bottom centered.

= 0.11.5 =
* When calling JS or CSS files now MSS use 'ver'.
* Simpleslide controls by default at bottom.
* Solved a bug with some galleries in Picasa with '__' in the URL.

= 0.11.4 =
* Hacking the code to fit in IE7 (please, update if you use such an old browser)

= 0.11.3 =
* New CSS to show an icon when the pouinter is over the images.
* New CSS in FancyBox.

= 0.11.2 =
* Solved a bug that display comments in a local media without commnents.
* Solved a bug with the Fancybox viewer that display '|' even without link. 

= 0.11.1 =
* FancyBox viewer with new event system.

= 0.11 =
* New viewer (FancyBox)
* Can define thumbnail's size in widgets.

= 0.10.12 =
* Configuration form allows define thumbnail's size.  

= 0.10.11 =
* Solved a bug when checking users in Picasa. MSS recognice now accounts with emails diferent from gmail
* Modified the UI to guide the users to the galleries list.

= 0.10.10 =
* First release with belarusan translation.
* Now MSS allow to add galleries in the list.

= 0.10.9 =
* New database system.
* New update system.
* Images with legend.
* Fixed issues with feeds.

= 0.10.8 =
* Solved an activation issue.

= 0.10.7 =
* The long awaited new ache system.
* The rss galleries appears as them comes, not order by photo date.

= 0.10.6 =
* Solved a bug with old PHP versions.

= 0.10.5 =
* Solved a bug with current path.
* Solved an issue with Litebox viewer.

= 0.10.4 =
* New cache system fixed.

= 0.10.3 =
* Solved a bug in deactivation process.
* Solved a bug in CSS.

= 0.10.2 =
* Deactivate the plugin will erase the configuration data.

= 0.10.1 =
* Solved a bug in the widget with empty albums.

= 0.10 =
* Stable release
* First works with rss feeds (not yet in box editor).

= 0.9.101 =
* Solved a strange bug in the editor.
* New cache system to solve a bug with cache plugins.

= 0.9.100 =
* Solved a bug with simple photo and zero albums.

= 0.9.99 =
* Precharging image in the editor.
* Zero album without comments in picasa while I figure how to get the comments in just one Api call.
* Solved a bug in editor with last_source variable.

= 0.9.98 =
* Solved a bug with tag albums.

= 0.9.97 =
* Solved a bug with 'most recent photos' gallery in Flickr.
* Added 'most recent photos' to Picasa.

= 0.9.96 =
* Solved a bug with update system.

= 0.9.95 =
* First release with Flickr features.
* Updated the configuration values. 

= 0.9.6 =
* Modified the text editor. Now press ENTER in username fields changes the focus to next selector instead sends the form.

= 0.9.5 =
* MudSlideShow can show the linked images added with the Wordpress image library.
* Solved a bug to show a single image at the begin of a post or page.

= 0.9.4.1 =
* Solved a bug with old PHP versions.

= 0.9.4 =
* Manage activation errors.

= 0.9.3.1 =
* Solved a bug with the update function.

= 0.9.3 =
* Using new widget system. Have to recreate your widget.
* Now you can have multiple widget.

= 0.9.2.10 =
* Solved a bug with old PHP versions.

= 0.9.2.9 =
* Solved a bug with the user name, now you can use the domain (gmail.com) in the username.

= 0.9.2.8 =
* Solved a bug with HTML Entities.

= 0.9.2.7 =
* Updated CSS files for standardization.

= 0.9.2.6 =
* Solved a situation with lytebox-viewer.

= 0.9.2.5 =
* Solved a bug with CSS (IE).

= 0.9.2.4 = 
* Solved a bug with CSS.
* Updated spanish translation.

= 0.9.2.3 =
* MudSlideShow can add a link to the photo or gallery to the source page. See configuration to enable it.
* First release with french translation. Thanks __[Oyabi](http://www.oyabi.fr/ "Actualité informatique et multimédia")__.

= 0.9.2.2 = 
* Now you can set the photo size in the widget.

= 0.9.2.1 = 
* Solving a bug in the Rich Text Editor.

= 0.9.2 = 
* Using nonce to not show data when someone call the ajax script outside the plugin.
* Silence is gold.

= 0.9.1 =
* Solved a bug with the size of table's cells in the html code (IE and Chrome).

= 0.9 =
* Stable release
* New XML definition. Any album with with an old version should be updated automatically.

= 0.8.3.3 =
* Solved a bug with the new AJAX scripts

= 0.8.3.2 =
* Solved a bug to show a gallery

= 0.8.3.1 =
* Solved a bug with cache system

= 0.8.3 =
* Using minimax 0.3

= 0.8.2 =
* The code has been indented, documented and standardised.
* Solved a bug with the headers, now MudSlideShow works with the plugin POD.
* Solved a bug when tinyMCE editor in full window.

= 0.8.1 =
* Updated scripts in the administrator to fit with new schreikaten.

= 0.8 =
* First release in SVN.
