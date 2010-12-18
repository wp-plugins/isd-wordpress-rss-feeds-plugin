<?php
/*
Plugin Name: ISD Wordpress RSS Feeds Dashboard Plugin
Plugin URI: http://www.mikeleachcreative.co.uk/wordpress-plugins/ISD-feeds-plugin
Description: I created this plugin to pull in feeds from a category from our blog so that we can promote to our clients from the dashboard.
Version: 1.0
Author: Samuel East
Author URI: http://www.mikeleachcreative.co.uk
License: A "Slug" license name e.g. GPL2
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

// Some Defaults
$feed_link	=  get_bloginfo('url') . "/?feed=rss2";
$head_text	= 'Change this title via the ISD Feed Options';
$icon	= '';
$limit	= '4';
$excerpt = '200';


// Put our defaults in the "wp-options" table
add_option("ISD-feed-link", $feed_link);
add_option("ISD-head-text", $head_text);
add_option("ISD-icon", $icon);
add_option("ISD-limit", $limit);
add_option("ISD-excerpt", $excerpt);

// Start the plugin
if ( ! class_exists( 'ISD_Feed_Admin' ) ) {
	
	class ISD_Feed_Admin {

// prep options page insertion
function add_config_page() {
			if ( function_exists('add_submenu_page') ) {
				add_options_page('ISD Options', 'ISD Feed Options', 10, basename(__FILE__), array('ISD_Feed_Admin','config_page'));
			}	
	}
// Options/Settings page in WP-Admin
		function config_page() {
			if ( isset($_POST['submit']) ) {
				$nonce = $_REQUEST['_wpnonce'];
				if (! wp_verify_nonce($nonce, 'ISD-updatesettings') ) die('Security check failed'); 
				if (!current_user_can('manage_options')) die(__('You cannot edit the search-by-category options.'));
				check_admin_referer('ISD-updatesettings');
				
				
			// Get our new option values
			$feed_link	= $_POST['feed-link'];
			$head_text	= $_POST['head-text'];
			$icon	= $_POST['icon-text'];
			$limit	= $_POST['limit-text'];
			$excerpt	= $_POST['excerpt-text'];

	        // Update the DB with the new option values
			update_option("ISD-feed-link", mysql_real_escape_string($feed_link));
			update_option("ISD-head-text", mysql_real_escape_string($head_text));
			update_option("ISD-icon", mysql_real_escape_string($icon));
			update_option("ISD-limit", mysql_real_escape_string($limit));
			update_option("ISD-excerpt", mysql_real_escape_string($excerpt));
                
			}

$feed_link	= get_option("ISD-feed-link");
$head_text	= get_option("ISD-head-text");
$icon	= get_option("ISD-icon");
$limit	= get_option("ISD-limit");
$excerpt	= get_option("ISD-excerpt");
?>

<div class="wrap">
  <h2>ISD Feed Options</h2>
  <form action="" method="post" id="isd-config">
    <table class="form-table">
      <?php if (function_exists('wp_nonce_field')) { wp_nonce_field('ISD-updatesettings'); } ?>
      <tr>
        <th scope="row" valign="top"><label for="feed-link">Your Feed URL:</label></th>
        <td><input type="text" name="feed-link" id="feed-link" class="regular-text" value="<?php echo $feed_link; ?>"/><small> Please enter the feed url here! view the samples below...</small></td>
      </tr>
      
      
              <tr>
        <th scope="row" valign="top"><label for="icon-text">Your Icon URL:</label></th>
        <td><input type="text" name="icon-text" id="icon-text" class="regular-text" value="<?php echo $icon; ?>"/><small> This must be the full link to image... size 25px by 25px.</small></td>
      </tr>
      
        <tr>
        <th scope="row" valign="top"><label for="head-text">Your Header Text:</label></th>
        <td><input type="text" name="head-text" id="head-text" class="regular-text" value="<?php echo $head_text; ?>"/><small> This must be set to something! if left blank the plugin will not show...</small></td>
      </tr>
      
       <tr>
        <th scope="row" valign="top"><label for="limit-text">Amount Of Posts to Show:</label></th>
        <td><input type="text" name="limit-text" id="limit-text" class="regular-text" value="<?php echo $limit; ?>"/><small> This is Default to 4!...</small></td>
      </tr>
      
      <tr>
        <th scope="row" valign="top"><label for="excerpt-text">Excerpt Length:</label></th>
        <td><input type="text" name="excerpt-text" id="excerpt-text" class="regular-text" value="<?php echo $excerpt; ?>"/><small> This is Default to 200!...</small></td>
      </tr>
      
    </table>
    <br/>
    <span class="submit" style="border: 0;">
    <input type="submit" name="submit" value="Save Settings" />
    </span>
  </form>
</div>
<h2>Examples:</h2>
<span>http://example.com/comments/feed/</span><br />
<span>http://example.com/?feed=commentsrss2</span><br />
<span>http://example.com/post-name/feed/</span><br />
<span>http://example.com/?feed=rss2</span><br />
<span>http://www.example.com/?cat=42&amp;feed=rss2</span><br />
<span>http://www.example.com/?tag=tagname&amp;feed=rss2</span><br />
<span>http://example.com/category/categoryname/feed</span><br />
<span>http://example.com/tag/tagname/feed</span>


<h2>You can also grab any RSS feeds links from the following site</h2>
<span><a href="http://www.feedage.com" target="_blank">http://www.feedage.com/</a></span>

<?php		}
	}
}
 
// insert into admin panel
add_action('admin_menu', array('ISD_Feed_Admin','add_config_page'));

function ISD_feed() {
     
	$feed_link_url = get_option("ISD-feed-link");
	$post_limit = get_option("ISD-limit");
	$excerpt_limit = get_option("ISD-excerpt");
	include_once(ABSPATH.WPINC.'/feed.php');
	$feed = fetch_feed($feed_link_url);

	$limit = $feed->get_item_quantity($post_limit); // specify number of items
	$items = $feed->get_items(0, $limit); // create an array of items
	
	


if ($limit == 0) echo '<div>The feed is either empty or unavailable.</div>';
else foreach ($items as $item) : 

echo "<div class='isd-head'>
      <a href='" . $item->get_permalink() . "' title='" . $item->get_date('j F Y @ g:i a') . "'>" . $item->get_title() . "</a>       </div>
      <div class='isd-body'><p>" . substr($item->get_description(), 0, $excerpt_limit) . "<span> <a href='" . $item->get_permalink() . "'>Read More</a></span></p></div>";
	  
endforeach;
} 

function example_add_dashboard_widgets() {
	$head_text_url = get_option("ISD-head-text"); 
	$head_icon_url = get_option("ISD-icon");
	wp_add_dashboard_widget("example_dashboard_widget", "<img style='float:left;' src='$head_icon_url' width='25' height='25' alt='' /><span class='isd-head'>$head_text_url</span>", "ISD_feed");	
} 



add_action('wp_dashboard_setup', 'example_add_dashboard_widgets' );


function style_insert() {
		$current_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__));
		
		echo '<link href="'.$current_path.'/css/isd-dashboard-style.css" type="text/css" rel="stylesheet" />';
		
} 
// insert custom stylesheet
add_action('in_admin_header','style_insert');
?>
