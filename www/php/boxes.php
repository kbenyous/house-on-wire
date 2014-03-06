<?php
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);

function insert_box1_temp($db, $id, $show_min_max = false)
{
	insert_box1($db, $id.'.t', $show_min_max, null, $id.'.b', $id.'.s');	
}
function insert_box1_temphum($db, $id, $show_min_max = false)
{
	insert_box1($db, $id.'.t', $show_min_max, $id.'.h', $id.'.b', $id.'.s');
}

function insert_box2_temphum($db, $id, $show_min_max = false)
{
        insert_box2($db, $id.'.t', $show_min_max, $id.'.h', $id.'.b', $id.'.s');
}

function insert_box3_temphumpress($db, $id, $id_press)
{
	insert_box3($db, $id.'.t', $id.'.h', $id_press.'.p', $id.'.b', $id.'.s');
}

function get_simple_data($db, $id)
{
	$query = "SELECT * FROM onewire_data where id = '".$id."' AND date > current_timestamp - interval '20 minutes' order by date desc limit 1";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
        return pg_fetch_array($result);
}

function get_data_variation($db, $id)
{
$query = "select
    round(current, 0) as current,
    case when current>last_hour then 'increase' else 'decrease' end as last_hour_variation
from
    (
    select
        (select case when last_update > current_timestamp - interval '20 minutes' then round(avg(last_value::numeric), 1) else 0::numeric end from onewire where id = '".$id."' group by last_update) as current,
        (select round(avg(value::numeric), 1) from onewire_data where id = '".$id."' and date > current_timestamp - interval '1 hour') as last_hour
    ) a;";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
        return pg_fetch_array($result);


}

function insert_box1($db, $id_temp, $show_min_max = false, $id_hum = null, $id_bat = null, $id_sig = null)
{
	insert_box(1, $db, $id_temp, $show_min_max, $id_hum, $id_bat, $id_sig);
}

function insert_box2($db, $id_temp, $show_min_max = false, $id_hum = null, $id_bat = null, $id_sig = null)
{
        insert_box(2, $db, $id_temp, $show_min_max, $id_hum, $id_bat, $id_sig);
}


function insert_box($type, $db, $id_temp, $show_min_max = false, $id_hum = null, $id_bat = null, $id_sig = null)
{
	global $config;
	// Récupération des valeurs de chaque sonde
	if ($id_temp != null)
	{
		$data = file_get_contents("http://".$config['template']['uri']."/php/get_onewire_data.php?id=".$id_temp);
		$temp_data = json_decode($data, true);
	}
	if($id_hum != null)
	{
		$hum_data = get_data_variation($db, $id_hum);
	}
        if($id_bat != null)
        {
                $bat_data = get_simple_data($db, $id_bat);
        }
        if($id_sig != null)
        {
                $sig_data = get_simple_data($db, $id_sig);
        }	
	
echo ' 
        <div class="box">
                <div class="box_titre'.$type.'">
                        <div class="titre empile">';
echo $temp_data["content"]["name"]["value"];
echo "                   </div>";

if(isset($bat_data) && $bat_data != '')
{
	$level = intval($bat_data["value"]/2);
	echo '                        <div class="empile info"><img src="/image/Battery'.$level.'.png"/></div>';
}
if(isset($sig_data) && $sig_data != '')
{
        $level = intval($sig_data["value"]/2);
        echo '                        <div class="empile info"><img src="/image/SignalLevel'.$level.'.png"/></div>';
}
if($temp_data['content']['deltaPlusOneHour']['direction'] == 'increase')
{
	$img = 'ArrowUpGreen.png';
}
elseif($temp_data['content']['deltaPlusOneHour']['direction'] == 'decrease')
{
        $img = 'ArrowDownRed.png';
}

echo '
                </div>
                <div class="box_body_'.$type.'">
                        <div class="empile">
                                <div class="empile temp'.$type.' popupLink" data-type="graph" data-parameters="%7B%22id%22%3A%22'.$id_temp.'%22%7D">
                                        <div class="empile value">'.$temp_data["content"]["last_value"]["value"].'</div>
                                        <div class="empile"><img src="/image/'.$img.'"/></div>
                                        <div class="empile unit">°C</div>
                                        <div class="subtitle">Température</div>
                                </div>';

if($show_min_max)
{
echo '
                                <div class="empile temp'.$type.'_minmax popupLink" data-type="graphTemp" data-parameters="%7B%22sonde%22%3A%22'.$id_temp.'%22,%22date%22%3A%22'.date("Y").'-01-01%22%7D">
                                        <div>
                                                <div class="empile value">'.$temp_data['content']['deltaPlusOneDay']['max'].'</div>
                                                <div class="empile unit">°C</div>
                                                <div class="subtitle">Max</div>
                                        </div>
                                        <div>
                                                <div class="empile value">'.$temp_data['content']['deltaPlusOneDay']['min'].'</div>
                                                <div class="empile unit">°C</div>
                                                <div class="subtitle">Min</div>
                                        </div>
                                </div>';
}
echo '                        </div>';

if(isset($hum_data) && $hum_data != '')
{
if($hum_data['last_hour_variation'] == 'increase')
{
        $img = 'ArrowUpGrey.png';
}
elseif($hum_data['last_hour_variation'] == 'decrease')
{
        $img = 'ArrowDownGrey.png';
}

echo '                       <div class="separation empile">&nbsp;</div>
                        <div class="empile hum'.$type.' popupLink" data-type="graph" data-parameters="%7B%22id%22%3A%22'.$id_hum.'%22%7D">
                                <div class="empile value">'.$hum_data["current"].'</div>
                                <div class="empile"><img src="/image/'.$img.'"/></div>
                                <div class="empile unit">%</div>
                                <div class="subtitle" >Humidité</div>
                        </div>';
}


echo '                </div>
        </div>';




}

function insert_box3($db, $id_temp, $id_hum, $id_press, $id_bat = null, $id_sig = null)
{
        global $config;
        // Récupération des valeurs de chaque sonde
        if ($id_temp != null)
        {
                $data = file_get_contents("http://".$config['template']['uri']."/php/get_onewire_data.php?id=".$id_temp);
                $temp_data = json_decode($data, true);
        }
        if($id_hum != null)
        {
                $data = file_get_contents("http://".$config['template']['uri']."/php/get_onewire_data.php?id=".$id_hum);
                $hum_data = json_decode($data, true);
	}
	if($id_press != null)
	{
                $press_data = get_data_variation($db, $id_press);
        }
        if($id_bat != null)
        {
                $bat_data = get_simple_data($db, $id_bat);
        }
        if($id_sig != null)
        {
                $sig_data = get_simple_data($db, $id_sig);
        }

echo ' 
        <div class="box">
                <div class="box_titre2">
                        <div class="titre empile">
				Extérieur
	                </div>';

if(isset($bat_data) && $bat_data != '')
{
        $level = intval($bat_data["value"]/2);
        echo '                        <div class="empile info"><img src="/image/Battery'.$level.'.png"/></div>';
}
if(isset($sig_data) && $sig_data != '')
{
        $level = intval($sig_data["value"]/2);
        echo '                        <div class="empile info"><img src="/image/SignalLevel'.$level.'.png"/></div>';
}
if($temp_data['content']['deltaPlusOneHour']['direction'] == 'increase')
{
        $img = 'ArrowUpGreen.png';
}
elseif($temp_data['content']['deltaPlusOneHour']['direction'] == 'decrease')
{
        $img = 'ArrowDownRed.png';
}
echo '
                </div>
                <div class="box_body_3">
                        <div class="empile">
                                <div class="empile temp3 popupLink" data-type="graph" data-parameters="%7B%22id%22%3A%22'.$id_temp.'%22%7D">
                                        <div class="empile value">'.$temp_data["content"]["last_value"]["value"].'</div>
                                        <div class="empile"><img src="/image/'.$img.'"/></div>
                                        <div class="empile unit">°C</div>
                                        <div class="subtitle">Température</div>
                                </div>';
echo '
                                <div class="empile popupLink" data-type="graphTemp" data-parameters="%7B%22sonde%22%3A%22'.$id_temp.'%22,%22date%22%3A%22'.date("Y").'-01-01%22%7D">
                                        <div class="temp3_minmax">
                                                <div class="empile value">'.$temp_data['content']['deltaPlusOneDay']['max'].'</div>
                                                <div class="empile unit">°C</div>
                                                <div class="subtitle">Max</div>
                                        </div>
                                        <div class="temp3_minmax">
                                                <div class="empile value">'.$temp_data['content']['deltaPlusOneDay']['min'].'</div>
                                                <div class="empile unit">°C</div>
                                                <div class="subtitle">Min</div>
                                        </div>
                                </div>';

if($hum_data['content']['deltaPlusOneHour']['direction'] == 'increase')
{
        $img = 'ArrowUpGrey.png';
}
elseif($hum_data['content']['deltaPlusOneHour']['direction'] == 'decrease')
{
        $img = 'ArrowDownGrey.png';
}

echo '                  </div>
                        <div class="separation empile">&nbsp;</div>
                        <div class="empile hum_press">
                                <div>
                                        <div class="empile hum3 popupLink" data-type="graph" data-parameters="%7B%22id%22%3A%22'.$id_hum.'%22%7D">
				                <div class="empile value">'.$hum_data["content"]["last_value"]["value"].'</div>
				                <div class="empile"><img src="/image/'.$img.'"/></div>
				                <div class="empile unit">%</div>
				                <div class="subtitle">Humidité</div>
                                        </div>
		                        <div class="empile popupLink" data-type="graphTemp" data-parameters="%7B%22sonde%22%3A%22'.$id_hum.'%22,%22date%22%3A%22'.date("Y").'-01-01%22%7D">
		                                <div class="hum3_minmax">
		                                        <div class="empile value">'.$hum_data['content']['deltaPlusOneDay']['max'].'</div>
		                                        <div class="empile unit">%</div>
		                                        <div class="subtitle">Max</div>
		                                </div>
		                                <div  class="hum3_minmax">
		                                        <div class="empile value">'.$hum_data['content']['deltaPlusOneDay']['min'].'</div>
		                                        <div class="empile unit">%</div>
		                                        <div class="subtitle">Min</div>
		                                </div>
		                        </div>
                                </div>';

if($press_data['last_hour_variation'] == 'increase')
{
        $img = 'ArrowUpGrey.png';
}
elseif($press_data['last_hour_variation'] == 'decrease')
{
        $img = 'ArrowDownGrey.png';
}
echo '                                <div class="separation_h">&nbsp;</div>
                                <div class="press3 popupLink" data-type="graph" data-parameters="%7B%22id%22%3A%22'.$id_press.'%22%7D">
			                <div class="empile value">'.$press_data["current"].'</div>
			                <div class="empile"><img src="/image/'.$img.'"/></div>
			                <div class="empile unit">HPa</div>
			                <div class="subtitle">Pression</div>
                                </div>

                        </div>
                </div>
        </div>
';

}

function insert_box_rain($db, $id)
{

$query = "
SELECT
        (max_day-max_yest)::integer as rain_day,
        (max_yest-min_yest)::integer as rain_yest,
        (max_day-min_week)::integer as rain_week,
        (max_day-min_month)::integer as rain_month,
	date_trunc('day', current_date)::date as day,
	date_trunc('day', current_date - interval '1 day')::date as yest,
	date_trunc('month', current_date)::date as week,
        date_trunc('year', current_date)::date as month

FROM (
        SELECT
                max(case when date_trunc('day', current_date) = date_trunc('day', date) then value end)::numeric as max_day,
                max(case when date_trunc('day', current_date - interval '2 day') = date_trunc('day', date) then value end)::numeric as min_yest,
                max(case when date_trunc('day', current_date - interval '1 day') = date_trunc('day', date) then value end)::numeric as max_yest,
                min(case when date_trunc('day', current_date - interval '8 day') < date_trunc('day', date) then value end)::numeric as min_week,
                min(case when date_trunc('month', current_date) = date_trunc('month', date) then value end)::numeric as min_month
        FROM (
                SELECT value::numeric, date
                FROM onewire_data
                WHERE
                        id = '".$id.".rt'
                        and date > date_trunc('month', current_date) - interval '8 day' 
        ) a
) b";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
        $rain_data =  pg_fetch_array($result);

        $bat_data = get_simple_data($db, $id.'.b');
        $sig_data = get_simple_data($db, $id.'.s');


echo '
       <div class="box">
                <div class="box_titre2" style="min-width: 200px;">
                        <div class="titre empile">
                                Pluviométrie
                        </div>';
if(isset($bat_data) && $bat_data != '')
{
        $level = intval($bat_data["value"]/2);
        echo '                        <div class="empile info"><img src="/image/Battery'.$level.'.png"/></div>';
}
if(isset($sig_data) && $sig_data != '')
{
        $level = intval($sig_data["value"]/2);
        echo '                        <div class="empile info"><img src="/image/SignalLevel'.$level.'.png"/></div>';
}


echo '
                </div>
                <div class="box_body_3">
			<div class="empile pluiv" >
				<div class="popupLink" data-type="graphRain" data-parameters="%7B%22type%22%3A%22hourly_rain%22,%22date%22%3A%22'.$rain_data['day'].'%22%7D">
                        	        <div class="empile value">'.$rain_data['rain_day'].'</div>
	                                <div class="empile unit">mm</div>
        	                        <div class="subtitle">Aujourd\'hui</div>
				</div>
                                <div class="popupLink" data-type="graphRain" data-parameters="%7B%22type%22%3A%22daily_rain%22,%22date%22%3A%22'.$rain_data['week'].'%22%7D">
                                        <div class="empile value">'.$rain_data['rain_week'].'</div>
                                        <div class="empile unit">mm</div>
                                        <div class="subtitle">7 jours</div>
                                </div>
			</div>
                        <div class="empile pluiv">
                                <div class="popupLink" data-type="graphRain" data-parameters="%7B%22type%22%3A%22hourly_rain%22,%22date%22%3A%22'.$rain_data['yest'].'%22%7D">
                                        <div class="empile value">'.$rain_data['rain_yest'].'</div>
                                        <div class="empile unit">mm</div>
                                        <div class="subtitle">Hier</div>
                                </div>
                                <div class="popupLink" data-type="graphRain" data-parameters="%7B%22type%22%3A%22monthly_rain%22,%22date%22%3A%22'.$rain_data['month'].'%22%7D">
                                        <div class="empile value">'.$rain_data['rain_month'].'</div>
                                        <div class="empile unit">mm</div>
                                        <div class="subtitle">Mois</div>
                                </div>
                        </div>
                </div>
        </div>';

}

function insert_box_elect($db)
{
	$query = "
	  SELECT
		round((hchc::numeric+hchp::numeric)/1000, 1) as hchchp,
		round(cout_hc+cout_hp+cout_abo, 1) as cout_total
	  FROM
		teleinfo_cout
	  WHERE 
		date = current_date - interval '1 day'";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
        $data_veille =  pg_fetch_array($result);

	$query = "
		select 
			round(sum((hchc::numeric+hchp::numeric)/1000), 0) as hchchp, 
			round(sum(cout_hc+cout_hp+cout_abo), 1) as cout_total 
		from teleinfo_cout 
		where 
		case 
			when EXTRACT(DAY from current_date) = 1 
				THEN date_trunc('month', date) = date_trunc('month', current_date -interval '1day')
				ELSE date_trunc('month', date) = date_trunc('month', current_date)
		end";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
        $data_mois =  pg_fetch_array($result);

	$query = "select papp from teleinfo order by date desc limit 1;";
        $result = pg_query( $db, $query ) or die ("Erreur SQL sur recuperation des valeurs: ". pg_result_error() );
        $data_inst =  pg_fetch_array($result);



echo '
        <div class="box">
                        <div class="box_titre2 empile">
                                <div class="titre">Conso. Electricité</div>
                        </div>
                <div class="box_body_4">
                                <div class="elect_i popupLink" data-type="graphPuissanceApparente" data-parameters="undefined" >
                                        <div class="empile value">'.$data_inst['papp'].'</div>
                                        <div class="empile unit">W</div>
                                        <div class="subtitle">Instantanée</div>
                                </div>
                       <div class="separation_h">&nbsp;</div>

                        <div style="text-align:right;" class="popupLink" data-type="graphConsoElect" data-parameters="undefined">
                                <div class="empile elect" >
                                        <div class="empile value">'.$data_veille['hchchp'].'</div>
                                        <div class="empile unit">Kw</div>
                                        <div class="subtitle">Veille</div>
                                </div>
                                <div class="empile elect elect_2">
                                        <div class="empile value">'.$data_veille['cout_total'].'</div>
                                        <div class="empile unit">€</div>
                                        <div class="subtitle">&nbsp;</div>
                                </div>
                                <div></div>
                                <div class="empile elect" >
                                        <div class="empile value">'.$data_mois['hchchp'].'</div>
                                        <div class="empile unit">Kw</div>
                                        <div class="subtitle">Mois</div>
                                </div>
                                <div class="empile elect elect_2">
                                        <div class="empile value">'.$data_mois['cout_total'].'</div>
                                        <div class="empile unit">€</div>
                                        <div class="subtitle">&nbsp;</div>
                                </div>

                        </div>
                </div>
        </div>
';
}
?>
