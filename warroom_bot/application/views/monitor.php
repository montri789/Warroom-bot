<html>
<head>
<title>Thoth Monitor</title>
<style type="text/css" media="screen">
	.error {
		color: red;
	}
</style>
</head>
<body>
	<h1>Thoth Monitor</h1>
	<?php foreach ($results as $result):?>
		<?php if ($result['error']):?>
			<div class="error">
				<h2><?php echo $result['name']?></h2>
				<div>ERROR: Count is less than threshold</div>
		<?php else:?>
			<div>
				<h2><?php echo $result['name']?></h2>
		<?php endif;?>
				<ul>
					<li>Pattern: <?php echo $result['pattern']?></li>
					<li>Count/Threshold: <?php echo $result['count'];?>/<?php echo $result['threshold'];?></li>
					<li>Response: <pre><?php echo $result['response'];?></pre></li>
				</ul>
			</div>
	<?php endforeach;?>
</body>
</html>