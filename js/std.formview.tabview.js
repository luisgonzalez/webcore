/**
 * Standard class to handle TabView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var TabView = new Class
({
    /**
     * Constructor
     *
     * @param tabviewId {string}
     * @param cssClassName {string}
     */
    initialize: function(tabviewId, cssClassName)
    {
        var instance = this;
        instance.cssClassName = cssClassName;
        instance.activeContainerNameControl = $(tabviewId + '_activeTabPage');
        instance.activeContainerName = instance.activeContainerNameControl.value;
        instance.tabViewId = tabviewId;

        $$('#' + tabviewId + ' li.' + cssClassName + '-tabtitle').each(
            function(item, index) {
                if (item.get('container') == instance.activeContainerName)
                {
                    instance.activeTitle = item;
                }
                else
                {
                    item.removeClass(cssClassName + '-tabtitle');
                    item.addClass(cssClassName + '-tabtitle-inactive');
                }
                
                item.addEvent('click', function() { instance.showTabPage(item); }
            )}
        );
        
        $$('#' + tabviewId + ' div.' + cssClassName + '-tabpage').each(
            function(item, index) {
                if (item.get('id') == 'tabpage_' + instance.activeContainerName) {
                    instance.activeTab = item;
                } else {
                    item.setStyle('display', 'none');
                }
            }
        );
    },
    
    setTabPage: function(tabPageName)
    {
        var instance = this;
        var titleEl = $(instance.tabViewId).getElement('li[container=' + tabPageName + ']');
        titleEl.fireEvent('click');
    },
    
    showTabPage: function(el)
    {
        var instance = this;
        var tab = $('tabpage_' + el.get('container'));
        if (tab.getStyle('display') == 'block') return;

        instance.activeTab.setStyle('display', 'none');
        instance.activeTitle.addClass(instance.cssClassName + '-tabtitle-inactive');
        instance.activeTitle.removeClass(instance.cssClassName + '-tabtitle');
        
        el.removeClass(instance.cssClassName + '-tabtitle-inactive');
        el.addClass(instance.cssClassName + '-tabtitle');
        
        tab.setStyle('display', 'block');
        tab.setStyle('visibility', 'visible');
        tab.setOpacity(0.0);
        tab.fade('in');
        instance.activeTab = tab;
        instance.activeTitle = el;

        instance.activeContainerNameControl.value = el.get('container');
        
        tab.getElements('.' + instance.cssClassName + '-buttonpanel').each(
            function (item)
            {
                item.setStyle('height', '40px'); // HACK: regular postbacks make the button panel flicker without this
                // Nice animation to signal the user the response is being loaded.
                var slideFx = new Fx.Slide(item, {
                    duration: 300,
                    transition: Fx.Transitions.Cubic.easeIn
                });
                slideFx.addEvent('complete', function() { this.show(); });
                slideFx.hide().slideIn();
            }
        );
    }
});