<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
	      google.setOnLoadCallback(drawChart);
	      function drawChart() {
	        var data = google.visualization.arrayToDataTable([
	          ['Month', 'Likes'],
			  <?PHP foreach($acer_pagelike as $p){ $date = explode("-",$p["date"]); $date = $date[2]."-".$date[1]."-".$date[0];  ?>
			  ['<?=$date;?>', <?=$p["likes"];?>],
			  <?PHP } ?>
	          ]);

	        var options = {
	          title: 'Acer Page Likes Facebook',
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
		<th width="100px" align="center">Date</th>
		<th width="100px" align="center">Facebook</th>
		<th width="100px" align="center">Increase</th>
	</tr>
</thead>
<tbody>
	<?PHP
		foreach($acer_pagelike as $w){
		
		$date = explode("-",$w["date"]); $date = $date[2]."-".$date[1]."-".$date[0]; 
	?>
			<tr>
			<td align="center"><?=$date;?></td>
			<td align="center"><?=$w["likes"]?></td>
			<td align="center"><?=$w["increase"];?></td>
			</tr>
	<?PHP
		}
	?>
</tbody>
</table>
<br clear="all"/>
