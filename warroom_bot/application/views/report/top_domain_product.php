<?php
	$total = array();
		?>
		<table class="data" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<th>Website</th>
			<?php
				foreach($subjects as $s)
				{ 	
					$total[$s['id']] = 0;
					?>
				<th><?=$s['subject'];?></th>
				<?php }
			?>
			</tr>
	<?php
		$td_class = null;
		
		foreach($data as $k=>$row)
		{
			$td_class = $k%2?"even":"odd";
			?>
			<tr>
				<td class="<?=$td_class;?>"><?=$row['name'];?></td>
			<?php
				foreach($subjects as $s)
				{ 
					$count = is_null($row['s'.$s['id']])?'0':$row['s'.$s['id']];
					$total[$s['id']] += $count;
					?>
				<td class="<?=$td_class;?>"><?=$count;?></td>
				<?php }
			?>
			</tr>
		<?php }
	?>
			<tr>
				<th>Total</td>
		<?php
			foreach($subjects as $s)
			{ ?>
				<th><?=$total[$s['id']];?></td>
			<?php }
		?>
			</tr>
		</table>
		<p></p>
	<?php 
	
?>