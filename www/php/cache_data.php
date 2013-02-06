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
	file_put_contents(realpath (dirname(__FILE__))."./../cache/last_days_".$row['id'].".csv", file_get_contents("http://house.vitre.info/php/get_data_csv.php?cache=true&type=last_days&sonde=".$row['id']));
	file_put_contents(realpath (dirname(__FILE__))."./../cache/full_".$row['id'].".csv", file_get_contents("http://house.vitre.info/php/get_data_csv.php?cache=true&type=full&sonde=".$row['id']));

}

echo "mise en cache de papp_full".'\n';
// http://house.vitre.info/php/get_data_csv.php?type=papp_full
        file_put_contents(realpath (dirname(__FILE__))."./../cache/papp_full_".$row['id'].".csv", file_get_contents("http://house.vitre.info/php/get_data_csv.php?cache=true&type=papp_full"));

echo "Fin de la mise en cache".'\n';
/*
}
switch($type)
{
    case 'last_days' :
        echo "Date".$s_separateur.$info_sonde['type'].$s_fin_ligne;

        $query = "
        SELECT
            date_trunc('hour', date) as date,
            round(avg(value::numeric), 2) as value,
            round(min(value::numeric), 2) as min_value,
            round(max(value::numeric), 2) as max_value
        FROM onewire_data
        WHERE
            id IN (".implode(',',$liste_id).") and
            value != ''
        GROUP BY
            date_trunc('hour', date)
        ORDER BY
            date ASC;";

        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

        while ($row = pg_fetch_array($result))
        {
                echo $row['date'].$s_separateur.$row['min_value'].';'.$row['value'].';'.$row['max_value'].$s_fin_ligne;
        }

        break;

    case 'full' :
                echo "Date".$s_separateur.$info_sonde['type'].$s_fin_ligne;

        $query = "
        SELECT
            date_trunc('day', date) as date,
            round(avg(value::numeric), 2) as value,
            round(min(value::numeric), 2) as min_value,
            round(max(value::numeric), 2) as max_value
        FROM
            onewire_data
        where
            id IN (".implode(',',$liste_id).") and
            value != ''
        group by
            date_trunc('day', date)
        order by
            date ASC;";

               $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

                while ($row = pg_fetch_array($result))
                {
                        echo $row['date'].$s_separateur.$row['min_value'].';'.$row['value'].';'.$row['max_value'].$s_fin_ligne;
                }

        break;

    case 'temp_full' :
        // Récupération des noms des champs en sortie
        $liste_champ_type = array();
        $liste_champ = array();
        $query = "
            SELECT
                                name
                        FROM
                                onewire
                        WHERE
                                type = 'Température'
                        ORDER BY
                                id";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
                while ($row = pg_fetch_array($result))
                {
                        array_push($liste_champ_type, '"'.$row['name'].'" numeric');
                        array_push($liste_champ, '"'.$row['name'].'"');

                }

        $query = "
        SELECT *
        FROM crosstab
        (
            'SELECT
                date_trunc(''day'', date) as date,
                id,
                round(avg(value::numeric), 2) as value
            FROM
                onewire_data
            JOIN onewire USING (id)
            WHERE
                value != '''' AND
                type = ''Température''
            GROUP BY
                id,
                date_trunc(''day'', date)
            ORDER BY
                1, 2',

            'SELECT DISTINCT
                id
            FROM
                onewire
            WHERE
                type = ''Température''
            ORDER BY
                1'
        )
        AS
        (
            date timestamp,
            ".implode(',', $liste_champ_type)."
        )";
        echo "Date".$s_separateur.implode($s_separateur, $liste_champ).$s_fin_ligne;
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

                while ($row = pg_fetch_assoc($result))
                {
            $date = $row['date'];
            unset($row['date']);
            echo $date.$s_separateur.implode($s_separateur, $row).$s_fin_ligne;
                }

        break;

	case 'papp_live' :
		$return = array();		
		$return['status'] == 'success';
		$query = "SELECT date, papp FROM teleinfo ORDER BY date DESC LIMIT 1;";
		$result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
		$row = pg_fetch_array( $result );
    		$return['content']['date'] = $row["date"];
		$return['content']['papp'] = $row['papp'];
	//	header("Content-type: text/json");
		echo json_encode($return);
	break;


        case 'papp_full' :
                echo "Date".$s_separateur."Puissance instantanée".$s_fin_ligne;

               $query = "
             SELECT
                                date,
                                papp
                          FROM teleinfo
                          WHERE date >= ('now'::text::date - '2 days'::interval)
                ORDER BY date";

               $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

                while ($row = pg_fetch_array($result))
                {
//                        echo $row['date'].$s_separateur.$row['papp_min'].';'.$row['papp'].';'.$row['papp_max'].$s_fin_ligne;
                        echo $row['date'].$s_separateur.$row['papp'].$s_fin_ligne;

                }

                break;

	case 'conso_elect' :
		echo "Date".$s_separateur."Heures Pleines".$s_separateur."Heures Creuses".$s_fin_ligne;

               $query = "
	             SELECT
                                date,
                                trunc(hchp::numeric/1000, 2) AS hchp,
				trunc(hchc::numeric/1000, 2) AS hchc
                          FROM teleinfo_cout
                	ORDER BY date";

               $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

                while ($row = pg_fetch_array($result))
                {
                        echo $row['date'].$s_separateur.$row['hchp'].$s_separateur.$row['hchc'].$s_fin_ligne;

                }

		// Insertion d'une fausse ligne pour la journée en court pour afficher la barre sur le graph
		$row = pg_fetch_array($result, pg_num_rows($result)-1);		
		echo date('Y-m-d 00:00:00').$s_separateur.$row['hchp'].$s_separateur.$row['hchc'].$s_fin_ligne;
              break;

}

*/
?>

