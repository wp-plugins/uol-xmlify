<?php
/*
Plugin Name: uol-xmlify
Plugin URI: http://girino.org/wordpress/plugins/uol-xmlify/
Description: Add ads from uol-xml to your posts and feeds
Author: Girino Vey!
Author URI: http://www.girino.org/
Version: 0.0.2

Copyright 2010 Girino Vey!

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.


HISTORY

Version		Date		Author		Description
--------	--------	-----------	------------------------------
0.0.1		20100502	girino		first version.
0.0.2		20100613	girino		fixed typos, started docs,
						removed my site ids.
*/

define('default_style', 'width: 468px; height: 60px; border: 1px solid grey; margin 5px; padding: 5px');
define('default_numads', 2);
define('default_feeds', 1);
define('default_at_bottom', 1);
define('default_keywords', 'tv');
define('default_cat', 'eletronicos');
define('default_sub', 'tv');
define('default_idtURL', '0000');
define('default_idtLabel', '00000');

$urls = array(
	//'todos' => 'http://xml.shopping.uol.com.br/todos.html',
	//'categoria' => 'http://xml.shopping.uol.com.br/<<CAT>>/index.html',
	'categoria' => 'http://xml.shopping.uol.com.br/<<CAT>>/<<SUB>>/index.html',
	'busca' => 'http://xml.shopping.uol.com.br/busca.html?q=<<KEYWORD>>'
	);

// Adds uol xml ads to content (currently only at the bottom)
function uolXMLifyContent($content)
{
        $feed_only=get_option('uolxmlify_feeds');
	// only affects feeds
	if ( ! is_feed() && $feed_only ) return $content;
	
	$ads_at_bottom = get_option('uolxmlify_at_bottom');

	$new_content="";
	if ($ads_at_bottom) {
		$new_content=$content;
	}
        $xml_url=build_xml_url();
        $style=get_option('uolxmlify_style');
        $numads=get_option('uolxmlify_numads');

	// loads xml
	global $uolxmlify_offers;
	if ($uolxmlify_offers == null) {
		$xml = @simplexml_load_file($xml_url);
		// DEBUG
		//echo '<pre>'.htmlentities($xml->asXML()).'</pre>';
		if ($xml) {
			$uolxmlify_offers = $xml->xpath('//prd');
		} else {
			return $content;
		}
	}
	shuffle($uolxmlify_offers);

        $new_content .= "\n<br clear=\"all\" />\n";
	$new_content .= "<div style=\"width:100%; text-align: center;\">\n";
        $new_content .= "<div style=\"$style; overflow: hidden;\">\n";
	$table_style = 'width:100%; height:100%; border: none; padding: 0; margin: 0;';
	$new_content .= "<table style=\"$table_style\">\n";
	$new_content .= "<tr style=\"vertical-align: middle; text-align: left;\">\n";
	$width = 100/$numads;
	for ($i = 0; $i < $numads; $i++) {
		$offer = $uolxmlify_offers[$i];
		$img = $offer->img->src[url];
		$url = $offer->lnk[url];
		$name = (string) $offer->n;
		// cuts name in 50 chars, so it fits:
		if (strlen($name) > 50) {
			$name = substr($name, 0, 50)."...";
		}
		$new_content .= "<td style=\"width: $width%;\">\n";
		$new_content .= "<table><tr><td style=\"width: 45px;\">\n";
		$new_content .= "<a target=\"_blank\" href=\"$url\">\n";
		$new_content .= "<img height=\"40px\" src=\"$img\">";
		$new_content .= "</a>\n";
		$new_content .= "</td><td>\n";
		$new_content .= "<a target=\"_blank\" href=\"$url\">\n";
		$new_content .= $name;
		$new_content .= "</a>\n";
		$new_content .= "</td></tr></table>\n";
		$new_content .= "</td>\n";
	}
	$new_content .= "</tr>\n";
	$new_content .= "</table>\n";
        $new_content .= "</div>\n";
        $new_content .= "</div>\n";
        $new_content .= "\n<br clear=\"all\" />\n";
	
	if (!$ads_at_bottom) {
		$new_content.=$content;
	}
	
	return $new_content;
}

// Admin Options Page
function uolXMLifyOptionsPage()
{

?>
	<div class="wrap">
		<h2>uol-XMLify</h2>
		<form action="options.php" method="POST">
			<?php settings_fields('uolxmlify_options'); ?>
			<?php do_settings_sections('plugin'); ?>
			<p class="submit"><input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>"></p>
		</form>
	</div>
	<div class="wrap">
		<h2>More Information</h2>
		<p>... comming soon ...</p>
	</div>
<?php
}

// Add Options Page
function uolXMLifyAdminSetup()
{
	add_options_page('uolXMLify', 'uolXMLify', 8, basename(__FILE__), 'uolXMLifyOptionsPage');	
}

function uolXMLify_activate() {
	add_option('uolxmlify_xml_url', array('type' => 'categoria'));
	add_option('uolxmlify_style', default_style);
	add_option('uolxmlify_numads', default_numads);
	add_option('uolxmlify_feeds', default_feeds);
	add_option('uolxmlify_at_bottom', default_at_bottom);
	add_option('uolxmlify_idtURL', default_idtURL);
	add_option('uolxmlify_idtLabel', default_idtLabel);
	add_option('uolxmlify_cat', default_cat);
	add_option('uolxmlify_sub', default_sub);
	add_option('uolxmlify_keyword', default_keywords);
}

function uolXMLify_deactivate() {
	delete_option('uolxmlify_xml_url');
	delete_option('uolxmlify_style');
	delete_option('uolxmlify_numads');
	delete_option('uolxmlify_feeds');
	delete_option('uolxmlify_at_bottom');
	delete_option('uolxmlify_idtURL');
	delete_option('uolxmlify_idtLabel');
	delete_option('uolxmlify_cat');
	delete_option('uolxmlify_sub');
	delete_option('uolxmlify_keyword');
}

function uolxmlify_section_text() {
	echo '<p>Main options:</p>';
}

function uolxmlify_option_text($args) {
	$name = $args[0];
	$type = $args[1];
	$option = get_option($name);
	if ($type == 'checkbox') {
		$line = '<input id="'.$name.'" name="'.$name.'" type="'.$type.'" ';
		if ($option) {
			$line .= ' checked="checked" ';
		}
		$line .= ' value="1"/>';
		echo $line;
	} else {
		echo '<input id="'.$name.'" name="'.$name.'" type="'.$type.'" value="'.$option.'" />';
	}
}

function build_xml_url() {
	global $urls;
        $xml_url=get_option('uolxmlify_xml_url');
        $idtURL = get_option('uolxmlify_idtURL');
        $idtLabel = get_option('uolxmlify_idtLabel');
        $cat = get_option('uolxmlify_cat');
        $sub = get_option('uolxmlify_sub');
        $keyword = get_option('uolxmlify_keyword');

	if ($xml_url["type"] == "outro") {
		$url = $xml_url["url"];
	} else {
		$url = $urls[$xml_url["type"]];
		$url = subst_url($url, $cat, $sub, $keyword, $idtURL, $idtLabel);
	}
	return $url;
}

function subst_url(
			$url,
			$cat = 'categoria', 
			$sub = 'subcategoria', 
			$keyword = 'palavra+chave', 
			$idtURL = 'XXXX', 
			$idtLabel = 'XXXXX') {
	
	$url = str_replace('<<CAT>>', $cat, $url);
	$url = str_replace('<<SUB>>', $sub, $url);
	$url = str_replace('<<KEYWORD>>', $keyword, $url);
	$sep = '?';
	if (strpos($url, $sep) !== false) {
		$sep = '&';
	}
	$url .= $sep."idtURL=".$idtURL;
	if (strlen($idtLabel) > 0) {
		$url .= "&idtLabel".$idtLabel;
	}
	return $url;
}

function uolxmlify_radio_url() {
	global $urls;
	$option = get_option(uolxmlify_xml_url);
	echo '<ol>';
	foreach ($urls as $name => $url) {
		$line = $name." (".subst_url($url).")";
		$checked = ($option["type"] == $name)?' checked="checked"':'';
		echo '<li><input id="uolxmlify_xml_url[type]" name="uolxmlify_xml_url[type]" type="radio" value="'.$name.'"'.$checked.'>'.$line.'</input></li>';
	}
	$checked = ($option["type"] == 'outro')?' checked="checked"':'';
	echo '<li><input id="uolxmlify_xml_url[type]" name="uolxmlify_xml_url[type]" type="radio" value="outro"'.$checked.' />outro ';
	echo '<input id="uolxmlify_xml_url[url]" name="uolxmlify_xml_url[url]" type="text" value="'.$option["url"].'" size="75" />';
	echo '</li>';
	echo '</ol>';
}

function uolXMLifyAdminInit() {
	register_setting( 'uolxmlify_options', 'uolxmlify_xml_url' );
	register_setting( 'uolxmlify_options', 'uolxmlify_style' );
	register_setting( 'uolxmlify_options', 'uolxmlify_numads' );
	register_setting( 'uolxmlify_options', 'uolxmlify_feeds' );
	register_setting( 'uolxmlify_options', 'uolxmlify_at_bottom' );
	register_setting( 'uolxmlify_options', 'uolxmlify_idtURL' );
	register_setting( 'uolxmlify_options', 'uolxmlify_idtLabel' );
	register_setting( 'uolxmlify_options', 'uolxmlify_cat' );
	register_setting( 'uolxmlify_options', 'uolxmlify_sub' );
	register_setting( 'uolxmlify_options', 'uolxmlify_keyword' );

	add_settings_section('uolxmlify_main', 'uolxmlify Settings', 'uolxmlify_section_text', 'plugin');
	add_settings_field('uolxmlify_cat', 'Category', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_cat','text'));
	add_settings_field('uolxmlify_sub', 'Subcategory', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_sub','text'));
	add_settings_field('uolxmlify_keyword', 'Search keywords', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_keyword','text'));
	add_settings_field('uolxmlify_idtURL', 'idtURL', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_idtURL','text'));
	add_settings_field('uolxmlify_idtLabel', 'idtLabel (optional)', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_idtLabel','text'));
	add_settings_field('uolxmlify_xml_url', 'URL', 'uolxmlify_radio_url', 'plugin', 'uolxmlify_main');
	add_settings_field('uolxmlify_style', 'CSS Style', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_style','text'));
	add_settings_field('uolxmlify_numads', 'Number of ads per section', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_numads','text'));
	add_settings_field('uolxmlify_feeds', 'Appears only in feeds?', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_feeds','checkbox'));
	add_settings_field('uolxmlify_at_bottom', 'Ads at the bottom of the post?', 'uolxmlify_option_text', 'plugin', 'uolxmlify_main', array('uolxmlify_at_bottom','checkbox'));

}

// Load uolXMLify Actions
if (function_exists('add_action'))
{
	add_action('the_content', 'uolXMLifyContent');
	add_action('admin_menu', 'uolXMLifyAdminSetup');
	add_action('admin_init', 'uolXMLifyAdminInit');
}



if (function_exists('register_activation_hook')) {
	register_activation_hook( __FILE__, 'uolXMLify_activate' );
}


if (function_exists('register_deactivation_hook')) {
	register_deactivation_hook( __FILE__, 'uolXMLify_deactivate' );
}

?>
