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


while(1){

    // Récupération des sondes
    $result = pg_query(
        $db,
        'select distinct id, coalesce(o.name, om.name) as name, coalesce(o.unity, om.unity) as unity, round(last_value::numeric, 1) as last_value, level from onewire_meta om join onewire o using (id) order by coalesce(o.name, om.name);'
    ) or die('Erreur SQL sur recuperation des valeurs: '.pg_error());

    $sondes = array();
    while($row = pg_fetch_array($result)) {
        $sondes[$row['level']][] = array(
            'id' => $row['id'],
            'title' => $row['name'],
            'unit' => $row['unity'],
            'value' => $row['last_value']
        );
    }

//var_dump($sondes);
//exit;





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
$tl=ncurses_newwin($height, $width, $bspace+1, $bspace);
ncurses_wborder($tl,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($tl, 0, 1, " Etage " );

// $tr = Top Right
$tr=ncurses_newwin($height, $width, $bspace+1, $bspace+$width+$cspace);
ncurses_wborder($tr,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($tr, 0, 1, " Consommation " );

// $tr = Top Right
$bl=ncurses_newwin($height, $width, $bspace+1+$height+$cspace, $bspace);
ncurses_wborder($bl,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($bl, 0, 1, " Rez de chaussée " );

// $tr = Top Right
$br=ncurses_newwin($height, $width, $bspace+1+$height+$cspace, $bspace+$width+$cspace);
ncurses_wborder($br,0,0, 0,0, 0,0, 0,0);
ncurses_mvwaddstr($br, 0, 1, " Divers " );

// $tl => Temperatures de l'etage
$ligne = 2;
while (list($key, $val) = each($sondes['level1'])) {
	ncurses_mvwaddstr($tl, $ligne, 2, sprintf("%-18s",$val['title']));
        ncurses_mvwaddstr($tl, $ligne, 19, sprintf("%6s",$val['value'])." ".$val['unit'] );
	$ligne++;
}

// $br => Température du Rdc
$ligne = 2;
while (list($key, $val) = each($sondes['level0'])) {
        ncurses_mvwaddstr($bl, $ligne, 2, sprintf("%-18s",$val['title']));
        ncurses_mvwaddstr($bl, $ligne, 19, sprintf("%6s",$val['value'])." ".$val['unit'] );
        $ligne++;
}

// $tr => Consommation electrique
$conso=file_get_contents("/tmp/papp");
$conso = intval($conso);
ncurses_mvwaddstr($tr, 2, 2, "Consommation Electrique : ".sprintf("%5s",$conso)." W");
ncurses_mvwaddstr($tr, 4, 2, "Hier : HC xxxxx kwH   zzz €"); 
ncurses_mvwaddstr($tr, 5, 2, "       HP xxxxx kwH   zzz €");
ncurses_mvwaddstr($tr, 6, 2, "          xxxxx kwH   zzz €");

ncurses_mvwaddstr($tr, 8, 2, "Décembre   wwwww KwH  zzz €");
ncurses_mvwaddstr($tr, 9, 2, "Janvier    wwwww KwH  zzz €");

// $br => Divers ( temp ext, ??? )
ncurses_mvwaddstr($br, 2, 2, "Temp Ext : 44.3 C");

ncurses_mvwaddstr($br, 4, 2, "Min : ");
ncurses_wcolor_set($br, 2);

ncurses_mvwaddstr($br, 4, 10, "44.3");
ncurses_wcolor_set($br, 0);

ncurses_mvwaddstr($br, 4, 15, " C  Max : ");
ncurses_wcolor_set($br, 1);
ncurses_mvwaddstr($br, 4, 25, "44.6");
ncurses_wcolor_set($br, 0);
ncurses_mvwaddstr($br, 4, 29, " C");


ncurses_wcolor_set($br, 1);
ncurses_mvwaddstr($br, 5, 2, "RED");
ncurses_wcolor_set($br, 2);
ncurses_mvwaddstr($br, 6, 2, "GREEN");
ncurses_wcolor_set($br, 3);
ncurses_mvwaddstr($br, 7, 2, "YELLOW");
ncurses_wcolor_set($br, 4);
ncurses_mvwaddstr($br, 8, 2, "BLUE");
ncurses_wcolor_set($br, 5);
ncurses_mvwaddstr($br, 9, 2, "MAGENTA");
ncurses_wcolor_set($br, 6);
ncurses_mvwaddstr($br, 10, 2, "CYAN");
ncurses_wcolor_set($br, 7);
ncurses_mvwaddstr($br, 11, 2, "WHITE");

ncurses_wcolor_set($br, 0);



ncurses_wattron($tl, NCURSES_A_BOLD);
ncurses_mvwaddstr($tl, 9, 2, "ligne : $lines" );
ncurses_wattroff($tl, NCURSES_A_BOLD);

ncurses_mvwaddstr($tl, 10, 2, "colonne : $columns" );
/*
ncurses_mvwaddstr($tl, 5, 2, "width : $width" );
ncurses_mvwaddstr($tl, 6, 2, "height : $height" );
*/
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
?>
