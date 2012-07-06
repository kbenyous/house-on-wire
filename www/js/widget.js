var widgetClass = AbstractClass.extend({
    _timer: null,
    _timerInterval: 60000,
    _widgetData: null,

    init: function(widgetData) {
        this._widgetData = widgetData;
        this._widgetData.domId = Math.floor(Math.random() * 1000);

        this._timer = window.setInterval(
            this.getData.bind(this),
            this._timerInterval + Math.floor(Math.random() * 1000)
        );
        
        this.createLayout();
        this.fillUp({
            'temperature': {
                'value': 0
            },
            'deltaPlusOneHour': {
                'direction': 'increase',
                'value': 0
            },
            'deltaPlusOneDay': {
                'direction': 'increase',
                'value': 0
            }
        });
        this.getData();
    },
    
    destroy: function() {
        if(this._timer != null) {
            window.clearInterval(this._timer);
            this._timer = null;
        }
    },
    
    createLayout: function() {
        $('#widgets').append('<div id="widget' + this._widgetData.domId + '" class="widget">' +
            '<fieldset>' +
                '<legend>' + this._widgetData.title + '</legend>' +
                '<div class="widgetContent">' +
                    '<div class="widgetTemperature">' +
                        '<p class="widgetTemperatureThermometer blue">&nbsp;</p>' +
                        '<p class="widgetTemperatureValue">&nbsp;</p>' +
                        '<p class="widgetTemperatureUnit">' + this._widgetData.unit + '</p>' +
                    '</div>' +
                    '<div class="widgetDelta">' +
                        '<p class="widgetDeltaTitle">Fréquences :</p>' +
                        '<div class="widgetDeltaPlusOneHour">' +
                            '<p class="widgetDeltaFrequency">1h</p>' +
                            '<p class="widgetDeltaImage decrease">&nbsp;</p>' +
                            '<p class="widgetDeltaValue">&nbsp;</p>' +
                            '<p class="widgetDeltaUnit">' + this._widgetData.unit + '</p>' +
                        '</div>' +
                        '<div class="widgetDeltaPlusOneDay">' +
                            '<p class="widgetDeltaFrequency">1d</p>' +
                            '<p class="widgetDeltaImage increase">&nbsp;</p>' +
                            '<p class="widgetDeltaValue">&nbsp;</p>' +
                            '<p class="widgetDeltaUnit">' + this._widgetData.unit + '</p>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</fieldset>' +
        '</div>');
    },
    
    fillUp: function(response) {
        var widget = $('#widget' + this._widgetData.domId);
        if(widget.length > 0) {
            // Temperature
            $(widget).find('.widgetTemperatureValue').html((parseFloat(response.temperature.value || 0)).toFixed(2));
            var thermometerColor = 'blue';
            if(response.temperature.value >= 22) {
                thermometerColor = 'red';
            } else if(response.temperature.value >= 18) {
                thermometerColor = 'green';
            }
            
            $(widget).find('.widgetTemperatureThermometer')
                .removeClass('blue')
                .removeClass('green')
                .removeClass('red')
                .addClass(thermometerColor);
            
            $(widget).find('.widgetDeltaImage')
                .removeClass('increase')
                .removeClass('decrease');
            
            // Delta Plus One Hour
            $(widget).find('.widgetDeltaPlusOneHour .widgetDeltaImage').addClass(response.deltaPlusOneHour.direction);
            $(widget).find('.widgetDeltaPlusOneHour .widgetDeltaValue').html((parseFloat(response.deltaPlusOneHour.value || 0)).toFixed(2));
            
            // Delta Plus One Day
            $(widget).find('.widgetDeltaPlusOneDay .widgetDeltaImage').addClass(response.deltaPlusOneDay.direction);
            $(widget).find('.widgetDeltaPlusOneDay .widgetDeltaValue').html((parseFloat(response.deltaPlusOneDay.value || 0)).toFixed(2));
        }
    },
    
    getData: function() {
        $.ajax({
            'url': '/test.php',
            'data': {
                'id': this._widgetData.id
            },
            'success': (function(data, textStatus, jqXHR) {
                if(data && data.status == 'success') {
                    this.fillUp(data.content);
                }
            }).bind(this),
            'dataType': 'json'
        });
    }
});

(function() {
    for(widgetDataIndex in window.widgetsData) {
        new widgetClass(window.widgetsData[widgetDataIndex]);
    }
})();