<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th width="120px">Date</th>
		<th width="400px">Topic</th>
		<th width="100px">Type</th>
		<th width="100px">Like</th>
		<th width="250px">Link</th>
	</tr>
</thead>
<tbody>
	<?php
		
		foreach($acer_report as $w)
		{
			$linkfb = explode("_",$w->link);
			$url = 'http://www.facebook.com/'.$linkfb[0].'/posts/'.$linkfb[1];
	?>
		<tr>
		<td><?=$w->date?></td>
		<td><?=urldecode($w->topic)?></td>
		<td></td>
		<td><?=$w->likes?></td>
		<td><a target="_blank" href="<?=$url;?>"><?=$url;?></a></td>
	</tr>
	<?PHP 		
	}
	?>
</tbody>
</table>
