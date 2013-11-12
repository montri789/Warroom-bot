<?php
	foreach($subjects as $s)
	{ 
//		var_dump($data[$s['id']]);
		?>
		<table class="data" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<th colspan=<?=count($competitor[$s['id']])+2;?>><?=$s['subject'];?></th>
			</tr>
			<tr>
				<th>Date</th>
				<th><?=$s['subject'];?></th>
			<?php
				foreach($competitor[$s['id']] as $c)
				{ ?>
					<th><?=$c['subject'];?></th>
				<?php }
			?>
	<?php
		for($i=1;$i<=$days;$i++)
		{ 
			$td_class = $i%2?"even":"odd";
			
			// prepare date to match with db result
			if($month<10) $date_month = '0'.$month;
			else $date_month = $month;

			if($i<10) $date_day = '0'.$i;
			else $date_day = $i;

			$date = $year.'-'.$date_month.'-'.$date_day;
			
			?>
				<tr>
					<td class="<?=$td_class?>"><?=date('j-M',mktime(0,0,0,$month,$i,$year));?></td>
					<td class="<?=$td_class?>"><?php
						$talk = 0;
						foreach($data[$s['id']] as $d)
						{
							if($d['subject_id'] == $s['id'] && $d['date'] == $date)
							{
								$talk = $d['count'];
								break;
							}
						}
						echo $talk;
					?></td>
					<?php
						foreach($competitor[$s['id']] as $c)
						{ ?>
							<td class="<?=$td_class?>"><?php
								$talk = 0;
								foreach($data[$s['id']] as $d)
								{
									if($d['subject_id'] == $c['id'] && $d['date'] == $date)
									{
										$talk = $d['count'];
										break;
									}
								}
								echo $talk;
							?></td>
						<?php }
					?>
				</tr>
		<?php }
	?>
		</table>
		<p></p>		
	<?php }
?>
