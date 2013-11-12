<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th width="100px">Count</th>
		<th width="150px">Username</th>
		<th>Links</th>
	</tr>
</thead>
<tbody>
	<?php
		foreach($acer_facebook_top_post as $w)
		{
			$url = 'http://www.facebook.com/profile.php?id='.$w->facebook_id;
	?>
	<tr>
	<td><?=$w->count?></td>
	<td><a target="_blank" href="<?=$url;?>"><?=$w->username;?></a></td>
	<td><?php
		foreach($acer_facebook_top_post_list as $row)
		{
			if($row->facebook_id != $w->facebook_id) continue;
			$linkfb = explode("_",$row->link);
			$url = 'http://www.facebook.com/'.$linkfb[0].'/posts/'.$linkfb[1];
			?>
			[<?=$row->post_date;?>] [<?=substr($row->type,3,strlen($row->type));?>] <a target="_blank" href="<?=$url;?>"><?=mb_strlen($row->body)>70?mb_substr($row->body,0,70).'...':$row->body;?></a><br />
			<?php
		}
	?></td>
	</tr>
	<?PHP 		
		}
	?>
</tbody>
</table>
