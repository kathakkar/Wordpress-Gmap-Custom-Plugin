<?php
/**

*Trigger this file on Plugin uninstall

* @package CustomPlugin
*/


if(!defined('WP_UNINSTALL_PLUGIN')){
	die;
}

//Clear Database stored data
$locations=get_posts(array('post_type'=>'location','numberposts'=>-1));

foreach($books as $book){

	wp_delete_post($book->ID,true);
}
?>