var Popup_GraphTempClass = Popup_AbstractClass.extend({
    _buttons: {
        'Close': 'close'
    },
    _title: 'Graph',
    _width: 800,
    _height: 400,

    init: function(parameters) {
        parameters = $.parseJSON(unescape(parameters));
        this._content = '<iframe frameborder="0" scrolling="no" width="' + this._width + '" height="' + this._height + '" ' +
                                'src="/php/chart_temp_annee.php?sonde=' + parameters.sonde + '&date=' + parameters.date + '"></iframe>';

        this._super(parameters);
    }
});
