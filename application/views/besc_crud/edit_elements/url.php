

				<div class="bc_column <? if(($num_row) %2 == 0): ?>erow<?endif;?>">
					<p class="bc_column_header"><?= $display_as?>:</p>
					<? if(isset($col_info) && $col_info != ""):?><p class="bc_column_info"><i><?= $col_info ?></i></p><? endif; ?>
					<div class="bc_column_input bc_col_url">
						<input type="text" name="col_<?= $db_name?>" value="<? if(isset($value)):?><?=$value;?><? endif;?>">
					</div>
				</div>