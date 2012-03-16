<?php
	/*
		Plugin Name: Eralha Image Galery
		Plugin URI: 
		Description: You can create galerys and upload your fotos/images, then just include [eralha-galery id:x] to render a galery in a post or page
		Version: 0.0.0.1
		Author: Emanuel Ralha
		Author URI: 
	*/

// No direct access to this file
defined('ABSPATH') or die('Restricted access');

if (!class_exists("eralha_galeria")){
	class eralha_galeria{

		var $optionsName = "eralha_image_gallery";
		var $dbVersion = "0.1";

		function eralha_galeria(){
			
		}

		function init(){
			$installed_ver = get_option($this->optionsName."_db_version");
			if($installed_ver != $this->dbVersion){
				$this->activationHandler();
				update_option($this->optionsName."_db_version", $this->dbVersion);
			}
		}
		function activationHandler(){
			global $wpdb;

			$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";
			$table_images = $wpdb->prefix.$this->optionsName."_images";

			$sqlTblGalerias = "CREATE TABLE ".$table_galerias." 
			(
				`idGaleria` int(6) NOT NULL auto_increment, 
				`iData` int(32) NOT NULL, 
				`iUserId` int(32) NOT NULL, 
				`vchGalleryName` varchar(255) NOT NULL,
				`vchGalleryDescription` varchar(510) NOT NULL, 
				`iDisp` int(1) NOT NULL, 
				PRIMARY KEY  (`idGaleria`)
			);";

			$sqlTblImages= "CREATE TABLE ".$table_images."
			(
				`idImagem` INT(8) NOT NULL AUTO_INCREMENT, 
				`idGaleria` INT(6) NOT NULL,  
				`iData` INT(32) NOT NULL, 
				`iUserId` INT(32) NOT NULL, 
				`vchImageName` VARCHAR(255) NOT NULL, 
				`vchImageDescription` VARCHAR(510) NOT NULL, 
				PRIMARY KEY  (`idImagem`)
			);";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sqlTblGalerias);
			dbDelta($sqlTblImages);

			add_option($this->optionsName."_db_version", $this->dbVersion);
		}
		function deactivationHandler(){
			global $wpdb;

			$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";
			$table_images = $wpdb->prefix.$this->optionsName."_images";

			//$wpdb->query("DROP TABLE IF EXISTS ". $table_galerias);
			//$wpdb->query("DROP TABLE IF EXISTS ". $table_images);
		}

		function printAdminPage(){
			global $wpdb;
			global $user_ID;

			$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";
			$table_images = $wpdb->prefix.$this->optionsName."_images";

			//$pluginDir = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

			if(isset($_GET["page"]) && $_GET["page"] == "insert-screen"){
				//GET TEMPLATE ADMIN
					include "templates/insert_page.php";
					include "templates/list_page.php";
			}
			if(isset($_GET["page"]) && $_GET["page"] == "gallery-screen"){
				//SHOW UPLOAD SCREEN
				if(isset($_GET["handler"])){
					if($_GET["handler"] == "upload"){
						include "templates/gallery_page.php";
						return;
					}
					//OPEN FILE UPLOAD HANDLERS
					if($_GET["handler"] == "upload-file"){
						include "objects/SimpleImage.php";
						include "templates/upload_page.php";
						return;
					}
					//DELETE GALLERY AND ALL ASSOCIATED IMAGES
					if($_GET["handler"] == "delete-gallery"){
						if(current_user_can('administrator') || current_user_can('editor')){
							$query = $wpdb->query($wpdb->prepare("DELETE FROM ".$table_galerias." WHERE idGaleria = '".$_GET["id"]."' "));
						}else{
							$query = $wpdb->query($wpdb->prepare("DELETE FROM ".$table_galerias." WHERE idGaleria = '".$_GET["id"]."' AND iUserId = '".$user_ID."' "));
						}

						if($query){
							$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = '".$_GET["id"]."'"), ARRAY_A);
							foreach($imagesData as $img){
								$this->deleteImage($img["idImagem"]);
							}
						}
					}
				}
				//GET TEMPLATE GALLERY LIST
				include "templates/list_page.php";
			}
		}

		function get_token($str){
			return md5($this->optionsName.$str);
		}

		function getAjaxGalleryImages(){
			global $wpdb;
			global $user_ID;

			$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";
			$table_images = $wpdb->prefix.$this->optionsName."_images";

			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);
				
				if(isset($_POST["handler"])){
					if($_POST["handler"] == "delete-image"){
						if($this->get_token($_POST["imageID"]) == $_POST["token"]){
							$this->deleteImage($_POST["imageID"]);
						}
					}
				}
				include "templates/image_list_page_ajax.php";

			die(); // this is required to return a proper result

		}

		function deleteImage($imageID){
			global $wpdb;
			global $user_ID;

			$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";
			$table_images = $wpdb->prefix.$this->optionsName."_images";

			if(current_user_can('administrator') || current_user_can('editor')){
				$image = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idImagem = '".$imageID."' "), ARRAY_A);
			}else{
				$image = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idImagem = '".$imageID."' AND iUserId = '".$user_ID."'"), ARRAY_A);
			}

			foreach($image as $img){
				//DELETE IMAGE FROM UPLOAD FOLDER
					$uploadPath = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
					@unlink("..".$uploadPath."uploads/".$img[0]["vchImageName"]);

				//DELETE FILE FROM DATA BASE
					$wpdb->query($wpdb->prepare("DELETE FROM ".$table_images." WHERE idImagem = '".$imageID."' "));
			}
		}

		function checkPostGallery(){
			global $wpdb;
			global $user_ID;

			if (isset($_POST['update_gallery'])) {
				$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";

				if (isset($_POST['galleryDescription'])) {
					$rows_affected = $wpdb->insert($table_galerias, 
													array(
														'iData'=>time(), 
														'iUserId'=>$user_ID, 
														'vchGalleryName'=>$_POST['galleryName'], 
														'vchGalleryDescription'=>$_POST['galleryDescription']
													));
				}
				?><div class="updated"><p><strong><?php _e("Gallery Inserted", $this->optionsName);?></strong></p></div><?php
			}
		}

		function addContent($content=''){
			global $wpdb;

			$table_images = $wpdb->prefix.$this->optionsName."_images";

			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

			preg_match_all('(\[eralha-gallery id:[0-9]*\])', $content, $matches, PREG_PATTERN_ORDER);
			
			for($i=0; $i < count($matches[0]); $i++){
				$id = str_replace("[eralha-gallery id:", "", $matches[0][$i]);
				$id = str_replace("]", "", $id);
				$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = '".$id."' ORDER BY idImagem DESC"), ARRAY_A);
				
				//OUTPUT ALL IMAGES FOR GIVEN ID.
					$template = "<div class='eralha-gallery-post-container' id='eralha-gallery-".$id."-container'>";
						foreach($imagesData as $img){
							$template .= "<div class='eralha-gallery-post-item'>";
								$template .= "<a href='".$pluginDir."uploads/".$img["vchImageName"]."'>";
									$template .= "<img src='".$pluginDir."uploads/".$img["vchImageName"]."' />";
								$template .= "</a>";
							$template .= "</div>";
						}
					$template .= "</div>";
					$template .= "
						<script>
							(function($) {
								$(document).ready( function() {
									$('#eralha-gallery-".$id."-container a').lightBox(); 
									$('#eralha-gallery-".$id."-container').isotope(); 
								});
							})(jQuery);
						</script>
					";

				$content = str_replace("[eralha-gallery id:".$id."]", $template, $content);
			}

			return $content;
		}
	}
}
if (class_exists("eralha_galeria")) {
	$eralha_galeria_obj = new eralha_galeria();
}

//Actions and Filters
if (isset($eralha_galeria_obj)) {
	//VARS

	//Actions
		register_activation_hook(__FILE__, array($eralha_galeria_obj, 'activationHandler'));
		register_deactivation_hook(__FILE__, array($eralha_galeria_obj, 'deactivationHandler'));
		add_action('admin_menu', 'eralha_gallery_admin_initialize');
		add_action('plugins_loaded', array($eralha_galeria_obj, 'init'));
		add_action('wp_ajax_get_gallery_images_ajax', array($eralha_galeria_obj, 'getAjaxGalleryImages'));

	//Filters
		//Search the content for galery matches
		add_filter('the_content', array($eralha_galeria_obj, 'addContent'));
	
	//ADD SCRIPTS ACTIONS
		add_action('wp_enqueue_scripts', 'eralha_gallery_enqueue_script');
		add_action('admin_enqueue_scripts', 'eralha_gallery_enqueue_script');

}

//ENQUE SCRIPTS
if (!function_exists("eralha_gallery_enqueue_script")) {
	function eralha_gallery_enqueue_script(){
		$plugindir = plugin_dir_url( __FILE__ );

    	//ADD SCRIPTS
    		wp_register_script('eralha_galery_scripts', $plugindir.'js/swfobject/swfobject.js');
	    	wp_enqueue_script('eralha_galery_scripts');
	    	wp_register_script('eralha_galery_light_box', $plugindir.'js/jquery.lightbox-0.5.min.js');
	    	wp_enqueue_script('eralha_galery_light_box');

	   //ADD STYLES
	   		wp_register_style('eralha_galery_styles', $plugindir."css/styles.css");
	   		wp_register_style('eralha_galery_light_box', $plugindir."css/jquery.lightbox-0.5.css");
	    	wp_enqueue_style( 'eralha_galery_styles');
	    	wp_enqueue_style( 'eralha_galery_light_box');
	}
}

//Initialize the admin panel
if (!function_exists("eralha_gallery_admin_initialize")) {
	function eralha_gallery_admin_initialize() {
		global $eralha_galeria_obj;
		if (!isset($eralha_galeria_obj)) {
			return;
		}
		if ( function_exists('add_submenu_page') ){
			//ADDS A LINK TO TO A SPECIFIC ADMIN PAGE
			add_menu_page('Eralha Gallery', 'Eralha Gallery', 'publish_posts', 'gallery-screen', array($eralha_galeria_obj, 'printAdminPage'));
				add_submenu_page('gallery-screen', 'Gallery List', 'Gallery List', 'publish_posts', 'gallery-screen', array($eralha_galeria_obj, 'printAdminPage'));
				add_submenu_page('gallery-screen', 'Create Gallery', 'Create Gallery', 'publish_posts', 'insert-screen', array($eralha_galeria_obj, 'printAdminPage'));
		}
	}
}
?>