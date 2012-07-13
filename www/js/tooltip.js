new (AbstractClass.extend({
    _tooltipAnchor: null,
    _tooltipContent: '',
    _canvasRadius: 10,
    _canvasArrowSize: 10,
    _canvasPadding: 10,
    _canvasLineWidth: 1,
    _canvasLineColor: '#3e6778',
    _canvasBackgroundColor: '#ffffff',
    _iconHeight: 20,
    _iconWidth: 4,

    init: function() {
        // Définit les handlers d'évènement
        this.showHandler = this.show.bind(this);
        this.hideHandler = this.hide.bind(this);

        this._super();
    },

    addObservers: function() {
        this._super();

        $(document)
            .off('mouseenter', '.tooltipHandle', this.showHandler)
            .on('mouseenter', '.tooltipHandle', this.showHandler)
            .off('mouseleave', '.tooltipHandle', this.hideHandler)
            .on('mouseleave', '.tooltipHandle', this.hideHandler);
    },

    destroy: function() {
        this._super();

        // Détruit les handlers d'évènement
        this.showHandler = null;
        this.hideHandler = null;
    },

    removeObservers: function() {
        this._super();

        $(document)
            .off('mouseenter', '.tooltipHandle', this.showHandler)
            .off('mouseleave', '.tooltipHandle', this.hideHandler);
    },

    show: function(evt) {
        this.stopEvent(evt);

        var tooltipAnchor = $(evt.currentTarget).parents('.tooltipAnchor')[0];
        var tooltipContent = $(evt.currentTarget).next('.tooltipContent')[0];
        if(tooltipAnchor && tooltipContent) {
            // Détruit tout tooltip déjà existant
            this.hide();

            // Enregistre le contexte du tooltip
            this._tooltipAnchor = $(tooltipAnchor);
            this._tooltipContent = $(tooltipContent);

            // Créé un nouveau tooltip
            this.create();
        }
    },

    hide: function() {
        if(this._tooltipAnchor != null) {
            // Cache le tooltip
            this._tooltipAnchor.find('.tooltip').remove();

            this._tooltipAnchor = null;
            this._tooltipContent = '';
        }
    },

    create: function() {
        if(this._tooltipAnchor == null) {
            return false;
        }

        var tooltip = document.createElement('div');
        tooltip.id = 'tooltip' + Math.floor(Math.random() * 1000000);
        tooltip.className = 'tooltip';
        this._tooltipAnchor.append(tooltip);

        if(this._tooltipContent.data('width') != null) {
            this._tooltipContent.css('width', this._tooltipContent.data('width'));
        }

        if(this.drawTooltip(tooltip.id) == true) {
            if(this._tooltipContent.data('url') != null) {
                // Télécharge la légende
                var url = this._tooltipContent.data('url');
                new Utils_XDomainRequestClass(
                    url,
                    this.populate.bind(this, tooltip.id)
                );
            }
        } else {
            this.hide();
        }
        return true;
    },

    populate: function(tooltipId, tooltipObj) {
        try {
            this._tooltipContent.removeData('url');
            this._tooltipContent.removeAttr('data-url');

            if(tooltipObj != null && tooltipObj.usage) {
                this._tooltipContent.html(tooltipObj.usage);
                return this.drawTooltip(tooltipId);
            }
        } catch(ex) {
        }
        return false;
    },

    drawTooltip: function(tooltipId) {
        try {
            var tooltip = $('#' + tooltipId);
            if(tooltip.length == 0) {
                return false;
            }

            tooltip.html('<canvas class="tooltipCanvas"></canvas>' +
            '<div class="tooltipCanvasText">' + this._tooltipContent.html() + '</div>');

            var tooltipCanvas = this._tooltipAnchor.find('.tooltipCanvas')[0];
            var tooltipCanvasText = this._tooltipAnchor.find('.tooltipCanvasText')[0];
            if(tooltipCanvas == null || tooltipCanvasText == null) {
                return false;
            }

            var viewport = new Utils_ViewportClass();

            var textWidth = parseInt(this._tooltipContent.css('width'), 10);
            var textHeight = parseInt(this._tooltipContent.css('height'), 10);

            var canvasWidth = textWidth + (this._canvasPadding * 2);
            var canvasHeight = textHeight + (this._canvasPadding * 2);

            var tooltipWidth = canvasWidth + (2 * this._canvasLineWidth);
            var tooltipHeight = canvasHeight + this._canvasArrowSize + (2 * this._canvasLineWidth);
            var tooltipTop = parseInt(tooltip.offset().top, 10) - viewport.scrollTop();
            var tooltipLeft = parseInt(tooltip.offset().left, 10) - viewport.scrollLeft();

            var arrowPosition = [];
            var canvasLeft = this._canvasArrowSize;

            if((tooltipTop + tooltipHeight) > viewport.height()) {
                var canvasTop = 0;
                arrowPosition.push('bottom');
            } else {
                var canvasTop = this._canvasArrowSize;
                arrowPosition.push('top');
            }

            if((tooltipLeft + tooltipWidth) > viewport.width()) {
                arrowPosition.push('left');
            } else {
                arrowPosition.push('right');
            }

            canvasTop += this._canvasLineWidth;
            canvasLeft += this._canvasLineWidth;

            var textTop = canvasTop + this._canvasPadding;
            var textLeft = canvasLeft + this._canvasPadding;

            $(tooltipCanvasText).css('width', textWidth + 'px')
            .css('height', textHeight + 'px')
            .css('top', textTop + 'px')
            .css('left', textLeft + 'px');

            if($.browser.msie === true) {
                tooltipCanvas = window.G_vmlCanvasManager.initElement(tooltipCanvas);
            }

            var ctx = tooltipCanvas.getContext('2d');
            this.drawBubble(ctx, arrowPosition, canvasLeft, canvasTop, canvasWidth, canvasHeight);

            if($.inArray('bottom', arrowPosition) != -1) {
                $(tooltip).css(
                    'top',
                    (parseInt($(tooltip).position().top, 10) -
                     tooltipHeight -
                     this._iconHeight) + 'px'
                );
            }
            if($.inArray('left', arrowPosition) != -1) {
                $(tooltip).css(
                    'left',
                    (parseInt($(tooltip).position().left, 10) -
                     tooltipWidth -
                     this._iconWidth) + 'px'
                );
            }

            return true;
        } catch(ex) {
            return false;
        }
    },

    drawBubble: function(ctx, arrowPosition, x, y, w, h) {
        var r = x + w;
        var b = y + h;

        ctx.canvas.width  = this._canvasArrowSize + w + (2 * this._canvasLineWidth);
        ctx.canvas.height = this._canvasArrowSize + h + (2 * this._canvasLineWidth);

        ctx.beginPath();
        ctx.strokeStyle = this._canvasLineColor;
        ctx.lineWidth = this._canvasLineWidth;
        ctx.fillStyle = this._canvasBackgroundColor;

        ctx.moveTo(x + this._canvasRadius, y);

        if($.inArray('top', arrowPosition) != -1) {
            if($.inArray('right', arrowPosition) != -1) {
                ctx.lineTo(x + this._canvasRadius / 2, this._canvasLineWidth);
                ctx.lineTo(x + this._canvasRadius * 2, y);
            } else {
                ctx.lineTo(r - this._canvasRadius - this._canvasArrowSize, y);
                ctx.lineTo(r - this._canvasArrowSize / 2, this._canvasLineWidth);
            }
        }
        ctx.lineTo(r - this._canvasRadius, y);
        ctx.quadraticCurveTo(r, y, r, y + this._canvasRadius);

        ctx.lineTo(r, y + h - this._canvasRadius);
        ctx.quadraticCurveTo(r, b, r - this._canvasRadius, b);

        if($.inArray('bottom', arrowPosition) != -1) {
            if($.inArray('right', arrowPosition) != -1) {
                ctx.lineTo(x + this._canvasRadius * 2, b);
                ctx.lineTo(x + this._canvasRadius / 2, b + this._canvasArrowSize);
            } else {
                ctx.lineTo(r - this._canvasArrowSize / 2, b + this._canvasArrowSize);
                ctx.lineTo(r - this._canvasArrowSize * 2, b);
            }
        }
        ctx.lineTo(x + this._canvasRadius, b);
        ctx.quadraticCurveTo(x, b, x, b - this._canvasRadius);

        ctx.lineTo(x, y + this._canvasRadius);
        ctx.quadraticCurveTo(x, y, x + this._canvasRadius, y);

        ctx.fill();
        ctx.stroke();
        ctx.closePath();
    }
}));