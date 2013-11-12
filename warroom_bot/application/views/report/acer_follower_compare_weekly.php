<?PHP $stringmonth = date("F"); //date("F", mktime(0, 0, 0, ($month + 1)));  ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() { 
        var data = google.visualization.arrayToDataTable([
          ['Twitter', 'Follower', 'Increase'],
          <?PHP foreach($acer_report as $val){ ?>
		  ['@<?=$val->username;?>',  <?=$val->follower;?>, <?=$val->diff;?>],
		  <?PHP } ?>
        ]);

        var options = {
          title: 'Twitter Compare of <?=$stringmonth;?>',
          hAxis: {title: '', titleTextStyle: {color: 'red'}},
           isStacked:true
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
		<th width="100px">Follower</th>
		<th width="100px">Increase</th>
	</tr>
</thead>
<tbody>
	<?php
		$i=1;
		foreach($acer_report as $w){
	?>
			<tr>
			<td><?=$i?></td>
			<td>@<?=$w->username?></td>
			<td><?=$w->follower?></td>
			<td><?=$w->diff?></td>
			</tr>
	<?PHP
		$i++;
		}
	?>
</tbody>
</table>
<br clear="all"/>