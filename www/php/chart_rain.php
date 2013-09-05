
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>
    	Pluviom&eacute;trie mensuelle <? echo $_GET['month'];?> &agrave; Rennes
    </title>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script type="text/javascript">
      google.load('visualization', '1', {packages: ['corechart']});
    </script>
    <script type="text/javascript">
    
      var getJSONandDisplay = function(){
      		$.getJSON("http://maison.coroller.com/php/get_data_json.php?sonde=PCR918N.rt&month=<? echo $_GET['month'];?>&type=rain", function(data)
		{
			var dataGoogle = [['Date','Pluie Quotidienne','Pluie cumulee']];
			for(i=0;i<data.length;i++)
			{
				dataGoogle[i+1] = [data[i].Date,parseFloat(data[i].Cumul),parseFloat(data[i]["Cumul Mensuel"])];
			}

			var dataGoogleVis = new google.visualization.arrayToDataTable(dataGoogle);
			var ac = new google.visualization.ComboChart(document.getElementById('visualization'));
			ac.draw(dataGoogleVis, {
				focusTarget: 'category',
  				title : 'Pluviometrie mensuelle Rennes',
      				width: 800,
      				height: 400,
      				vAxes: [
      					{ title: "mm / j" },
      					{ title: "mm / mois" }
      				],
      				hAxis: {title: "Date"},
      				//seriesType: "bars",
      				//series: {1: {type: "line"}}
      				series: {
      					0:{ type: "bars", targetAxisIndex: 0 },
      					1:{ type: "line", targetAxisIndex: 1 }
      				}
    			});

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