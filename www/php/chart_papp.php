<html>
<head>
         <title>House-On-Wire</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="js/dygraph-combined.js"></script>
</head>
<body>
<div id="graphdiv" style="width:1150px; height:550px;"></div>
<?
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$result = pg_query( $db, "SELECT extract(epoch from current_timestamp)*1000  as finish_date, extract( epoch from current_date - interval '1 day')*1000 as start_date" ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
$date = pg_fetch_array($result);

?>

<script type="text/javascript">

g = new Dygraph(
    // containing div
    document.getElementById("graphdiv"),
    '/get_data_csv.php?type=papp_full',
    {
        title: 'Puissance instantanée',
        ylabel: 'PAPP (W)',
        legend: 'always',
        labelsDivStyles: { 'textAlign': 'right' },
        showRangeSelector: true,
        dateWindow:[<?=$date['start_date']?>, <?=$date['finish_date']?>]
    }
  );

</script>
</body>
</html>
