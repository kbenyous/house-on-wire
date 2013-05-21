<?php

// Connection à la base de données
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

// Lecture de la liste des sondes à générer
//$query = "select id, type from onewire union select distinct regroupement, type from onewire_meta join onewire using (id) where regroupement is not null";
$query = "select id from onewire union select id from onewire_meta";
echo "Debut programme de mise en cache".'\n';


$liste_id = array();
$result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
while ($row = pg_fetch_array($result))
{
echo "mise en cache de la sonde ".$row['id'].'\n';
	file_put_contents(realpath (dirname(__FILE__))."./../cache/last_days_".$row['id'].".csv", file_get_contents("http://".$config['template']['uri']."/php/get_data_csv.php?cache=true&type=last_days&sonde=".$row['id']));
	file_put_contents(realpath (dirname(__FILE__))."./../cache/full_".$row['id'].".csv", file_get_contents("http://".$config['template']['uri']."/php/get_data_csv.php?cache=true&type=full&sonde=".$row['id']));

}

echo "mise en cache de papp_full".'\n';
// http://house.vitre.info/php/get_data_csv.php?type=papp_full
        file_put_contents(realpath (dirname(__FILE__))."./../cache/papp_full_".$row['id'].".csv", file_get_contents("http://".$config['template']['uri']."/php/get_data_csv.php?cache=true&type=papp_full"));

echo "Fin de la mise en cache".'\n';
?>

