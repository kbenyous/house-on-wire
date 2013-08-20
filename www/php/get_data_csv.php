<?php


if(!ob_start("ob_gzhandler")) ob_start();

/*
    Fichier de génération des données au format CSV

    Evite l'insertion des données directement dans le code source JS de la page.

    Referencement des Graph :
        - Historique des températures / luminosité
            - Graph Last days ( Grouper par heure sur 10 jours ) type=last_days&sonde=id
            - Graph Historique histo ( Grouper par jour sur l'historique complet ) type=full&sonde=id
        - Comparaison des températures
            - Graph Histo ( Groupé par jour et par courbe sur l'historique complet ) type=temp_full
        - Comsommation electrique
            - Graph Historique ( Groupé par minutes sur l'historique complet ? ) type=papp_full

    Paramètres attendus en entrée :
        - $_GET['type'] =
        - $_GET['sonde'] = id_sonde ou null
*/

// choix du séparateur
$s_separateur=',';
// choix du fin de ligne
$s_fin_ligne="\r\n";
// Connection à la base de données
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

if(isset($_GET['type']) && $_GET['type'] != '')
{
        $type = $_GET['type'];
}
else
{
    echo "Absence du type";
    exit;
}
if(isset($_GET['sonde']) && $_GET['sonde'] != '')
{
        $sonde = $_GET['sonde'];
}
else
{
    $sonde = '';
}
if(isset($_GET['cache']) && $_GET['cache'] == 'true')
{
	$use_cache = false;
        $cond_date = " AND date < current_date ";
}
else
{
	if(file_exists("./../cache/".$type."_".$sonde.".csv"))
	{
        	$use_cache = true;
	        $date_cond = " AND date_trunc('day', date) = current_date ";
	}
	else
	{
		$use_cache = false;
	        $date_cond = '';
	}
}

//header('Content-type: text/csv');
//header('Content-disposition: attachment;filename=data.csv');

if($sonde != '')
{

// On a passé un ID ou un regroupement d'id ?
$query = "SELECT
                *
        FROM
                onewire
        WHERE
                id = ANY ( case
                        when exists(select 1 from onewire where id = '".$sonde."')
                        then (select array_agg(id) from onewire where id = '".$sonde."')
                        else (select array_agg(id) from onewire_meta where regroupement = '".$sonde."' )
                end )";

$liste_id = array();
$result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
while ($row = pg_fetch_array($result))
{
        array_push($liste_id, "'".$row['id']."'");
    // On garde en memoire les infos de la derniere sonde lue ( on d'en fout c'est juste pour le type )
    $info_sonde = $row;
}

if($sonde == 'all')
{
 $liste_id = array('10.22E465020800', '10.28BD65020800', '10.380166020800', '10.A8EB65020800', '10.D6F865020800', '10.EDFA65020800', '28.BB1A53030000');
}


}
switch($type)
{
    case 'last_days' :
	if($use_cache)
	{
        	echo file_get_contents("./../cache/".$type."_".$sonde.".csv");
	}
	else
	{
		echo "Date".$s_separateur.$info_sonde['type'].$s_fin_ligne;
	}
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
	    ".$date_cond."
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

    case 'day_temp' :
        echo "Date".$s_separateur."min".$s_separateur."moy".$s_separateur."max".$s_separateur."delta".$s_fin_ligne;

        $query = "
        SELECT
            date_trunc('day', date)::date as date,
            round(avg(value::numeric), 2) as value,
            round(min(value::numeric), 2) as min_value,
            round(max(value::numeric), 2) as max_value,
	    round(max(value::numeric)-min(value::numeric), 2) as delta_value
        FROM
            onewire_data
        where
            id IN (".implode(',',$liste_id).") and
            value != ''
                ".$date_cond." 
        group by
            date_trunc('day', date)
        order by
            date ASC;";

               $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

                while ($row = pg_fetch_array($result))
                {
                        echo $row['date'].$s_separateur.$row['min_value'].$s_separateur.$row['value'].$s_separateur.$row['max_value'].$s_separateur.$row['delta_value'].$s_fin_ligne;
                }

        break;


    case 'full' :
        if($use_cache)
        {
                echo file_get_contents("./../cache/".$type."_".$sonde.".csv");
        }
        else
        {
		echo "Date".$s_separateur.$info_sonde['type'].$s_fin_ligne;
	}

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
		".$date_cond." 
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
		select distinct(id), coalesce(om.name, o.name) as name from onewire_meta om left join onewire o using (id)  where statistique = true order by id;";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
                while ($row = pg_fetch_array($result))
                {
                        array_push($liste_champ_type, '"'.$row['name'].'" text');
                        array_push($liste_champ, '"'.$row['name'].'"');

                }



        $query = "
        SELECT *
        FROM crosstab
        (
            'SELECT
                mois as date,
                id,
                round(min_value::numeric, 2) || '';'' || round(avg_value::numeric, 2) || '';'' || round(max_value::numeric, 2) as value
            FROM
                statistiques
            WHERE
		id in (select distinct(id) from onewire_meta  where statistique = true)
            ORDER BY
                1, 2',

            'SELECT DISTINCT
                id
            FROM
                onewire_meta
            WHERE
		statistique = true
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
		$return['status'] = 'success';
		$query = "SELECT date, papp FROM teleinfo ORDER BY date DESC LIMIT 1;";
		$result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
		$row = pg_fetch_array( $result );
    		$return['content']['date'] = $row["date"];
		$return['content']['papp'] = $row['papp'];
	//	header("Content-type: text/json");
		echo json_encode($return);
	break;


        case 'papp_full' :
        if($use_cache)
        {
                echo file_get_contents("./../cache/".$type."_".$sonde.".csv");
        }
        else
        {

                echo "Date".$s_separateur."Puissance instantanée".$s_fin_ligne;
	}
               $query = "
             SELECT
                                date,
                                papp
                          FROM teleinfo
                          WHERE date >= ('now'::text::date - '2 days'::interval)
		".$date_cond."
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


        case 'conso_elect_full' :
                echo "Date".$s_separateur."Heures Pleines".$s_separateur."Heures Creuses".$s_separateur."Total".$s_fin_ligne;

               $query = "
                     SELECT
                                date::date as date,
                                trunc(hchp::numeric/1000, 2) AS hchp,
                                trunc(hchc::numeric/1000, 2) AS hchc,
				trunc((hchc::numeric+hchp::numeric)/1000, 2) AS hpc
                          FROM teleinfo_cout
                        ORDER BY date";

               $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

                while ($row = pg_fetch_array($result))
                {
                        echo $row['date'].$s_separateur.$row['hchp'].$s_separateur.$row['hchc'].$s_separateur.$row["hpc"].$s_fin_ligne;

                }

              break;


        case 'conso_elect_euro' :
                echo "Date".$s_separateur."Heures Pleines".$s_separateur."Heures Creuses".$s_separateur."Abonnement".$s_fin_ligne;

               $query = "
                     SELECT
                                date,
				trunc(cout_abo, 2) AS abo,
                                trunc(cout_hp, 2) AS hp,
                                trunc(cout_hc, 2) AS hc
                          FROM teleinfo_cout
                        ORDER BY date";

               $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );

                while ($row = pg_fetch_array($result))
                {
                        echo $row['date'].$s_separateur.$row['hp'].$s_separateur.$row['hc'].$s_separateur.$row['abo'].$s_fin_ligne;

                }

                // Insertion d'une fausse ligne pour la journée en court pour afficher la barre sur le graph
                $row = pg_fetch_array($result, pg_num_rows($result)-1);
                echo date('Y-m-d 00:00:00').$s_separateur.$row['hp'].$s_separateur.$row['hc'].$s_separateur.$row['abo'].$s_fin_ligne;
              break;

}


?>

