var widgetClass = AbstractClass.extend({
    _timer : null,
    _timerInterval : 300000,
    _widgetData : null,

    init : function(widgetData) {
        this._widgetData = widgetData;
        this._widgetData.domId = Math.floor(Math.random() * 10000000);

        this.createLayout();
        this.fillUp({
            'maj' : {
                'value' : 0
            },
            'last_value' : {
                'value' : 0
            },
            'deltaPlusOneHour' : {
                'direction' : 'increase',
                'value' : 0
            },
            'deltaPlusOneDay' : {
                'direction' : 'increase',
                'value' : 0
            }
        });

        this.getData();
        this._timer = window.setInterval(
            this.getData.bind(this),
            this._timerInterval + Math.floor(Math.random() * 5000)
        );
    },

    destroy : function() {
        if (this._timer != null) {
            window.clearInterval(this._timer);
            this._timer = null;
        }
    },

    createLayout : function() {
        var widgetParameters = {
            'id' : this._widgetData.id
        }
        $('#' + this._widgetData.level).append(
            '<div id="widget' + this._widgetData.domId + '" ' +
                 'style="top: ' + this._widgetData.top + 'px; ' +
                        'left: ' + this._widgetData.left + 'px;" ' +
                 'class="widget">' +
                '<span class="widgetTitle">' + this._widgetData.title + '</span>' +
                '<span> : </span>' +
                '<span class="widgetValue"></span>' +
                '<span>&nbsp;</span>' +
                '<span>' + this._widgetData.unit + '</span>' +
                '<div class="tooltipAnchor">' +
                    '<img class="tooltipHandle" src="/image/info.png" />' +
                    '<div class="tooltipContent">' +
                        '<p class="widgetContentTitle">' +
                            '<span>' + this._widgetData.title + ' : </span>' +
                            '<span class="widgetUpdate">&nbsp;</span>' +
                        '</p>' +
                        '<div class="widgetContent">' +
                            '<div class="widgetTemperature">' +
                                '<p class="widgetTemperatureThermometer blue">&nbsp;</p>' +
                                '<p class="widgetValue">&nbsp;</p>' +
                                '<p class="widgetUnit">' + this._widgetData.unit + '</p>' +
                            '</div>' +
                            '<div class="widgetDelta">' +
                                '<p class="widgetDeltaTitle">Moyenne :</p>' +
                                '<div class="widgetDeltaPlusOneHour">' +
                                    '<p class="widgetDeltaFrequency">1h</p>' +
                                    '<p class="widgetDeltaImage decrease">&nbsp;</p>' +
                                    '<p class="widgetDeltaValue">&nbsp;</p>' +
                                    '<p class="widgetDeltaUnit">' + this._widgetData.unit + '</p>' +
                                '</div>' +
                                '<div class="widgetDeltaPlusOneDay">' +
                                    '<p class="widgetDeltaFrequency">1j</p>' +
                                    '<p class="widgetDeltaImage increase">&nbsp;</p>' +
                                    '<p class="widgetDeltaValue">&nbsp;</p>' +
                                    '<p class="widgetDeltaUnit">' + this._widgetData.unit + '</p>' +
                                '</div>' +
                                '<p class="widgetDeltaTitle">Min / Max 24H :</p>' +
                                '<div class="widgetDeltaPlusOneDay">' +
                                    '<p class="widgetDeltaImageMax increase">&nbsp;</p>' +
                                    '<p class="widgetDeltaValueMax">&nbsp;</p>' +
                                    '<p class="widgetDeltaUnit">' + this._widgetData.unit + '</p>' +
                                '</div>' +
                                '<div class="widgetDeltaPlusOneDay">' +
                                    '<p class="widgetDeltaImageMin decrease">&nbsp;</p>' +
                                    '<p class="widgetDeltaValueMin">&nbsp;</p>' +
                                    '<p class="widgetDeltaUnit">' + this._widgetData.unit + '</p>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                   '</div>' +
               '</div>' +
               '<img class="popupLink" data-type="graph" data-parameters="' + escape(JSON.stringify(widgetParameters)) + '" src="/image/graph.png" />' +
           '</div>'
       );
    },

    fillUp : function(response) {
        var widget = $('#widget' + this._widgetData.domId);
        if (widget.length > 0) {
            // Log
            $(document).trigger(
                'how::log', {
                    'message': 'Mise à jour de infos de la sonde ' + this._widgetData.id + ' (' + this._widgetData.title + ')'
                }
            );

            // Update date
            $(widget).find('.widgetUpdate').html('(' + response.maj.value + ')');

            // Temperature
            $(widget).find('.widgetValue').html((parseFloat(response.last_value.value || 0)));

// Coloration du thermo
// On vire pour le moment
//            var thermometerColor = 'blue';
//            if (response.temperature.value >= 22) {
                thermometerColor = 'red';
//            } else if (response.temperature.value >= 18) {
//                thermometerColor = 'green';
//            }
            $(widget).find('.widgetTemperatureThermometer').removeClass('blue').removeClass('green').removeClass('red').addClass(thermometerColor);
            $(widget).find('.widgetDeltaImage').removeClass('increase').removeClass('decrease');

            // Delta Plus One Hour
            $(widget).find('.widgetDeltaPlusOneHour .widgetDeltaImage').addClass(response.deltaPlusOneHour.direction);
            $(widget).find('.widgetDeltaPlusOneHour .widgetDeltaValue').html((parseFloat(response.deltaPlusOneHour.value || 0)));

            // Delta Plus One Day
            $(widget).find('.widgetDeltaPlusOneDay .widgetDeltaImage').addClass(response.deltaPlusOneDay.direction);
            $(widget).find('.widgetDeltaPlusOneDay .widgetDeltaValue').html((parseFloat(response.deltaPlusOneDay.value || 0)));
            $(widget).find('.widgetDeltaPlusOneDay .widgetDeltaValueMin').html((parseFloat(response.deltaPlusOneDay.min || 0)));
            $(widget).find('.widgetDeltaPlusOneDay .widgetDeltaValueMax').html((parseFloat(response.deltaPlusOneDay.max || 0)));
        }
    },

    getData : function() {
        new Utils_XhrRequestClass({
            'url' : '/php/get_onewire_data.php',
            'data' : {
                'id' : this._widgetData.id
            },
            'success' : this.fillUp.bind(this),
            'dataType' : 'json'
        });
    }
});

(function() {
    for (widgetDataIndex in window.widgetsData) {
        new widgetClass(window.widgetsData[widgetDataIndex]);
    }
})();
