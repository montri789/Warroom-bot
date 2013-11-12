<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th>Web/Blog Category List</th>
		<?PHP foreach($subjects as $val){ ?>
		<th colspan="2"><?=$val['subject'];?></th>
		<?PHP } ?>
	</tr>
</thead>
<tbody>
	<?PHP foreach($data as $val){ ?>
	<?PHP
		$vneg = array();
		$neg = array();
		$neu = array();
		$pos  = array();
		$vpos = array();
		$total = array();
		
		foreach($val["row"] as $key => $r){
			$$key = $r;
		}
	?>
	<tr>
		<td><b><?=$val["name"];?></b></td>
		<?PHP foreach($subjects as $p){ ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<?PHP } ?>	
	</tr>
	<tr>
		<td>Very Positive</td>
		<?PHP  $i = 0;  foreach($vpos  as $p){ ?>
		<td><?=$p?></td>
		<td><?PHP echo ($total[$i] == 0 )? "0" : sprintf("%10.2f",($p*100/$total[$i])) ; ?>%</td>
		<?PHP $i++; } ?>
	</tr>
	<tr>
		<td>Positive</td>
		<?PHP $i = 0; foreach($pos  as $p){ ?>
		<td><?=$p?></td>
		<td><?PHP echo ($total[$i] == 0 )? "0" : sprintf("%10.2f",($p*100/$total[$i])) ; ?>%</td>
		<?PHP $i++;  } ?>
	</tr>
	<tr>
		<td>Neutral</td>
		<?PHP $i = 0; foreach($neu as $p){ ?>
		<td><?=$p?></td>
		<td><?PHP echo ($total[$i] == 0 )? "0" : sprintf("%10.2f",($p*100/$total[$i])) ; ?>%</td>
		<?PHP $i++;  } ?>
	</tr>
	<tr>
		<td>Negative</td>
		<?PHP $i = 0; foreach($neg as $p){ ?>
		<td><?=$p?></td>
		<td><?PHP echo ($total[$i] == 0 )? "0" : sprintf("%10.2f",($p*100/$total[$i])) ; ?>%</td>
		<?PHP $i++;  } ?>
	</tr>
	<tr>
		<td>Very Negative</td>
		<?PHP $i = 0; foreach($vneg as $p){ ?>
		<td><?=$p?></td>
		<td><?PHP echo ($total[$i] == 0 )? "0" : sprintf("%10.2f",($p*100/$total[$i])) ; ?>%</td>
		<?PHP $i++; } ?>
	</tr>
	<tr>
		<td class="even"><b>Total Message (<?=$val["name"];?>)</b></td>
		<?PHP foreach($total as $p){ ?>
		<td class="even"><b><?=$p?></b></td>
		<td class="even"><b><?PHP echo ($p == 0 )? "0" : "100" ; ?>%</b></td>
		<?PHP } ?>
	</tr>
	<?PHP } ?>
</tbody>
</table>