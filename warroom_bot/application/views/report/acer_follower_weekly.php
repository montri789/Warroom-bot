<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
	      google.setOnLoadCallback(drawChart);
	      function drawChart() {
	        var data = google.visualization.arrayToDataTable([
	          ['Day', 'Likes'],
			  <?PHP foreach($acer_follower as $p){ ?>
			  ['<?=$p["date"];?>',<?=$p["follower"];?>],
			  <?PHP } ?>
	          ]);

	        var options = {
	          title: 'Acer Follower Twitter',
			  pointSize: 5,
	        };

	        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
	        chart.draw(data, options);
	      }
    </script>

<style>
#chart_div{
	float:left;
}
 table{	
float:left;
margin-top:47px;
 }
</style>

<div id="chart_div" style="width: 900px; height: 500px;"></div>
  

<table class="data" border="0" cellspacing="0" cellpadding="5">
<thead>
	<tr>
		<th align="center" width="100px">Date</th>
		<th align="center" width="200px">Twitter Follower</th>
	</tr>
</thead>
<tbody>
	<?PHP
		foreach($acer_follower as $w){
	?>
			<tr>
			<td align="center"><?=$w["date"];?></td>
			<td align="center"><?=$w["follower"];?></td>
			</tr>
	<?PHP
		}
	?>
</tbody>
</table>
<br clear="all"/>
