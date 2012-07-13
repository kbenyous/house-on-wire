new (AbstractClass.extend({
    init: function() {
        // Définit les handlers d'évènement
        this.showHideHandler = this.showHide.bind(this);

        this._super();

        var tabName = document.location.href.split('#')[1];
        if(tabName != null) {
            this.showHide(null, tabName);
        };
    },

    addObservers: function() {
        this._super();

        $(document)
            .off('click', '.tabs .tab', this.showHideHandler)
            .on('click', '.tabs .tab', this.showHideHandler);
    },

    destroy: function() {
        this._super();

        // Détruit les handlers d'évènement
        this.showHideHandler = null;
    },

    removeObservers: function() {
        this._super();

        $(document)
            .off('click', '.tabs .tab', this.showHideHandler);
    },

    showHide: function(evt, tabName) {
        var urlBase = document.location.href.split('#')[0];

        if(evt != null) {
            tabName = $(evt.currentTarget).data('tab-name');
            document.location.href = urlBase + '#' + tabName;
        }

        if($('.' + tabName).length > 0) {
            $('.tabs .tab').removeClass('selected');
            $('.tabs .tab.' + tabName).addClass('selected');

            $('.tabBody').hide();
            $('.tabBody.' + tabName).show();

            // Update form urls
            $('form').each(function (index, form) {
                var formAction = $(form).attr('action').split('#')[0];
                if(formAction.indexOf(urlBase) != -1) {
                    $(form).attr('action', formAction + '#' + tabName);
                }
            });
        }
    }
}));