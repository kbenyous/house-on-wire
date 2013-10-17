<?php
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>
    	Pluviom&eacute;trie 
    </title>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script type="text/javascript">
      google.load('visualization', '1', {packages: ['corechart']});
    </script>
    <script type="text/javascript">
    
      var getJSONandDisplay = function(){
      		$.getJSON("http://<? echo $config['template']['uri'];?>/php/get_data_json.php?sonde=PCR800.rt&date=<? echo $_GET['date'];?>&type=<? echo $_GET['type'];?>", function(data)
		{
			var dataGoogle = [['Date','Pluie','Pluie Cumulee']];
			for(i=0;i<data.length;i++)
			{
				dataGoogle[i+1] = [data[i].Date, parseFloat(data[i]["Valeur"]), parseFloat(data[i]["Cumul"])];
			}

			var dataGoogleVis = new google.visualization.arrayToDataTable(dataGoogle);
			var ac = new google.visualization.ComboChart(document.getElementById('visualization'));
			ac.draw(dataGoogleVis, {
				focusTarget: 'category',
  				title : 'Pluviometrie',
      				width: 800,
      				height: 400,
      				vAxes: [
      					{ title: "mm" },
      					{ title: "mm" }
      				],
      				hAxis: {title: "Date"},
      				//seriesType: "bars",
      				//series: {1: {type: "line"}}
      				series: {
      					0:{ type: "bars", targetAxisIndex: 0 },
      					1:{ type: "line", targetAxisIndex: 1 }
      				}
    			});
    			google.visualization.events.addListener(ac, 'select', selectHandler);
    			
    			function selectHandler() {
    				var selection = ac.getSelection();
    				var message = '';
    				for (var i=0;i<selection.length; i++) {
    					var item = selection[i];
    					if (item.row != null && item.column != null) {
    					  message += '{row:' + item.row + ',column:' + item.column + '}';
    					} else if (item.row != null) {
    					  //alert(dataGoogleVis.getFormattedValue(item.row,0));
    					  <?php
						switch($_GET['type'])
						{
							case 'monthly_rain' :
								echo "window.location = 'http://".$config['template']['uri']."/php/chart_rain_total.php?type=daily_rain&date='+dataGoogleVis.getFormattedValue(item.row,0);";
								break;
                                                        case 'daily_rain' :
                                                                echo "window.location = 'http://".$config['template']['uri']."/php/chart_rain_total.php?type=hourly_rain&date='+dataGoogleVis.getFormattedValue(item.row,0);";
                                                                break;
						}
    					  
					?>
					message += '{row:' + item.row + '}';
    					} else if (item.column != null) {
    					  message += '{column:' + item.column + '}';
    					}
    				}
    			}
    			                                

       		}
		)
	}
	google.setOnLoadCallback(getJSONandDisplay);
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;">
    <div id="visualization" style="width: 800px; height: 400px;"></div>
  </body>
</html>
