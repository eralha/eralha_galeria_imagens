<?php
	$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_galerias." WHERE idGaleria = %d", $_GET["id"]), ARRAY_A);

	foreach($gallerysDataSet as $data){
?>
	<div class=wrap>

		<h2>Envio de imagens para: <?php echo $data["vchGalleryName"];?></h2>

		<h3>Descrição da galeria:</h3>
		<p><?php echo $data["vchGalleryDescription"];?></p>
		<p><b>Para adicionar esta galeria a uma página ou post cole o seguinte código no corpo de texto da página ou post:</b> [eralha-gallery id:<?php echo $data["idGaleria"];?> nr_thumbs:4]</p>
		<p>Pode mudar o numero de thumbnails que aparece na galeria alterando o valor, nr_thumbs:X onde X será o valor que deseja.</p>

		<script>
			function deleteImage(idObject, imageID){
				var data = {
					action: 'get_gallery_images_ajax',
					imageID: imageID,
					idObject: idObject,
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

		<h3>Para enviar imagens para "<?php echo $data["vchGalleryName"];?>" carregue no botão "browse button".</h3>

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
