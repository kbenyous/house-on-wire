<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$query = "SELECT MAX(papp) as max FROM teleinfo WHERE date >= ( current_timestamp - interval '24h' );";
$result = pg_query( $db, $query ) or die ("Erreur SQL sur selection MIN() et MAX() de PAPP: ". pg_error() );
$row = pg_fetch_array( $result );
$papp_max_24h = $row[ 'max' ];

// Recuperation du plus recent timestamp auquel le MAX des dernieres 24h a ete atteint
$query = "SELECT ROUND( 1000*EXTRACT( EPOCH FROM date ) ) AS when FROM teleinfo WHERE papp = " . $papp_max_24h . " AND date >= ( current_timestamp - interval '24h' ) ORDER BY date DESC LIMIT 1";
$result = pg_query( $db, $query ) or die ("Erreur SQL sur selection MIN() et MAX() de PAPP: ". pg_error() );
$row = pg_fetch_array( $result );
$when_papp_max_24h = $row[ 'when' ];

echo $_GET[ "jqueryCallback" ] . "(";
echo "{ x: $when_papp_max_24h, title: 'Maxi 24h', text: 'Maxi des dernieres 24h : $papp_max_24h W' })";
