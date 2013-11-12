<?PHP foreach($data as $val){ ?>
<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th>Topics Mentioned - <br/><?=$val["subject"]?></th>
		<th>Sentiment</th>
		<th>Mood</th>
		<th>Post Date</th>
		<th>Source of Information</th>
		<th>Website</th>
		<th width="300px">Content Details</th>
		<th>Link</th>
		<th>Influencer name / Username</th>
	</tr>
</thead>
<tbody><?PHP foreach($val["post"] as $p){ ?>
	<tr>
		<td><?=$p["topic"];?></td>
		<td><?=$p["sentiment"];?></td>
		<td><?=$p["mood"];?></td>
		<td><?=$p["post_date"];?></td>
		<td><?=$p["source"];?></td>
		<td><?=$p["website"];?></td>
		<td><?=$p["content"];?></td>
		<td><a href="<?=$p["link"];?>"><?=$p["link"];?></a></td>
		<td><?=$p["influencer"];?></td>
	</tr>
	<?PHP } ?>
</tbody>
</table>
<br/>
<?PHP } ?>