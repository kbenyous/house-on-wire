<script type="text/javascript">
var live_chart;
$(document).ready(function() {
    // Create the chart
    live_chart = new Highcharts.Chart({
        chart : {
            renderTo : 'graphe_live',
            defaultSeriesType: 'area',
            events: {
                load: requestManyData
            }
        },
        title: { text: 'Consommation instantanee en direct' },
        plotOptions: { area: { marker: { enabled: false } } },
        xAxis: {
            type: 'datetime',
            tickPixelInterval: 150
        },
        yAxis : {
            title: { text: 'Puissance apparente (W)' },
            min: 0,
            labels: { formatter: function() { return this.value+'w'; } },
        },
        series : [{
            name : 'PAPP',
            data : [],
            tooltip: { yDecimals: 0, ySuffix: 'W' }
        }]
    });
});
var nb_points=500;
function requestManyData() {
    $.ajax({
        url: 'data/papp_live.php?nb='+nb_points,
        success: function(points) {
            var singlePoint = ( nb_points == 1 );
            var series = live_chart.series[0];
            nb_points=1;
            $.each(points, function(i,point) {
                series.addPoint( point, singlePoint, singlePoint ); 
            });
            if( singlePoint == false ) live_chart.redraw();
            setTimeout( requestManyData, 1500 );
        },
        cache: false
    });
}

</script>
