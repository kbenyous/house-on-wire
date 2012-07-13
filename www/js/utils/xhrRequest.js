var Utils_XhrRequestClass = Class.extend({
    _ajaxParameters: null,

    init: function(ajaxParameters) {
        // Extend object
        ajaxParameters = $.extend({
            'data': {},
            'type': 'POST',
            'cache': false
        }, ajaxParameters);

        // Save options by cloning object
        this._ajaxParameters = $.extend({}, ajaxParameters);

        // Extra properties
        ajaxParameters.data.xhr = 1;
        if(ajaxParameters.success) {
            ajaxParameters.success = this.success.bind(this);
        }
        if(ajaxParameters.error) {
            ajaxParameters.error = this.error.bind(this);
        }

        // Send
        $.ajax(ajaxParameters);
    },

    formatResponseData: function(response) {
        if(typeof response == 'string') {
            return $.parseJSON(response);
        }
        return response;
    },

    success: function(response) {
        try {
            var responseData = this.formatResponseData(response);
            if(responseData.status === 'success') {
                this._ajaxParameters.success(responseData.content);
            } else {
                this.error(response);
            }
        } catch(ex) {
            this.error(response);
        }
    },

    error: function(response) {
        try {
            var responseData = this.formatResponseData(response);
            this._ajaxParameters.error(responseData.content);
        } catch(ex) {
            this._ajaxParameters.error('Internal error.');
        }
    }
});