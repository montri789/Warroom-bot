<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th width="120px">Date</th>
		<th width="400px">Topic</th>
		<th>Link</th>
		<th>Likes</th>
		<th>Action</th>
		<th>Status</th>
		<th width="400px">Details</th>
	</tr>
</thead>
<tbody>
	<?php
		foreach($acer_report_op as $w)
		{
			$linkfb = explode("_",$w->link);
			$url = 'http://www.facebook.com/'.$linkfb[0].'/posts/'.$linkfb[1];
	?>
	<tr>
	<td><?=$w->date?></td>
	<td><?=urldecode($w->topic)?></td>
	<td><a target="_blank" href="<?=$url;?>"><?=$url;?></a></td>
	<td><?=$w->likes?></td>
	<td>--</td>
	<td>--</td>
	<td><?=urldecode($w->topic)?></td>
	</tr>
	<?PHP 		
		}
	?>
</tbody>
</table>
