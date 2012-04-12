<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");


//$query = "SELECT ROUND( 1000*EXTRACT(EPOCH FROM date) ) as when, papp, ptec FROM teleinfo where date >= ( current_timestamp - interval '1min');";
$query = "SELECT ROUND( 1000*EXTRACT(EPOCH FROM date) ) AS when, papp, ptec FROM teleinfo_view;";
$result = pg_query( $db, $query ) or die ("Erreur SQL ". pg_error() );
if( !$result )
{
	echo "Erreur SQL: " . pg_error();
	exit;
}

$first=true;
$ptec_courant="";
$ptec_from=0;
$hc_periodes=array();
echo $_GET[ "jqueryCallback" ] . "(";
echo "{'papp':[";
while( $row = pg_fetch_row( $result ) )
{
    if( $first == true ) { $first=false; } else { echo ","; }
	echo '[' . $row[0] . ',' . $row[1] . "]";
	if( $ptec_courant != $row[2] )
	{
		if( $row[2] == "HC" ) {
			$ptec_from = $row[0];
		}
		if( ( $row[2] != "HC" ) && ( $ptec_from!=0 ) ) {
            $hc_periodes[] = array('from' => $ptec_from, 'to' => $row[0], 'color' => 'rgba(128,128,255,0.15)');
		}
		$ptec_courant = $row[2];
	}
}
echo "],'hc_periodes':" . json_encode($hc_periodes, JSON_NUMERIC_CHECK);
echo "});";
?>
