<?php

add_action('admin_menu','add_available_menus');
add_action('admin_head', 'admin_register_head');
$menus = array();
function add_available_menus(){
	global $menus;
	$i = -100;
	
	foreach($menus as $menu)
	{
	$name 		= 	$menu['name'];
	$function	=	$menu['function'];
	
	add_menu_page($name,$name,'read',strtolower(preg_replace("/\s+/","-",$name)),$function,"",$i++);
	}
}

function register_menu($name,$function){
	global $menus;
	$new_menu['name'] 		= $name;
	$new_menu['function']	= $function;
	array_push($menus, $new_menu);

}
function admin_register_head() {
	?>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo plugins_url();?>/style.css" />
	<?php 
}
?>