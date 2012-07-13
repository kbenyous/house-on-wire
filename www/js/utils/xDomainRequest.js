var Utils_XDomainRequestClass = AbstractClass.extend({
    scriptId: 'xDomainRequest' + Math.floor(Math.random() * 1000000),
    scriptCallback: '',

    init: function(src, callback) {
        if(src == null || callback == null) {
            return false;
        }

        // Définit les handlers d'évènement
        this.loadHandler = this.load.bind(this);
        this.errorHandler = this.error.bind(this);

        this._super();

        return this.create(src, callback);
    },

    destroy: function() {
        this._super();

        // Détruit les handlers d'évènement
        this.loadHandler = null;
        this.errorHandler = null;
    },

    removeObservers: function() {
        this._super();

        $('#' + this.scriptId).off({
            'load': this.loadHandler,
            'error': this.errorHandler
        }).remove();
    },

    create: function(src, callback) {
        var head = document.getElementsByTagName('head')[0];
        if(head == null) {
            return false;
        }

        this.scriptCallback = callback;

        if(src.indexOf('?') == -1) {
            src += '?';
        } else {
            src += '&';
        }
        src += 'callbackId=' + encodeURIComponent(this.scriptId);

        var script = document.createElement('script');
        script.id = this.scriptId;
        script.src = src;

        $(script).on({
            'load': this.loadHandler,
            'error': this.errorHandler
        });

        head.appendChild(script);

        return true;
    },

    load: function() {
        // Appelle le callback en passant le résultat de la requête
        try {
            this.scriptCallback(eval(this.scriptId));
        } catch(ex) {
            this.scriptCallback(null);
        }
        this.destroy();
    },

    error: function() {
        // Appelle le callback en mode erreur
        this.scriptCallback(null);
        this.destroy();
    }
});
