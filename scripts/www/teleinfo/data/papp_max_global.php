<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$query = "SELECT MAX(papp) as max FROM teleinfo;";
$result = pg_query( $db, $query ) or die ("Erreur SQL sur selection MIN() et MAX() de PAPP: ". pg_error() );
$row = pg_fetch_array( $result );
$papp_max = $row[ 'max' ];

// Recuperation du plus recent timestamp auquel le MAX global a ete atteint
$query = "SELECT ROUND( 1000*EXTRACT( EPOCH FROM date ) ) AS when FROM teleinfo WHERE papp = " . $papp_max . " ORDER BY date DESC LIMIT 1";
$result = pg_query( $db, $query ) or die ("Erreur SQL sur selection MIN() et MAX() de PAPP: ". pg_error() );
$row = pg_fetch_array( $result );
$when_papp_max = $row[ 'when' ];

echo $_GET[ "jqueryCallback" ] . "(";
echo "{ x: $when_papp_max, title: 'Maxi', text: 'Maxi general: $papp_max W' })";
