<div class="bc_column <? if(($num_row) %2 == 0): ?>erow<?endif;?>">	<p class="bc_column_header"><?= $display_as?>:</p>
					<? if(isset($col_info) && $col_info != ""):?><p class="column_info">		<i><?= $col_info ?></i>	</p><? endif; ?>
					<div class="bc_column_input bc_col_select">		<select name="col_<?= $db_name?>">
							<? foreach($options as $option): ?>
								<option value="<?= $option['key']?>"				<? if(isset($value) && $value == $option['key']) echo "SELECTED";?>><?= $option['value']?></option>
							<? endforeach; ?>
						</select>	</div></div>