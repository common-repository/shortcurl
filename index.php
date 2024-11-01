<?php
/*
Plugin Name: EZ SHORTCURL Shortcodes to Fetch and Parse External Content
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/shortcurl/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Description: Use the shortcode "remote_get" with the parameter "url" to insert the content from that url into your page or post.
Version: 3.17.49
*/
$SHORTCURL_Version="3.17.49";
/*            ___
 *           /  /\     SHORTCURL Main Plugin File
 *          /  /:/     @package SHORTCURL
 *         /__/::\
 Copyright \__\/\:\__  © 2012-2017 Eli Scheetz (email: wordpress@ieonly.com)
 *            \  \:\/\
 *             \__\::/ This program is free software; you can redistribute it
 *     ___     /__/:/ and/or modify it under the terms of the GNU General Public
 *    /__/\   _\__\/ License as published by the Free Software Foundation;
 *    \  \:\ /  /\  either version 2 of the License, or (at your option) any
 *  ___\  \:\  /:/ later version.
 * /  /\\  \:\/:/
  /  /:/ \  \::/ This program is distributed in the hope that it will be useful,
 /  /:/_  \__\/ but WITHOUT ANY WARRANTY; without even the implied warranty
/__/:/ /\__    of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
\  \:\/:/ /\  See the GNU General Public License for more details.
 \  \::/ /:/
  \  \:\/:/ You should have received a copy of the GNU General Public License
 * \  \::/ with this program; if not, write to the Free Software Foundation,
 *  \__\/ Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA        */

foreach (array("get_option", "add_action", "add_shortcode", "register_activation_hook") as $func)
	if (!function_exists("$func"))
		die('You are not allowed to call this page directly.<p>You could try starting <a href="/">here</a>.');

function SHORTCURL_admin_notices() {
	$admin_notices = get_option('SHORTCURL_admin_notices');
	if (isset($_GET['SHORTCURL_admin_key']) && isset($admin_notices[$_GET['SHORTCURL_admin_key']])) {
		unset($admin_notices[$_GET['SHORTCURL_admin_key']]);
		update_option('SHORTCURL_admin_notices', $admin_notices);
	}
	$_SERVER_REQUEST_URI = str_replace('&amp;','&', htmlspecialchars( $_SERVER['REQUEST_URI'] , ENT_QUOTES ) );
	$script_URI = $_SERVER_REQUEST_URI.(strpos($_SERVER_REQUEST_URI,'?')?'&':'?').'ts='.microtime(true);
	if (is_array($admin_notices))
		foreach ($admin_notices as $key=>$admin_notice)
			echo "<div class=\"error\">$admin_notice <a href='$script_URI&SHORTCURL_admin_key=$key'>[dismiss]</a></div>";
}
add_action("admin_notices", "SHORTCURL_admin_notices");

function SHORTCURL_install() {
	global $wp_version;
	if (version_compare($wp_version, "2.7", "<") || !function_exists("wp_remote_get"))
		die("This Plugin requires WordPress version 2.7 or higher and wp_remote_get() to work!");
}
register_activation_hook(__FILE__, "SHORTCURL_install");

function SHORTCURL_set_plugin_row_meta($links_array, $plugin_file) {
	if ($plugin_file == substr(str_replace("\\", "/", __FILE__), (-1 * strlen($plugin_file))) && strlen($plugin_file) > 10)
		$links_array = array_merge($links_array, array('<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ"><span class="dashicons dashicons-heart"></span>Donate</a>'));
	return $links_array;
}
add_filter("plugin_row_meta", "SHORTCURL_set_plugin_row_meta", 1, 2);

function SHORTCURL_preg_replace($attr = array(), $content) {
	return SHORTCURL_preg_replace_shortcode($attr, do_shortcode($content));
}
add_shortcode("preg_replace", "SHORTCURL_preg_replace");

function SHORTCURL_preg_replace_shortcode($attr = array(), $content) {
	$regex = array();
	$with = array();
	foreach (array_keys($attr) as $k)
		if (substr($k, 0, 1) == 'r')
			$regex[] = $attr[$k];
		elseif (substr($k, 0, 1) == 'w')
			$with[] = $attr[$k];
	if (count($regex) && count($with))
		$content = preg_replace($regex, $with, $content);
	return do_shortcode($content);
}
add_shortcode("preg_replace_shortcode", "SHORTCURL_preg_replace_shortcode");

function SHORTCURL_str_replace($attr = array(), $content) {
	if (isset($attr['replace']) && strlen($attr['replace']) && isset($attr['with']))
		$content = str_replace($attr['replace'], $attr['with'], $content);
//		$content = str_replace(html_entity_decode($attr['replace']), html_entity_decode($attr['with']), $content);//maybe this would be better, maybe...
	return $content;
}
add_shortcode("str_replace", "SHORTCURL_str_replace");

function SHORTCURL_remote_get($attr, $url = "") {
	$return = '';
	$debug = '';
	$error = '';
	if (strlen(trim($url)))
		$attr["url"] = $url;
	if (isset($attr['url']) && strlen(trim($attr['url']))) {
		if (!(isset($attr['timeout']) && is_numeric($attr['timeout'])))
			$attr['timeout'] = 30; //default remote page to timeout after 30 seconds
		if (!(isset($attr['expire']) && is_numeric($attr['expire'])))
			$attr['expire'] = 60*60*24; //default cache to expire in 24 hours
		if (!isset($GLOBALS["SC_URL"][$attr['url']]['date'])) {
			$cache_file = dirname(__FILE__).'/cache/'.md5($attr['url']);
			if (is_file($cache_file) && $GLOBALS["SC_URL"][$attr['url']]['body'] = @file_get_contents($cache_file))
				$GLOBALS["SC_URL"][$attr['url']]['date'] = filemtime($cache_file);
		}
		if (isset($GLOBALS["SC_URL"][$attr['url']]['date']) && $GLOBALS["SC_URL"][$attr['url']]['date']>(time()-($attr['expire'])))
			$debug .= html_entity_decode($attr["url"]).'====='.$attr["url"].'SHORTCURL cached('.date("Y-m-d H:i:s", $GLOBALS["SC_URL"][$attr['url']]['date'])."): ".(floor((time()-$GLOBALS["SC_URL"][$attr['url']]['date'])/60)>59?floor((time()-$GLOBALS["SC_URL"][$attr['url']]['date'])/60/60)." hours":floor((time()-$GLOBALS["SC_URL"][$attr['url']]['date'])/60)." minutes")." ago;\n";
		elseif ($got = wp_remote_get(html_entity_decode($attr['url']), (isset($attr['timeout'])?array("timeout" => $attr['timeout']):array()))) {
			if (is_wp_error($got))
				$error .= "SHORTCURL ERROR: wp_remote_get(".html_entity_decode($attr['url']).") returned ".print_r(array("ERROR"=>$got), true)."\n";
			elseif (isset($got['body']) && strlen($got['body'])) {
				$GLOBALS["SC_URL"][$attr['url']]['body'] = $got['body'];
				$GLOBALS["SC_URL"][$attr['url']]['date'] = time();
				if ($written = @file_put_contents($cache_file, $GLOBALS["SC_URL"][$attr["url"]]["body"]))
					$debug .= "SHORTCURL cached(".strlen($GLOBALS["SC_URL"][$attr["url"]]["body"]).") bytes to ".md5($attr["url"]).";\n";
			}
		}
		if (isset($GLOBALS["SC_URL"][$attr['url']]['body'])) {
			$return = $GLOBALS["SC_URL"][$attr['url']]['body'];
			$debug .= "SHORTCURL body_length(".strlen($return).");\n";
			if (isset($attr['start']) && strpos($return, html_entity_decode($attr['start'])))
				$return = substr($return, strpos($return, html_entity_decode($attr['start'])));
			elseif (isset($attr['start'])) $error .= "SHORTCURL start=<b>".htmlspecialchars($attr['start'])."</b> but not found in ($attr[url])!\n";
			if (isset($attr['stop']) && strpos($return, html_entity_decode($attr['stop'])))
				$return = substr($return, 0, strpos($return, html_entity_decode($attr['stop'])));
			elseif (isset($attr['stop'])) $error .= "SHORTCURL stop=<b>".htmlspecialchars($attr['stop'])."</b> but not found in ($attr[url])!\n";
			if (isset($attr['end']) && strpos($return, $attr['end']))
				$return = substr($return, 0, strpos($return, $attr['end']) + strlen($attr['end']));
			elseif (isset($attr['end'])) $error .= "SHORTCURL end=<b>".htmlspecialchars($attr['end'])."</b> but not found in ($attr[url])!\n";
			if (isset($attr['length']) && is_numeric($attr['length']) && strlen($return) > abs($attr['length']))
				$return = substr($return, 0, $attr['length']);
			elseif (isset($attr['length'])) $error .= "SHORTCURL length=<b>".($attr['length'])."</b> Invalid when content length=<b>".strlen($return)."</b>!\n";
			if (isset($attr['replace']) && isset($attr['with']) && strlen($attr['replace']))
				$return = str_replace($attr['replace'], $attr['with'], $return);
			if (isset($attr['replace2']) && isset($attr['with2']) && strlen($attr['replace2']))
				$return = str_replace($attr['replace2'], $attr['with2'], $return);
		} else
			$error .= "SHORTCURL ERROR: wp_remote_get($attr[url]) returned NOTHING!\n";
	}
	if ($error) {
		$admin_notices = get_option('SHORTCURL_admin_notices');
		$admin_notices[md5($error)] = date("m-d H:i: ").$_SERVER["REQUEST_URI"]."<li>$error</li><br /><textarea>".(isset($GLOBALS["SC_URL"][$attr["url"]]["body"])?htmlspecialchars($GLOBALS["SC_URL"][$attr["url"]]["body"]):"No Content found!!!")."</textarea>";
		update_option('SHORTCURL_admin_notices', $admin_notices);
	}
	return "<!-- $debug -->\n$return";
}
add_shortcode("remote_get", "SHORTCURL_remote_get");

$SHORTCURL_align_types = array("fixed", "relative", "absolute", "static");
$SHORTCURL_align_x = array("right", "left", "top", "bottom");
$SHORTCURL_align_y = array("top", "bottom", "right", "left");
class SHORTCURL_Widget_Class extends WP_Widget {
	function __construct() {
		parent::__construct('SHORTCURL-Widget', __('Shortcode Widget'), array('classname' => 'SHORTCURL_Widget_Class', 'description' => __('Execute a Shortcode with this widget')));
	}
	function widget($args, $instance) {
		global $SHORTCURL_align_x, $SHORTCURL_align_y, $SHORTCURL_align_types;
		extract($args);
		if (!(isset($instance['title']) && $instance['title']))
			$instance['title'] = "";
		if (!(isset($instance['code']) && strlen(trim($instance['code'])) > 4))
			$instance['code'] = "";
		if (!(isset($instance['popout']) && $instance['popout']))
			$instance['popout'] = "no";
		if (!(isset($instance['usecat']) && $instance['usecat']))
			$instance['usecat'] = "no";
		if (!(isset($instance['x-position']) && is_numeric($instance['x-position'])))
			$instance['x-position'] = 10;
		if (!(isset($instance['y-position']) && is_numeric($instance['y-position'])))
			$instance['y-position'] = 30;
		if (!(isset($instance['x-align']) && is_numeric($instance['x-align'])))
			$instance['x-align'] = 0;
		if (!(isset($instance['y-align']) && is_numeric($instance['y-align'])))
			$instance['y-align'] = 0;
		if (!(isset($instance['x-type']) && is_numeric($instance['x-type'])))
			$instance['x-type'] = 0;
		if (!(isset($instance['y-type']) && is_numeric($instance['y-type'])))
			$instance['y-type'] = 0;
		echo $before_widget;
		if (strlen($instance["title"]) > 0)
			echo $before_title.$instance["title"].$after_title;
		if ($instance['popout'] == "yes") {
			echo '<div id="SHORTCURL_DIV_0" style="position: relative;"><div id="SHORTCURL_DIV_1" style="position: '.$SHORTCURL_align_types[$instance['y-type']].'; '.$SHORTCURL_align_y[$instance['y-align']].': '.$instance['y-position'].'px;"><div id="SHORTCURL_DIV_2" style="position: '.$SHORTCURL_align_types[$instance['x-type']].'; '.$SHORTCURL_align_x[$instance['x-align']].': '.$instance['x-position'].'px;">';
			$after_widget = '</div></div></div>'.$after_widget;
		}
		echo do_shortcode($instance['code']).$after_widget;
	}
	function flush_widget_cache() {
		wp_cache_delete('SHORTCURL_Widget_Class', 'widget');
	}
	function update($new, $old) {
		$instance = $old;
		$instance['title'] = strip_tags($new['title']);
		$instance['code'] = ($new['code']);
		$instance['popout'] = strip_tags($new['popout']);
		$instance['x-position'] = (int) $new['x-position'];
		$instance['y-position'] = (int) $new['y-position'];
		$instance['x-type'] = (int) $new['x-type'];
		$instance['y-type'] = (int) $new['y-type'];
		$instance['x-align'] = (int) $new['x-align'];
		$instance['y-align'] = (int) $new['y-align'];
		return $instance;
	}
	function form($instance) {
		global $SHORTCURL_align_x, $SHORTCURL_align_y, $SHORTCURL_align_types;
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$code = isset($instance['code']) ? esc_attr($instance['code']) : '';
		$popout = isset($instance['popout']) ? esc_attr($instance['popout']) : 'no';
		$x_position = isset($instance['x-position']) ? (int) ($instance['x-position']) : 10;
		$y_position = isset($instance['y-position']) ? (int) ($instance['y-position']) : 30;
		$x_align = isset($instance['x-align']) ? absint($instance['x-align']) : 0;
		$y_align = isset($instance['y-align']) ? absint($instance['y-align']) : 0;
		$x_type = isset($instance['x-type']) ? absint($instance['x-type']) : 0;
		$y_type = isset($instance['y-type']) ? absint($instance['y-type']) : 0;
		$type_opts = '';
		for ($o=0; $o<count($SHORTCURL_align_types); $o++)
			$type_opts .= '<option value="'.$o.'">'.$SHORTCURL_align_types[$o].'</option>';
		$x_opts = '<select name="'.$this->get_field_name('x-type').'" id="'.$this->get_field_id('x-type').'">'.str_replace('value="'.$x_type.'"', 'value="'.$x_type.'" selected', $type_opts).'</select><select name="'.$this->get_field_name('x-align').'" id="'.$this->get_field_id('x-align').'">';
		for ($o=0; $o<count($SHORTCURL_align_x); $o++)
			$x_opts .= '<option value="'.$o.'"'.($x_align==$o?" selected":"").'>'.$SHORTCURL_align_x[$o].'</option>';
		$y_opts = '<select name="'.$this->get_field_name('y-type').'" id="'.$this->get_field_id('y-type').'">'.str_replace('value="'.$y_type.'"', 'value="'.$y_type.'" selected', $type_opts).'</select><select name="'.$this->get_field_name('y-align').'" id="'.$this->get_field_id('y-align').'">';
		for ($o=0; $o<count($SHORTCURL_align_y); $o++)
			$y_opts .= '<option value="'.$o.'"'.($y_align==$o?" selected":"").'>'.$SHORTCURL_align_y[$o].'</option>';
		echo '<p><label for="'.$this->get_field_id('title').'">'.__('Optional Widget Title').':</label>
		<input type="text" name="'.$this->get_field_name('title').'" id="'.$this->get_field_id('title').'" value="'.$title.'" /></p>
		<p><label for="'.$this->get_field_id('code').'">'.__('Shortcode (or any Text/HTML)').':</label><br />
		<textarea name="'.$this->get_field_name('code').'" id="'.$this->get_field_id('code').'" rows="5" style="width: 100%;">'.$code.'</textarea></p>
		<p><label for="'.$this->get_field_id('popout').'">'.__('Use custom positioned DIVs').':</label>
		<input type="checkbox" name="'.$this->get_field_name('popout').'" id="'.$this->get_field_id('popout').'" value="yes"'.($popout=="yes"?" checked":"").' />yes</p>
		<p><label for="'.$this->get_field_id('y-position').'">DIV_1 Alignment and Position:</label><br />
		'.$y_opts.'</select><input type="text" size="2" name="'.$this->get_field_name('y-position').'" id="'.$this->get_field_id('y-position').'" value="'.$y_position.'" />px</p>
		<p><label for="'.$this->get_field_id('x-position').'">DIV_2 Alignment and Position:</label><br />
		'.$x_opts.'</select><input type="text" size="2" name="'.$this->get_field_name('x-position').'" id="'.$this->get_field_id('x-position').'" value="'.$x_position.'" />px</p>';
	}
}
add_action('widgets_init', create_function('', 'return register_widget("SHORTCURL_Widget_Class");'));
