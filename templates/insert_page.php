<?php 
	$this->checkPostGallery();
?>
<div class=wrap>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<h2>Eralha Insert Gallery Screen</h2>

		<div>
			<h3>Gallery Name</h3>
			<label for="devloungeHeader_yes">
				<input type="text" id="galleryName" name="galleryName" />
			</label>
		</div>

		<div>
			<h3>Gallery Description</h3>
			<textarea name="galleryDescription" id="galleryDescription" style="width: 80%; height: 100px;"></textarea>
		</div>

		<div class="submit"><input type="submit" name="update_gallery" id="update_gallery" value="<?php _e('Insert Gallery', $this->optionsName) ?>" /></div>
	</form>
</div>