<?php
/*

Plugin Name: Video Bracket Tag
Plugin URI: http://blog.gneu.org/software-releases/video-bracket-tags/
Description: Insert videos into posts using bracket method. Supported Players: Youtube, Youtube Custom Player, Google Video, Vimeo, Liveleak, Veoh, Brightcove, Blip.tv, Revver, Dailymotion, Myspace Video
Author: Bob Chatman
Version: 2.4.0
Author URI: http://blog.gneu.org

*/
	class VideoParser
	{
		private static $Tags = array("youtube", "youtubecp", "google", "vimeo", "liveleak", "veoh", "brightcove", "brightcovecp", "bliptv", "revver", "dailymotion", "myspace", "hulu", "yahoo", "cnn");

		private static $Width = 0;
		private static $Height = 0;

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
			add_option("WPVID_AutoPlay"	, '0');
			add_option("WPVID_DivFormatting", 'padding: 3px; margin: 6px; border: 1px solid #ccc;');

		}

		function Reset()
		{
			update_option("WPVID_MaxVideoWidth", 600);
			update_option("WPVID_DefaultRatio"	, '4:3');
			update_option("WPVID_IncludeLink"	, '1');
			update_option("WPVID_AutoPlay"	, '0');
			update_option("WPVID_DivFormatting"	, 'padding: 3px; margin: 6px; border: 1px solid #ccc;');
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
			$Ret = "<div style='" . get_option('WPVID_DivFormatting');

			switch($entry['JUST'])
			{
				case "LEFT":
				case "RIGHT":
					$Ret .= "float: " . $entry['JUST'] . "'>";

					break;
				default:
					$Ret .= "' align='center'>";

					break;
			}

			return $Ret;
		}

	    function getEndJustification($entry)
		{
			return '</div>';
		}

	    function getElements($entry)
        {
            $Ret = array("ID" => null,
						"RATIO" => get_option('WPVID_DefaultRatio'),
						"JUST" => "CENTER",
						"LINK" => get_option('WPVID_IncludeLink'),
						"BLURB" => "",
						"SIZE" => get_option('WPVID_MaxVideoWidth'),
						"AUTOPLAY" => get_option('WPVID_AutoPlay'));

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
                             $Ret['JUST'] = 'LEFT';
                             break;

                        case "LINK":
                             $Ret['LINK'] = true;
                             break;

                        case "NOLINK":
                             $Ret['LINK'] = false;
                             break;

                        case "AUTOPLAY":
                             $Ret['AUTOPLAY'] = true;
                             break;

                        case "NOAUTOPLAY":
                             $Ret['AUTOPLAY'] = false;
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
                if ($_POST['Submit'] == "Reset Values")
                {
                    VideoParser::Reset();

    				$message = "<strong>Your Settings Have Been Reset.</strong>";
                }
                else
                {
    				if (ctype_digit($_POST['WPVID_MaxVideoWidth']))
    					update_option('WPVID_MaxVideoWidth', (int)$_POST['WPVID_MaxVideoWidth']);

    				update_option('WPVID_IncludeLink', $_POST['WPVID_IncludeLink'] == "on" ? "1" : "0");
    				update_option('WPVID_AutoPlay', $_POST['WPVID_AutoPlay'] == "on" ? "1" : "0");

    				if (in_array($_POST['WPVID_DefaultRatio'], $Ratios))
    					update_option('WPVID_DefaultRatio', $_POST['WPVID_DefaultRatio']);

                    update_option("WPVID_DivFormatting"	, $_POST['WPVID_DivFormatting']);

    				$message = "<strong>Your Settings Have Been Saved.</strong>";
                }
			}

			?>
<?php if ($message) : ?>
<div id="message" class="updated fade">
	<p><?php echo $message; ?></p>
</div>
<?php endif; ?>
<div class="wrap">
	<h2>Configure Embedded Video Options</h2>
	All of these settings are sitewide and can all be overridden on each video, excluding the styling which is site wide. <br />
	If you are having issues or would like more information on the embedding of tags, please consult the <a href="http://blog.gneu.org/software-releases/video-bracket-tags/">plugins homepage</a>.<br />
	Supported Players: <strong><?php echo join(", ", self::$Tags); ?></strong>
<form name="submit_video_options" action="" method="post">
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('plugin-name-action_WPVID'); } ?>
	<table class="form-table">
		<tr>
			<th scope="row" style="text-align:right; vertical-align:top;"> Maximum Embedded Video Width (Pixels): </th>
			<td><input size="5" name="WPVID_MaxVideoWidth" value="<?php echo (int)get_option('WPVID_MaxVideoWidth'); ?>"/>
			</td>
		</tr>
		<tr>
			<th scope="row" style="text-align:right; vertical-align:top;"> Embedded Video Styling: </th>
			<td><input size="35" name="WPVID_DivFormatting" value="<?php echo get_option('WPVID_DivFormatting'); ?>"/>
			</td>
		</tr>
		<tr>
			<th scope="row" style="text-align:right; vertical-align:top;"> Show Video Links By Default: </th>
			<td><input type="checkbox" name="WPVID_IncludeLink" <?php echo get_option('WPVID_IncludeLink') == "1" ? "checked='1'" : ""; ?>/>
			</td>
		</tr>
		<tr>
			<th scope="row" style="text-align:right; vertical-align:top;"> Auto Play Videos: </th>
			<td><input type="checkbox" name="WPVID_AutoPlay" <?php echo get_option('WPVID_AutoPlay') == "1" ? "checked='1'" : ""; ?>/> <small>*Be very careful with this one.</small>
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
		<input type="submit" name="Submit" value="Save Changes" /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		<input type="submit" name="Submit" value="Reset Values" />
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
						<embed src='http://www.youtube.com/v/{$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=1" : "&autoplay=0" ) . "' type='application/x-shockwave-flash' wmode='transparent' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
					 </object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.youtube.com/watch?v={$arr['ID']}&eurl={$_SERVER['SCRIPT_URI']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function youtubecp_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to YouTube [{$arr['ID']}]";

			return VideoParser::getJustification($arr['RATIO'], $arr['SIZE']) . "<object width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO']) . "'>
						<param name='movie' value='{$arr['ID']}'></param>
						<param name='wmode' value='transparent' ></param>
						<embed src='http://www.youtube.com/cp/{$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=1" : "&autoplay=0" ) . "' type='application/x-shockwave-flash' wmode='transparent' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
					</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.youtube.com/cp/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function google_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Google [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<embed style='width:" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "px; height:" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "px' src='http://video.google.com/googleplayer.swf?docId={$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=true" : "" ) . "' id='VideoPlayback' type='application/x-shockwave-flash' quality='best' bgcolor='#ffffff' scale='noScale' salign='TL' flashvars='playerMode=embedded' align='middle'></embed>
		  	   " . ( $arr['LINK'] ? "<br /><center><a href='http://video.google.com/videoplay?docid={$arr['ID']}&hl=en'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function vimeo_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Vimeo [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<object type='application/x-shockwave-flash' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://www.vimeo.com/moogaloop.swf?clip_id={$arr['ID']}&server=www.vimeo.com&fullscreen=1&show_title=1&show_byline=1&show_portrait=0&color=" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=1" : "&autoplay=0" ) . "'>
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

			return VideoParser::getJustification($arr) . "<embed src='http://www.liveleak.com/e/{$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=true" : "&autoplay=false" ) . "' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' scale='showall' name='index'></embed>
                " . ( $arr['LINK'] ? "<br /><center><a href='http://www.liveleak.com/view?i={$arr['ID']}&hl=en'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function veoh_Content($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Veoh [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<embed src='http://www.veoh.com/videodetails2.swf?player=videodetails&type=v&permalinkId={$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&Autoplay=1" : "&Autoplay=0" ) . "' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>
    			" . ( $arr['LINK'] ? "<br /><center><a href='http://www.veoh.com/videos/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
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

            return VideoParser::getJustification($arr) . "<embed src='http://blip.tv/play/{$arr['ID']}" . ( get_option('WPVID_AutoPlay') == "1" ? "?autostart=true" : "?autostart=false" ) . "' type='application/x-shockwave-flash' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' allowscriptaccess='always' allowfullscreen='true' /></embed>
                " . ( $arr['LINK'] ? "<br /><center><a href='http://blip.tv/file/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

		function revver_Content($arr) # &autostart=true
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to RevveR [{$arr['ID']}]";
			
			return VideoParser::getJustification($arr) . "<object width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}' type='application/x-shockwave-flash'>
    			<param name='Movie' value='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}'></param>
    			<param name='FlashVars' value='allowFullScreen=true'></param>
    			<param name='AllowFullScreen' value='true'></param>
    			<param name='AllowScriptAccess' value='always'></param>
    			<param name='AutoStart' value='" . ( $arr['AUTOPLAY'] == "1" ? "true" : "false" ) . "' />
    			<embed type='application/x-shockwave-flash' src='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}' pluginspage='http://www.macromedia.com/go/getflashplayer' allowScriptAccess='always' flashvars='allowFullScreen=true' allowfullscreen='true' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
    			</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.revver.com/video/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
		}

        function dailymotion_Content($arr)
        {
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Daily Motion [{$arr['ID']}]";

            return VideoParser::getJustification($arr) . "<object type='application/x-shockwave-flash' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://www.dailymotion.com/swf/{$arr['ID']}' type='application/x-shockwave-flash'>
                <param name='movie' value='http://www.dailymotion.com/swf/{$arr['ID']}'></param>
                <param name='allowFullScreen' value='true'></param>
                <param name='allowScriptAccess' value='always'></param>
    			<param name='flashvars' value='" . ( $arr['AUTOPLAY'] == "1" ? "" : "" ) . "' />
                </object>
                " . ( $arr['LINK'] ? "<br /><center><a href='http://www.dailymotion.com/swf/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
        }

        function myspace_Content($arr)
        {
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Myspace Video [{$arr['ID']}]";

            return VideoParser::getJustification($arr) . "<embed src='http://lads.myspace.com/videos/vplayer.swf' flashvars='m={$arr['ID']}&a=" . ( $arr['AUTOPLAY'] == "1" ? "1" : "0" ) . "&type=video' type='application/x-shockwave-flash' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
            " . ( $arr['LINK'] ? "<br /><center><a href='http://vids.myspace.com/index.cfm?fuseaction=vids.individual&VideoID={$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
        }


        function hulu_Content($arr)
        {
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Myspace Video [{$arr['ID']}]";

            return VideoParser::getJustification($arr) . "<object height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "'>
            <param name='movie' value='http://www.hulu.com/embed/{$arr['ID']}'>
            <embed src='http://www.hulu.com/embed/{$arr['ID']}' type='application/x-shockwave-flash' height='" . VideoParser::getHeight($arr['RATIO'], $arr['SIZE']) . "' width='" . VideoParser::getWidth($arr['RATIO'], $arr['SIZE']) . "'></object>
            " . VideoParser::getEndJustification($arr);
        }

        function brightcovecp_Content($arr)
        {
            $width = VideoParser::getWidth($arr['RATIO'], $arr['SIZE']);
            $height = VideoParser::getHeight($arr['RATIO'], $arr['SIZE']);

            return "<script src='http://admin.brightcove.com/js/experience_util.js' type='text/javascript'></script>

<script type='text/javascript'>
 // By use of this code snippet, I agree to the Brightcove Publisher T and C
 // found at http://corp.brightcove.com/legal/terms_publisher.cfm.

 var config = new Array();

 /*
 * feel free to edit these configurations
 * to modify the player experience
 */
 config['videoId'] = null; //the default video loaded into the player
 config['videoRef'] = null; //the default video loaded into the player by ref id specified in console
 config['lineupId'] = null; //the default lineup loaded into the player
 config['playerTag'] = null; //player tag used for identifying this page in brightcove reporting
 config['autoStart'] = false; //tells the player to start playing video on load
 config['preloadBackColor'] = '#ffffff'; //background color while loading the player
 config['continuousPlay'] = 'false';
 config['maximized'] = 'true';

 /* do not edit these config items */
 config['playerId'] = '{$arr['ID']}';
 config['width'] = $width;
 config['height'] = $height;

 createExperience(config, 8);
</script>
";
        }
        
        function yahoo_Content($arr)
        {
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Yahoo Video [{$arr['ID']}]";
				
            $width = VideoParser::getWidth($arr['RATIO'], $arr['SIZE']);
            $height = VideoParser::getHeight($arr['RATIO'], $arr['SIZE']);
			
			list($vid, $id) = split("/", $arr['ID']);

			return VideoParser::getJustification($arr) . "<object width=\"$width\" height=\"$height\">
			<param name=\"movie\" value=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" />
			<param name=\"allowFullScreen\" value=\"true\" />
			<param name=\"AllowScriptAccess\" VALUE=\"always\" />
			<param name=\"bgcolor\" value=\"#000000\" />
			<param name=\"flashVars\" value=\"id=$id&vid=$vid&lang=en-us&intl=us&thumbUrl=http%3A//us.i1.yimg.com/us.yimg.com/i/us/sch/cn/video04/3265004_rnd5ffeb85f_19.jpg&embed=1&ap=butterfinger\" />
			<embed src=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" type=\"application/x-shockwave-flash\" width=\"$width\" height=\"$height\" allowFullScreen=\"true\" AllowScriptAccess=\"always\" bgcolor=\"#000000\" flashVars=\"id=$id&vid=$vid&lang=en-us&intl=us&embed=1&ap=butterfinger\" >
			</embed>
			</object>". ( $arr['LINK'] ? "<br /><center><a href='http://video.yahoo.com/watch/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . VideoParser::getEndJustification($arr);
        }
		
		function cnn_Content($arr)
		{
			return VideoParser::getJustification($arr) . "<script src=\"http://i.cdn.turner.com/cnn/.element/js/2.0/video/evp/module.js?loc=dom&vid={$arr['ID']}\" type=\"text/javascript\"></script>" . VideoParser::getEndJustification($arr);
		}
		
/* Begin Excerpt Function Section ****************************************************************************/

		function youtube_Excerpt($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To Youtube Video [{$arr['ID']}]";

			return VideoParser::getJustification($arr) . "<a href='http://www.youtube.com/watch?v={$arr['ID']}&eurl={$_SERVER['SCRIPT_URI']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($entry);
		}

		function youtubecp_Excerpt($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link To Youtube Custom Player [{$arr['ID']}]";

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
	
        function dailymotion_Excerpt($arr)
        {

			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Daily Motion [{$arr['ID']}]";

            return VideoParser::getJustification($arr) . "<a href='http://www.dailymotion.com/swf/{$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
        }

        function myspace_Excerpt($arr)
        {
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Myspace Video [{$arr['ID']}]";

            return "<a href='http://vids.myspace.com/index.cfm?fuseaction=vids.individual&VideoID={$arr['ID']}'>{$arr['BLURB']}</a>" . VideoParser::getEndJustification($arr);
        }

        function brightcovecp_Excerpt($arr)
        {
            return "Bright Cove Channel #" . $arr['ID'];
        }
        
        function yahoo_Excerpt($arr)
        {
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Yahoo Video [{$arr['ID']}]";

			return "<a href='http://video.yahoo.com/watch/{$arr['ID']}'>{$arr['BLURB']}</a>";
        }
		
		function cnn_Excerpt($arr)
		{
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to CNN Video [{$arr['ID']}]";
				
			return "<a href='http://www.cnn.com/video/#{$arr['ID']}'>{$arr['BLURB']}</a>";
		}

	}

	if ( get_option('WPVID_MaxVideoWidth') == "" )
		VideoParser::Install();

	add_filter('the_content', array('VideoParser', 'getContent'));
	add_filter('the_excerpt', array('VideoParser', 'getExcerpt'));

	// Link the options page up
	add_action('admin_menu', array('VideoParser', 'VideoAdministrationMenu'));

?>
