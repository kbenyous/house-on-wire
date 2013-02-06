<?php
    // Lecture du fichier de conf
    $config = parse_ini_file('/etc/house-on-wire/house-on-wire.ini', true);
    $db = pg_connect(
        'host='.$config['bdd']['host'].' '.
        'port='.$config['bdd']['port'].' '.
        'dbname='.$config['bdd']['dbname'].' '.
        'user='.$config['bdd']['username'].' '.
        'password='.$config['bdd']['password'].' '.
        'options=\'--client_encoding=UTF8\''
    ) or die('Erreur de connexion au serveur SQLfgsdf');

    // Récupération des sondes
    $result = pg_query(
        $db,
        'select generate_series as month from generate_series((select min(s.mois) from statistiques s), current_date, \'1 month\');'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $months = array();
    while($row = pg_fetch_array($result)) {
        $months[] = $row['month'];
    }

    $result = pg_query(
        $db,
	'select distinct(id) from onewire_meta where statistique = true order by id;'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $stats_ids= array();
    while($row = pg_fetch_array($result)) {
        $stats_ids[] = $row['id'];
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <!--[if IE]>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="/css/statistiques.css" />
    </head>
    <body>
	<table id="stats" border=1 width="100%">
	   <thead>	
		<tr class="entete">
			<td rowspan=2></td>
<?
        foreach ($stats_ids as $id)
        {
            echo "            <td colspan=3>".$id."</td>";
                        
        }
?>
			<td colspan=4>Consommation éléctrique</td>
		</tr>
		<tr class="entete">
<?      
        foreach ($stats_ids as $id)
        {
            echo "           <td class=\"min\">Minimum</td><td class=\"moy\">Moyenne</td><td class=\"max\">Maximum</td>";
                        
        }
?>
                        <td>H. Pleines</td><td>H. Creuses</td><td>Total</td><td>Cout</td>
		</tr>
	</thead>
<?
foreach ($months as $month) {

            $result = pg_query(
                $db,
	        'select to_char(\''.$month.'\'::timestamptz, \'Month YYYY\') as month_string;'
            ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

            $row = pg_fetch_array($result);


echo "                <tr class=\"content\">
                        <td class=\"libelle\">".$row['month_string']."</td>";
	foreach ($stats_ids as $id)
	{
	    $result = pg_query(
        	$db,
	        'select round(min_value::numeric, 2) as min_value, round(avg_value::numeric, 2) as avg_value, round(max_value::numeric, 2) as max_value from statistiques where id = \''.$id.'\' and mois = \''.$month.'\''
	    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    	    $row = pg_fetch_array($result);
            echo "            <td class=\"min\">".$row['min_value']."</td>
                        <td class=\"moy\">".$row['avg_value']."</td>
                        <td class=\"max\">".$row['max_value']."</td>";
                        
	}

            $result = pg_query(
                $db,
		'select sum(hchc)/1000 as hchc, sum(hchp)/1000 as hchp, sum(hchc+hchp)/1000 as hc, round(sum(cout_hc+cout_hp+cout_abo)::numeric, 2) as cout from teleinfo_cout where date_trunc(\'month\', date) = \''.$month.'\' group by date_trunc(\'month\', date)'
            ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

            $row = pg_fetch_array($result);


echo "                        <td>".$row['hchc']."</td>
                        <td>".$row['hchp']."</td>
                        <td>".$row['hc']."</td>
                        <td>".$row['cout']."</td>

                </tr>";


}
?>
	</table>

    </body>
</html>
