<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$nb_points = $_GET['nb'];
$query = "SELECT ROUND(1000*EXTRACT(EPOCH from date)) AS when, papp FROM teleinfo ORDER BY date DESC LIMIT $nb_points;";
$result = pg_query( $db, $query ) or die ("Erreur SQL : ". pg_error() );

$points=array();
while( $row = pg_fetch_array( $result ) )
{
    $point = array( $row['when'], $row['papp'] );
    $points[] = $point;
}

header("Content-type: text/json");
echo json_encode(array_reverse($points),JSON_NUMERIC_CHECK);

?>
