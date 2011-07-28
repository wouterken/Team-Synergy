<?php
/*
Plugin Name: Content Extractor
Plugin URI: pico.net.nz
Description: Allows you to extract content from a website.
Version: 0.3.0
Author: Wouter Coppieters
Author URI: rorohiko.com
License: GPL2
*/
global $wpdb;

include_once 'extractor.php';
add_action('admin_menu', 'content_extractor');

/**
Adds the Atomic Menu and the three sub-menus
*/
function content_extractor() {

  add_menu_page("Content Extractor","Content Extractor","manage_options","content-extractor","load_ui","",62);

}


function load_ui(){
?>
<div style="margin: 0px auto;"/>
<form action="" method="post">
	<?php if(!$_POST['url']){
		$_POST['url']="http://juxtr.net";}
		?>
<input  style="float: left;" type="text" size="30" name="url" value="<?php echo $_POST['url']; ?>"/>
  
    <input style="float: left;" type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Extract Content') ?>" />

</form>
<div style="line-height: 2em;
padding: 10px;
width: 500px;">
<?
if($_POST['url']){

$content = extract_content($_POST['url']);
if($content['hasContent'] == false){
$content = extract_content($_POST['url'],true);
}
print_content($content);
}
?></div></div><?php
}



?>
