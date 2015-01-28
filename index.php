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

			$wpdb->query("DROP TABLE IF EXISTS ". $table_galerias);
			$wpdb->query("DROP TABLE IF EXISTS ". $table_images);
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
					$wpdb->query($wpdb->prepare("DELETE FROM ".$table_galerias." WHERE idGaleria = '".$_GET["id"]."' "));
					$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = '".$_GET["id"]."'"), ARRAY_A);
					foreach($imagesData as $img){
						$this->deleteImage($img["idImagem"]);
					}
				}
				//GET TEMPLATE GALLERY LIST
				include "templates/list_page.php";
			}
		}

		function getAjaxGalleryImages(){
			global $wpdb;
			global $user_ID;

			$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";
			$table_images = $wpdb->prefix.$this->optionsName."_images";

			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

				if($_POST["handler"] == "delete-image"){
					$this->deleteImage($_POST["imageID"]);
				}
				include "templates/image_list_page_ajax.php";

			die(); // this is required to return a proper result

		}

		function deleteImage($imageID){
			global $wpdb;
			global $user_ID;

			$table_galerias = $wpdb->prefix.$this->optionsName."_gallery";
			$table_images = $wpdb->prefix.$this->optionsName."_images";

			$image = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idImagem = '".$imageID."'"), ARRAY_A);
			//DELETE IMAGE FROM UPLOAD FOLDER
				$uploadPath = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
				@unlink("..".$uploadPath."uploads/".$image[0]["vchImageName"]);

			//DELETE FILE FROM DATA BASE
				$wpdb->query($wpdb->prepare("DELETE FROM ".$table_images." WHERE idImagem = '".$imageID."' "));
		}

		function checkPostGallery(){
			global $wpdb;

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

			preg_match_all('(\[eralha-gallery id:([0-9]*) nr_thumbs:([0-9]*)\])', $content, $matches, PREG_PATTERN_ORDER);

			for($i=0; $i < count($matches[0]); $i++){
				$id = $matches[1][$i];
				$thumbs = $matches[2][$i];

				$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = '".$id."' ORDER BY idImagem DESC"), ARRAY_A);
				$template = "<div class='galeria clearfix gal_".$id."' id='eralha-gallery-".$id."-container'>";
					$dir = $pluginDir."uploads/";
					$dir = str_replace("http://www.maxiluz.pt", "", $dir);
					$template .= "<div class='galeria clearfix'>";
								$template .= "<div class='stage'></div>";
								$template .= "<div class='prev_page'>«</div>";
								$template .= "<div class='next_page'>»</div>";
								$template .= "<div class='thumbs clearfix'>";
							foreach($imagesData as $img){
								$template .= "<div class='thumb'><img src='".$dir.$img["vchImageName"]."' data='".$dir.$img["vchImageName"]."' /></div>";
							}
						$template .= "</div>";
							$template .= "</div>";
				$template .= "</div>";
				$template .= "<script>";
						$template .= "(function($) {";
								$template .= "$(document).ready(function() {";
							$template .= "iniGaleria('.gal_".$id."', ".$thumbs.");";
								$template .= "});";
						$template .= "})(jQuery);";
				$template .= "</script>";
				$content = str_replace("[eralha-gallery id:".$id." nr_thumbs:".$thumbs."]", $template, $content);
			}

			return $content;
		}

		function render_meta_box_content($post){
			global $wpdb;

			$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."eralha_image_gallery_gallery ORDER BY idGaleria DESC", ""), ARRAY_A);

			$options = "";
			foreach($gallerysDataSet as $data){
				$options .= "<option value='".$data["idGaleria"]."'>".$data["vchGalleryName"]."</option>";
			}
			?>
				<select id="cbo_gall_id" name="cbo_gall_id">
					<option id="">Selecione uma galeria</option>
					<?php echo $options;?>
				</select>
				Número de thumbnails que esta galeria tem: <input type="text" id="nr_thumbs" nmae="nr_thumbs" />
				<input type="button" id="add_gall" name="add_gall" value="Adicionar galeria"></button>
				<script>
					(function($) {
								$(document).ready(function() {
									$("#add_gall").click(function(){
										if($("#cbo_gall_id").val() != "" && $("#nr_thumbs").val() != ""){
											var snipet = "[eralha-gallery id:"+$("#cbo_gall_id").val()+" nr_thumbs:"+$("#nr_thumbs").val()+"]";
											tinyMCE.execCommand('mceInsertContent',false,snipet);
										}
									});
								});
						})(jQuery);
				</script>
			<?php
		}
	}
}
if (class_exists("eralha_galeria")) {
	$eralha_galeria_obj = new eralha_galeria();
}

//Actions and Filters
if (isset($eralha_galeria_obj)) {
	//VARS
		$plugindir = plugin_dir_url( __FILE__ );

	//Actions
		register_activation_hook(__FILE__, array($eralha_galeria_obj, 'activationHandler'));
		register_deactivation_hook(__FILE__, array($eralha_galeria_obj, 'deactivationHandler'));
		add_action('admin_menu', 'eralha_gallery_admin_initialize');
		add_action('plugins_loaded', array($eralha_galeria_obj, 'init'));
		add_action('wp_ajax_get_gallery_images_ajax', array($eralha_galeria_obj, 'getAjaxGalleryImages'));

	//Filters
		//Search the content for galery matches
		add_filter('the_content', array($eralha_galeria_obj, 'addContent'));

	//ADD SCRIPTS
		wp_register_script( 'eralha_galery_scripts', $plugindir.'js/swfobject/swfobject.js');
			wp_enqueue_script( 'eralha_galery_scripts' );

	//ADD STYLES
			wp_register_style('eralha_galery_styles', $plugindir."css/styles.css");
			wp_enqueue_style( 'eralha_galery_styles');
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
			add_menu_page('Galeria de Imagens', 'Galeria de Imagens', 'manage_options', 'gallery-screen', array($eralha_galeria_obj, 'printAdminPage'));
				add_submenu_page('gallery-screen', 'Lista de galerias', 'Lista de galerias', 'manage_options', 'gallery-screen', array($eralha_galeria_obj, 'printAdminPage'));
				add_submenu_page('gallery-screen', 'Criar galeria', 'Criar galeria', 'manage_options', 'insert-screen', array($eralha_galeria_obj, 'printAdminPage'));

			/* Define the custom box */
				add_action( 'add_meta_boxes', 'myplugin_add_custom_box' );

			/* Adds a box to the main column on the Post and Page edit screens */
				function myplugin_add_custom_box() {
					$eralha_galeria_obj = new eralha_galeria();
					add_meta_box('add_gall_box', __('Adicionar Galeria', 'PT' ),array($eralha_galeria_obj, 'render_meta_box_content'), 'post');
					add_meta_box('add_gall_box', __('Adicionar Galeria', 'PT' ),array($eralha_galeria_obj, 'render_meta_box_content'), 'page');
				}
		}
	}
}
?>
