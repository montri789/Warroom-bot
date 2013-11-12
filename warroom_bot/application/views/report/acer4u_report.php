<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th width="70px">Date</th>
		<th width="400px">Topic</th>
		<th width="200px">Link</th>
	</tr>
</thead>
<tbody>
	<?php
		
		foreach($acer4u_report as $w)
		{
	?>
	<tr>
		<td><?=$w->date?></td>
		<td><?=$w->topic?></td>
		<td><a href="http://www.acer4u.in.th<?=$w->link?>">http://www.acer4u.in.th<?=$w->link?></a></td>
		
	<?PHP
		}
		?>
		
	</tr>
	<?PHP 		
	?>
</tbody>
</table>
