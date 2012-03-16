<?php

	/*
		//THIS ONLY SHOWS IMAGES THAT YOU OWN ADMIN AND EDITOR CAN VIEW ALL
		if(current_user_can('administrator') || current_user_can('editor')){
			$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = '".$_POST["idObject"]."' ORDER BY idImagem DESC"), ARRAY_A);
		}else{
			$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = '".$_POST["idObject"]."' AND iUserId = '".$user_ID."' ORDER BY idImagem DESC"), ARRAY_A);
		}
	*/
	
	if(current_user_can('administrator') || current_user_can('editor')){
		$galleryData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_galerias." WHERE idGaleria = '".$_POST["idObject"]."' ORDER BY idGaleria DESC"), ARRAY_A);
	}else{
		$galleryData = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM ".$table_galerias." WHERE idGaleria = '".$_POST["idObject"]."' AND iUserId = '".$user_ID."' ORDER BY idGaleria DESC"), ARRAY_A);
	}

	$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = '".$_POST["idObject"]."' ORDER BY idImagem DESC"), ARRAY_A);

	foreach($galleryData as $gall){
	foreach($imagesData as $data){
	?>
		<div class="imgListItem">
			<a href="<?php echo $pluginDir."uploads/".$data["vchImageName"];?>" target="_blank">
				<img src="<?php echo $pluginDir."uploads/".$data["vchImageName"];?>" />
				<?php if($data["iUserId"] == $user_ID || (current_user_can('administrator') || current_user_can('editor'))){?>
					<a href="javascript:deleteImage(<?php echo $data["idGaleria"];?>, <?php echo $data["idImagem"];?>, '<?php echo $this->get_token($data["idImagem"]);?>')">
						delete image</a>
				<?php }?>
			</a>
		</div>
<?php }}?>