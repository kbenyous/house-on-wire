<?php
    $pageTitle = 'House On Wire';

// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <script type='text/javascript' src='http://code.jquery.com/jquery-2.0.0.js'></script>
  <script type="text/javascript" src="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.js"></script>
  <link rel="stylesheet" type="text/css" href="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.css">


<!-- Home -->
<div data-role="page" id="page1">
    <div data-role="content">
        <ul data-role="listview" data-divider-theme="b" data-inset="false">
            <li data-role="list-divider" role="heading">
                Température
            </li>
<?php
$result = pg_query( $db, "select name,round(last_value::numeric,1) as temp from onewire where type='Température'" ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
while ($row = pg_fetch_array($result))
{
?>
            <li data-theme="c">
<?php echo $row["name"] ?>
                    <span class="ui-li-count">
<?php echo $row["temp"] ?>&#x00b0;C
                    </span>
            </li>
<?php } ?>
            <li data-role="list-divider" role="heading">
                Energie
            </li>
            <li data-theme="c">
                    EDF
                    <span class="ui-li-count">
<?php
$result = pg_query( $db, " select * from (select extract(EPOCH FROM date) as ts, (hchp+hchc) as conso  from teleinfo where date >= (current_timestamp - interval '5min') order by date asc) a limit 1; " ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
$row=pg_fetch_array($result);
$ts_prec = $row["ts"];
$conso_prec = $row["conso"];

$result = pg_query( $db, " select extract(EPOCH FROM date) as ts, (hchp+hchc) as conso  from teleinfo order by date desc limit 1; " ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_error() );
$row=pg_fetch_array($result);
$ts_now = $row["ts"];
$conso_now = $row["conso"];

echo round( 3600 * ($conso_now-$conso_prec)/($ts_now-$ts_prec) );


?> W
                    </span>
            </li>
        </ul>
    </div>
</div>

</body>
</html>
