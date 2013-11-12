<table class="data" border="0" cellspacing="0" cellpadding="5">
	<tr>
		<th>Date</th>
<?php
	foreach($headers as $h)
	{ 
		$total[$h['id']]['talk'] = 0;
		$total[$h['id']]['comment'] = 0;
		
		?>
		<th id="tc_<?=$h['id'];?>"><?=$h['subject'];?><br/>T/C</th>
		<th id="tt_<?=$h['id'];?>"><?=$h['subject'];?><br/>Total</th>
	<?php }
?>
	</tr>
<?php
	for($i=1;$i<=$days;$i++)
	{ ?>
	<tr>
		<td><?=date('jM',mktime(0,0,0,$month,$i,$year));?></td>
	<?php
		foreach($headers as $k=>$h)
		{ 
			$td_class = $k%2?"even":"odd";
			
			$post = 0;
			$comment = 0;
			$talk = 0;
			
			// prepare date to match with db result
			if($month<10) $date_month = '0'.$month;
			else $date_month = $month;

			if($i<10) $date_day = '0'.$i;
			else $date_day = $i;

			$date = $year.'-'.$date_month.'-'.$date_day;
			
			// TALK : find count value only if array is not empty
			if(count($subject[$h['id']]['talk']))
			{
				// TALK : traverse match date to get count from result
				foreach($subject[$h['id']]['talk'] as $s)
				{
					if($s->date == $date)
					{
						$talk = $s->count;
						break;
					}
				}
			}
			
			// Comment : find count value only if array is not empty
/*			if(count($subject[$h['id']]['comment']))
			{
				// Comment : traverse match date to get count from result
				foreach($subject[$h['id']]['comment'] as $s)
				{
					if($s->date == $date)
					{
						$comment = $s->count;
						break;
					}
				}
			}
*/			
			// total 
			$total[$h['id']]['talk'] += $talk;
//			$total[$h['id']]['comment'] += $comment;

			?>
			<td class="<?=$td_class?>" id="tc_<?=$h['id'];?>"><?=$talk;?></td>
			<td class="<?=$td_class?>" id="tt_<?=$h['id'];?>"><?=$total[$h['id']]['talk'];?></td>
		<?php }
	?>
	</tr>		
	<?php }
?>

</table>