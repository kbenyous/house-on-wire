<html>
	<head>
		<title>House-On-Wire - Module Teleinfo</title>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.js"></script>
		<script src="http://code.highcharts.com/stock/highstock.js"></script>
		<script src="http://code.highcharts.com/stock/modules/exporting.js"></script>

		<?php include 'includes/highcharts_translation.php' ?>
		<?php include 'includes/highcharts_theme_gray.php' ?>
        <?php include 'includes/graphe_papp.php' ?>
		<?php include 'includes/graphe_live.php' ?>

	</head>

<body bgcolor="black" >
    <button id="zoomOut">Dezoomer axe Y</button>
    <div id="graphe_papp" style="height: 600px"></div>
    <div id="graphe_live" style="height: 300px"></div>
</body>
</html>
