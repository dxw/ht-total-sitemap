<?php

/*
Plugin Name: HT Total Sitemap
Plugin URI: http://www.helpfultechnology.com
Description: Create simple nested list-style sitemap of all site content (pages and posts/post types)
Author: Steph Gray
Version: 0.1
Author URI: http://www.helpfultechnology.com
*/

function hp_totalsitemap_shortcode($atts,$content){
    //get any attributes that may have been passed; override defaults
    $opts=shortcode_atts( array(
        'childof' => '',
        'depth' => '',
        'exclude' => ''
        ), $atts );
	

	$postTypes1 = get_post_types(array( '_builtin' => true), 'objects');
	$postTypes2 = get_post_types(array( '_builtin' => false), 'objects');
	$postTypes = array_merge($postTypes1,$postTypes2);
	
	$exclude = array_merge(explode(",",$opts['exclude']),array('nav_menu_item','acf','acf-field','acf-field-group','spot'));
	//print_r($exclude);
	
	foreach((array)$postTypes as $pt) {
		if (!in_array($pt->name,$exclude)) {
			
			$query = get_posts(array(
				"post_type" => $pt->name,
				"posts_per_page" => -1,
			));
			
			foreach((array)$query as $r) {
				$results[$pt->name][] = array(
					"id" => $r->ID,
					"date" => $r->post_date,
					"title" => $r->post_title,
					"content" => $r->post_content,
					"url" => get_permalink($r->ID),
					"author" => get_the_author_meta('display_name',$r->post_author)
				);
			}
		}
	}

	foreach((array)$results as $k => $r) {
		$html .= "<h2>".ucfirst($k). " (" . count($r) . " items)</h2>";
		$html .= "<table class='table' style='width:100%;' id='{$k}'><thead><th style='width:10%'>Date</th><th>Title</th><th>URL</th><th>Author</th></thead><tbody>";

		foreach((array)$r as $item) {
			$html .= "<tr><td>".date("d-m-Y",strtotime($item['date']))."</td><td>{$item['title']}</td><td><a href='{$item['url']}'>{$item['url']}</a></td><td>{$item['author']}</td></tr>";
		}	
			
		$html .= "</tbody></table>";
		
		$toc .= "<li><a href='#".$k."'>".ucfirst($k). " (" . count($r) . " items)</a></li>";
	}

	$output = "<div class='htautositemap'>";
	$output .= "<p>Jump to:</p><ul id='toc'>".$toc."<li><a href='#page-sitemap'>Page structure</a></ul>";
	$output .= $html;
	$output .="</div>";
	
	$output .= "<h2 id='page-sitemap'>Sitemap (page structure)</h2>";
	$output .= wp_list_pages("echo=0&title_li=&child_of=" . $opts['childof'] . "&depth=10&exclude=" . $opts['exclude'] . "&sort_column=menu_order");
	
	return $output;
}

add_shortcode("totalsitemap", "hp_totalsitemap_shortcode");

?>