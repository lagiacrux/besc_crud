

        <div class="bc_paging_and_filtering">
            <div class="bc_filtering">
                <?= $filtering?>
                <?php if($filtering != ''):?>
                    <div class="bc_filter_search"></div>
                    <div class="bc_filter_reset"></div>
                <?php endif;?>
            </div>
            <div class="bc_paging">
                <?php if($paging['currentPage'] < $paging['totalPages']-1):?><div class="bc_paging_button bc_paging_last" target=<?= $paging['totalPages'] - 1?>></div><?php endif;?>
                <?php if($paging['currentPage'] < $paging['totalPages']-1):?><div class="bc_paging_button bc_paging_next" target="<?= $paging['currentPage']+1?>"></div><?php endif;?>
                <div class="bc_paging_sites">
                    <?php for($i = $paging['list_start'] ; $i <= $paging['list_end'] ; $i++):?>
                        <div class="bc_paging_page <?php if($i == $paging['currentPage']):?>bc_current_page<?php endif;?>" page="<?= $i?>"><?= $i + 1 ?></div>
                    <?php endfor;?>
                </div>
                <?php if($paging['currentPage'] != 0):?><div class="bc_paging_button bc_paging_prev" target=<?= $paging['currentPage'] -1?>></div><?php endif;?>
                <?php if($paging['currentPage'] != 0):?><div class="bc_paging_button bc_paging_first" target=<?= 0?>></div><?php endif;?>
            </div>
        </div>
        