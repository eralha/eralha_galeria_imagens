<?php
	$imagesData = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idGaleria = %d ORDER BY idImagem DESC", $_POST["idObject"]), ARRAY_A);

	foreach($imagesData as $data){
?>
	<div class="imgListItem">
		<a href="<?php echo $pluginDir."uploads/".$data["vchImageName"];?>" target="_blank">
			<img src="<?php echo $pluginDir."uploads/".$data["vchImageName"];?>" />
			<a href="javascript:deleteImage(<?php echo $data["idGaleria"];?>, <?php echo $data["idImagem"];?>)">delete image</a>
		</a>
	</div>
<?php }?>
