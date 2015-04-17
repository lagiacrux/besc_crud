<div class="bc_column <? if(($num_row) %2 == 0): ?>erow<?endif;?>">	<p class="bc_column_header"><?= $display_as?>:</p>
					<? if(isset($col_info) && $col_info != ""):?><p		class="bc_column_info">		<i><?= $col_info ?></i>	</p><? endif; ?>
					<div class="bc_column_input bc_col_m_n"		m_n_relation_id="<?= $relation_id?>">		<div			class="bc_m_n_selected <? if(($num_row) %2 == 1): ?>erow<?endif;?>">
							<?php foreach($selected->result() as $sel):?>
								<span class="bc_m_n_element bc_m_n_element_edit bc_m_n_sel"				n_id="<?= $sel->$table_mn_col_n?>"><?= $sel->$table_n_value?></span>
							<?php endforeach;?>
						</div>		<div class="bc_m_n_avail <? if(($num_row) %2 == 1): ?>erow<?endif;?>">
							<?php foreach($avail->result() as $av):?>
								<span class="bc_m_n_element bc_m_n_element_edit bc_m_n_av"				n_id="<?= $av->$table_n_pk?>"><?= $av->$table_n_value?></span>
							<?php endforeach;?>
						</div>	</div></div>