<?php
/*

Plugin Name: Video Bracket Tag
Plugin URI: http://blog.gneu.org/software-releases/video-bracket-tags/
Description: Insert videos into posts using bracket method. 
Author: Bob Chatman
Version: 3.0
Author URI: http://blog.gneu.org

*/


	class VideoParser
	{
		private static $Width = 0;
		private static $Height = 0;
		
		private static $Debug = false;

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
		
		function checkArgs($argv)
		{
            $Ret = array("ID" => null,
				"RATIO" => get_option('WPVID_DefaultRatio'),
				"JUST" => "CENTER",
				"LINK" => get_option('WPVID_IncludeLink'),
				"BLURB" => "",
				"SIZE" => get_option('WPVID_MaxVideoWidth'),
				"AUTOPLAY" => get_option('WPVID_AutoPlay'));
			
			foreach (array_keys($argv) as $e)
			{
				if (strpos($argv[$e], ","))
				{
					$var = $argv[$e];
					unset($argv[$e]);
					$argv = array_merge($argv,explode(",", trim($var)));
				}
			}
			if (count($argv) == 1)
			{
				if ($Ret['ID'] == "")
				{
					$el = array_keys($argv);
					$Ret['ID'] = $argv[$el[0]];
				}
				
				if (strpos($Ret['ID'], "=") === 0)
					$Ret['ID'] = substr($Ret['ID'], 1);	
			}
			else
			{ 
				foreach (array_keys($argv) as $el)
				{					
					if (array_search(strtoupper($el), array('RATIO', 'ID', 'JUST', 'BLURB', 'SIZE', 'LINK', 'AUTOPLAY')))
					{
						$Ret[strtoupper($el)] = $argv[$el];
					}
					else
					{
						$tArr = explode(":", $argv[$el]);	
						if (count($tArr) == 2 && (ctype_digit($tArr[0]) === true && ctype_digit($tArr[1]) === true))
						{
							$Ret['RATIO'] = $argv[$el];
						}
						else if (ctype_digit($argv[$el]) === true)
							$Ret['SIZE'] = (int)$argv[$el];
						else if (strpos($argv[$el], "=") === 0)
						{
							$Ret['ID'] = substr($argv[$el], 1);	
						}
						else
							switch (strtoupper($argv[$el]))
							{
								case "LEFT" :
								case "RIGHT" :
									$Ret['JUST'] = strtoupper($argv[$el]);
									break;
									
								case "FLOAT" :
									if ($Ret['JUST'] != 'RIGHT')
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
								
									if (strpos($argv[$el], " ") === false)
										$Ret['ID'] = $argv[$el];	
									else
										$Ret['BLURB'] = htmlentities($argv[$el], ENT_QUOTES, "UTF-8");
							}
					}
				}
			}
			
			return $Ret;
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

    				$message = "<strong>Settings saved.</strong>";
                }
			}

			?>
<?php if ($message) : ?>
<div id="message" class="updated fade">
	<p><?php echo $message; ?></p>
</div>
<?php endif; ?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2>Configure Embedded Video Options</h2>
	<p>Supported Players: Youtube, Youtube Custom Player, College Humor, Google, Vimeo, Veoh, LiveLeak, Blip.tv, Revver, Daily Motion, MySpace, Hulu, Yahoo</p>
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
		<input type="submit" name="Submit" class="button-primary" value="Save Changes"> 
		<input type="submit" name="Submit" value="Reset Values" />
	</p>
</form>
</div>
<?php
		}

		function youtube($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to YouTube [{$arr['ID']}]";

			return self::getJustification($arr) . "<object width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'>
						<param name='movie' value='{$arr['ID']}'></param>
						<param name='wmode' value='transparent' ></param>
						<embed src='http://www.youtube.com/v/{$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=1" : "&autoplay=0" ) . "' type='application/x-shockwave-flash' wmode='transparent' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
					 </object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.youtube.com/watch?v={$arr['ID']}&eurl={$_SERVER['SCRIPT_URI']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}
		function youtubepl($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to YouTube [{$arr['ID']}]";

			return self::getJustification($arr) . "<object width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'>
<param name='movie' value='http://www.youtube.com/p/{$arr['ID']}&hl=en_US&fs=1'></param>
<param name='allowFullScreen' value='true'></param>
<param name='allowscriptaccess' value='always'></param>
<embed src='http://www.youtube.com/p/{$arr['ID']}&hl=en_US&fs=1' type='application/x-shockwave-flash' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' allowscriptaccess='always' allowfullscreen='true'></embed></object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.youtube.com/view_play_list?p={$arr['ID']}&eurl={$_SERVER['SCRIPT_URI']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}

		function youtubecp($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to YouTube [{$arr['ID']}]";

			return self::getJustification($arr) . "<object width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'>
						<param name='movie' value='{$arr['ID']}'></param>
						<param name='wmode' value='transparent' ></param>
						<embed src='http://www.youtube.com/cp/{$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=1" : "&autoplay=0" ) . "' type='application/x-shockwave-flash' wmode='transparent' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
					</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.youtube.com/cp/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}

		function google($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Google [{$arr['ID']}]";

			return self::getJustification($arr) . "<embed style='width:" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "px; height:" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "px' src='http://video.google.com/googleplayer.swf?docId={$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=true" : "" ) . "' id='VideoPlayback' type='application/x-shockwave-flash' quality='best' bgcolor='#ffffff' scale='noScale' salign='TL' flashvars='playerMode=embedded' align='middle'></embed>
		  	   " . ( $arr['LINK'] ? "<br /><center><a href='http://video.google.com/videoplay?docid={$arr['ID']}&hl=en'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}

		function vimeo($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Vimeo [{$arr['ID']}]";

			return self::getJustification($arr) . "<object type='application/x-shockwave-flash' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://www.vimeo.com/moogaloop.swf?clip_id={$arr['ID']}&server=www.vimeo.com&fullscreen=1&show_title=1&show_byline=1&show_portrait=0&color=" . ( $arr['AUTOPLAY'] == "1" ? "&autoplay=1" : "&autoplay=0" ) . "'>
    			 <param name='quality' value='best' />
    			 <param name='allowfullscreen' value='true' />
    			 <param name='scale' value='showAll' />
    			 <param name='movie' value='http://www.vimeo.com/moogaloop.swf?clip_id={$arr['ID']}&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=' /></object>
    			 " . ( $arr['LINK'] ? "<br /><center><a href='http://www.vimeo.com/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}

		function liveleak($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to LiveLeak [{$arr['ID']}]";

			return self::getJustification($arr) . "
			<object width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'>
			<param name='movie' value='http://www.liveleak.com/e/{$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "?autoplay=true" : "?autoplay=false" ) . "'></param>
			<param name='wmode' value='transparent'></param>
			<embed src='http://www.liveleak.com/e/{$arr['ID']}" . ( $arr['AUTOPLAY'] == "1" ? "?autoplay=true" : "?autoplay=false" ) . "' type='application/x-shockwave-flash' wmode='transparent' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
			</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.liveleak.com/view?i={$arr['ID']}&hl=en'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}

		function veoh($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Veoh [{$arr['ID']}]";

			return self::getJustification($arr) . "
			<object width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' id='veohFlashPlayer' name='veohFlashPlayer'>
			<param name='movie' value='http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.4.9.1004&permalinkId={$arr['ID']}&player=videodetailsembedded" . ( $arr['AUTOPLAY'] == "1" ? "&videoAutoPlay=1" : "&videoAutoPlay=0") . "&id=anonymous'></param>
			<param name='allowFullScreen' value='true'></param>
			<param name='allowscriptaccess' value='always'></param>
			<embed src='http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?version=AFrontend.5.4.9.1004&permalinkId={$arr['ID']}&player=videodetailsembedded" . ( $arr['AUTOPLAY'] == "1" ? "&videoAutoPlay=1" : "&videoAutoPlay=0") . "&id=anonymous'
			type='application/x-shockwave-flash' 
			allowscriptaccess='always' 
			allowfullscreen='true'
			width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' id='veohFlashPlayerEmbed' name='veohFlashPlayerEmbed'></embed>
			</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.veoh.com/browse/videos/category/webseries/watch/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}
		
		function bliptv($arr)
		{
			$arr = self::checkArgs($arr); 
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Blip.tv [{$arr['ID']}]";

            return self::getJustification($arr) . "<embed src='http://blip.tv/play/{$arr['ID']}" . ( get_option('WPVID_AutoPlay') == "1" ? "?autostart=true" : "?autostart=false" ) . "' type='application/x-shockwave-flash' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' allowscriptaccess='always' allowfullscreen='true' /></embed>
                " . ( $arr['LINK'] ? "<br /><center><a href='http://blip.tv/file/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}

		function revver($arr) # &autostart=true
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to RevveR [{$arr['ID']}]";
			
			return self::getJustification($arr) . "<object width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}' type='application/x-shockwave-flash'>
    			<param name='Movie' value='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}'></param>
    			<param name='FlashVars' value='allowFullScreen=true'></param>
    			<param name='AllowFullScreen' value='true'></param>
    			<param name='AllowScriptAccess' value='always'></param>
    			<param name='AutoStart' value='" . ( $arr['AUTOPLAY'] == "1" ? "true" : "false" ) . "' />
    			<embed type='application/x-shockwave-flash' src='http://flash.revver.com/player/1.0/player.swf?mediaId={$arr['ID']}' pluginspage='http://www.macromedia.com/go/getflashplayer' allowScriptAccess='always' flashvars='allowFullScreen=true' allowfullscreen='true' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "'></embed>
    			</object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.revver.com/video/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
		}

        function dailymotion($arr)
        {
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Daily Motion [{$arr['ID']}]";

            return self::getJustification($arr) . "<object type='application/x-shockwave-flash' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' data='http://www.dailymotion.com/swf/{$arr['ID']}' type='application/x-shockwave-flash'>
                <param name='movie' value='http://www.dailymotion.com/swf/{$arr['ID']}'></param>
                <param name='allowFullScreen' value='true'></param>
                <param name='allowScriptAccess' value='always'></param>
    			<param name='flashvars' value='" . ( $arr['AUTOPLAY'] == "1" ? "" : "" ) . "' />
                </object>
                " . ( $arr['LINK'] ? "<br /><center><a href='http://www.dailymotion.com/swf/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
        }

        function myspace($arr)
        {
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Myspace Video [{$arr['ID']}]";

            return self::getJustification($arr) . "<object width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "px' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "px' >
			<param name='allowFullScreen' value='true'/>
			<param name='wmode' value='transparent'/>
			<param name='movie' value='http://mediaservices.myspace.com/services/media/embed.aspx/m={$arr['ID']},t=1,mt=video,a=" . ( $arr['AUTOPLAY'] == "1" ? "1" : "0" ) . "'/>
			<embed src='http://mediaservices.myspace.com/services/media/embed.aspx/m={$arr['ID']},a=" . ( $arr['AUTOPLAY'] == "1" ? "1" : "0" ) . ",t=1,mt=video' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' allowFullScreen='true' type='application/x-shockwave-flash' wmode='transparent'></embed>
			</object>
            " . ( $arr['LINK'] ? "<br /><center><a href='http://vids.myspace.com/index.cfm?fuseaction=vids.individual&VideoID={$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
        }


        function hulu($arr)
        {
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Myspace Video [{$arr['ID']}]";

            return self::getJustification($arr) . "<object height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "'>
            <param name='movie' value='http://www.hulu.com/embed/{$arr['ID']}'>
            <embed src='http://www.hulu.com/embed/{$arr['ID']}' type='application/x-shockwave-flash' height='" . self::getHeight($arr['RATIO'], $arr['SIZE']) . "' width='" . self::getWidth($arr['RATIO'], $arr['SIZE']) . "'></object>
            " . self::getEndJustification($arr);
        }
        
        function yahoo($arr)
        {
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to Yahoo Video [{$arr['ID']}]";
				
            $width = self::getWidth($arr['RATIO'], $arr['SIZE']);
            $height = self::getHeight($arr['RATIO'], $arr['SIZE']);
			
			list($vid, $id) = split("/", $arr['ID']);

			return self::getJustification($arr) . "<object width=\"$width\" height=\"$height\">
			<param name=\"movie\" value=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" />
			<param name=\"allowFullScreen\" value=\"true\" />
			<param name=\"AllowScriptAccess\" VALUE=\"always\" />
			<param name=\"bgcolor\" value=\"#000000\" />
			<param name=\"flashVars\" value=\"id=$id&vid=$vid&lang=en-us&intl=us&thumbUrl=http%3A//us.i1.yimg.com/us.yimg.com/i/us/sch/cn/video04/3265004_rnd5ffeb85f_19.jpg&embed=1&ap=butterfinger\" />
			<embed src=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" type=\"application/x-shockwave-flash\" width=\"$width\" height=\"$height\" allowFullScreen=\"true\" AllowScriptAccess=\"always\" bgcolor=\"#000000\" flashVars=\"id=$id&vid=$vid&lang=en-us&intl=us&embed=1&ap=butterfinger\" >
			</embed>
			</object>". ( $arr['LINK'] ? "<br /><center><a href='http://video.yahoo.com/watch/{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);
        }
		
		function collegehumor($arr)
		{
			$arr = self::checkArgs($arr);
			
			if (self::$Debug)
				return "<pre>" . print_r($arr) . "</pre>";
			
			if ($arr['BLURB'] == "")
				$arr['BLURB'] = "Direct Link to College Humor Video [{$arr['ID']}]";
				
            $width = self::getWidth($arr['RATIO'], $arr['SIZE']);
            $height = self::getHeight($arr['RATIO'], $arr['SIZE']);
			
			return self::getJustification($arr) . "<object type='application/x-shockwave-flash' data='http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id={$arr['ID']}&fullscreen=1' width='$width' height='$height' >
            <param name='allowfullscreen' value='true'/>
            <param name='wmode' value='transparent'/>
            <param name='allowScriptAccess' value='always'/>
            <param name='movie' quality='best' value='http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id={$arr['ID']}&fullscreen=1'/>
            <embed src='http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id={$arr['ID']}&fullscreen=1' type='application/x-shockwave-flash' wmode='transparent' width='$width' height='$height' allowScriptAccess='always'>
            </embed>
            </object>" . ( $arr['LINK'] ? "<br /><center><a href='http://www.collegehumor.com/video:{$arr['ID']}'>{$arr['BLURB']}</a></center>" : "" ) . self::getEndJustification($arr);			
		}
	}

	if ( get_option('WPVID_MaxVideoWidth') == "" )
		VideoParser::Install();

	add_shortcode('collegehumor', array('VideoParser', 'collegehumor'));
	add_shortcode('youtube', array('VideoParser', 'youtube'));
	add_shortcode('youtubecp', array('VideoParser', 'youtubecp'));
	add_shortcode('youtubepl', array('VideoParser', 'youtubepl'));
	add_shortcode('google', array('VideoParser', 'google'));
	add_shortcode('vimeo', array('VideoParser', 'vimeo'));
	add_shortcode('liveleak', array('VideoParser', 'liveleak'));
	add_shortcode('veoh', array('VideoParser', 'veoh'));
	add_shortcode('bliptv', array('VideoParser', 'bliptv'));
	add_shortcode('revver', array('VideoParser', 'revver'));
	add_shortcode('dailymotion', array('VideoParser', 'dailymotion'));
	add_shortcode('myspace', array('VideoParser', 'myspace'));
	add_shortcode('hulu', array('VideoParser', 'hulu'));
	add_shortcode('yahoo', array('VideoParser', 'yahoo'));

	// Link the options page 
	add_action('admin_menu', array('VideoParser', 'VideoAdministrationMenu'));
	
?>
