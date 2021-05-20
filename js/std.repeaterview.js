/**
 * Standard class to handle HtmlRepeaterView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var HtmlRepeaterView = new Class
({
    Implements: HtmlView,
    
    bindEvents: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        var initialIndex = 0;
        var targetItemHeight = 0;
        var targetContainerHeight = 0;
        var activeItem = null;
        
        if ($chk(formInstance.getAttribute('initialindex')))
            initialIndex = formInstance.getAttribute('initialindex');
            
        formInstance.getElements('.' + instance.cssClassName + '-accordion-item').each(
            function(item, index) {
                var h = item.getSize().y;
                if (h > targetItemHeight) targetItemHeight = h;
                item.store('initialHeight', h);
                if (index != initialIndex) item.setStyle('display', 'none');
                else activeItem = item;
            }
        );
        
        targetContainerHeight += targetItemHeight;
        if ($chk(activeItem)) activeItem.setStyle('height', targetItemHeight + 'px');
        
        formInstance.getElements('.' + instance.cssClassName + '-accordion-title-inactive').each(
            function(item, index) {
                targetContainerHeight += item.getSize().y;
                targetContainerHeight += isNaN(parseInt(item.getComputedStyle('margin-top'))) ? 2 : parseInt(item.getComputedStyle('margin-top'));
                targetContainerHeight += isNaN(parseInt(item.getComputedStyle('margin-bottom'))) ? 2 : parseInt(item.getComputedStyle('margin-bottom'));
                targetContainerHeight += isNaN(parseInt(item.getParent().getComputedStyle('margin-top'))) ? 2 : parseInt(item.getParent().getComputedStyle('margin-top'));
                targetContainerHeight += isNaN(parseInt(item.getParent().getComputedStyle('margin-bottom'))) ? 2 : parseInt(item.getParent().getComputedStyle('margin-bottom'));
                 
                if (index == initialIndex)
                {
                    item.removeClass(instance.cssClassName + '-accordion-title-inactive');
                    item.addClass(instance.cssClassName + '-accordion-title');
                    
                    formInstance.getElements('.' + instance.cssClassName + '-container').each(
                        function (container) { container.addClass(instance.cssClassName + '-container-accordion'); }
                    );
                }
                
                item.addEvent('mouseover', function () {
                    item.addClass(instance.cssClassName + '-accordion-title-hover');
                });
                
                item.addEvent('mouseout', function () {
                    item.removeClass(instance.cssClassName + '-accordion-title-hover');
                });
                
                item.addEvent('click', function () {
                    var activeTitle = $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-accordion-title')[0];
                    var activePanel = activeTitle.getParent().getElements('.' + instance.cssClassName + '-accordion-item')[0];
                    var newPanel = item.getParent().getElements('.' + instance.cssClassName + '-accordion-item')[0];
                    
                    if (activePanel == newPanel) return;
                    
                    activeTitle.removeClass(instance.cssClassName + '-accordion-title');
                    activeTitle.addClass(instance.cssClassName + '-accordion-title-inactive');
                    
                    var slideOut = new Fx.Tween(activePanel, {property: 'height', duration: 300});
                    activePanel.setStyle('overflow', 'hidden');
                    slideOut.start(0).chain(function() { activePanel.setStyle('display', 'none'); });
                    
                    item.removeClass(instance.cssClassName + '-accordion-title-inactive');
                    item.addClass(instance.cssClassName + '-accordion-title');
                    
                    var slideIn = new Fx.Tween(newPanel, {property: 'height', duration: 200});
                    newPanel.setStyle('height', '0');
                    newPanel.setStyle('display', 'block');
                    newPanel.setStyle('overflow', 'hidden');
                    var finalHeight = newPanel.retrieve('initialHeight');
                    finalHeight = targetItemHeight; // @todo calc repeaterview-container-accordion height ans set overflow to hidden
                    slideIn.start(finalHeight).chain(function() { newPanel.setStyle('overflow', ''); newPanel.setStyle('height', finalHeight + 'px'); });
                });
            }
        );
        
        formInstance.getElements('.' + instance.cssClassName + '-container-accordion').each(
            function (item) {
                item.setStyle('height', targetContainerHeight + 'px');
                item.setStyle('overflow', 'hidden');
        });
        
        // Pager button events
        formInstance.getElements('a[eventname=GoPageIndex]').each(function (item) {
            item.addEvent('click', function() {
                return instance.raiseServerEvent(
                    $(instance.htmlViewId).getAttribute('id'),
                    $(instance.htmlViewId).getAttribute('name') + "Pager",
                    'GoPageIndex',
                    item.getAttribute('eventvalue'));
                }
            );
        });
        
        // Handles button clicks and server-side event firing
        formInstance.getElements('.' + instance.cssClassName + '-button').each(function(item) {
            item.addEvent('click', function() {
                var hiddenEl = new Element('input', { 'type' : 'hidden', 'name' : this.name, 'value' : item.getAttribute('eventvalue') });
                formInstance.appendChild(hiddenEl);
                
                return instance.raiseServerEvent(
                    this.id,
                    this.name,
                    item.getAttribute('eventname'),
                    item.getAttribute('eventvalue'));
            });
        });
    }
});