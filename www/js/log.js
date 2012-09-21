new (AbstractClass.extend({
    init : function() {
        this.logHandler = this.log.bind(this);

        $(document)
            .off('how::log', this.logHandler)
            .on('how::log', this.logHandler);
    },

    destroy : function() {
        $(document)
            .off('how::log', this.logHandler);

        this.logHandler = null;
    },

    log: function(evt, parameters) {
        if(parameters != null && parameters.message != null) {
            $('#logConsole').prepend(
                '<p class="logLine">' + this.getDateTime() + ' : ' + parameters.message + '</p>'
            );
        }
    },

    getDateTime : function() {
        var now = new Date();
        var year = '' + now.getFullYear();
        var month = '' + (now.getMonth() + 1);
        if (month.length == 1) {
            month = '0' + month;
        }
        var day = '' + now.getDate();
        if (day.length == 1) {
            day = '0' + day;
        }
        var hour = '' + now.getHours();
        if (hour.length == 1) {
            hour = '0' + hour;
        }
        var minute = '' + now.getMinutes();
        if (minute.length == 1) {
            minute = '0' + minute;
        }
        var second = '' + now.getSeconds();
        if (second.length == 1) {
            second = '0' + second;
        }
        return day + '/' + month + '/' + year + ' ' + hour + ':' + minute + ':' + second;
    }
}))();
