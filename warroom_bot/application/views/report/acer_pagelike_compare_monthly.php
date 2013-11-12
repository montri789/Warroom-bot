<?PHP $stringmonth = date("F"); //date("F", mktime(0, 0, 0, ($month + 1)));  ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() { 
        var data = google.visualization.arrayToDataTable([
          ['Facebook Page', 'Likes'],
          <?PHP foreach($acer_report as $val){ ?>
		  ['<?=$val->name;?>',  <?=$val->likes;?>],
		  <?PHP } ?>
        ]);

        var options = {
          title: 'Facebook Fan no <?=$stringmonth;?>',
          hAxis: {title: '', titleTextStyle: {color: 'red'}},
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
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
		<th width="30px">Rank</th>
		<th width="100px">Brand</th>
		<th width="100px">Like</th>
	</tr>
</thead>
<tbody>
	<?php
		$i=1;
		foreach($acer_report as $w){
	?>
			<tr>
			<td><?=$i?></td>
			<td><?=$w->name?></td>
			<td><?=$w->likes?></td>
			</tr>
	<?PHP
		$i++;
		}
	?>
</tbody>
</table>
<br clear="all"/>