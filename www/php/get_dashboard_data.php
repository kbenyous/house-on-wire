<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$return['status'] = 'success';
/*

$query = "
select
	to_char(current_timestamp, 'DD-MM-YYYY HH24:MI') as maj,
	current,
	last_hour,
	case when current>last_hour then 'increase' else 'decrease' end as last_hour_variation,
	last_day,
        case when current>last_day then 'increase' else 'decrease' end as last_day_variation,
	min_last_day,
	max_last_day
from
	(
	select 
		(select case when last_update > current_timestamp - interval '20 minutes' then round(avg(last_value::numeric), 2) else 0::numeric end from onewire where id IN (".implode(',',$liste_id).") group by last_update) as current, 
		(select round(avg(value::numeric), 2) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 hour') as last_hour, 
		(select round(avg(value::numeric), 2) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 day') as last_day,
                (select round(min(value::numeric), 2) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 day') as min_last_day,
                (select round(max(value::numeric), 2) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 day') as max_last_day

	) a;";

$result = pg_query( $db, $query ) or die ("Erreur SQL : ". pg_result_error( $result ) );

$row = pg_fetch_array($result);

$return['content']['maj']['value'] = $row['maj'];
$return['content']['temperature']['value'] = $row['current'];
$return['content']['deltaPlusOneHour']['direction'] = $row['last_hour_variation'];
$return['content']['deltaPlusOneHour']['value'] = $row['last_hour'];
$return['content']['deltaPlusOneDay']['direction'] = $row['last_day_variation'];
$return['content']['deltaPlusOneDay']['value'] = $row['last_day'];
$return['content']['deltaPlusOneDay']['min'] = $row['min_last_day'];
$return['content']['deltaPlusOneDay']['max'] = $row['max_last_day'];

*/

$return['content']['maj']['value'] = $row['maj'];
$return['content']['electricity']['current']['value'] = 10;
$return['content']['electricity']['current']['unit'] = 'kW/h';

$return['content']['electricity']['power']['yesterday'] = 8;
$return['content']['electricity']['power']['beforeYesterday'] = 6;
$return['content']['electricity']['power']['unit'] = 'kW';

$return['content']['electricity']['cost']['yesterday'] = 2;
$return['content']['electricity']['cost']['beforeYesterday'] = 4;
$return['content']['electricity']['cost']['unit'] = '&euro;';

$return['content']['water']['current']['value'] = 10;
$return['content']['water']['current']['unit'] = 'm3';

$return['content']['luminosity']['current']['value'] = 90;
$return['content']['luminosity']['current']['unit'] = '%';

//var_dump($return);
echo json_encode($return);
?>
