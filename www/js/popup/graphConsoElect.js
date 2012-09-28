var Popup_GraphConsoElectClass = Popup_AbstractClass.extend({
    _buttons: {
        'Close': 'close'
    },
    _title: 'Graph',
    _width: 1200,
    _height: 620,

    init: function(parameters) {
        //parameters = $.parseJSON(unescape(parameters));
        this._content = '<iframe frameborder="0" scrolling="no" width="' + this._width + '" height="' + this._height + '" ' +
                                'src="/php/chart_conso_elect.php"></iframe>';

        this._super(parameters);
    }
});
