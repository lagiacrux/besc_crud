

		<?php if(!$ajax):?>			<link rel="stylesheet" type="text/css" href="<?=site_url("items/besc_crud/css/fonts.css"); ?>">			<link rel="stylesheet" type="text/css" href="<?=site_url("items/besc_crud/css/besc_crud.css"); ?>">			<link rel="stylesheet" type="text/css" href="<?=site_url("items/besc_crud/css/lightbox.css"); ?>">						<script type="text/javascript" src="<?=site_url("items/besc_crud/js/jquery-1.11.2.min.js"); ?>"></script>			<script type="text/javascript" src="<?=site_url("items/besc_crud/js/lightbox.min.js"); ?>"></script>			<script type="text/javascript" src="<?=site_url("items/besc_crud/js/besc_crud.js"); ?>"></script>			<script type="text/javascript" src="<?=site_url("items/besc_crud/js/besc_crud_list.js"); ?>"></script>			<script>				<?php foreach($bc_urls as $key => $value):?>					var <?= $key?> = "<?= $value?>";				<?php endforeach;?>

				var bc_paging_active = <?= $paging['currentPage']?>;			</script>			<div class="bc_title"><?= $title ?></div>				<div class="bc_table_actions">				<?php if($allow_add): ?>					<span class="bc_table_action"> 						<a href="<?= current_url() . '/add'?>"> 							<img class="bc_table_action_icon" src="<?= site_url('items/besc_crud/img/add.png')?>" title="Add new <?= $title?>" /> Add <?= $title?>						</a>					</span>				<?php endif; ?>				<?php foreach($custom_action as $action): ?>					<span class="bc_table_action"> 						<a href="<? echo $action['url']; if($action['add_pk']) echo '/' . $row['pk'];?>">							<img class="bc_table_action_icon" src="<?= $action['icon']?>" title="<?= $action['name']?>" /> <?= $action['name']?>						</a>					</span>				<?php endforeach;?>			</div>

            <?= $paging_and_filtering?>
					<?php endif;?>
		
		<table class="bc_table">			<thead>				<th style="width: <?= (count($custom_button) + 2) * 20?>px;"></th>				<?php foreach($headers as $header): ?>					<th><?= $header?></th>				<?php endforeach; ?>			</thead>			<tbody>				<?php $i = 0; foreach($rows as $row): ?>				<tr <?php if($i %2 == 1):?> class="bc_erow" <?php endif;?>>					<td class="bc_row_actions_container" style="width: <?= (count($custom_button) + 2) * 20?>px;">						
						<?php if($allow_delete):?>
						<img class="bc_row_action delete" src="<?= site_url('items/besc_crud/img/delete.png')?>" row_id="<?= $row['pk']?>" />
						<?php endif;?>
						
						<?php if($allow_edit):?> 
                        <a href="<?= current_url() . '/edit/' . $row['pk']?>">                            <img class="bc_row_action edit"	src="<?= site_url('items/besc_crud/img/edit.png')?>" />						</a>
						<?php endif;?>						<?php foreach($custom_button as $button): ?>							<a href="<?php echo $button['url']; if($button['add_pk']) echo '/' . $row['pk'];?>">								<img class="bc_row_action" src="<?= $button['icon']?>" title="<?= $button['name']?>" />							</a>						<?php endforeach;?>					</td>
					<?php foreach($row as $key => $value): ?>						<?php if($key != 'pk'): ?>							<?= $value?>						<?php endif;?>					<?php endforeach; ?>				</tr>
				<?php $i++; endforeach; ?>			</tbody>		</table>		<?php if(!$ajax):?>			<div class="bc_fade"></div>						<div class="bc_delete_dialog">				Are you sure you want to delete this record?				<div class="bc_delete_button bc_delete_ok">OK</div>				<div class="bc_delete_button bc_delete_cancel">Cancel</div>			</div>		<?php endif;?>

		