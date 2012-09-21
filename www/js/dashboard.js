new (AbstractClass.extend({
    _timer : null,
    _timerInterval : 300000,

    init : function() {
        this.createLayout();
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
        $('#global').append(
            '<div id="dashboard">' +
                '<p id="dashboardTitle">Dashboard</p>' +
                '<div id="dashboardBody"></div>' +
            '</div>'
        );
    },

    fillUp : function(response) {
        // Log
        $(document).trigger(
            'how::log', {
                'message': 'Mise à jour des infos du dashboard'
            }
        );

        var dashboardBodyContent = '';
        for(lineName in response) {
            switch(lineName) {
                case 'electricity': {
                    dashboardBodyContent += '<div class="dashboardLine">' +
                        '<span class="dashboardLineTitle">Electricité</span>' +
                        '<span> : </span>' +
                        '<span>' + response[lineName].current.value + '</span>' +
                        '<span>&nbsp;</span>' +
                        '<span>' + response[lineName].current.unit + '</span>' +
                        '<div class="tooltipAnchor">' +
                            '<img class="tooltipHandle" src="/image/info.png" />' +
                            '<div class="tooltipContent">' +
                                '<p class="dashboardTooltipTitle">Historique</p>' +
                                '<div class="dashboardTooltipContent">' +
                                    '<div class="dashboardTooltipContentLine">' +
                                        'Hier : ' +
                                        response[lineName].power.yesterday + ' ' + response[lineName].power.unit + '&nbsp;/&nbsp;' +
                                        response[lineName].cost.yesterday + ' ' + response[lineName].cost.unit +
                                    '</div>' +
                                    '<div class="dashboardTooltipContentLine">' +
                                        'Avant hier : ' +
                                        response[lineName].power.beforeYesterday + ' ' + response[lineName].power.unit + '&nbsp;/&nbsp;' +
                                        response[lineName].cost.beforeYesterday + ' ' + response[lineName].cost.unit +
                                    '</div>' +
                                '</div>' +
                           '</div>' +
                       '</div>' +
                       '<img class="popupLink" data-type="graph" data-parameters="' + escape(JSON.stringify(response[lineName].graph)) + '" src="/image/graph.png" />' +
                   '</div>';
                    break;
                }
                case 'water': {
                    dashboardBodyContent += '<div class="dashboardLine">' +
                        '<span class="dashboardLineTitle">Eau</span>' +
                        '<span> : </span>' +
                        '<span>' + response[lineName].current.value + '</span>' +
                        '<span>&nbsp;</span>' +
                        '<span>' + response[lineName].current.unit + '</span>' +
                        '<div class="tooltipAnchor">' +
                            '<img class="tooltipHandle" src="/image/info.png" />' +
                            '<div class="tooltipContent">' +
                                '<p class="dashboardTooltipTitle">Historique</p>' +
                           '</div>' +
                       '</div>' +
                       '<img class="popupLink" data-type="graph" data-parameters="' + escape(JSON.stringify(response[lineName].graph)) + '" src="/image/graph.png" />' +
                   '</div>';
                    break;
                }
                case 'luminosity': {
                    dashboardBodyContent += '<div class="dashboardLine">' +
                        '<span class="dashboardLineTitle">Luminosité</span>' +
                        '<span> : </span>' +
                        '<span>' + response[lineName].current.value + '</span>' +
                        '<span>&nbsp;</span>' +
                        '<span>' + response[lineName].current.unit + '</span>' +
                        '<div class="tooltipAnchor">' +
                            '<img class="tooltipHandle" src="/image/info.png" />' +
                            '<div class="tooltipContent">' +
                                '<p class="dashboardTooltipTitle">Historique</p>' +
                           '</div>' +
                       '</div>' +
                       '<img class="popupLink" data-type="graph" data-parameters="' + escape(JSON.stringify(response[lineName].graph)) + '" src="/image/graph.png" />' +
                   '</div>';
                    break;
                }
            }
        }
        $('#dashboardBody').html(dashboardBodyContent);
    },

    getData : function() {
        new Utils_XhrRequestClass({
            'url' : '/php/get_dashboard_data.php',
            'success' : this.fillUp.bind(this),
            'dataType' : 'json'
        });
    }
}))();