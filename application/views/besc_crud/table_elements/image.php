<td>
				<? if($filename != "" && $filename !== null): ?>
					<a href="<?= $uploadpath  . '/' . $filename?>"	data-lightbox="<?= $filename?>"> <img class="table_image_preview"		src="<?= $uploadpath  . '/' . $filename?>" /></a>
				<? endif; ?>
			</td>