<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$return['status'] = 'success';

$query = "select case when last_update > current_timestamp - interval '20 minutes' then trunc(last_value::numeric, 2) else 0 end as luminosite from onewire where id = '26.24AE60010000.v'";
$result = pg_query( $db, $query ) or die ("Erreur SQL : ". pg_result_error( $result ) );
$row = pg_fetch_array($result);

$return['content']['luminosity']['current']['value'] = $row['luminosite'];
$return['content']['luminosity']['current']['unit'] = '%';

$query = "select date, ptec, case when date > current_timestamp - interval '20 minutes' then papp else 0 end as papp from teleinfo where date = (select max(date) from teleinfo)";
$result = pg_query( $db, $query ) or die ("Erreur SQL : ". pg_result_error( $result ) );
$row = pg_fetch_array($result);

$return['content']['electricity']['current']['value'] = $row['papp'];
$return['content']['electricity']['current']['unit'] = 'W';
$return['content']['electricity']['current']['ptec'] = $row['ptec'];

$query = "select date, trunc(hchc::numeric/1000, 2) as hchc, trunc(hchp::numeric/1000, 2) as hchp, trunc((hchc+hchp)::numeric/1000, 2) as hc, trunc(cout_hc+cout_hp+cout_abo, 2) as cout from teleinfo_cout where date = current_date - interval '1 day'";
$result = pg_query( $db, $query ) or die ("Erreur SQL : ". pg_result_error( $result ) );
$row = pg_fetch_array($result);

$return['content']['electricity']['power']['yesterday'] = $row['hc'];
$return['content']['electricity']['power']['yesterday_hc'] = $row['hchc'];
$return['content']['electricity']['power']['yesterday_hp'] = $row['hchp'];
$return['content']['electricity']['power']['unit'] = 'kW';

$return['content']['electricity']['cost']['yesterday'] = $row['cout'];
$return['content']['electricity']['cost']['unit'] = '&euro;';

$query = "select date, trunc(hchc::numeric/1000, 2) as hchc, trunc(hchp::numeric/1000, 2) as hchp, trunc((hchc+hchp)::numeric/1000, 2) as hc, trunc(cout_hc+cout_hp+cout_abo, 2) as cout from teleinfo_cout where date = current_date - interval '2 day'";
$result = pg_query( $db, $query ) or die ("Erreur SQL : ". pg_result_error( $result ) );
$row = pg_fetch_array($result);

$return['content']['electricity']['power']['beforeYesterday'] = $row['hc'];
$return['content']['electricity']['power']['beforeYesterday_hc'] = $row['hchc'];
$return['content']['electricity']['power']['beforeYesterday_hp'] = $row['hchp'];
$return['content']['electricity']['cost']['beforeYesterday'] = $row['cout'];


$return['content']['water']['current']['value'] = 10;
$return['content']['water']['current']['unit'] = 'm3';


//var_dump($return);
echo json_encode($return);
?>
