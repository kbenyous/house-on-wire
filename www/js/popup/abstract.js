var Popup_AbstractClass = AbstractClass.extend({
    _id: 'popup' + Math.floor(Math.random() * 1000000),
    _className: '',
    _parameters: {},
    _border: 50,
    _title: '',
    _content: '',
    _buttons: null,
    _width: null,
    _height: null,

    init: function(parameters) {
        // Définit les handlers d'évènement
        this.resizeHandler = this.resize.bind(this);
        this.scrollHandler = this.scroll.bind(this);
        this.moveBodyHandler = this.moveBody.bind(this);
        this.launchActionHandler = this.launchAction.bind(this);

        if(parameters.title != null) {
            this._title = parameters.title;
        }
        if(parameters.content != null) {
            this._content = parameters.content;
        }
        if(parameters.width != null) {
            this._width = parameters.width;
        }
        if(parameters.height != null) {
            this._height = parameters.height;
        }
        if(parameters.buttons != null) {
            this._buttons = parameters.buttons;
        }

        this._parameters = parameters;

        // Affiche le popup
        this.show();

        this._super();
    },

    addObservers: function() {
        this._super();

        $(document)
            .off('click', '#' + this._id + ' .popupAction', this.launchActionHandler)
            .on('click', '#' + this._id + ' .popupAction', this.launchActionHandler)
            .off('click', '#' + this._id + ' .resizeableObject', this.moveBodyHandler)
            .on('click', '#' + this._id + ' .resizeableObject', this.moveBodyHandler);

        $(window)
            .off('resize', this.resizeHandler)
            .on('resize', this.resizeHandler)
            .off('scroll', this.scrollHandler)
            .on('scroll', this.scrollHandler);
    },

    destroy: function() {
        this._super();

        this.resizeHandler = null;
        this.scrollHandler = null;
        this.moveBodyHandler = null;
        this.launchActionHandler = null;

        // Détruit le popup
        this.hide();
    },

    removeObservers: function() {
        this._super();

        $(document)
            .off('click', '#' + this._id + ' .popupAction', this.launchActionHandler)
            .off('click', '#' + this._id + ' .resizeableObject', this.moveBodyHandler);

        $(window)
            .off('resize', this.resizeHandler)
            .off('scroll', this.scrollHandler);
    },

    createButtons: function() {
        if(this._buttons != null && $.isPlainObject(this._buttons)) {
            var popupBodyButtons = '';
            for(buttonText in this._buttons) {
                var buttonClass = [];
                var buttonOnClick = [];

                if($.isArray(this._buttons[buttonText])) {
                    for(var i=0; i<this._buttons[buttonText].length; i++) {
                        if(this._buttons[buttonText][i] == 'close') {
                            buttonClass.push('popupClose');
                        } else {
                            buttonClass.push('popupAction');
                            buttonOnClick.push(this._buttons[buttonText][i]);
                        }
                    }
                } else {
                    if(this._buttons[buttonText] == 'close') {
                        buttonClass.push('popupClose');
                    } else {
                        buttonClass.push('popupAction');
                        buttonOnClick.push(this._buttons[buttonText]);
                    }
                }

                if(buttonClass.length > 0) {
                    buttonClass = 'class="' + buttonClass.join(' ') + '" ';
                }
                if(buttonOnClick.length > 0) {
                    buttonOnClick = 'data-action="' + buttonOnClick.join('; ') + '" ';
                }

                popupBodyButtons += '<input type="button" value="' + buttonText + '" ' +
                    buttonClass + buttonOnClick + ' />';
            }
            return '<div class="popupBodyButtons">' + popupBodyButtons + '</div>';
        }
        return '';
    },

    createLayout: function() {
        if(this._parameters.url != null) {
            this.createLoader();
            this.createContentIframe();
        }

        var popupBodyContentStyle = '';
        if(this._width != null) {
            popupBodyContentStyle += 'width: ' + this._width + 'px;'
        }
        if(this._height != null) {
            popupBodyContentStyle += 'height: ' + this._height + 'px;'
        }

        return '<div id="popupBackground" class="popupClose">&nbsp;</div>' +
        '<div id="popupBody" class="shadowed legend">' +
            '<div class="popupCross popupClose">&nbsp;</div>' +
            '<div class="popupBodyTitle">' +
                '<span>' + this._title + '</span>' +
            '</div>' +
            '<div class="popupBodyContent" style="' + popupBodyContentStyle + '">' +
                this._content +
            '</div>' +
            this.createButtons() +
        '</div>';
    },

    createContentIframe: function() {
        var urlParser = this._parameters.url.split('?')[1].split('&');
        var urlParameters = {};
        for(var i=0; i<urlParser.length; i++) {
            var urlParameter = urlParser[i].split('=');
            urlParameters[urlParameter[0]] = urlParameter[1];
        }

        var popupIframe = document.createElement('iframe');
        popupIframe.id = 'popupIframe' + Math.floor(Math.random() * 1000000);
        popupIframe.style.position = 'absolute';
        popupIframe.style.top = '-10000px';
        popupIframe.style.left = '-10000px';
        popupIframe.src = this._parameters.url;
        $(popupIframe).load(this.readIframeContent.bind(this, popupIframe.id));
        $('body').append(popupIframe);
    },

    readIframeContent: function(popupIframeId) {
        this.removeLoader();

        var popupIframe = $('#' + popupIframeId);
        if(popupIframe.length == 0) {
            return false;
        }

        var popupIframeContent = popupIframe.contents().find('body').html() || '';
        var popupBodyContent = $('#popupBody > .popupBodyContent');
        if(popupIframeContent == '' || popupBodyContent.length == 0) {
            return false;
        }

        var popupIframeContentSpan = document.createElement('span');
        popupIframeContentSpan.innerHTML = popupIframeContent;
        popupBodyContent.append(popupIframeContentSpan);
        popupBodyContent.css('width', 'auto').css('height', 'auto');

        this.moveBody(true);

        popupIframe.remove();

        return true;
    },

    show: function() {
        // Création du corps du popup
        var popup = document.createElement('div');
        popup.id = this._id;
        popup.className = 'popup' + ((this._className == '') ? '' : ' ' + this._className);
        popup.innerHTML = this.createLayout();
        $('body').append(popup);

        // Faire que le popup puisse être déplacé dans la page
        this.makeDraggable();

        // Place le popup correctement dans la page
        this.resizeBackground();
        this.moveBody(true);
    },

    hide: function() {
        // Détruit les observateurs
        this.removeDraggable();

        // Enlève le popup du DOM
        $('#' + this._id).remove();
    },

    setBodyContainment: function() {
        var popupBody = $('#popupBody');
        if(popupBody.length == 0) {
            return false;
        }

        var websiteDimensions = this.getWebsiteDimensions();
        var containmentValue = [
            this._border,
            this._border,
            websiteDimensions.width - popupBody.width() - this._border,
            websiteDimensions.height - popupBody.height() - this._border
        ];
        $(popupBody).draggable('option', 'containment', containmentValue);
        return true;
    },

    makeDraggable: function() {
        var popup = $('#' + this._id);
        var popupBody = $('#popupBody');
        var popupBodyTitle = $('#popupBody .popupBodyTitle')[0];
        if(popup.length == 0 || popupBody.length == 0 || popupBodyTitle.length == 0) {
            return false;
        }

        $(popupBody).draggable({
            'handle': $(popupBodyTitle)
        });
        this.setBodyContainment();
        return true;
    },

    removeDraggable: function() {
        var popupBody = $('#popupBody');
        if(popupBody.length == 0) {
            return false;
        }

        $(popupBody).draggable('destroy');
        return true;
    },

    getWebsiteDimensions: function() {
        // Le viewport est en fait la dimension de l'écran
        var viewport = new Utils_ViewportClass();

        // La dimension du site Internet est soit la dimension de l'écran,
        // soit la dimension du body du site si l'écran est plus petit que le
        // body
        return {
            'width': Math.max(
                parseInt($('body').width(), 10),
                parseInt($('html').width(), 10),
                viewport.width()
            ),
            'height': Math.max(
                parseInt($('body').height(), 10),
                parseInt($('html').height(), 10),
                viewport.height()
            )
        }
    },

    resize: function() {
        this.resizeBackground();
        this.setBodyContainment();
        this.moveBody();
    },

    scroll: function() {
        this.moveBody();
    },

    resizeBackground: function() {
        var popup = $('#' + this._id);
        if(popup.length == 0) {
            this.destroy();
            return false;
        }

        var websiteDimensions = this.getWebsiteDimensions();
        
        var popupBody = $('#popupBody');
        if(popupBody.length != 0) {
            websiteDimensions.width = Math.max(
                websiteDimensions.width,
                $(popupBody).width() + 2 * this._border
            );
            
            websiteDimensions.height = Math.max(
                websiteDimensions.height,
                $(popupBody).height() + 2 * this._border
            );
        }

        popup.css('width', websiteDimensions.width + 'px')
        .css('height', websiteDimensions.height + 'px');
        return true;
    },

    moveBody: function(init) {
        var popupBody = $('#popupBody');
        if(popupBody.length == 0) {
            this.destroy();
            return false;
        }

        // On regarde que le popup n'a pas la classe 'ui-draggable-dragging' ce
        // qui signifirait qu'il est déjà en train d'être déplacé
        if(popupBody.hasClass('ui-draggable-dragging') !== false) {
            return false;
        }

        // Le viewport est en fait la dimension de l'écran
        var viewport = new Utils_ViewportClass();

        // Si l'écran est moins haut que le corps du popup + bordures, on
        // bloque le popup en haut de l'écran pour toujours pouvoir visualiser
        // son contenu
        if(viewport.height() > (popupBody.height() + (2 * this._border))) {
            var popupBodyTop = parseInt((viewport.height() - popupBody.height()) / 2, 10) + viewport.scrollTop();
        } else {
            var popupBodyTop = this._border;
        }

        // Si l'écran est moins large que le corps du popup + bordures, on
        // bloque le popup à gauche de l'écran pour toujours pouvoir visualiser
        // son contenu
        if(viewport.width() > (popupBody.width() + (2 * this._border))) {
            var popupBodyLeft = parseInt((viewport.width() - popupBody.width()) / 2, 10) + viewport.scrollLeft();
        } else {
            var popupBodyLeft = this._border;
        }

        if(init === true) {
            // Si on est dans le cas de l'initialisation, on place le corps
            // du popup au milieu de la page sans effet
            popupBody.css('left', popupBodyLeft + 'px')
            .css('top', popupBodyTop + 'px');
        } else {
            // Place le corps du popup au milieu de la page en le faisant
            // slider
            popupBody.clearQueue();
            popupBody.animate({
                'left': popupBodyLeft + 'px',
                'top': popupBodyTop + 'px'
            });
        }
        return true;
    },

    createLoader: function(domLoader) {
        if(domLoader == true) {
            var popupBodyContent = $('#popupBody > .popupBodyContent');
            if(popupBodyContent.length == 0) {
                return false;
            }

            var popupLoader = document.createElement('div');
            popupLoader.className = 'loader popupLoader';
            popupLoader.innerHTML = '<img src="/image/loader.gif" />';
            popupBodyContent.append(popupLoader);
        } else {
            this._content += '<div class="loader popupLoader">' +
                '<img src="/image/loader.gif" />' +
            '</div>';
        }

        return true;
    },

    removeLoader: function() {
        $('#popupBody > .popupBodyContent > .popupLoader').remove();
    },

    launchAction: function(evt) {
        this.stopEvent(evt);

        var popupAction = $(evt.currentTarget);
        if(popupAction.length == 0) {
            return false;
        }

        if(this[popupAction.data('action')]) {
            this[popupAction.data('action')]();
        }

        return true;
    }
});