new (AbstractClass.extend({
    popup: null,

    init: function() {
        // Définit les handlers d'évènement
        this.openHandler = this.open.bind(this);
        this.openDirectHandler = this.openDirect.bind(this);
        this.closeHandler = this.close.bind(this);
        this.keyUpHandler = this.keyUp.bind(this);

        this._super();
    },

    addObservers: function() {
        this._super();

        $(document)
            .off('click', '.popupLink', this.openHandler)
            .on('click', '.popupLink', this.openHandler)
            .off('click', '.popupClose', this.closeHandler)
            .on('click', '.popupClose', this.closeHandler)
            .off('ap::openPopup', this.openDirectHandler)
            .on('ap::openPopup', this.openDirectHandler)
            .off('ap::closePopup', this.closeHandler)
            .on('ap::closePopup', this.closeHandler)
            .off('keyup', this.keyUpHandler)
            .on('keyup', this.keyUpHandler);
    },

    destroy: function() {
        this._super();

        // Détruit les handlers d'évènement
        this.openHandler = null;
        this.openDirectHandler = null;
        this.closeHandler = null;
        this.keyUpHandler = null;
    },

    removeObservers: function() {
        this._super();

        $(document)
            .off('click', '.popupLink', this.openHandler)
            .off('click', '.popupClose', this.closeHandler)
            .off('ap::openPopup', this.openDirectHandler)
            .off('ap::closePopup', this.closeHandler)
            .off('keyup', this.keyUpHandler);
    },

    open: function(evt) {
        this.stopEvent(evt);

        // Détruit tout popup déjà existant
        this.close();

        var popupLink = $(evt.currentTarget);
        if(popupLink == null || popupLink.data('type') == null) {
            return false;
        }

        // Créé un nouveau popup
        var popupType = popupLink.data('type');
        var popupParameters = popupLink.data('parameters') || {};
        if(popupLink.attr('href') != null) {
            popupParameters.url = popupLink.attr('href');
        }

        var result = this.createPopup(popupType, popupParameters);

        // Return false pour annuler un evt click
        if(popupLink.attr('href') != null) {
            return false;
        }
        return result;
    },

    openDirect: function(evt, options) {
        this.stopEvent(evt);

        // Détruit tout popup déjà existant
        this.close();

        if(options.type == null) {
            return false;
        }

        // Créé un nouveau popup
        var popupType = options.type;
        var popupParameters = options.parameters || {};

        return this.createPopup(popupType, popupParameters);
    },

    createPopup: function(popupType, popupParameters) {
        var popupClass = 'Popup_' + popupType.substr(0, 1).toUpperCase() + popupType.substr(1) + 'Class';
        if(eval('typeof ' + popupClass) != 'undefined') {
            this.popup = eval('new ' + popupClass + '(' + JSON.stringify(popupParameters).replace(/'/gi, "\'") + ')');
            return true;
        }
        return false;
    },

    close: function(evt) {
        this.stopEvent(evt);

        // Appel la fonction de destruction du popup
        if(this.popup != null) {
            this.popup.destroy();
            this.popup = null;
        }
    },

    keyUp: function(evt) {
        if(evt.keyCode == 27) {
            // Détruit le popup dans le cas où la touche est 'Echap'
            this.close();
        }
    }
}));