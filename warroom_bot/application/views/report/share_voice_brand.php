<?php
	foreach($subjects as $s)
	{
		?>
		<table class="data" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<th>Products</th>
				<th><?=$s['subject'];?></th>
			</tr>
		<?php
			$t_count = 0;
			foreach($data[$s['id']] as $d){ $t_count += $d['count']; }
			
			foreach($data[$s['id']] as $k=>$d)
			{ 
				$td_class = $k%2?"even":"odd";
				?>
			<tr>
				<td class="<?=$td_class;?>"><?=$d['subject'];?></td>
				<td class="<?=$td_class;?>"><?php printf("%10.2f",$d['count']*100/$t_count);?>%</td>
			</tr>
			<?php }
		?>	
			<tr>
				<th>Total</td>
				<th><?=$t_count;?> messages</td>
			</tr>
		</table>
		<p></p>
	<?php 
}
?>
