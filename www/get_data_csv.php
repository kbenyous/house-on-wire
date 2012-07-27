<?php
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


        case 'papp_full' :
                echo "Date".$s_separateur."Puissance instantanée".$s_fin_ligne;

                $query = "
			SELECT 
				date, 
				round(papp) AS papp, 
                                round(papp_min) AS papp_min, 
                                round(papp_max) AS papp_max
           		FROM teleinfo_histo
          		WHERE date < ('now'::text::date - '1 day'::interval)
		UNION ALL 
		         SELECT 
				date, 
                                papp,
                                papp as papp_min,
                                papp as papp_max
			  FROM teleinfo
		          WHERE date >= ('now'::text::date - '1 day'::interval)
		ORDER BY date";

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


}


?>

