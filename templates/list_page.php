<?php
	$gallerysDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_galerias ORDER BY idGaleria DESC", $table_galerias), ARRAY_A);
?>

<div class=wrap>

	<h2>Lista de galerias</h2>
	<h3>Envie imagens para as galerias, apague ou edite galerias.</h3>

	<div class="eralhaGalleryListagemContainer">
		<div class="eglcTop clearfix">
			<div class="eglctName">Nome</div>
			<div class="eglctDescription">Descrição</div>
			<div class="eglctEditLink">Opções</div>
		</div>
		<?php foreach($gallerysDataSet as $data){?>
			<div class="eralhaGalleryListItem clearfix">
				<div class="egliName">
					<a href="admin.php?page=gallery-screen&id=<?php echo $data["idGaleria"];?>&handler=upload"><?php echo $data["vchGalleryName"];?></a><br />
					<b>Código:</b> [eralha-gallery id:<?php echo $data["idGaleria"];?> nr_thumbs:6]
				</div>
				<div class="egliDescription"><?php echo $data["vchGalleryDescription"];?></div>
				<div class="egliEditLink">
					<a href="admin.php?page=gallery-screen&id=<?php echo $data["idGaleria"];?>&handler=upload">envio de imagens</a> |
					<a href="admin.php?page=gallery-screen&id=<?php echo $data["idGaleria"];?>&handler=delete-gallery">apagar galeria</a>
				</div>
			</div>
		<?php }?>
	</div>

</div>
