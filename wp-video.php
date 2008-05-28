<?php
/*

Plugin Name: Video Bracket Tag
Plugin URI: http://blog.gneu.org/software-releases/video-bracket-tags/
Description: Insert videos into posts using bracket method. Currently supported video formats include Blip.tv, BrightCove, Google, LiveLeak, RevveR, Vimeo, Veoh, Youtube and Youtube Custom Players
Author: Bob Chatman
Version: 2.1.1
Author URI: http://blog.gneu.org

*/ 
	class VideoParser
	{
		private static $Tags = array("youtube", "youtubecp", "google", "vimeo", "liveleak", "veoh", "brightcove", "bliptv", "revver");

		private static $Width = 0;
		private static $Height = 0;

		private static $IncludeLink = true;

		public static function registerTag($tag, $content, $excerpt)
		{
			if(!array_key_exists($tag, self::$Tags))
			{
				self::$Tags[$tag] = array($content, $excerpt);
			}
		}
		
		function Install()
		{
			add_option("WPVID_MaxVideoWidth", 600);
			add_option("WPVID_DefaultRatio"	, '4:3');
			add_option("WPVID_IncludeLink"	, '1');
			
			
			update_option("WPVID_MaxVideoWidth", 600);
			update_option("WPVID_DefaultRatio"	, '4:3');
			update_option("WPVID_IncludeLink"	, '1');
		}
		
		function registerLink()
		{
		}
		
		function ShouldIncludeLink()
		{
		  return self::$IncludeLink;
		}
		
		public static function getWidth($ratio = "4:3", $size)
		{
		    self::ValidateSize($ratio, $size);
		 
            return self::$Width;
		}

		public static function getHeight($ratio = "4:3", $size)
		{
		    self::ValidateSize($ratio, $size);
		    
		    return self::$Height;
		}
		
		private static function ValidateSize($ratio, $size)
		{
    		if (stripos($ratio, ":") !== FALSE || $ratio == "")
    		{
    			self::$Width = $size;
				
    			switch ($ratio)
    			{
    				case "16:9":
    					self::$Height = (int)(self::$Width / 16) * 9;
    					break;
    					
    				case "16:10":
    					self::$Height = (int)(self::$Width / 16) * 10;
    					break;
    
    				case "1:1":
    					self::$Height = self::$Width;
    					break;
    					
    				case "221:100":
    					self::$Height = (int)(self::$Width / 221) * 100;
    					break;
    					
    				case "5:4":
    					self::$Height = (int)(self::$Width / 5) * 4;
    					break;
    					
    				default:
    					self::$Height = (int)(self::$Width / 4) * 3;
    			}
    		}
    		else
    		{				
    			self::$Width = $size;
				self::$Height = (int)(self::$Width / 4) * 3;
    		}
		}

	    function getJustification($entry)
		{
			$Ret = "<div style='";
			
			switch($entry['JUST'])
			{
				case "LEFT":
				case "RIGHT":
					if ($entry['FLOAT'] == true)
						$Ret .= "float: " . $entry['JUST'] . "'>";
					else
						$Ret .= "align: " . $entry['JUST'] . "'>";
					
					break;
				default:
					$Ret = "<center>";
					
					break;
			}
			
			return $Ret;
		}

	    function getEndJustification($entry)
		{
			switch($entry['JUST'])
			{
				case "LEFT":
				case "RIGHT":
					$Ret = "</div>";
					
					break;
				default:
					$Ret = "</center>";
					
					break;
			}
			
			return $Ret;
		}
		
	    function getElements($entry)
        {
            $Ret = array("ID" => null, 
						"RATIO" => get_option('WPVID_DefaultRatio'), 
						"JUST" => "CENTER", 
						"LINK" => get_option('WPVID_IncludeLink'), 
						"BLURB" => "", 
						"FLOAT" => false, 
						"SIZE" => get_option('WPVID_MaxVideoWidth'));
        
            $entry = trim($entry, "[]");
        
			$arr = explode("=", $entry, 2);
			$arr = explode(",", $arr[1]);
			
			for($i = 0; $i < count($arr); $i++)
				$arr[$i] = trim($arr[$i]);
			
			$Ret['ID'] = array_shift($arr);
	
            foreach ($arr as $el)
            {
				$tArr = split(":", $el, 3);
                if (strpos($el, ":") !== false && count($tArr) == 2 && (ctype_digit($tArr[0]) === true && ctype_digit($tArr[1]) === true)) 
                    $Ret['RATIO'] = $el;
                else if (ctype_digit($el) === true)
                    $Ret['SIZE'] = (int)$el;
                else
                    switch (strtoupper($el))
                    {
                        case "LEFT" :
                        case "RIGHT" :
                             $Ret['JUST'] = strtoupper($el);
                             break;
							 
                        case "FLOAT" :
                             $Ret['FLOAT'] = true;
							 if ($Ret['JUST'] == "CENTER")
							 	 $Ret['JUST'] = "LEFT";
                             break;

                        case "NOLINK":
                             $Ret['LINK'] = false;
                             break;

                        default:
                                $Ret['BLURB'] = htmlentities($el, ENT_QUOTES, "UTF-8");
                    }
            }
			
    		return $Ret;
	    }
		
		private function executeParse(&$content, $s_pos, $tag, $func)
		{
			$e_pos = strpos($content, "]", $s_pos + strlen($tag));

			$beginning = substr($content, 0, $s_pos);
			$end       = substr($content, $e_pos + 1 );
			$entry     = substr($content, $s_pos, $e_pos - $s_pos + 1);
			
			$embed     = @call_user_func(array('VideoParser', "{$tag}{$func}"), VideoParser::getElements($entry));

			if ($embed !== false)
				$content = $beginning . $embed . $end;
			else
				$content = $beginning . $end;
		}
	
		function getContent($content)
		{
			foreach (self::$Tags as $tag)
			{
    			list ($s_pos, $e_pos) = array();
    		
    		    $s_pos = strpos(strtolower($content), "[$tag=");
    			
    			while ($s_pos !== false && method_exists('VideoParser',"{$tag}_Content"))
    			{
 					VideoParser::executeParse($content, $s_pos, $tag, '_Content');
					$s_pos = strpos($content, "[$tag=");
    			}
			}

			return $content;
		}

		function getExcerpt($content)
		{
			foreach (self::$Tags as $tag)
			{
    			list ($s_pos, $e_pos) = array();
    		
    		    $s_pos = strpos(strtolower($content), "[$tag=");
    			
    			while ($s_pos !== false && method_exists('VideoParser',"{$tag}_Excerpt"))
    			{
 					VideoParser::executeParse($content, $s_pos, $tag, '_Content');
					$s_pos = strpos($content, "[$tag=");
    			}
			}

			return $content;
		}
		
		function VideoAdministrationMenu() 
		{
			global $user_level;
			get_currentuserinfo();
			
			if (function_exists('current_user_can') && !current_user_can('manage_options')) 
				return;
				
			if ($user_level < 8) 
				return;
		
			if (function_exists('add_options_page')) 
				add_options_page("Video Settings", "Configure Videos", 'manage_options', __FILE__, array('VideoParser', 'VideoAdministrationMenu_Form'));
		
		}
	
		function VideoAdministrationMenu_Form()
		{
			if( function_exists( 'is_site_admin' ) )
				if( !is_site_admin() )
					return;
					
			$Ratios = array("1:1", "16:10", "16:9", "221:100", "4:3", "5:4");
			
			if(isset($_POST) && $_POST['Action'] && check_admin_referer('plugin-name-action_WPVID'))
			{
				$CLEAN = array();add_option("aiosp_home_description", null, 'All in One SEO Plugin Home Description', 'yes');
				
				if (ctype_digit($_POST['WPVID_MaxVideoWidth']))
					update_option('WPVID_MaxVideoWidth', (int)$_POST['WPVID_MaxVideoWidth']);
				
				update_option('WPVID_IncludeLink', $_POST['WPVID_IncludeLink'] == "on" ? "1" : "0");
				
				if (in_array($_POST['WPVID_DefaultRatio'], $Ratios))
					update_option('WPVID_DefaultRatio', $_POST['WPVID_DefaultRatio']);				
					
				$message = "<strong>Settings saved.</strong>";
			}
			
			?>
<?php if ($message) : ?>
<div id="message" class="updated fade">
	<p><?php echo $message; ?></p>
</div>
<?php endif; ?>
<div class="wrap">
	<h2>Configure Embedded Video Options</h2>
<form name="submit_video_options" action="" method="post">
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('plugin-name-action_WPVID'); } ?>
	<table class="form-table">
		<tr>
			<th scope="row" style="text-align:right; vertical-align:top;"> Maximum Embedded Video Width (Pixels): </th>
			<td><input size="5" name="WPVID_MaxVideoWidth" value="<?php echo (int)get_option('WPVID_MaxVideoWidth'); ?>"/>
			</td>
		</tr>
		<tr>
			<th scope="row" style="text-align:right; vertical-align:top;"> Show Video Links By Default: </th>
			<td><input type="checkbox" name="WPVID_IncludeLink" <?php echo get_option('WPVID_IncludeLink') == "1" ? "checked='1'" : ""; ?>/>
			</td>
		</tr>
		<tr>
			<th scope="row" style="text-align:right; vertical-align:top;"> Default Video Aspect Ratio: </th>
			<td><select name="WPVID_DefaultRatio">
					<?php foreach ($Ratios as $el) : ?>
					<option value="<?php print $el; ?>" <?php echo get_option('WPVID_DefaultRatio') == $el ? "selected" : ""; ?>><?php print $el; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input type="hidden" name="Action" value="WPVID_SubmitValues" />
		<input type="submit" name="Submit" value="Save Changes" />
	</p>
</form>
</div> 
<?php
		}

		
/* Begin Content Function Section ****************************************************************************/
		
		function youtube_Content($arr)
		{			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to YouTube [{$arr['ID']}]";
				
			return VideoParser::getJustification($arr) . "<object width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'>
						<param name='movie' value='{$arr['ID']}'></param>
						<param name='wmode' value='transparent' ></param>
						<embed src='http://www.youtube.com/v/{$arr['ID']}' type='application/x-shockwave-flash' wmode='transparent' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
					 </object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.youtube.com/watch?v={$arr['ID']}&eurl={$_SERVER['SCRIPT_URI']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}
			
		function youtubecp_Content($arr)
		{	
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to YouTube [{$arr['ID']}]";
			
			return VideoParser::getJustification($arr['RATIO'], $arr['SIZE']) . "<object width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO']) . "'>
						<param name='movie' value='{$arr['ID']}'></param>
						<param name='wmode' value='transparent' ></param>
						<embed src='http://www.youtube.com/cp/{$arr['ID']}' type='application/x-shockwave-flash' wmode='transparent' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
					</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.youtube.com/cp/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function google_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Google [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<embed style='width:" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "px; height:" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "px' src='http://video.google.com/googleplayer.swf?docId={$arr['ID']}' id='VideoPlayback' type='application/x-shockwave-flash' quality='best' bgcolor='#ffffff' scale='noScale' salign='TL' flashvars='playerMode=embedded' align='middle'></embed>
			" . ( $arr['LINK'] ? "<br /><center><a href='http://video.google.com/videoplay?docid={$arr['ID']}&hl=en'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function vimeo_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Vimeo [{$arr['ID']}]";
			
			return VideoParser::getJustification($arr) . "<object type='application/x-shockwave-flash' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://www.vimeo.com/moogaloop.swf?clip_id={$arr['ID']}&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color='>
			 <param name='quality' value='best' />
			 <param name='allowfullscreen' value='true' />
			 <param name='scale' value='showAll' />
			 <param name='movie' value='http://www.vimeo.com/moogaloop.swf?clip_id={$arr['ID']}&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=' /></object>

			 " . ( $arr['LINK'] ? "<br /><center><a href='http://www.vimeo.com/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function liveleak_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to LiveLeak [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<embed src='http://www.liveleak.com/e/{$arr['ID']}' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' scale='showall' name='index'></embed>
			" . ( $arr['LINK'] ? "<br /><center><a href='http://video.google.com/videoplay?docid={$arr['ID']}&hl=en'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function veoh_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Veoh [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<embed src='http://www.veoh.com/videodetails2.swf?player=videodetailsembedded&type=v&permalinkId={$arr['ID']}' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>
			" . ( $arr['LINK'] ? "<br /><center><a href='http://video.google.com/videoplay?docid={$arr['ID']}&hl=en'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function brightcove_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to BrightCove [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<embed src='http://www.brightcove.tv/playerswf' bgcolor='#FFFFFF' flashVars='initVideoId={$arr['ID']}&servicesURL=http://www.brightcove.tv&viewerSecureGatewayURL=https://www.brightcove.tv&cdnURL=http://admin.brightcove.com&autoStart=false' base='http://admin.brightcove.com' name='bcPlayer' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' allowFullScreen='true' allowScriptAccess='always' seamlesstabbing='false' type='application/x-shockwave-flash' swLiveConnect='true' pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'></embed>
			" . ( $arr['LINK'] ? "<br /><center><a href='http://www.brightcove.tv/title.jsp?title={$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function bliptv_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Blip.tv [{$arr['ID']}]";

            return VideoParser::getJustification($arr) . "<embed src='http://blip.tv/play/{$arr['ID']}' type='application/x-shockwave-flash' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' allowscriptaccess='always' allowfullscreen='true' /></embed>
            " . ( $arr['LINK'] ? "<br /><center><a href='http://blip.tv/file/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}
		
		function revver_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to RevveR [{$arr['ID']}]";
			
			return VideoParser::getJustification($arr) . "<object width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}' type='application/x-shockwave-flash'>
			<param name='Movie' value='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}'></param>
			<param name='FlashVars' value='allowFullScreen=true'></param>
			<param name='AllowFullScreen' value='true'></param>
			<param name='AllowScriptAccess' value='always'></param>
			<embed type='application/x-shockwave-flash' src='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}' pluginspage='http://www.macromedia.com/go/getflashplayer' allowScriptAccess='always' flashvars='allowFullScreen=true' allowfullscreen='true' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
			</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.revver.com/video/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

/* Begin Excerpt Function Section ****************************************************************************/

		function youtube_Excerpt($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To Yahoo Video [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<a href='http://www.youtube.com/watch?v={$arr['ID']}&eurl={$_SERVER['SCRIPT_URI']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($entry);
		}

		function youtubecp_Excerpt($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To Yahoo Custom Player [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<a href='http://www.youtube.com/cp/{$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}

		function google_Excerpt($arr)
		{	
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To Google Video [{$arr['ID']}]";
	
			return VideoParser::getJustification($arr) . "<a href='http://video.google.com/videoplay?docid={$arr['ID']}&hl=en'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}
	
		function vimeo_Excerpt($arr)
		{	
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To Vimeo Video [{$arr['ID']}]";
	
			return VideoParser::getJustification($arr) . "<a href='http://www.vimeo.com/{$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}	
		
		function liveleak_Excerpt($arr)
		{	
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To LiveLeak Video [{$arr['ID']}]";
	
			return VideoParser::getJustification($arr) . "<a href='http://www.liveleak.com/view?i={$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}
		
		function veoh_Excerpt($arr)
		{	
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To Veoh Video [{$arr['ID']}]";
		
			return VideoParser::getJustification($arr) . "<a href='http://www.veoh.com/videos/{$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}
		
		function brightcove_Excerpt($arr)
		{	
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to BrightCove [{$arr['ID']}]";
		
			return VideoParser::getJustification($arr) . "<a href='http://www.brightcove.tv/title.jsp?title={$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}
		
		function bliptv_Excerpt($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Blip.tv [{$arr['ID']}]";

            return VideoParser::getJustification($arr) . "<a href='http://blip.tv/file/{$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}
				
		function revver_Excerpt($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to RevveR [{$arr['ID']}]";
			
			return VideoParser::getJustification($arr) . "<a href='http://www.revver.com/video/{$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
		}
	}
	
	if ( get_option('WPVID_MaxVideoWidth') == "" )
		VideoParser::Install();
	
	add_filter('the_content', array('VideoParser', 'getContent'));
	add_filter('the_excerpt', array('VideoParser', 'getExcerpt')); 
	
	// Link the options page up
	add_action('admin_menu', array('VideoParser', 'VideoAdministrationMenu'));
?>
