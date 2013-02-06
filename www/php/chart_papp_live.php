<?
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$query = "SELECT
                220 * isousc as papp_max
        FROM
                teleinfo
	order by date desc
limit 1
                ";

$result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
// On prend les infos de la premiere sonde
$papp = pg_fetch_array($result);


$result = pg_query( $db, "SELECT date, papp from teleinfo where date > current_timestamp - interval '1 minute' order by date asc" ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );

?>
    <script type="text/javascript">
      var data = [];
      <?
        while ($row = pg_fetch_array($result))
        {
          echo "data.push([new Date(\"".$row["date"]."\"), ".$row["papp"]."]);\n";
        }
      ?>

      var g = new Dygraph(
		document.getElementById("div_g"), 
                data,
                {
                    stepPlot: true,
                    fillGraph: true,
                    stackedGraph: true,
                  valueRange: [0, <?=$papp['papp_max']?>],
//		    includeZero: true,
		    drawXGrid: false,
		    drawYGrid: false,
		    drawXAxis: false,
		    rightGap: false,
		    drawYAxis: false
                });
/*
      var g2 = new Dygraph(
                document.getElementById("papp_live_gauge"), 
                data,
                {
                    stepPlot: true,
                    fillGraph: true,
                    stackedGraph: true,
                  valueRange: [0, <?=$papp['papp_max']?>],
//                  includeZero: true,
                    drawXGrid: false,
                    drawYGrid: false,
                    drawXAxis: false,
                    rightGap: false,
                    drawYAxis: false
                });
*/
      setInterval(function() {

    $.ajax({
        url: '/php/get_data_csv.php?type=papp_live',
        success: function(response) {
                var parsedResponse = $.parseJSON(response);
//		alert(parsedResponse.content.date);
		data.shift();
		data.push([new Date(parsedResponse.content.date), parsedResponse.content.papp]);
		g.updateOptions( { 'file': data } );
//                g2.updateOptions( { 'file': data } );
		$('#pappValue').html(parsedResponse.content.papp);
}
      });
	}, 1500);

</script>
