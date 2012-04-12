<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");


$query = "SELECT ROUND( 1000*EXTRACT(EPOCH FROM date) ), papp FROM teleinfo where date >= ( current_timestamp - interval '5min');";
//$query = "SELECT ROUND( 1000*EXTRACT(EPOCH FROM date) ), papp FROM teleinfo;";
$result = pg_query( $db, $query ) or die ("Erreur SQL ". pg_error() );
if( !$result )
{
	echo "Erreur SQL: " . pg_error();
	exit;
}

$first=TRUE;
echo $_GET[ "jqueryCallback" ] . "(";
echo "[";
while( $row = pg_fetch_row( $result ) )
{
	if( $first == FALSE )
	{
		echo ',';
	} else {
		$first = FALSE;
	}
	echo '[' . $row[0] . ',' . $row[1] . ']';
	echo "\n";
}
echo "]";
echo ");";
?>
