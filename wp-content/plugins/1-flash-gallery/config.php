<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');

global $wpdb;

if (isset($_GET['gall_id']) && is_numeric($_GET['gall_id'])) {
    $gall_id = $_GET['gall_id'];
	$config = fgallery_get_album_settings($gall_id);
	$album = fgallery_get_album($gall_id);
	if (empty($album)) {
		$album['gall_width'] = 450;
		$album['gall_height'] = 385;
		$album['gall_bgcolor'] = "#ffffff";
		$album['gall_type'] = 3;
	}
	header("Content-type: text/xml");
	echo "<?xml version='1.0' encoding='UTF-8'?>";
			echo '<config>';
			switch ($album['gall_type']) {
			case 1:
					echo '<gallery>
							<source>'.$config['slide_source'].'</source>
						  </gallery>
						 <slideshow>
							<enable>'.$config['sc_slideshow__enable'].'</enable>
							<stopByClick>'.$config['sc_slideshow__stopByClick'].'</stopByClick>
							<delay>'.$config['sc_slideshow__delay'].'</delay>
							<music>'.$config['sc_slideshow__music'].'</music>';
							echo get_option('1_flash_gallery_acosta', MUSIC_PATH);
					echo '</slideshow>
						<preview>
							<width>'.$config['sc_preview__width'].'</width>
							<height>'.$config['sc_preview__height'].'</height>
							<alpha>'.$config['sc_preview__alpha'].'</alpha>
							<cornerRadius>'.(int)$config['sc_preview__cornerRadius'].'</cornerRadius>
							<transitionEffect>'.$config['sc_preview__transitionEffect'].'</transitionEffect>
							<transitionDuration>'.$config['sc_preview__transitionDuration'].'</transitionDuration>
							<shadow>'.$config['sc_preview__shadow'].'</shadow>
							<borderColor>0x'.str_replace('#','',$config['sc_preview__borderColor']).'</borderColor>
							<distanceFromScroller>'.$config['sc_preview__distanceFromScroller'].'</distanceFromScroller>
							<shadowColor>0x'.str_replace('#','',$config['sc_preview__shadowColor']).'</shadowColor>
							<shadowAlpha>'.$config['sc_preview__shadowAlpha'].'</shadowAlpha>
							<shadowBlur>'.$config['sc_preview__shadowBlur'].'</shadowBlur>
							<shadowDistance>'.$config['sc_preview__shadowDistance'].'</shadowDistance>
							<borderWidth>'.$config['sc_preview__borderWidth'].'</borderWidth>
						</preview>
						<image>
							<scaleMode>'.$config['sc_image__scaleMode'].'</scaleMode>
							<cornerRadius>'.(int)$config['sc_image__cornerRadius'].'</cornerRadius>
							<transitionEffect>'.$config['sc_image__transitionEffect'].'</transitionEffect>
							<transitionDuration>'.$config['sc_image__transitionDuration'].'</transitionDuration>
						</image>
						<background>
							<type>'.$config['sc_background__type'].'</type>
							<src>'.$config['sc_background__src'].'</src>
							<alpha>'.$config['sc_background__alpha'].'</alpha>
							<color>0x'.str_replace('#','',$config['sc_background__color']).'</color>
						</background>
						<navigation>
							<enable>'.$config['sc_navigation__enable'].'</enable>
							<align>'.$config['sc_navigation__align'].'</align>
							<visible>'.$config['sc_navigation__visible'].'</visible>
							<position>'.$config['sc_navigation__position'].'</position>
						</navigation>
						<caption>
							<align>'.$config['sc_caption__align'].'</align>
							<visible>'.$config['sc_caption__visible'].'</visible>
							<enable>'.(int)$config['sc_caption__enable'].'</enable>
							<fontSize>'.$config['sc_caption__fontSize'].'</fontSize>
							<textColor>0x'.str_replace('#','',$config['sc_caption__textColor']).'</textColor>
							<backgroundColor>0x'.str_replace('#','',$config['sc_caption__backgroundColor']).'</backgroundColor>
							<backgroundAlpha>'.$config['sc_caption__backgroundAlpha'].'</backgroundAlpha>
						</caption>
						<scroller>
							<scrollBy>'.$config['sc_scroller__scrollBy'].'</scrollBy>
							<speed>'.$config['sc_scroller__speed'].'</speed>
							<color>0x'.str_replace('#','',$config['sc_scroller__color']).'</color>
							<alpha>'.$config['sc_scroller__alpha'].'</alpha>
							<cornerRadius>'.(int)$config['sc_scroller__cornerRadius'].'</cornerRadius>
							<lineStyle>'.$config['sc_scroller__lineStyle'].'</lineStyle>
							<hideButtons>'.$config['sc_scroller__hideButtons'].'</hideButtons>
							<itemDistance>'.$config['sc_scroller__itemDistance'].'</itemDistance>
							<borderWidth>'.$config['sc_scroller__borderWidth'].'</borderWidth>
							<borderColor>0x'.str_replace('#','',$config['sc_scroller__borderColor']).'</borderColor>
							<width>'.$config['sc_scroller__width'].'</width>
							<height>'.$config['sc_scroller__height'].'</height>
						</scroller>	
						<scrollerItem>
							<width>'.$config['sc_scrollerItem__width'].'</width>
							<height>'.$config['sc_scrollerItem__height'].'</height>
							<cornerRadius>'.(int)$config['sc_scrollerItem__cornerRadius'].'</cornerRadius>
							<alpha>'.$config['sc_scrollerItem__alpha'].'</alpha>
							<shadow>'.$config['sc_scrollerItem__shadow'].'</shadow>
							<borderColor>0x'.str_replace('#','',$config['sc_scrollerItem__borderColor']).'</borderColor>
							<borderColorOnHover>0x'.str_replace('#','',$config['sc_scrollerItem__borderColorOnHover']).'</borderColorOnHover>
							<borderColorOnClick>0x'.str_replace('#','',$config['sc_scrollerItem__borderColorOnClick']).'</borderColorOnClick>
							<shadowColor>0x'.str_replace('#','',$config['sc_scrollerItem__shadowColor']).'</shadowColor>
							<shadowAlpha>'.$config['sc_scrollerItem__shadowAlpha'].'</shadowAlpha>
							<shadowBlur>'.$config['sc_scrollerItem__shadowBlur'].'</shadowBlur>
							<shadowDistance>'.$config['sc_scrollerItem__shadowDistance'].'</shadowDistance>
							<borderWidth>'.$config['sc_scrollerItem__borderWidth'].'</borderWidth>
							<gradientStart>0x'.str_replace('#','',$config['sc_scrollerItem__gradientStart']).'</gradientStart>
							<gradientEnd>0x'.str_replace('#','',$config['sc_scrollerItem__gradientEnd']).'</gradientEnd>
							<textColor>0x'.str_replace('#','',$config['sc_scrollerItem__textColor']).'</textColor>
							<fontSize>'.$config['sc_scrollerItem__fontSize'].'</fontSize>
						</scrollerItem>';
				break;
				
				case 2: 
				echo '<gallery>
							<source>'.$config['slide_source'].'</source>
						  </gallery>
						<slideshow>
							<autostart>'.(int)$config['sc_slideshow__autostart'].'</autostart>
							<delay>'.$config['sc_slideshow__delay'].'</delay>
							<music>'.$config['sc_slideshow__music'].'</music>';
							echo get_option('1_flash_gallery_airion', MUSIC_PATH);
						echo '</slideshow>
						<preview>
							<width>'.$config['sc_preview__width'].'</width>
							<height>'.$config['sc_preview__height'].'</height>
							<alpha>'.$config['sc_preview__alpha'].'</alpha>
							<cornerRadius>'.(int)$config['sc_preview__cornerRadius'].'</cornerRadius>
							<transitionEffect>'.$config['sc_preview__transitionEffect'].'</transitionEffect>
							<transitionDuration>'.$config['sc_preview__transitionDuration'].'</transitionDuration>
							<shadow>'.(int)$config['sc_preview__shadow'].'</shadow>
							<borderColor>0x'.str_replace('#','',$config['sc_preview__borderColor']).'</borderColor>
							<distanceFromScroller>'.$config['sc_preview__distanceFromScroller'].'</distanceFromScroller>
							<shadowColor>0x'.str_replace('#','',$config['sc_preview__shadowColor']).'</shadowColor>
							<shadowAlpha>'.$config['sc_preview__shadowAlpha'].'</shadowAlpha>
							<shadowBlur>'.$config['sc_preview__shadowBlur'].'</shadowBlur>
							<shadowDistance>'.$config['sc_preview__shadowDistance'].'</shadowDistance>
							<borderWidth>'.$config['sc_preview__borderWidth'].'</borderWidth>
						</preview>
						<image>
							<scaleMode>'.$config['sc_image__scaleMode'].'</scaleMode>
							<cornerRadius>'.(int)$config['sc_image__cornerRadius'].'</cornerRadius>
							<transitionEffect>'.$config['sc_image__transitionEffect'].'</transitionEffect>
							<transitionDuration>'.$config['sc_image__transitionDuration'].'</transitionDuration>
						</image>
						<background>
							<type>'.$config['sc_background__type'].'</type>
							<src>'.$config['sc_background__src'].'</src>
							<alpha>'.$config['sc_background__alpha'].'</alpha>
							<color>0x'.str_replace('#','',$config['sc_background__color']).'</color>
						</background>
						<navigation>
							<enable>'.(int)$config['sc_navigation__enable'].'</enable>
							<align>'.$config['sc_navigation__align'].'</align>
							<visible>'.$config['sc_navigation__visible'].'</visible>
							<position>'.$config['sc_navigation__position'].'</position>
						</navigation>
						<caption>
							<align>'.$config['sc_caption__align'].'</align>
							<visible>'.$config['sc_caption__visible'].'</visible>
							<enable>'.(int)$config['sc_caption__enable'].'</enable>
							<fontSize>'.$config['sc_caption__fontSize'].'</fontSize>
							<fontColor>0x'.str_replace('#','',$config['sc_caption__fontColor']).'</fontColor>
							<backgroundColor>0x'.str_replace('#','',$config['sc_caption__backgroundColor']).'</backgroundColor>
							<backgroundAlpha>'.$config['sc_caption__backgroundAlpha'].'</backgroundAlpha>
						</caption>
						<scroller>
							<enable>'.(int)$config['sc_scroller__enable'].'</enable>
							<scrollBy>'.$config['sc_scroller__scrollBy'].'</scrollBy>
							<speed>'.$config['sc_scroller__speed'].'</speed>
							<color>0x'.str_replace('#','',$config['sc_scroller__color']).'</color>
							<alpha>'.$config['sc_scroller__alpha'].'</alpha>
							<cornerRadius>'.(int)$config['sc_scroller__cornerRadius'].'</cornerRadius>
							<lineStyle>'.$config['sc_scroller__lineStyle'].'</lineStyle>
							<hideButtons>'.((int)$config['sc_scroller__hideButtons'] == 1 ? '1':'0').'</hideButtons>
							<itemDistance>'.$config['sc_scroller__itemDistance'].'</itemDistance>
							<borderWidth>'.$config['sc_scroller__borderWidth'].'</borderWidth>
							<borderColor>0x'.str_replace('#','',$config['sc_scroller__borderColor']).'</borderColor>
							<size>'.$config['sc_scroller__size'].'</size>
							<direction>'.$config['sc_scroller__direction'].'</direction>
						</scroller>	
						<scrollerItem>
							<width>'.$config['sc_scrollerItem__width'].'</width>
							<height>'.$config['sc_scrollerItem__height'].'</height>
							<cornerRadius>'.(int)$config['sc_scrollerItem__cornerRadius'].'</cornerRadius>
							<alpha>'.$config['sc_scrollerItem__alpha'].'</alpha>
							<shadow>'.(int)$config['sc_scrollerItem__shadow'].'</shadow>
							<borderColor>0x'.str_replace('#','',$config['sc_scrollerItem__borderColor']).'</borderColor>
							<borderColorOnHover>0x'.str_replace('#','',$config['sc_scrollerItem__borderColorOnHover']).'</borderColorOnHover>
							<borderColorOnClick>0x'.str_replace('#','',$config['sc_scrollerItem__borderColorOnClick']).'</borderColorOnClick>
							<shadowColor>0x'.str_replace('#','',$config['sc_scrollerItem__shadowColor']).'</shadowColor>
							<shadowAlpha>'.$config['sc_scrollerItem__shadowAlpha'].'</shadowAlpha>
							<shadowBlur>'.$config['sc_scrollerItem__shadowBlur'].'</shadowBlur>
							<shadowDistance>'.$config['sc_scrollerItem__shadowDistance'].'</shadowDistance>
							<borderWidth>'.$config['sc_scrollerItem__borderWidth'].'</borderWidth>
						</scrollerItem>';
					
				break;
			case 3:
				echo '<gallery>
							<source>'.$config['slide_source'].'</source>
						  </gallery>
						  <slideshow>
							<autostart>'.(int)$config['sc_slideshow__autostart'].'</autostart>
							<delay>'.$config['sc_slideshow__delay'].'</delay>
							<music>'.$config['sc_slideshow__music'].'</music>
							<source>'.$config['slide_source'].'</source>';
						echo get_option('1_flash_gallery_arai', MUSIC_PATH);
				echo	'</slideshow>
						<image>
							<imageAsLink>'.(int)$config['sc_image__imageAsLink'].'</imageAsLink>
							<transitionEffect>'.$config['sc_image__transitionEffect'].'</transitionEffect>
							<transitionDuration>'.$config['sc_image__transitionDuration'].'</transitionDuration>
							<scaleMode>'.$config['sc_image__scaleMode'].'</scaleMode>
						</image>
						<caption>
							<align>'.$config['sc_caption__align'].'</align>
							<enable>'.(int)$config['sc_caption__enable'].'</enable>
							<visible>'.$config['sc_caption__visible'].'</visible>
							<fontSize>'.$config['sc_caption__fontSize'].'</fontSize>
							<fontColor>0x'.str_replace('#','',$config['sc_caption__fontColor']).'</fontColor>
							<backgroundColor>0x'.str_replace('#','',$config['sc_caption__backgroundColor']).'</backgroundColor>
							<backgroundAlpha>'.$config['sc_caption__backgroundAlpha'].'</backgroundAlpha>
						</caption>
						<scroller>
							<distanceFromBorder>'.$config['sc_scroller__distanceFromBorder'].'</distanceFromBorder>
							<align>'.$config['sc_scroller__align'].'</align>
							<enable>'.$config['sc_scroller__enable'].'</enable>
							<size>'.$config['sc_scroller__size'].'</size>
							<color>0x'.str_replace('#','',$config['sc_scroller__color']).'</color>
							<alpha>'.$config['sc_scroller__alpha'].'</alpha>
							<direction>'.$config['sc_scroller__direction'].'</direction>
							<speed>'.$config['sc_scroller__speed'].'</speed>
							<borderColor>0x'.str_replace('#','',$config['sc_scroller__borderColor']).'</borderColor>
							<borderWidth>'.$config['sc_scroller__borderWidth'].'</borderWidth>
							<itemDistance>'.$config['sc_scroller__itemDistance'].'</itemDistance>
							<itemIndent>'.$config['sc_scroller__itemIndent'].'</itemIndent>
							<useImagesInItem>'.(int)$config['sc_scroller__useImagesInItem'].'</useImagesInItem>
						</scroller>	
						<scrollerItem>
							<width>'.$config['sc_scrollerItem__width'].'</width>
							<height>'.$config['sc_scrollerItem__height'].'</height>
							<alpha>'.$config['sc_scrollerItem__alpha'].'</alpha>
							<borderColor>0x'.str_replace('#','',$config['sc_scrollerItem__borderColor']).'</borderColor>
							<color>0x'.str_replace('#','',$config['sc_scrollerItem__color']).'</color>
							<borderWidth>'.$config['sc_scrollerItem__borderWidth'].'</borderWidth>
						</scrollerItem>';
				break;
				
				case 4:
				echo '<gallery>
							<source>'.$config['slide_source'].'</source>
							<type>byFolder</type>
						  </gallery>
						<slideshow>
							<enable>'.(int)$config['sc_slideshow__enable'].'</enable>
							<delay>'.$config['sc_slideshow__delay'].'</delay>';
							echo get_option('1_flash_gallery_pax', MUSIC_PATH);
				echo '</slideshow>
						<preview>
							<width>'.$config['sc_preview__width'].'</width>
							<height>'.$config['sc_preview__height'].'</height>
							<status>'.$config['sc_preview__status'].'</status>
							<description>'.$config['sc_preview__description'].'</description>
							<descriptionSize>'.$config['sc_preview__descriptionSize'].'</descriptionSize>
							<countByWidth>'.$config['sc_preview__countByWidth'].'</countByWidth>
							<countByHeight>'.$config['sc_preview__countByHeight'].'</countByHeight>
							<scrollingDirection>'.$config['sc_preview__scrollingDirection'].'</scrollingDirection>
							<shadow>'.(int)$config['sc_preview__shadow'].'</shadow>
							<border>'.(int)$config['sc_preview__border'].'</border>
							<borderColor>0x'.str_replace('#','',$config['sc_preview__borderColor']).'</borderColor>
							<shadowColor>0x'.str_replace('#','',$config['sc_preview__shadowColor']).'</shadowColor>
							<borderWidth>'.$config['sc_preview__borderWidth'].'</borderWidth>
							<selectTint>'.(int)$config['sc_preview__selectTint'].'</selectTint>
							<scrollingSpeed>'.$config['sc_preview__scrollingSpeed'].'</scrollingSpeed>
						</preview>
						<image>
							<width>'.$config['sc_image__width'].'</width>
							<height>'.$config['sc_image__height'].'</height>
							<status>'.$config['sc_image__status'].'</status>
							<description>'.$config['sc_image__description'].'</description>
							<descriptionSize>'.$config['sc_image__descriptionSize'].'</descriptionSize>
							<stageBlackout>'.(int)$config['sc_image__stageBlackout'].'</stageBlackout>
							<paginates>'.(int)$config['sc_image__paginates'].'</paginates>
							<isURL>'.(int)$config['sc_image__isURL'].'</isURL>
						</image>
						<background>
							<type>'.$config['sc_background__type'].'</type>
							<src>'.$config['sc_background__src'].'</src>
							<alpha>'.$config['sc_background__alpha'].'</alpha>
							<color>0x'.str_replace('#','',$config['sc_background__color']).'</color>
						</background>
						<navigation>
							<align>'.$config['sc_navigation__align'].'</align>
							<name>'.$config['sc_navigation__name'].'</name>
							<albumIcon>'.(int)$config['sc_navigation__albumIcon'].'</albumIcon>
						</navigation>
						<controls>
							<fullscreen>'.(int)$config['sc_controls__fullscreen'].'</fullscreen>
						</controls>
						<screen>
							<theme>'.$config['sc_screen__theme'].'</theme>
							<fog>'.(int)$config['sc_screen__fog'].'</fog>
							<fogWidth>'.(int)$config['sc_screen__fogWidth'].'</fogWidth>
							<mainPreloader>'.(int)$config['sc_screen__mainPreloader'].'</mainPreloader>
							<navigationsButton>'.(int)$config['sc_screen__navigationsButton'].'</navigationsButton>
							<previewPreloader>'.(int)$config['sc_screen__previewPreloader'].'</previewPreloader>
						</screen>';
					
				break;
				
				case 5:
					echo '<gallery>
							<source>'.$config['slide_source'].'</source>
							<type>byFolder</type>
						  </gallery>
						<slideshow>
							<enable>'.(int)$config['sc_slideshow__enable'].'</enable>
							<delay>'.$config['sc_slideshow__delay'].'</delay>';
							echo get_option('1_flash_gallery_pazin', MUSIC_PATH);
					echo '</slideshow>
						<preview>
							<width>'.$config['sc_preview__width'].'</width>
							<height>'.$config['sc_preview__height'].'</height>
							<status>'.$config['sc_preview__status'].'</status>
							<description>'.$config['sc_preview__description'].'</description>
							<descriptionSize>'.$config['sc_preview__descriptionSize'].'</descriptionSize>
							<shadow>'.(int)$config['sc_preview__shadow'].'</shadow>
							<border>'.(int)$config['sc_preview__border'].'</border>
							<borderColor>0x'.str_replace('#','',$config['sc_preview__borderColor']).'</borderColor>
							<cornerRadius>'.(int)$config['sc_preview__cornerRadius'].'</cornerRadius>
							<scatter>'.$config['sc_preview__scatter'].'</scatter>
							<mouseClick>'.(int)$config['sc_preview__mouseClick'].'</mouseClick>
							<font>'.$config['sc_preview__font'].'</font>
						</preview>
						<image>
							<width>'.$config['sc_image__width'].'</width>
							<height>'.$config['sc_image__height'].'</height>
							<status>'.$config['sc_image__status'].'</status>
							<fullscreen>'.(int)$config['sc_image__fullscreen'].'</fullscreen>
							<dimmingBackground>'.$config['sc_image__dimmingBackground'].'</dimmingBackground>
							<paginates>'.(int)$config['sc_image__paginates'].'</paginates>
							<buttonsAlpha>'.$config['sc_image__buttonsAlpha'].'</buttonsAlpha>
						</image>
						<background>
							<type>'.$config['sc_background__type'].'</type>
							<src>'.$config['sc_background__src'].'</src>
							<alpha>'.$config['sc_background__alpha'].'</alpha>
							<color>0x'.str_replace('#','',$config['sc_background__color']).'</color>
						</background>';
				
				break;
				
				case 6:
				echo '<gallery>
							<source>'.$config['slide_source'].'</source>
							<type>byFolder</type>
						  </gallery>
						<slideshow>
							<enable>'.(int)$config['sc_slideshow__enable'].'</enable>
							<delay>'.$config['sc_slideshow__delay'].'</delay>
							<music>'.(int)$config['sc_slideshow__music'].'</music>';
							echo get_option('1_flash_gallery_postma', MUSIC_PATH);
				  echo '</slideshow>
						<scroller>
							<visible>'.(int)$config['sc_scroller__visible'].'</visible>
							<width>'.$config['sc_scroller__width'].'</width>
						</scroller>
						<caption>
							<align>'.$config['sc_caption__align'].'</align>
							<visible>'.$config['sc_caption__visible'].'</visible>
							<enable>'.(int)$config['sc_caption__enable'].'</enable>
							<fontSize>'.$config['sc_caption__fontSize'].'</fontSize>
							<textColor>0x'.str_replace('#','',$config['sc_caption__textColor']).'</textColor>
							<background>'.(int)$config['sc_caption__background'].'</background>
							<backgroundColor>0x'.str_replace('#','',$config['sc_caption__backgroundColor']).'</backgroundColor>
							<backgroundAlpha>'.$config['sc_caption__backgroundAlpha'].'</backgroundAlpha>
						</caption>
						<preview>
							<type>'.$config['sc_preview__type'].'</type>
							<width>'.$config['sc_preview__width'].'</width>
							<height>'.$config['sc_preview__height'].'</height>
							<borderWidth>'.$config['sc_preview__borderWidth'].'</borderWidth>
							<borderColor>0x'.str_replace('#','',$config['sc_preview__borderColor']).'</borderColor>
							<alpha>'.$config['sc_preview__alpha'].'</alpha>
							<backgroundColor>0x'.str_replace('#','',$config['sc_preview__backgroundColor']).'</backgroundColor>
							<cornerRadius>'.(int)$config['sc_preview__cornerRadius'].'</cornerRadius>
							<isURL>'.(int)$config['sc_preview__isURL'].'</isURL>
							<rotation>'.$config['sc_preview__rotation'].'</rotation>
							<scaleEffect>'.$config['sc_preview__scaleEffect'].'</scaleEffect>
							<reflection>'.$config['sc_preview__reflection'].'</reflection>
							<reflectionAlpha>'.$config['sc_preview__reflectionAlpha'].'</reflectionAlpha>
							<reflectionDistance>'.$config['sc_preview__reflectionDistance'].'</reflectionDistance>
							<reflectionGradientColorStart>0x'.str_replace('#','',$config['sc_preview__reflectionGradientColorStart']).'</reflectionGradientColorStart>
							<reflectionGradientColorFinish>0x'.str_replace('#','',$config['sc_preview__reflectionGradientColorFinish']).'</reflectionGradientColorFinish>
						</preview>';
				break;
				case 7:
					echo  '<slideshow>';
							echo get_option('1_flash_gallery_pageflip', MUSIC_PATH);
					echo '</slideshow>';

					echo '<main>
							<flipWidth>'.$config['sc_main__flipWidth'].'</flipWidth>
							<flipHeight>'.$config['sc_main__flipHeight'].'</flipHeight>
							<backgroundColor>'.str_replace('#','0x',$config['sc_main__backgroundColor']).'</backgroundColor>
							<backgroundImage>'.$config['sc_main__backgroundImage'].'</backgroundImage>
							<backgroundImagePlacement>'.$config['sc_main__backgroundImagePlacement'].'</backgroundImagePlacement>
							<alwaysOpened>'.$config['sc_main__alwaysOpened'].'</alwaysOpened>
							<handOverCorner>'.$config['sc_main__handOverCorner'].'</handOverCorner>
							<dropShadowEnabled>'.$config['sc_main__dropShadowEnabled'].'</dropShadowEnabled>
							<dropShadowHideWhenFlipping>'.$config['sc_main__dropShadowHideWhenFlipping'].'</dropShadowHideWhenFlipping>
							<shadowDepth>'.$config['sc_main__shadowDepth'].'</shadowDepth>
							<flipSound>'.$config['sc_main__flipSound'].'</flipSound>
							<navigationBar>'.$config['sc_main__navigationBar'].'</navigationBar>
						</main>
						<pages>
							<pageAlignContent>'.$config['sc_pages__pageAlignContent'].'</pageAlignContent>
							<pageBackgroundColor>'.str_replace('#','0x',$config['sc_pages__pageBackgroundColor']).'</pageBackgroundColor>
							<pageBackgroundImage>'.$config['sc_pages__pageBackgroundImage'].'</pageBackgroundImage>
							<pageFrame>'.$config['sc_pages__pageFrame'].'</pageFrame>
							<pageFrameColor>'.$config['sc_pages__pageFrameColor'].'</pageFrameColor>
							<pageFrameAlpha>'.$config['sc_pages__pageFrameAlpha'].'</pageFrameAlpha>
							<coverFrame>'.$config['sc_pages__coverFrame'].'</coverFrame>
							<coverFrameColor>'.str_replace('#','0x',$config['sc_pages__coverFrameColor']).'</coverFrameColor>
							<coverFrameAlpha>'.$config['sc_pages__coverFrameAlpha'].'</coverFrameAlpha>
						</pages>
						<menu>
							<albumListBtn>'.$config['sc_menu__albumListBtn'].'</albumListBtn>
							<exactFitBtn>'.$config['sc_menu__exactFitBtn'].'</exactFitBtn>
							<zoomInBtn>'.$config['sc_menu__zoomInBtn'].'</zoomInBtn>
							<zoomOutBtn>'.$config['sc_menu__zoomOutBtn'].'</zoomOutBtn>
							<fitToStageBtn>'.$config['sc_menu__fitToStageBtn'].'</fitToStageBtn>
							<firstBtn>'.$config['sc_menu__firstBtn'].'</firstBtn>
							<previousBtn>'.$config['sc_menu__previousBtn'].'</previousBtn>
							<navigationString>'.$config['sc_menu__navigationString'].'</navigationString>
							<nextBtn>'.$config['sc_menu__nextBtn'].'</nextBtn>
							<lastBtn>'.$config['sc_menu__lastBtn'].'</lastBtn>
							<soundOnBtn>'.$config['sc_menu__soundOnBtn'].'</soundOnBtn>
							<sounOffBtn>'.$config['sc_menu__sounOffBtn'].'</sounOffBtn>
							<fullscreenBtn>'.$config['sc_menu__fullscreenBtn'].'</fullscreenBtn>
							<exitFullscreenBtn>'.$config['sc_menu__exitFullscreenBtn'].'</exitFullscreenBtn>
						</menu>';
				break;
				
			}	
			if ($album['gall_type'] != 7)
			echo   '<flickr>
						<searchBy>'.$config['sc_flickr__searchBy'].'</searchBy>
						<userID>'.$config['sc_flickr__userID'].'</userID>
						<keyword>'.$config['sc_flickr__keyword'].'</keyword>
						<max>'.$config['sc_flickr__max'].'</max>
						<albumID>'.$config['sc_flickr__albumID'].'</albumID>
					</flickr>

					<picasa>
						<searchBy>'.$config['sc_picasa__searchBy'].'</searchBy> 
						<userName>'.$config['sc_picasa__userName'].'</userName>
						<keyword>'.$config['sc_picasa__keyword'].'</keyword>
						<max>'.$config['sc_picasa__max'].'</max>
						<albumID>'.$config['sc_picasa__albumID'].'</albumID>
						<albumName>'.$config['sc_picasa__albumName'].'</albumName>
					</picasa>

					<photobucket>
						<max>'.$config['sc_photobucket__max'].'</max> 
						<searchString>'.$config['sc_photobucket__searchString'].'</searchString>
					</photobucket>';
					
			echo '</config>';
} else {
    die();
}

die();

?>
