<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th>Website</th>
		<?php
			$date_from = new Datetime($from);
			$date_to = new Datetime($to);
			
			$date = $date_from;
			while($date<=$date_to)
			{
				?>
				<th><?=$date->format('d/m');?></th>
				<?php
				$date->add(new DateInterval('P1D'));
			}
			
			unset($date_from);
			unset($date_to);
			unset($date);
		?>
	</tr>
</thead>
<tbody>
<?php
	$q_website = $this->db->get('domain');
	$website = $q_website->result();
	
	foreach($website as $k=>$w)
	{ 
		$td_class = $k%2?"even":"odd";
		?>
		<tr>
			<td class="<?=$td_class;?>"><?=$w->id?> - <?=$w->name?></td>
	<?php
		$date = new Datetime($from);
		$date_to = new Datetime($to);
		while($date<=$date_to)
		{ 
			$count = '-';
			foreach($post_count as $p)
			{
				if($p->date == $date->format('Y-m-d') && $p->domain_id == $w->id)
				{ 
					$count = $p->post_count;
					break;
				}
			}
			?>
				<td class="<?=$td_class;?>"><?=$count;?></td>
			<?php
			$date->add(new DateInterval('P1D'));
		}
		
		unset($date_to);
		unset($date);
	?>
		</tr>
	<?php }
?>
</tbody>
</table>