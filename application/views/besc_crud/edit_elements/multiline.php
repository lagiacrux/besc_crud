<div class="bc_column <? if(($num_row) %2 == 0): ?>erow<?endif;?>">	<p class="bc_column_header"><?= $display_as?>:</p>
					<? if(isset($col_info) && $col_info != ""):?><p		class="bc_column_info">		<i><?= $col_info ?></i>	</p><? endif; ?>
					<div class="bc_column_input bc_col_multiline">
						<? if(count($formatting) > 0):?>
							<div class="bc_col_multiline_formatting">
								<? foreach($formatting as $button):?>
									<div class="bc_col_multiline_formatting_button"				tag="<?= $button?>"><?= strtoupper($button) ?></div>
								<? endforeach; ?>
							</div>
						<? endif; ?>
						<textarea 	name="col_<?= $db_name?>" 
									style="<?if(isset($height)) echo 'height: ' . $height . 'px;';?> <?if(isset($width)) echo 'width: ' . $width . 'px;';?>"><? if(isset($value)) echo $value;?></textarea>	</div></div>