<?php
setlocale(LC_ALL , "fr_FR" );
date_default_timezone_set("Europe/Paris");

// Adapté du code de Domos.
// cf . http://vesta.homelinux.net/wiki/teleinfo_papp_jpgraph.html

// Lecture du fichier de conf
$config = parse_ini_file("/etc/hm-automation/hm-automation.ini", true); 

// prix du kWh :
// prix TTC au 1/01/2012 :
$prixHP = 0.1312;
$prixHC = 0.0895;
// Abpnnement pour disjoncteur 45 A
$abo_annuel = 191.59;

// Base de donnée Téléinfo:
/*
Format de la table:
timestamp   rec_date   rec_time   adco     optarif isousc   hchp     hchc     ptec   inst1   inst2   inst3   imax1   imax2   imax3   pmax   papp   hhphc   motdetat   ppot   adir1   adir2   adir3
1234998004   2009-02-19   00:00:04   700609361116   HC..   20   11008467   10490214   HP   1   0   1   18   23   22   8780   400   E   000000     00   0   0   0
1234998065   2009-02-19   00:01:05   700609361116   HC..   20   11008473   10490214   HP   1   0   1   18   23   22   8780   400   E   000000     00   0   0   0
1234998124   2009-02-19   00:02:04   700609361116   HC..   20   11008479   10490214   HP   1   0   1   18   23   22   8780   390   E   000000     00   0   0   0
1234998185   2009-02-19   00:03:05   700609361116   HC..   20   11008484   10490214   HP   1   0   0   18   23   22   8780   330   E   000000     00   0   0   0
1234998244   2009-02-19   00:04:04   700609361116   HC..   20   11008489   10490214   HP   1   0   0   18   23   22   8780   330   E   000000     00   0   0   0
1234998304   2009-02-19   00:05:04   700609361116   HC..   20   11008493   10490214   HP   1   0   0   18   23   22   8780   330   E   000000     00   0   0   0
1234998365   2009-02-19   00:06:05   700609361116   HC..   20   11008498   10490214   HP   1   0   0   18   23   22   8780   320   E   000000     00   0   0   0
*/

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta content="no-cache" http-equiv="Pragma">
    <title>graph conso électrique</title>
<script type="text/javascript" src="./js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="./js/jquery-ui-1.8.5.custom.min.js"></script>
<link rel="stylesheet" href="./css/themes/ui-lightness/jquery.ui.all.css">

<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
<script type="text/javascript" src="http://code.highcharts.com/stock/highstock.js"></script>

<script type='text/javascript'>

var start = <?php echo time()*1000; ?>;

jQuery(function($) {

  Highcharts.setOptions({
    lang: {
      months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
      weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
      decimalPoint: ',',
      thousandsSep: '.',
      rangeSelectorFrom: 'Du',
      rangeSelectorTo: 'au'
    },
    legend: {
      enabled: false
    },
    global: {
      useUTC: false
    }
  });
});
</script>  
    
<?php
 
setlocale   ( LC_ALL , "fr_FR" );
 
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur MySql");

// Récupération à l'aide de la base de données de bornes de requete
$query = "SELECT current_timestamp - interval '1 day' as b1day, current_timestamp - interval '3 days' as b3day";
$result=pg_query($db, $query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . pg_error() . " !<br>");
$borne = pg_fetch_array($result);

$result=pg_query($db, $query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . pg_error() . " !<br>");
$borne = pg_fetch_array($result);

/*    Graph consomation w des 24 dernières heures + en parrallèle consomation d'Hier    */

// INITIALISATION DES VARIABLES

$courbe_titre[0]="Heure Pleines";
$courbe_min[0]=5000;
$courbe_max[0]=0;
$courbe_titre[1]="Heure Creuses";
$courbe_min[1]=5000;
$courbe_max[1]=0;

$date_deb=0; // date du 1er enregistrement
$date_fin=time();


$array_HP = array();
$array_HC = array();
$array_JPrec = array();
$array_Min = array();
$array_Max = array();
$navigator = array();

// Composition des différentes courbes :
//  - HP avec une precision de 1sec sur 24H / 1 min sur 72H / 5min sur plus
//  - HC idem
//  - JPrec avec une precision de 1 min augmenter de 24H
//  - Min 1 valeur par jour
//  - Max 1 valeur par jour
//  - Navigator avec une precision de 5 min

$query="SELECT date + interval '1 day' as date, EXTRACT(EPOCH FROM (date + interval '1 day'))*1000 as timestamp, papp::integer from teleinfo_minute where date between current_timestamp - interval '2 days' and current_timestamp - interval '1 day' order by date asc";
 
$result=pg_query($db, $query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . pg_error() . " !<br>");
while ($row = pg_fetch_array($result)) 
{
  array_push ( $array_JPrec , array( intval($row['timestamp']), intval($row['papp']) ));
}

$query = "SELECT 
                date,
                timestamp*1000 as timestamp,
                papp::integer as papp,
                ptec
        FROM
        (
                SELECT date, extract(epoch from date) as timestamp, papp, ptec from teleinfo_histo where date <= '".$borne['b3day']."'
                UNION
                SELECT date, extract(epoch from date) as timestamp, papp, ptec from teleinfo where date > '".$borne['b1day']."'
                UNION           
                SELECT date, extract(epoch from date) as timestamp, papp, ptec from teleinfo_minute where date <= '".$borne['b1day']."' AND date > '".$borne['b3day']."'
        ) foo
        ORDER BY date ASC";

$result=pg_query($db, $query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . pg_error() . " !<br>");

$prev_ptec = null;
$prev_ts = null;

while ($row = pg_fetch_array($result))
{
	if ($date_deb==0) 
	{
		$date_deb = $row["timestamp"];
	}
	
	$ts = intval($row["timestamp"]);

	$val = intval($row["papp"]);

	if ( $row["ptec"] == "HP" )      // Test si heures pleines.
  	{
		if($prev_ptec == 'HC')
		{
			array_push ( $array_HP , array($prev_ts, null ));
		 	array_push ( $array_HC , array($ts, null ));
		}
        	array_push ( $array_HP , array($ts, $val ));
	        if ($courbe_max[0]<$val) {$courbe_max[0] = $val; $courbe_maxdate[0] = $ts;};
        	if ($courbe_min[0]>$val) {$courbe_min[0] = $val; $courbe_mindate[0] = $ts;};
	}
	elseif ( $row["ptec"] == "HC" )
	{
                if($prev_ptec == 'HP')
                {
                        array_push ( $array_HC , array($prev_ts, null ));
                        array_push ( $array_HP , array($ts, null ));
                }

                array_push ( $array_HC , array($ts, $val ));
	        if ($courbe_max[1]<$val) {$courbe_max[1] = $val; $courbe_maxdate[1] = $ts;};
        	if ($courbe_min[1]>$val) {$courbe_min[1] = $val; $courbe_mindate[1] = $ts;};

        }

	$prev_ptec = $row['ptec'];
	$prev_ts = $ts;
}
$date_deb = $date_deb/1000;
$date_fin = $ts/1000;

if ($courbe_max[1] > $courbe_max[0]) $plotlines_max = $courbe_max[1]; else $plotlines_max = $courbe_max[0];
if ($courbe_min[1] > $courbe_min[0]) $plotlines_min = $courbe_min[0]; else $plotlines_min = $courbe_min[1];

// Creation de Navigator
$query = "SELECT 
                date,
                timestamp*1000 as timestamp,
                papp::integer as papp
        FROM
        (
                SELECT date, extract(epoch from date) as timestamp, papp from teleinfo_histo where date < '".$borne['b1day']."'
                UNION           
		SELECT
		    to_timestamp( trunc(EXTRACT(EPOCH FROM date)/300)*300 ) as date, 
         	    trunc(EXTRACT(EPOCH FROM date)/300)*300  as timestamp, 	
    		    avg(papp) as papp
		FROM    
		    teleinfo 
		WHERE 
		    date > '".$borne['b1day']."' 
		GROUP BY
			trunc(EXTRACT(EPOCH FROM date)/300)*300 
	) foo
        ORDER BY date ASC";


$result=pg_query($db, $query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . pg_error() . " !<br>");

while ($row = pg_fetch_array($result))
{
 array_push ( $navigator , array(intval($row["timestamp"]), intval($row['papp']) ));
}





$ddannee = date("Y",$date_deb);
$ddmois = date("m",$date_deb);
$ddjour = date("d",$date_deb);
$ddheure = date("G",$date_deb); //Heure, au format 24h, sans les zéros initiaux
$ddminute = date("i",$date_deb);

$ddannee_fin = date("Y",$date_fin);
$ddmois_fin = date("m",$date_fin);
$ddjour_fin = date("d",$date_fin);
$ddheure_fin = date("G",$date_fin); //Heure, au format 24h, sans les zéros initiaux
$ddminute_fin = date("i",$date_fin);

//$datetext = "$ddjour/$ddmois/$ddannee  $ddheure:$ddminute au $ddjour_fin/$ddmois_fin/$ddannee_fin  $ddheure_fin:$ddminute_fin";
$datetext = "$ddjour/$ddmois  $ddheure:$ddminute au $ddjour_fin/$ddmois_fin  $ddheure_fin:$ddminute_fin";
?>          

<script type="text/javascript">

$(function() {
    

  // Create the chart
  window.chart = new Highcharts.StockChart({
    chart : {
      renderTo : 'container',
      events: {
        load: function(chart) {
          this.setTitle(null, {
            text: 'Construit en '+ (new Date() - start) +'ms'
          });
        }
      },
      borderColor: '#EBBA95',
      borderWidth: 2,
      borderRadius: 10,
      ignoreHiddenSeries: false
    },
    subtitle: {
      text: 'Construit en...'
    },
    rangeSelector : {
      buttons : [{
        type : 'hour',
        count : 1,
        text : '1h'
      },{
        type : 'hour',
        count : 3,
        text : '3h'
      },{
        type : 'hour',
        count : 6,
        text : '6h'
      },{
        type : 'hour',
        count : 12,
        text : '12h'
      },{
        type : 'day',
        count : 1,
        text : '24h'
      }],
      selected : 4,
      inputEnabled : false
    },
    title : {
        text : '<?php echo "Graph du $datetext";?>'
    },
    xAxis: { 
      type: 'datetime',
       dateTimeLabelFormats: {
          hour: '%H:%M',
        	day: '%H:%M',
        	week: '%H:%M',
          month: '%H:%M'
       }
    },
    yAxis: [{
      labels: {
        formatter: function() {
           return this.value +' w';
        }
      },
      title: {
        text: 'Watt'
      },
      lineWidth: 2,
      showLastLabel: true,
      min: 0,
      alternateGridColor: '#FDFFD5',
      minorGridLineWidth: 0,
      plotLines : [{ // lignes min et max
        value : <?php echo $plotlines_min; ?>,
        color : 'green',
        dashStyle : 'shortdash',
        width : 2,
        label : {
          text : 'minimum <?php echo $plotlines_min; ?>w'
        }
      }, {
        value : <?php echo $plotlines_max; ?>,
        color : 'red',
        dashStyle : 'shortdash',
        width : 2,
        label : {
          text : 'maximum <?php echo $plotlines_max; ?>w'
        }
      }]
    }],
  
    series : [{
        name : '<?php echo $courbe_titre[1]." / min ".$courbe_min[1]." max ".$courbe_max[1]; ?>',
        data : array_HC,
        id: 'HC',
        type : 'area',
        threshold : null,
        tooltip : {
            yDecimals : 0
        },
	dataGrouping : {
	    forced: true
	}
    }, {
        name : '<?php echo $courbe_titre[0]." / min ".$courbe_min[0]." max ".$courbe_max[0]; ?>',
        data : array_HP,
        id: 'HP',
	type : 'area',
        threshold : null,
        tooltip : {
            yDecimals : 0
        },
        dataGrouping : {
            forced: true
        }

    },{
      data: array_JPrec,
      name : 'Hier',
      type: 'spline',
      width : 1,
      shape: 'squarepin'
    }], 
    legend: {
      enabled: true,
      borderColor: 'black',
      borderWidth: 1,
      shadow: true
    },
    navigator: {
      baseSeries: 2,
      top: 490,
      menuItemStyle: {
        fontSize: '10px'
      },
      series: {
        name: 'navigator',
        data: navigator
      }
    },
    scrollbar: { // scrollbar "stylée" grise 
      barBackgroundColor: 'gray',
      barBorderRadius: 7,
      barBorderWidth: 0,
      buttonBackgroundColor: 'gray',
      buttonBorderWidth: 0,
      buttonBorderRadius: 7,
      trackBackgroundColor: 'none',
      trackBorderWidth: 1,
      trackBorderRadius: 8,
      trackBorderColor: '#CCC'      
    },
  });
});


var array_HP = <?php echo json_encode($array_HP); ?>;
var array_HC = <?php echo json_encode($array_HC); ?>;
var array_JPrec = <?php echo json_encode($array_JPrec); ?>;
var navigator = <?php echo json_encode($navigator); ?>;
  

</script>


<?php

/*    Graph cout sur période [8jours|8semaines|8mois|1an]    */

$periode = $_GET['periode'] ;
 
if (! $periode) { $periode = "8jours" ; } ;
switch ($periode) {
  case "8jours":
    $nbjours = 7 ;                // nb jours.
    $xlabel = "8 jours" ;
    $periodesecondes = $nbjours*24*3600 ;          // Periode en secondes.
    $timestampheure = gmmktime(0,0,0,date("m"),date("d"),date("Y"));    // Timestamp courant.
    $timestampdebut = $timestampheure - $periodesecondes ;      // Recule de $periodesecondes.
    $dateformatsql = "%a %e" ;
    $abonnement = $abo_annuel / 365;
    break;
  case "8semaines":
    $timestampdebut = gmmktime(0,0,0, date("m")-2, date("d"), date("Y"));
    $nbjour=1 ;
    while ( date("w", $timestampdebut) != 1 )  // Avance d'un jour tant que celui-ci n'est pas un lundi. 
    {
      $timestampdebut = gmmktime(0,0,0, date("m")-2, date("d")+$nbjour, date("Y"));
      $nbjour++ ;
    }
    $xlabel = "8 semaines" ;
    $dateformatsql = "sem %v" ;
    $abonnement = $abo_annuel / 52;
    break;
  case "8mois":
    $timestampdebut = gmmktime(0,0,0, date("m")-7, 1, date("Y"));
    $xlabel = "8 mois" ;
    $dateformatsql = "%b" ;
    $abonnement = $abo_annuel / 12;
    break;
  case "1an":
    $timestampdebut = gmmktime(0,0,0, date("m")-11, 1, date("Y"));
    $xlabel = "1 an" ;
    $dateformatsql = "%b" ;
    $abonnement = $abo_annuel / 12;
    break;
  default:
    die("Periode erronée, valeurs possibles: [8jours|8semaines|8mois|1an] !");
    break;
}

//$query="SET lc_time_names = 'fr_FR'" ;  // Pour afficher date en français dans MySql.
//mysql_query($query) ; 
$query="SELECT rec_date, date_trunc('day', rec_date) AS periode ,
  ROUND( ((MAX(hchp) - MIN(hchp)) / 1000) ,1 ), 
  ROUND( ((MAX(hchc) - MIN(hchc)) / 1000) ,1 )  
  FROM teleinfo 
  WHERE timestamp > '$timestampdebut'
  GROUP BY rec_date, date_trunc('day', rec_date)
  ORDER BY rec_date" ; 

$result=pg_query($db, $query) or die ("<b>Erreur</b> dans la requète <b>" . $query . "</b> : "  . pg_error() . " !<br>"); 
$num_rows = pg_num_rows($result) ;
$no = 0 ;
while ($row = pg_fetch_array($result)) 
{
  $date[$no] = $row["rec_date"] ;
  $timestp[$no] = $row["periode"] ;
  $kwhhp[$no]=floatval(str_replace(",", ".", $row[2]));
  $kwhhc[$no]=floatval(str_replace(",", ".", $row[3]));
  $no++ ;
}
$date_digits_dernier_releve=explode("-", $date[count($date) -1]) ; 
$date_dernier_releve =  Date('d/m/Y', gmmktime(0,0,0, $date_digits_dernier_releve[1] ,$date_digits_dernier_releve[2], $date_digits_dernier_releve[0])) ;

pg_free_result($result) ;

pg_close($db) ;
?>

    <script type="text/javascript">
<?php
// cf. http://www.phpcs.com/codes/PHP-TO-JS-CONVERSION-VARIABLE-PHP-VERS-JAVASCRIPT_13232.aspx
function php2js ($var) {
    if (is_array($var)) {
        $res = "[";
        $array = array();
        foreach ($var as $a_var) {
            $array[] = php2js($a_var);
        }
        return "[" . join(",", $array) . "]";
    }
    elseif (is_bool($var)) {
        return $var ? "true" : "false";
    }
    elseif (is_int($var) || is_integer($var) || is_double($var) || is_float($var)) {
        return $var;
    }
    elseif ($var=="null") {
        return "" . addslashes(stripslashes($var)) . "";
    }if (is_string($var)) {
        return "\"" . addslashes(stripslashes($var)) . "\"";
    }
    // autres cas: objets, on ne les gère pas
    return FALSE;
}

$ddannee = date("Y",$date_deb);
$ddmois = date("m",$date_deb);
$ddjour = date("d",$date_deb);
$ddheure = date("G",$date_deb); //Heure, au format 24h, sans les zéros initiaux
$ddminute = date("i",$date_deb);

$date_deb_UTC=$date_deb*1000;

$datetext = "$ddjour/$ddmois/$ddannee  $ddheure:$ddminute";
$ddmois=$ddmois-1; // nécessaire pour Date.UTC() en javascript qui a le mois de 0 à 11 !!!

$mnt_kwhhp = 0;
$mnt_kwhhc = 0;
$mnt_abonnement = 0;
$i = 0;
while ($i < count($kwhhp)) 
{
  $mnt_kwhhp += $kwhhp[$i] * $prixHP;
  $mnt_kwhhc += $kwhhc[$i] * $prixHC;
  $mnt_abonnement += $abonnement;
  $i++ ;
}

$mnt_total = $mnt_abonnement + $mnt_kwhhp + $mnt_kwhhc; 

?>
      
var datakwhhp = <?php echo php2js($kwhhp); ?>;
var datakwhhc = <?php echo php2js($kwhhc); ?>;
var datatimestp = <?php echo php2js($timestp); ?>;

var prixHP = <?php echo $prixHP; ?>;
var prixHC = <?php echo $prixHC; ?>;
var abonnement = <?php echo $abonnement; ?>;

var totalHP = 0;
var totalHC = 0;
var totalprix = 0;

    
var chart_elec2;
$(document).ready(function() {
  chart_elec2 = new Highcharts.Chart({
    chart: {
      renderTo: 'container2',
      defaultSeriesType: 'column', 
      ignoreHiddenSeries: false,
    },
    title: {
      text: 'Consomation sur <?php echo $xlabel;?>'
    },
    xAxis: [{
       categories: datatimestp
    }],                                           
    yAxis: {
      title: {
        text: 'kWh'
      },
      min: 0,
      minorGridLineWidth: 0,
      labels: { formatter: function() { return this.value +' kWh' } }
    },
    tooltip: {
      formatter: function() {
        totalHP=prixHP*((this.series.name == 'Heures Pleines')? this.y :this.point.stackTotal-this.y);
        totalHC=prixHC*((this.series.name == 'Heures Creuses')? this.y :this.point.stackTotal-this.y);
        totalprix=Highcharts.numberFormat(( totalHP + totalHC + abonnement ),2);
        tooltip = '<b> '+ this.x +' <b><br /><b>'+ this.series.name +' '+ Highcharts.numberFormat(this.y, 2) +' kWh<b><br />';
        tooltip += 'HP : '+ Highcharts.numberFormat(totalHP,2) + ' Euro / HC : ' + Highcharts.numberFormat(totalHC,2) + ' Euro<br />';
        tooltip += 'Abonnement sur la période : '+ Highcharts.numberFormat(abonnement,2) +' Euro<br />';
        tooltip += '<b> Total: '+ totalprix +' Euro<b>';
        return tooltip;
      }
    },
    plotOptions: {
      column: {
        stacking: 'normal',
      }
    },
    series: [{
      name : 'Heures Pleines',
      data : datakwhhp ,
      dataLabels: {
        enabled: true,
        color: '#FFFFFF',
        y: 13,
        formatter: function() {
          return this.y;
        },
        style: {
          font: 'normal 13px Verdana, sans-serif'
        }
      },
      type: 'column'
    }, { 
      name : 'Heures Creuses',
      data : datakwhhc ,
      dataLabels: {
        enabled: true,
        color: '#FFFFFF',
        y: 13,
        formatter: function() {
          return this.y;
        },
        style: {
          font: 'normal 13px Verdana, sans-serif'
        }
      }

     }],
     navigation: {
       menuItemStyle: {
         fontSize: '10px'
       }
     }
   });  
 });
 
     </script>
 
 <style>
 input[type="submit"]{
 width:100px;
 height:30px;
 margin-left:20px;
 font-size:1em;
 font-weight:bold;
 border:none;
 color:#cecece;
 text-shadow:0px -1px 0px #000;
 background:#1f2026;
 background:-moz-linear-gradient(top,#1f2026,#15161a);
 background:-webkit-gradient(linear,left top,left bottom,from(#1f2026),to(#15161a));
 -webkit-border-radius:5px;
    -moz-border-radius:5px;
         border-radius:5px;
 -webkit-box-shadow:0px 0px 1px #000;
    -moz-box-shadow:0px 0px 1px #000;
         box-shadow:0px 0px 1px #000;
 }
 input[type="submit"]:hover{
 background:#343640;
 background:-moz-linear-gradient(top,#343640,#15161a);
 background:-webkit-gradient(linear,left top,left bottom,from(#343640),to(#15161a));
 }
 </style>
     
   </head>
   <body>
 
     <div id="container" style="width: 90%; height: 600px; margin: 0 auto"></div>
     <br /><br />
     <form method="GET" action="<?php echo $_SERVER['PHP_SELF'];?>" style="text-align: center;" >
       <input type="submit" value="8jours" name="periode">
       <input type="submit" value="8semaines" name="periode">
       <input type="submit" value="8mois" name="periode">
       <input type="submit" value="1an" name="periode">
     </form>
     <br />
     <div style="text-align: center;" ><?php echo "Coût sur la période ".round($mnt_total,2)." Euro<br />( Abonnement : ".round($mnt_abonnement,2)." + HP : ".round($mnt_kwhhp,2)." + HC 
: ".round($mnt_kwhhc,2)." )";?></div>
     <br />
     <div id="container2" style="width: 90%; height: 400px; margin: 0 auto"></div>
         
   </body>
 </html>

