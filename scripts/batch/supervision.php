<?
/*
    NCURSES_A_NORMAL
    NCURSES_A_STANDOUT   -> Inversion des couleurs
    NCURSES_A_UNDERLINE  -> Souligne
    NCURSES_A_REVERSE    -> Inversion des couleurs
    NCURSES_A_BLINK      -> Changement de couleur de fond
    NCURSES_A_DIM	 -> ?
    NCURSES_A_BOLD	 -> Gras
    NCURSES_A_PROTECT
    NCURSES_A_INVIS
    NCURSES_A_ALTCHARSET
    NCURSES_A_CHARTEXT
*/

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

$weather_trans = array();
$weather_trans[395] = "Neige Lourde";
$weather_trans[392] = "Tempête";
$weather_trans[389] = "Orage";
$weather_trans[386] = "Tempête";
$weather_trans[377] = "Pluie avec Grêles";
$weather_trans[374] = "Pluie légère avec Grêles";
$weather_trans[371] = "Neige Lourde";
$weather_trans[368] = "Neige Légère";
$weather_trans[365] = "Grésils Lourds";
$weather_trans[362] = "Grésils Légers";
$weather_trans[359] = "Pluie Torrentielle";
$weather_trans[356] = "Averses";
$weather_trans[353] = "Légère Pluie";
$weather_trans[350] = "Grêle";
$weather_trans[338] = "Neige Lourde";
$weather_trans[335] = "Neige Lourde et Disséminée";
$weather_trans[332] = "Neige Modérée";
$weather_trans[329] = "Neige Modérée et Disséminée";
$weather_trans[326] = "Neige Légère";
$weather_trans[323] = "Neige Légère et Disséminée";
$weather_trans[320] = "Grêle Lourde";
$weather_trans[317] = "Grêle Légère";
$weather_trans[314] = "Froid";
$weather_trans[311] = "Froid Léger";
$weather_trans[308] = "Pluie Torrentielle";
$weather_trans[305] = "Pluie Torrentielle Intermittente";
$weather_trans[302] = "Pluie Modérée";
$weather_trans[299] = "Pluie Modérée Intermittente";
$weather_trans[296] = "Pluie Légère";
$weather_trans[293] = "Pluie Légère Disséminée";
$weather_trans[284] = "Bruine Gelée";
$weather_trans[281] = "Bruine Froide";
$weather_trans[266] = "Bruine Légère";
$weather_trans[263] = "Bruine Légère Disséminée";
$weather_trans[260] = "Brouillard et Froid";
$weather_trans[248] = "Brouillard";
$weather_trans[230] = "Tempête de Neige";
$weather_trans[227] = "Neige et Vent";
$weather_trans[200] = "Orage Localisé";
$weather_trans[185] = "Bruine Froide Disséminée";
$weather_trans[182] = "Grêle Disséminée";
$weather_trans[179] = "Neige Disséminée";
$weather_trans[176] = "Pluie Disséminée";
$weather_trans[143] = "Brume";
$weather_trans[122] = "Nuage Épaisse";
$weather_trans[119] = "Nuageux";
$weather_trans[116] = "Partiellement Nuageux";
$weather_trans[113] = "Ensoleillé";


while(1){


// Initialisation de ncurses
$ncurse = ncurses_init();

ncurses_curs_set(0);
// Définition des 'pair' de couleurs utilisables
ncurses_start_color();
        ncurses_init_pair(1,NCURSES_COLOR_RED,NCURSES_COLOR_BLACK);
        ncurses_init_pair(2,NCURSES_COLOR_GREEN,NCURSES_COLOR_BLACK);
        ncurses_init_pair(3,NCURSES_COLOR_YELLOW,NCURSES_COLOR_BLACK);
        ncurses_init_pair(4,NCURSES_COLOR_BLUE,NCURSES_COLOR_BLACK);
        ncurses_init_pair(5,NCURSES_COLOR_MAGENTA,NCURSES_COLOR_BLACK);
        ncurses_init_pair(6,NCURSES_COLOR_CYAN,NCURSES_COLOR_BLACK);
        ncurses_init_pair(7,NCURSES_COLOR_WHITE,NCURSES_COLOR_BLACK);


ncurses_assume_default_colors ( NCURSES_COLOR_YELLOW , NCURSES_COLOR_BLACK );

//ncurses_color_set(1);
ncurses_clear();

// Création du cadre principal
$fullscreen = ncurses_newwin ( 0, 0, 0, 0); 
// Récuperation de la taille de la fenetre
ncurses_getmaxyx($fullscreen, $lines, $columns);
// On force la taille à 37x100 ce qui correspond à mon 800x600
$lines=37;
$columns=100;
// Creation de la bordure
ncurses_border(0,0, 0,0, 0,0, 0,0);
// Titre
ncurses_attron(NCURSES_A_REVERSE);
ncurses_mvaddstr(0,1," House On Wire ");
ncurses_attroff(NCURSES_A_REVERSE);
ncurses_refresh();

// Creation des 4 fenetres de contenus
//
// +-----+-----+
// |     |     |
// | $tl | $tr |
// |     |     |
// +-----+-----+
// |     |     |
// | $bl | $br |
// |     |     |
// +-----+-----+
//
// Constante d'espacement des fenetres
$bspace=1;
$cspace=0;

// Calcul des tailles des fenetres
$width=($columns-($bspace+$cspace+$bspace))/2;
$height=($lines-($bspace+$cspace+$bspace))/2;


// $tl = Top Left
$tl=ncurses_newwin($height+2, $width, $bspace+1, $bspace);
ncurses_wborder($tl,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($tl, 0, 1, " Température " );

// $tr = Top Right
$tr=ncurses_newwin($height+2, $width, $bspace+1, $bspace+$width+$cspace);
ncurses_wborder($tr,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($tr, 0, 1, " Consommation " );

// $tr = Top Right
$bl=ncurses_newwin($height-2, $width, $bspace+1+$height+2+$cspace, $bspace);
ncurses_wborder($bl,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($bl, 0, 1, " Supervision " );

// $tr = Top Right
$br=ncurses_newwin($height-2, $width, $bspace+1+$height+2+$cspace, $bspace+$width+$cspace);
ncurses_wborder($br,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($br, 0, 1, " Météo " );



///////////////////////////////////
//
// $tl => Temperatures
//
///////////////////////////////////

$liste_hum = array("'26.EAB360010000.v'", "'26.FF9E6E010000.v'");
$result = pg_query(
        $db,
        'select id, name, unity, round(last_value::numeric, 1) as last_value from onewire where id in ('.implode(',', $liste_hum).') order by name;'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$sonde_hum = array();
while($row = pg_fetch_array($result))
{
	$sonde_hum[$row['id']]['name'] = $row['name'];
        $sonde_hum[$row['id']]['unity'] = $row['unity'];
        $sonde_hum[$row['id']]['last_value'] = $row['last_value'];
}


$liste_etage = array("'10.28BD65020800'", "'10.A8EB65020800'", "'10.380166020800'", "'10.22E465020800'", "'26.EAB360010000.t'");
$liste_rdc = array("'10.D6F865020800'", "'10.D6D765020800'", "'10.EDFA65020800'", "'28.DEE652030000'", "'26.FF9E6E010000.t'");
$liste_autres = array("'10.C0625A020800'", "'28.57F652030000'", "'28.5BC47A030000'");


// Récupération des sondes
$result = pg_query(
        $db,
        'select id, name, unity, round(last_value::numeric, 1) as last_value from onewire where id in ('.implode(',', $liste_etage).') order by name;'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$ligne = 2;


    while($row = pg_fetch_array($result)) {
        ncurses_mvwaddstr($tl, $ligne, 2, sprintf("%-18s",$row['name']));
        ncurses_mvwaddstr($tl, $ligne, 24, sprintf("%6s",$row['last_value'])." ".$row['unity'] );
	if (array_key_exists(substr($row['id'], 0, -1)."v", $sonde_hum))
	{	
	        ncurses_mvwaddstr($tl, $ligne, 34, sprintf("%6s",$sonde_hum[substr($row['id'], 0, -1)."v"]['last_value'])." ".$sonde_hum[substr($row['id'], 0, -1)."v"]['unity'] );
	}
        $ligne++;

    }

$ligne ++;

// Récupération des sondes
$result = pg_query(
        $db,
        'select id, name, unity, round(last_value::numeric, 1) as last_value from onewire where id in ('.implode(',', $liste_rdc).') order by name;'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());


    while($row = pg_fetch_array($result)) {
        ncurses_mvwaddstr($tl, $ligne, 2, sprintf("%-18s",$row['name']));
        ncurses_mvwaddstr($tl, $ligne, 24, sprintf("%6s",$row['last_value'])." ".$row['unity'] );
        if (array_key_exists(substr($row['id'], 0, -1)."v", $sonde_hum))
        {
                ncurses_mvwaddstr($tl, $ligne, 34, sprintf("%6s",$sonde_hum[substr($row['id'], 0, -1)."v"]['last_value'])." ".$sonde_hum[substr($row['id'], 0, -1)."v"]['unity'] );
        }

        $ligne++;

    }

$ligne ++;

// Récupération des sondes
$result = pg_query(
        $db,
        'select id, name, unity, round(last_value::numeric, 1) as last_value from onewire where id in ('.implode(',', $liste_autres).') order by name;'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());


    while($row = pg_fetch_array($result)) {
        ncurses_mvwaddstr($tl, $ligne, 2, sprintf("%-18s",$row['name']));
        ncurses_mvwaddstr($tl, $ligne, 24, sprintf("%6s",$row['last_value'])." ".$row['unity'] );
        if (array_key_exists(substr($row['id'], 0, -1)."v", $sonde_hum))
        {
                ncurses_mvwaddstr($tl, $ligne, 34, sprintf("%6s",$sonde_hum[substr($row['id'], 0, -1)."v"]['last_value'])." ".$sonde_hum[substr($row['id'], 0, -1)."v"]['unity'] );
        }

        $ligne++;

    }


//////////////////////////////////////
//
// $tr => Consommation electrique
//
//////////////////////////////////////
$conso=file_get_contents("/tmp/papp");

$conso = intval($conso);
// Récuperation de la conso de la veille
    $result = pg_query(
        $db,
	'select date, round(hchc::numeric/1000, 3) as hchc, round(hchp::numeric/1000, 3) as hchp, round((hchc::numeric+hchp::numeric)/1000, 3) as hchchp, round(cout_hc, 2) as cout_hc, round(cout_hp, 2) as cout_hp, round(cout_hc+cout_hp+cout_abo, 2) as cout_total from teleinfo_cout where date = current_date - interval \'1 day\';'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $conso_veille = pg_fetch_array($result); 

ncurses_mvwaddstr($tr, 2, 2, "Consommation Electrique : ".sprintf("%5s",$conso)." W");

ncurses_mvwaddstr($tr, 4, 4, "Hier :   HC   ".sprintf("%6s",$conso_veille['hchc'])." kwH     ".sprintf("%5s",$conso_veille['cout_hc'])." €"); 
ncurses_mvwaddstr($tr, 5, 4, "         HP   ".sprintf("%6s",$conso_veille['hchp'])." kwH     ".sprintf("%5s",$conso_veille['cout_hp'])." €");
ncurses_mvwaddstr($tr, 6, 4, "              ".sprintf("%6s",$conso_veille['hchchp'])." kwH     ".sprintf("%5s",$conso_veille['cout_total'])." €");

$ligne = 8;
    $result = pg_query(
        $db,
        "select to_char(date_trunc('month', date), 'MonthYYYY') as month, round(sum((hchc::numeric+hchp::numeric)/1000), 3) as hchchp, round(sum(cout_hc+cout_hp+cout_abo), 2) as cout_total from teleinfo_cout where date > date_trunc('month', current_date - interval '4 months') group by date_trunc('month', date) order by date_trunc('month', date) asc;"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
    while($conso_mois = pg_fetch_array($result)) 
    {
        ncurses_mvwaddstr($tr, $ligne, 4, sprintf("%-14s",$conso_mois['month'])." ".sprintf("%8s",$conso_mois['hchchp'])." KwH    ".sprintf("%6s",$conso_mois['cout_total'])." €");
	$ligne++;
    }

/////////////////////////////////////
//
// $br => Méteo
//
/////////////////////////////////////

$result = pg_query( $db,
	"select id, round(last_value::numeric, 1) as last_value, unity from onewire where id = '28.BB1A53030000';"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$ext_now =  pg_fetch_array($result);
$result = pg_query( $db,
	"select round(min(value::numeric), 1) as min, round(max(value::numeric), 1) as max from onewire_data where id = '28.BB1A53030000' and date_trunc('day', date) = current_date;"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$ext_auj =  pg_fetch_array($result);
$result = pg_query( $db,
	"select round(min(value::numeric), 1) as min, round(max(value::numeric), 1) as max from onewire_data where id = '28.BB1A53030000' and date_trunc('day', date) = current_date - interval '1 day';"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$ext_hier =  pg_fetch_array($result);
$result = pg_query( $db,
        "select id, last_value::numeric::integer as last_value, unity from onewire where id = '26.0B8D6E010000.v';"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$pression_now =  pg_fetch_array($result);
$result = pg_query( $db,
        "select id, last_value::numeric::integer as last_value, unity from onewire where id = '26.24AE60010000.v';"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$luminosite_now =  pg_fetch_array($result);





ncurses_mvwaddstr($br, 2, 2, "Température Extérieur   : ".sprintf("%6s",$ext_now['last_value'])." ".$ext_now['unity']);
ncurses_mvwaddstr($br, 6, 2, "Luminosité  : ".sprintf("%5s",$luminosite_now['last_value'])." ".$luminosite_now['unity']);
ncurses_mvwaddstr($br, 7, 2, "Pres. Atmo. : ".sprintf("%5s",$pression_now['last_value'])." ".$pression_now['unity']);
ncurses_mvwaddstr($br, 8, 2, "Humidité    : ".sprintf("%5s","xx.x")." "."%");

ncurses_mvwaddstr($br, 3, 2, "Aujourd'hui   Min :");
ncurses_wcolor_set($br, 4);
ncurses_mvwaddstr($br, 3, 21, sprintf("%6s",$ext_auj['min'])." ".$ext_now['unity']);
ncurses_wcolor_set($br, 0);
ncurses_mvwaddstr($br, 3, 33, "Max : ");
ncurses_wcolor_set($br, 1);
ncurses_mvwaddstr($br, 3, 38, sprintf("%6s",$ext_auj['max'])." ".$ext_now['unity']);
ncurses_wcolor_set($br, 0);

ncurses_mvwaddstr($br, 4, 2, "Hier          Min :");
ncurses_wcolor_set($br, 4);
ncurses_mvwaddstr($br, 4, 21, sprintf("%6s",$ext_hier['min'])." ".$ext_now['unity']);
ncurses_wcolor_set($br, 0);
ncurses_mvwaddstr($br, 4, 33, "Max : ");
ncurses_wcolor_set($br, 1);
ncurses_mvwaddstr($br, 4, 38, sprintf("%6s",$ext_hier['max'])." ".$ext_now['unity']);
ncurses_wcolor_set($br, 0);


$xml = simplexml_load_file('http://free.worldweatheronline.com/feed/weather.ashx?q=saint-etienne-de-montluc,44360&format=xml&num_of_days=3&key=c2980fcdc8213432131602');

if(isset($xml->weather[0]))
{
ncurses_mvwaddstr($br, 11, 2, "Auj     : ".sprintf("%3s",$xml->weather[0]->tempMinC)." C ".sprintf("%3s",$xml->weather[0]->tempMaxC)." C   ".$weather_trans[(int)$xml->weather[0]->weatherCode]);
}
if(isset($xml->weather[1]))
{
ncurses_mvwaddstr($br, 12, 2, "Demain  : ".sprintf("%3s",$xml->weather[1]->tempMinC)." C ".sprintf("%3s",$xml->weather[1]->tempMaxC)." C   ".$weather_trans[(int)$xml->weather[1]->weatherCode]);
}
if(isset($xml->weather[2]))
{
ncurses_mvwaddstr($br, 13, 2, "Ap. dem : ".sprintf("%3s",$xml->weather[2]->tempMinC)." C ".sprintf("%3s",$xml->weather[2]->tempMaxC)." C   ".$weather_trans[(int)$xml->weather[2]->weatherCode]);
}


////////////////////////////////////
//
// $bl -> Supervision
//
///////////////////////////////////

// Congélateur, affichage en rouge sur > -10
$result = pg_query( $db,
	"select id, name, unity, round(last_value::numeric, 1) as last_value from onewire where id = '28.C81C53030000';"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$congel =  pg_fetch_array($result);

ncurses_mvwaddstr($bl, 2, 2, sprintf("%-18s",$congel['name']." : "));
if($congel['last_value']<-15)
{
	ncurses_wcolor_set($bl, 2);	
	ncurses_mvwaddstr($bl, 2, 24, sprintf("%15s",$congel['last_value']." ".$congel['unity']));
        ncurses_wcolor_set($bl, 0);
}
else
{
        ncurses_wattron($bl, NCURSES_A_BOLD);
        ncurses_wcolor_set($bl, 1);
        ncurses_mvwaddstr($bl, 2, 2, sprintf("%15s",$congel['last_value']." ".$congel['unity']));
        ncurses_wattroff($bl, NCURSES_A_BOLD);
        ncurses_wcolor_set($bl, 0);
}

$result = pg_query( $db,
        "select count(*) as nb_sonde, sum(case when date_trunc('minute', last_update) = (select date_trunc('minute', max(last_update)) from onewire) then 1 else 0 end ) as nb_sonde_ok,  sum(case when date_trunc('minute', last_update) != (select date_trunc('minute', max(last_update)) from onewire) then 1 else 0 end ) as nb_sonde_ko, date_trunc('minute', max(last_update)) as last_update, case when (select date_trunc('minute', max(last_update)) from onewire) < current_timestamp - interval '1 min' then 1 else 0 end as alerte, current_timestamp - interval '1 min' from onewire;"
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());
$maj =  pg_fetch_array($result);

if($maj['nb_sonde_ok'] == $maj['nb_sonde'])
{
        ncurses_wcolor_set($bl, 2);
	ncurses_mvwaddstr($bl, 4, 2, "Maj : ".$maj['last_update']." ".$maj['nb_sonde_ok']."/".$maj['nb_sonde']." sondes");
        ncurses_wcolor_set($bl, 0);
}
else
{
        ncurses_wattron($bl, NCURSES_A_BOLD);
        ncurses_wcolor_set($bl, 1);
	ncurses_mvwaddstr($bl, 4, 2, "Maj : ".$maj['last_update']." ".$maj['nb_sonde_ok']."/".$maj['nb_sonde']." sondes");
        ncurses_wattroff($bl, NCURSES_A_BOLD);
        ncurses_wcolor_set($bl, 0);
}


// nb_sonde | nb_sonde_ok | nb_sonde_ko |     last_update     | alerte |           ?column?
//----------+-------------+-------------+---------------------+--------+-------------------------------
//       35 |          35 |           0 | 2013-04-29 23:10:02 |      0 | 2013-04-29 21:11:27.299489+00


// Récupération des sondes
$result = pg_query(
        $db,
        'select id, name, last_value from onewire where type = \'ouverture\' order by name;'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $ligne = 6;
    while($row = pg_fetch_array($result)) {
       ncurses_mvwaddstr($bl, $ligne, 2, sprintf("%-18s",$row['name']." : "));
       if($row['last_value'] == 1)
       {
        ncurses_wcolor_set($bl, 2);
        ncurses_mvwaddstr($bl, $ligne, 24, sprintf("%15s","Fermée" ));
        ncurses_wcolor_set($bl, 0);
       }
       else
       {
        ncurses_wattron($bl, NCURSES_A_BOLD);
        ncurses_wcolor_set($bl, 1);
        ncurses_mvwaddstr($bl, $ligne, 24, sprintf("%15s","Ouverte" ));
        ncurses_wattroff($bl, NCURSES_A_BOLD);
        ncurses_wcolor_set($bl, 0);
       }

        $ligne++;

    }





$tmp=9;

ncurses_wcolor_set($bl, 1);
ncurses_mvwaddstr($bl, $tmp, 2, "RED");
ncurses_wcolor_set($bl, 2);
ncurses_mvwaddstr($bl, $tmp, 6, "GREEN");
ncurses_wcolor_set($bl, 3);
ncurses_mvwaddstr($bl, $tmp, 12, "YELLOW");
ncurses_wcolor_set($bl, 4);
ncurses_mvwaddstr($bl, $tmp, 19, "BLUE");
ncurses_wcolor_set($bl, 5);
ncurses_mvwaddstr($bl, $tmp, 24, "MAGENTA");
ncurses_wcolor_set($bl, 6);
ncurses_mvwaddstr($bl, $tmp, 32, "CYAN");
ncurses_wcolor_set($bl, 7);
ncurses_mvwaddstr($bl, $tmp, 37, "WHITE");
ncurses_wcolor_set($bl, 0);

$tmp++;

ncurses_wattron($bl, NCURSES_A_UNDERLINE);
ncurses_wcolor_set($bl, 1);
ncurses_mvwaddstr($bl, $tmp, 2, "RED");
ncurses_wcolor_set($bl, 2);
ncurses_mvwaddstr($bl, $tmp, 6, "GREEN");
ncurses_wcolor_set($bl, 3);
ncurses_mvwaddstr($bl, $tmp, 12, "YELLOW");
ncurses_wcolor_set($bl, 4);
ncurses_mvwaddstr($bl, $tmp, 19, "BLUE");
ncurses_wcolor_set($bl, 5);
ncurses_mvwaddstr($bl, $tmp, 24, "MAGENTA");
ncurses_wcolor_set($bl, 6);
ncurses_mvwaddstr($bl, $tmp, 32, "CYAN");
ncurses_wcolor_set($bl, 7);
ncurses_mvwaddstr($bl, $tmp, 37, "WHITE");
ncurses_wcolor_set($bl, 0);
ncurses_wattroff($bl, NCURSES_A_UNDERLINE);

$tmp++;

ncurses_wattron($bl, NCURSES_A_REVERSE);
ncurses_wcolor_set($bl, 1);
ncurses_mvwaddstr($bl, $tmp, 2, "RED");
ncurses_wcolor_set($bl, 2);
ncurses_mvwaddstr($bl, $tmp, 6, "GREEN");
ncurses_wcolor_set($bl, 3);
ncurses_mvwaddstr($bl, $tmp, 12, "YELLOW");
ncurses_wcolor_set($bl, 4);
ncurses_mvwaddstr($bl, $tmp, 19, "BLUE");
ncurses_wcolor_set($bl, 5);
ncurses_mvwaddstr($bl, $tmp, 24, "MAGENTA");
ncurses_wcolor_set($bl, 6);
ncurses_mvwaddstr($bl, $tmp, 32, "CYAN");
ncurses_wcolor_set($bl, 7);
ncurses_mvwaddstr($bl, $tmp, 37, "WHITE");
ncurses_wcolor_set($bl, 0);
ncurses_wattroff($bl, NCURSES_A_REVERSE);

$tmp++;

ncurses_wattron($bl, NCURSES_A_BOLD);
ncurses_wcolor_set($bl, 1);
ncurses_mvwaddstr($bl, $tmp, 2, "RED");
ncurses_wcolor_set($bl, 2);
ncurses_mvwaddstr($bl, $tmp, 6, "GREEN");
ncurses_wcolor_set($bl, 3);
ncurses_mvwaddstr($bl, $tmp, 12, "YELLOW");
ncurses_wcolor_set($bl, 4);
ncurses_mvwaddstr($bl, $tmp, 19, "BLUE");
ncurses_wcolor_set($bl, 5);
ncurses_mvwaddstr($bl, $tmp, 24, "MAGENTA");
ncurses_wcolor_set($bl, 6);
ncurses_mvwaddstr($bl, $tmp, 32, "CYAN");
ncurses_wcolor_set($bl, 7);
ncurses_mvwaddstr($bl, $tmp, 37, "WHITE");
ncurses_wcolor_set($bl, 0);
ncurses_wattroff($bl, NCURSES_A_BOLD);

$tmp++;

ncurses_wattron($bl, NCURSES_A_REVERSE);
ncurses_wattron($bl, NCURSES_A_BOLD);
ncurses_wcolor_set($bl, 1);
ncurses_mvwaddstr($bl, $tmp, 2, "RED");
ncurses_wcolor_set($bl, 2);
ncurses_mvwaddstr($bl, $tmp, 6, "GREEN");
ncurses_wcolor_set($bl, 3);
ncurses_mvwaddstr($bl, $tmp, 12, "YELLOW");
ncurses_wcolor_set($bl, 4);
ncurses_mvwaddstr($bl, $tmp, 19, "BLUE");
ncurses_wcolor_set($bl, 5);
ncurses_mvwaddstr($bl, $tmp, 24, "MAGENTA");
ncurses_wcolor_set($bl, 6);
ncurses_mvwaddstr($bl, $tmp, 32, "CYAN");
ncurses_wcolor_set($bl, 7);
ncurses_mvwaddstr($bl, $tmp, 37, "WHITE");
ncurses_wcolor_set($bl, 0);
ncurses_wattroff($bl, NCURSES_A_BOLD);
ncurses_wattroff($bl, NCURSES_A_REVERSE);



ncurses_wrefresh($tl);
ncurses_wrefresh($tr);
ncurses_wrefresh($bl);
ncurses_wrefresh($br);



	
	// Mise à jour des infos :
	// 	Consommation electrique : Tous les secondes ( 1 boucle )
	//	Reste des Infos ( Rechargement complet ? ) Toutes les 5 min ( 300 boucles )

	// Boucle pour faire 60 boucles
	for($i=0;$i<300;$i++)
	{
		// Mise à jour de heure
		$date = strftime(" %A %d %B %Y %H:%M:%S ");
		ncurses_mvaddstr(0,$columns-2-strlen($date),$date);

		// Mise à jour de la consommation
		$conso=file_get_contents("/tmp/papp");
	        $conso = intval($conso);
		ncurses_mvwaddstr($tr, 2, 2, "Consommation Electrique : ".sprintf("%5s",$conso)." W");
	        ncurses_wrefresh($tr);
	        ncurses_move(-1,1);
		ncurses_refresh();

	
	  	sleep(1);
	}
}
