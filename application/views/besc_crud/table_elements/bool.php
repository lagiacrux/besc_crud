

			<td>
				<? foreach($options as $option): ?>
					<? if($option['key'] == $value): ?>
						<?= $option['value'] ?>
					<? endif; ?>
				<? endforeach; ?>
			</td>