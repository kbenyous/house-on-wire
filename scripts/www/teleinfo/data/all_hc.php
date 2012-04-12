<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");


$query = "SELECT ROUND( EXTRACT(EPOCH FROM date) ), papp FROM teleinfo WHERE ptec='HC';";
$result = pg_query( $db, $query ) or die ("Erreur SQL ". pg_error() );
if( !$result )
{
	echo "Erreur SQL: " . pg_error();
	exit;
}

echo $_GET[ "callback" ] . "(";
$first=TRUE;
echo "[";
while( $row = pg_fetch_row( $result ) )
{
	echo '[' . $row[0] . ',' . $row[1] . ']';
	if( $first == FALSE )
	{
		echo ',';
		$first = FALSE;
	}
}
echo "]);";
?>
