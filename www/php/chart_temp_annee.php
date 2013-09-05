
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>
    	Graphique des temp&eacute;ratures mensuelles &agrave; Rennes
    </title>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script type="text/javascript">
      google.load('visualization', '1', {packages: ['corechart']});
    </script>
    <script type="text/javascript">
    <?php
    if (isset($_GET['sonde']) && $_GET['sonde'] != '')
    {
    	$sonde = $_GET['sonde'];
    }
    else
    {
    	//on prend par defaut la sonde jardin
    	$sonde = "THGR228N.t";
    }
    
    ?>
	var getJSONandDisplay = function()
	{
		//$.getJSON("http://maison.coroller.com/php/get_data_json.php?sonde=PCR918N.0.rt&month=<? echo $_GET['month'];?>&type=rain", function(data)
		$.getJSON("http://maison.coroller.com/php/get_data_json.php?sonde=<? echo $sonde;?>&type=temp_year&date=<? echo $_GET['date'];?>", function(data)
		{
			var dataGoogle = [['Mois','Maximum','Moyenne','Minimum']];
			for(i=0;i<data.length;i++)
			{
				dataGoogle[i+1] = [data[i].Mois,parseFloat(data[i].Max),parseFloat(data[i].Moy),parseFloat(data[i].Min)];
			}

			var dataGoogleVis = new google.visualization.arrayToDataTable(dataGoogle);
			// Create and draw the visualization.
			ac = new google.visualization.LineChart(document.getElementById('visualization'));
			ac.draw(dataGoogleVis, {
				curveType: "none",
                width: 700, height: 400,
                hAxis: {title: 'Mois'},
                series: [{color: 'red'},{color: 'orange'},{color: 'blue'}],
                title: 'Temperatures mensuelles a Rennes en 2013 (sonde=<? echo $sonde;?>)',
                focusTarget: 'category',
                vAxis: { 
					baseline: 0,
					minorGridlines: { count: 1 },
					title: 'Degres'
                }
			});
		google.visualization.events.addListener(ac, 'select', selectHandler);
		
		function selectHandler() {
			var selection = ac.getSelection();
			var message = '';
			for (var i=0;i<selection.length;i++) {
				var item = selection[i];
				if (item.row != null && item.column != null) {
				} else if (item.row != null) {
					window.location = 'http://maison.coroller.com/php/chart_temp.php?date='+dataGoogleVis.getFormattedValue(item.row,0)+'-01';
				}
			}
		}
				

		})
	}
	google.setOnLoadCallback(getJSONandDisplay);
	
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;">
    <div id="visualization" style="width: 800px; height: 400px;"></div>
  </body>
</html>