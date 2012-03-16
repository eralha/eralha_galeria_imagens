<?php
	if(current_user_can('administrator') || current_user_can('editor')){
		$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_galerias." WHERE idGaleria = '".$_GET["id"]."' ORDER BY idGaleria DESC"), ARRAY_A);
	}else{
		$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_galerias." WHERE idGaleria = '".$_GET["id"]."' AND iUserId = '".$user_ID."' ORDER BY idGaleria DESC"), ARRAY_A);
	}

	foreach($gallerysDataSet as $data){
?>
	<div class=wrap>
		
		<h2>Upload photos for: <?php echo $data["vchGalleryName"];?></h2>

		<h3>Gallery Description:</h3>
		<p><?php echo $data["vchGalleryDescription"];?></p>
		<p><b>To add this gallery to a post inset this tag in it:</b> [eralha-gallery id:<?php echo $data["idGaleria"];?>]</p>

		<script>
			function deleteImage(idObject, imageID, token){
				var data = {
					action: 'get_gallery_images_ajax',
					imageID: imageID,
					idObject: idObject,
					token: token,
					handler: "delete-image"
				};
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, getImagesAjaxComplete);
			}
			function onFileUploadComplete(idObject){
				var data = {
					action: 'get_gallery_images_ajax',
					idObject: idObject
				};
				//SETTING UP UPLOAD SCREEN;
					jQuery("#imageList").html("Reading Images");
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, getImagesAjaxComplete);
			}
			function getImagesAjaxComplete(response){
				jQuery("#imageList").html(response);
			}
		</script>

		<h3>To upload files for "<?php echo $data["vchGalleryName"];?>" press the "browse button".</h3>

		<div id="fileUploadField"></div>
		<div class="imageListContainer" id="imageList">
			<script>onFileUploadComplete(<?php echo $data["idGaleria"];?>);</script>
		</div>

		<script>
			var so = new SWFObject('<?php echo $pluginDir; ?>flash/fileupload.swf?v=22', 'fileupload', '505', '22', '8', '#000000', 'best');
			so.addParam("FlashVars", "onComplete=onFileUploadComplete&componentScreen=gallery-screen&idObject=<?php echo $data["idGaleria"];?>&action=upload-file&onlyImages=true");
			so.addParam("scale", "noscale");
			so.addParam("wmode", "transparent");
			so.write('fileUploadField');
		</script>

	</div>
<?php }?>