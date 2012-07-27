<html>
<head>
     	<title>House-On-Wire</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<script type="text/javascript" src="js/dygraph-combined.js"></script>
</head>
<body>
<form id="test" type="post">
<select>
  <option value="Température">Température</option>
</select>

<?
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");


$query = "SELECT 
                *
        FROM
                onewire
        WHERE
                id = ANY ( case 
                        when exists(select 1 from onewire where id = '".$_GET['id']."')
                        then (select array_agg(id) from onewire where id = '".$_GET['id']."') 
                        else (select array_agg(id) from onewire_meta where regroupement = '".$_GET['id']."' ) 
                end )";

$result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
// On prend les infos de la premiere sonde
$info_sonde = pg_fetch_array($result);

if(pg_num_rows($result) > 1)
{
	$info_sonde['name'] = $_GET['id'];
}


$result = pg_query( $db, "SELECT extract(epoch from current_timestamp)*1000  as finish_date, extract( epoch from current_date - interval '10 days')*1000 as start_date" ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
$date = pg_fetch_array($result);

?>


</form>
</body>
</html>
