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
	'select distinct(id), coalesce(om.name, o.name) as name from onewire_meta om left join onewire o using (id)  where statistique = true order by id;'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $stats_ids= array();
    $entete_label = "";
    $entete_val = "";
    $i = 0;
    $labels = array();
    array_push($labels, '"Date"');

    while($row = pg_fetch_array($result)) {
        $stats_ids[$row['id']] = $row['name'];
	$entete_label .= "<td rowspan=2><input id='".$i."' type=\"checkbox\" checked=\"\" onclick=\"change(this)\"><label for='".$i."'>".$row['name']."</label></td>";
        array_push($labels, '"'.$row['name'].'"');
        $i++;

    }
?>

<!DOCTYPE html>
<html>
    <head>
        <!--[if IE]>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="/js/dygraph-combined.js"></script>

        <link rel="stylesheet" type="text/css" href="/css/statistiques.css" />
    </head>
    <body>
<div id="graphdiv" style="width:1100px; height:400px; "></div>

<div id="tablediv">


	<table id="stats" border=1>
	   <thead>	
		<tr class="entete">
			<td rowspan=2></td>
			<? echo $entete_label; ?>
			<td colspan=4>Consommation éléctrique</td>
		</tr>
		<tr class="entete">
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
	foreach ($stats_ids as $id => $name)
	{
	    $result = pg_query(
        	$db,
	        'select round(min_value::numeric, 1) as min_value, round(avg_value::numeric, 1) as avg_value, round(max_value::numeric, 1) as max_value from statistiques where id = \''.$id.'\' and mois = \''.$month.'\''
	    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    	    $row = pg_fetch_array($result);
            echo "            <td><div class=\"moy\">".$row['avg_value']." °C</div><div class=\"delta\"><div class=\"max\">".$row['max_value']." °C</div><div class=\"min\">".$row['min_value']." °C</div></div></td>";
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
</div>

<script type="text/javascript">

  g = new Dygraph(

    // containing div
    document.getElementById("graphdiv"),
'/php/get_data_csv.php?type=temp_full&sonde=all',
{
title: 'Historique des températures',
customBars: true,
label: [<?=implode($labels, ',');?>],
ylabel: 'Temperature (C)',
legend: 'always',
labelsSeparateLines: true,

        highlightCircleSize: 2,
        strokeWidth: 1,


        highlightSeriesOpts: {
          strokeWidth: 3,
backgroundAlpha: 1,

          strokeBorderWidth: 1,
          highlightCircleSize: 5,
        },

}
  );

function change(el) {
g.setVisibility(el.id, el.checked);
}

</script>


    </body>
</html>
