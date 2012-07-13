/**
 * @class
 * @namespace
 */
var Utils_ViewportClass = Class.extend(
/**
 * Classe qui calcule la taille du viewport
 *
 * @lends Utils_ViewportClass
 */
{
    /**
     * Calcule la largeur du viewport
     *
     * @memberOf Utils_ViewportClass#
     * @return integer width
     */
    width: function() {
        return $(window).width();
    },

    /**
     * Calcule la hauteur du viewport
     *
     * @memberOf Utils_ViewportClass#
     * @return integer height
     */
    height: function() {
        return $(window).height();
    },

    /**
     * Calcule la valeur du scroll vertical du viewport
     *
     * @memberOf Utils_ViewportClass#
     * @return integer scrollTop
     */
    scrollTop: function() {
        return $(window).scrollTop();
    },

    /**
     * Calcule la valeur du scroll horizontal du viewport
     *
     * @memberOf Utils_ViewportClass#
     * @return integer scrollLeft
     */
    scrollLeft: function() {
        return $(window).scrollLeft();
    }
});