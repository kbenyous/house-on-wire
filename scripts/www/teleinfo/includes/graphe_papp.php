<?php
// Lecture du fichier de conf
$config = parse_ini_file("/etc/house-on-wire/house-on-wire.ini", true);
$db = pg_connect("host=".$config['bdd']['host']." port=".$config['bdd']['port']." dbname=".$config['bdd']['dbname']." user=".$config['bdd']['username']." password=".$config['bdd']['password']." options='--client_encoding=UTF8'") or die("Erreur de connexion au serveur SQL");

// Recuperation puissance maximale autorisee
$query = "SELECT isousc FROM teleinfo ORDER BY date DESC LIMIT 1;";
$result = pg_query( $db, $query ) or die ("Erreur SQL sur selection MIN() et MAX() de PAPP: ". pg_error() );
$row = pg_fetch_array( $result );
$papp_max_subscr = 220*$row[ 'isousc' ];

$query = "SELECT 1000*EXTRACT(EPOCH FROM date) AS end FROM teleinfo ORDER BY date DESC LIMIT 1;";
$result = pg_query( $db, $query ) or die ("Erreur SQL: ". pg_error() );
$row = pg_fetch_array( $result );
$x_end = $row['end'];

$query = "SELECT 1000*EXTRACT(EPOCH FROM date) AS start FROM teleinfo ORDER BY date ASC LIMIT 1;";
$result = pg_query( $db, $query ) or die ("Erreur SQL: ". pg_error() );
$row = pg_fetch_array( $result );
$x_start = $row['start'];

?>

<script type="text/javascript">
var chart;
$(document).ready(function() {
    // Create the chart
    chart = new Highcharts.StockChart({
        chart : {
            renderTo : 'graphe_papp',
            zoomType: 'xy',
            events: {
                load: function() {
                    this.showLoading( 'Telechargement des donnees...' );
                    $.getJSON( 'data/papp_all.php?jqueryCallback=?', null, function( data, status ) {
                        chart.hideLoading();
                        chart.get( 'papp_series' ).setData( data[ 'papp' ], false );
                        $.each( data['hc_periodes'], function(i,band) {
                            chart.xAxis[0].addPlotBand( band ); 
                        });
                        chart.redraw();
                    });
                    $.getJSON( 'data/papp_min_global.php?jqueryCallback=?', function(data) {
                        chart.addSeries( {
                            type: 'flags',
                            id : 'min_global_series',
                            data : [ data ],
                            onSeries: 'papp_series',
                            color: 'green',
                            fillColor: '#00D000',
                            style: { color: 'white' },
                            states: { hover: { fillColor: '#00B000' } }
                        });
                    });
                    $.getJSON( 'data/papp_min_24h.php?jqueryCallback=?', function(data) {
                        chart.addSeries( {
                            type: 'flags',
                            id : 'min_24h_series',
                            data : [ data ],
                            onSeries: 'papp_series',
                            color: 'green',
                            fillColor: '#00D000',
                            style: { color: 'white' },
                            states: { hover: { fillColor: '#00B000' } }
                        });
                    });
                    $.getJSON( 'data/papp_max_global.php?jqueryCallback=?', function(data) {
                        chart.addSeries( {
                            type: 'flags',
                            id : 'max_global_series',
                            data : [ data ],
                            onSeries: 'papp_series',
                            color: 'red',
                            fillColor: '#D00000',
                            style: { color: 'white' },
                            states: { hover: { fillColor: '#B00000' } }
                        });
                    });
                    $.getJSON( 'data/papp_max_24h.php?jqueryCallback=?', function(data) {
                        chart.addSeries( {
                            type: 'flags',
                            id : 'max_24h_series',
                            data : [ data ],
                            onSeries: 'papp_series',
                            color: 'red',
                            fillColor: '#D00000',
                            style: { color: 'white' },
                            states: { hover: { fillColor: '#B00000' } }
                        });
                    });
                }
            }
        },
        rangeSelector: {
            buttons: [
                { type: 'minute', count:5, text: '5min' },
                { type: 'minute', count:60, text: '1h' },
                { type: 'day', count:1, text: '24h' },
                { type: 'week', count:1, text: '7j' },
                { type: 'all', text: 'tout' }
            ],
            selected: 2
        },
        xAxis: {
            ordinal: false,
            min: <?php echo $x_start; ?>,
            max: <?php echo $x_end; ?>,
            minorTickInterval: 24*3600*1000,
            minorGridLineColor: 'rgba(128,255,128,0.3)',
            minorGridLineWidth: 8
        },
        yAxis : {
            offset: 70,
            min:0, max: <?php echo $papp_max_subscr; ?>,
            labels: { formatter: function() { return this.value+'w'; } },
            plotBands: [
                { color: 'rgba(255,102,0,0.3)', from: <?php echo $papp_max_subscr*.9; ?>, to: <?php echo $papp_max_subscr; ?>, label: { text: '90-100%', align: 'right', x: -10, style: { color: 'rgb(255,102,0)' } } },
                { color: 'rgba(144,0,0,0.3)', from: <?php echo $papp_max_subscr; ?>, to: <?php echo $papp_max_subscr*1.2; ?>, label: { text: 'Depassement', align: 'right', x: -10, style: { color: 'rgb(255,0,0)' } } },
            ],
        },
        series : [{
            name : 'PAPP',
            id : 'papp_series',
            type : 'area',
            data : [ <?php echo "[ $x_start, 0 ], [ $x_end, 0 ]"; ?> ],
            tooltip: { yDecimals: 0, ySuffix: 'W' }
        }]
    });
    
    $zoomButton = $('#zoomOut');
    $zoomButton.click( function() {
        chart.yAxis[0].setExtremes(0, <?php echo $papp_max_subscr; ?> );
    });

});

</script>
