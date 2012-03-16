<?php
	if(current_user_can('administrator') || current_user_can('editor')){
		$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_galerias." ORDER BY idGaleria DESC"), ARRAY_A);
	}else{
		$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_galerias." WHERE iUserId = '".$user_ID."' ORDER BY idGaleria DESC"), ARRAY_A);
	}
?>

<div class=wrap>

	<h2>Gallery List</h2>
	<h3>Upload photos to your gallerys, delete your gallerys or manage them as you whant enjoy!</h3>

	<div class="eralhaGalleryListagemContainer">
		<div class="eglcTop clearfix">
			<div class="eglctName">Name</div>
			<div class="eglctDescription">Description</div>
			<div class="eglctEditLink">Actions</div>
		</div>
		<?php foreach($gallerysDataSet as $data){
				$user = new WP_User($data["iUserId"]);
			?>
			<div class="eralhaGalleryListItem clearfix">
				<div class="egliName">
					<a href="admin.php?page=gallery-screen&id=<?php echo $data["idGaleria"];?>&handler=upload">
						<?php echo $data["vchGalleryName"];?> By: <?php echo $user->display_name;?>
					</a><br />
					<b>Tag:</b> [eralha-gallery id:<?php echo $data["idGaleria"];?>]
				</div>
				<div class="egliDescription"><?php echo $data["vchGalleryDescription"];?></div>
				<div class="egliEditLink">
					<a href="admin.php?page=gallery-screen&id=<?php echo $data["idGaleria"];?>&handler=upload">upload files</a> | 
					<a href="admin.php?page=gallery-screen&id=<?php echo $data["idGaleria"];?>&handler=delete-gallery">delete gallery</a>
				</div>
			</div>
		<?php }?>
	</div>

</div>