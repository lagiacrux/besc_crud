<div class="bc_column <?php if(($num_row) %2 == 0): ?>erow<?php endif;?>">	<p class="bc_column_header"><?= $display_as?>:</p>	<?php if(isset($col_info) && $col_info != ""):?>        <p class="column_info">            <i><?= $col_info ?></i>        </p>    <?php endif; ?>
	<div class="bc_column_input bc_col_image">	<?php if(!isset($value) || $value == ""): ?>		<input type="hidden" name="col_<?= $db_name ?>" class="col_fname" value="" />        <input type="file" name="col_<?= $db_name ?>_file"			class="bc_col_image_file" id="col_<?= $db_name ?>_file"			<?php if(isset($accept)): ?> accept="<?= $accept?>" <?php endif;?> 			uploadpath="<?= $uploadpath?>" />			 		<input type="button" class="col_image_upload_btn" value="Upload" /> 		<a href="" data-lightbox="<?= $db_name ?>">            <img class="bc_col_image_preview" src="" />		</a> 		<span class="bc_col_image_delete">Delete</span>	<?php else: ?>		<input type="hidden" name="col_<?= $db_name ?>"			class="bc_col_fname" value="<?= $value?>" /> <input type="file"			name="col_<?= $db_name ?>_file" class="bc_col_image_file"			id="col_<?= $db_name ?>_file" <?php if(isset($accept)): ?>			accept="<?= $accept?>" <?php endif;?> uploadpath="<?= $uploadpath?>" />		<input type="button" class="bc_col_image_upload_btn" value="Upload"	style="display: none;" /> 		<a href="<?= site_url($uploadpath . $value)?>" data-lightbox="<?= $db_name ?>">            <img class="bc_col_image_preview" style="display: block;" src="<?= site_url($uploadpath . $value)?>?>" />        </a>		<span class="bc_col_image_delete" style="display: block;">Delete</span>	<?php endif; ?>	</div></div>