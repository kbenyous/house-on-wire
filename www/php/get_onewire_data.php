<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

$return['status'] = 'success';

if(isset($_POST['id']) && $_POST['id'] != '')
{
	$id = $_POST['id'];
}
elseif(isset($_GET['id']) && $_GET['id'] != '')
{
        $id = $_GET['id'];
}
else
{
        $return['status'] = 'error';
}


if($return['status'] == 'success')
{
// On a passé un ID ou un regroupement d'id ?
$query = "SELECT
        id
    FROM
        onewire
    WHERE
        id = ANY ( case
            when exists(select 1 from onewire where id = '".$id."')
            then (select array_agg(id) from onewire where id = '".$id."')
                        else (select array_agg(id) from onewire_meta where regroupement = '".$id."' )
                end )";

$liste_id = array();
$result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
while ($row = pg_fetch_array($result))
{
    array_push($liste_id, "'".$row['id']."'");
}



$query = "
select
    name,
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
	(select coalesce(om.name, o.name) as name from onewire_meta om left join onewire o using (id) where id = '".$id."' limit 1) as name,
        (select case when last_update > current_timestamp - interval '20 minutes' then round(avg(last_value::numeric), 1) else 0::numeric end from onewire where id IN (".implode(',',$liste_id).") group by last_update) as current,
        (select round(avg(value::numeric), 1) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 hour') as last_hour,
        (select round(avg(value::numeric), 1) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 day') as last_day,
                (select round(min(value::numeric), 1) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 day') as min_last_day,
                (select round(max(value::numeric), 1) from onewire_data where id IN (".implode(',',$liste_id).") and date > current_timestamp - interval '1 day') as max_last_day

    ) a;";

$result = pg_query( $db, $query ) or die ("Erreur SQL : ". pg_result_error( $result ) );

$row = pg_fetch_array($result);

$return['content']['name']['value'] = $row['name'];
$return['content']['maj']['value'] = $row['maj'];
$return['content']['last_value']['value'] = $row['current'];
$return['content']['deltaPlusOneHour']['direction'] = $row['last_hour_variation'];
$return['content']['deltaPlusOneHour']['value'] = $row['last_hour'];
$return['content']['deltaPlusOneDay']['direction'] = $row['last_day_variation'];
$return['content']['deltaPlusOneDay']['value'] = $row['last_day'];
$return['content']['deltaPlusOneDay']['min'] = $row['min_last_day'];
$return['content']['deltaPlusOneDay']['max'] = $row['max_last_day'];

//var_dump($return);
}
echo json_encode($return);
?>
