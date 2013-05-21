<html>
<head>
         <title>House-On-Wire</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="/js/dygraph-combined.js"></script>

<style type='text/css'>
     #labels > span.highlight { border: 1px solid grey; }
    </style>
</head>
<body>
<div id="graphdiv" style="width:800px; height:600px; float:left;"></div>
<div id="labels"></div>
<p>
<b>Sondes : </b>
<?
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$result = pg_query( $db, "SELECT * from onewire where type ='temperature' order by id" ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
$i = 0;

$labels = array();
array_push($labels, '"Date"');
while ($row = pg_fetch_array($result))
{
    echo '<input id="'.$i.'" type="checkbox" checked="" onclick="change(this)">';
    echo '<label for="'.$i.'"> '.$row['name'].'</label>';
    array_push($labels, '"'.$row['name'].'"');
    $i++;
}



?>
</p>

<script type="text/javascript">

  g = new Dygraph(

    // containing div
    document.getElementById("graphdiv"),
//"data.csv",
'/php/get_data_csv.php?type=temp_full&sonde=all',
{
title: 'Historique des températures par pièce',
label: [<?=implode($labels, ',');?>],
ylabel: 'Temperature (C)',
legend: 'always',
labelsDiv: "labels",
labelsSeparateLines: true,
labelsDivStyles: { 'textAlign': 'right' },

        highlightCircleSize: 2,
        strokeWidth: 1,

        highlightSeriesOpts: {
          strokeWidth: 3,

          strokeBorderWidth: 1,
          highlightCircleSize: 5,
        },

}
  );

function change(el) {
g.setVisibility(el.id, el.checked);
}

</script>
</body>
</html>

