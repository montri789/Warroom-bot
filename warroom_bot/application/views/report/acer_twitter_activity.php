<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th width="120px">Date</th>
		<th width="400px">Topic</th>
		<th width="100px">Type</th>
		<th width="100px">Reach</th>
		<th width="250px">Link</th>
	</tr>
</thead>
<tbody>
	<?php
		foreach($acer_twitter_activity as $w)
		{
			$url = 'https://twitter.com/acerthailand/status/'.$w->tweet_id;
//			$thumb_url = 'http://api.thumbalizr.com/?url='.$url.'&api_key=86d1fb0fc1ffcb52b901f77ddf89317b&width=1024';
	?>
		<tr>
		<td><?=$w->date?></td>
		<td><?=urldecode($w->body)?></td>
		<td></td>
		<td><?=is_null($w->reach)?'-':$w->reach;?></td>
		<td><a target="_blank" href="<?=$url;?>"><?=$url;?></a></td>
	</tr>
	<?PHP 		
	}
	?>
</tbody>
</table>
