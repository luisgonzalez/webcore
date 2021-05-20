/**
 * Standard class to handle SiteMenu on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var SiteMenu = new Class ({
    initialize: function(htmlViewId, cssClassName)
	{
        this.cssClassName = cssClassName;
        this.htmlViewId = htmlViewId;
        this.topOffset = 0;
        this.leftOffset = 2;
        this.subTopOffset = -2;
        this.subLeftOffset = 0;
        var instance = this;
        window.addEvent('domready', function() { instance.bindEvents(); });
    },
    
    bindEvents: function()
    {
        var instance = this;
        
        $(instance.htmlViewId).getElements('ul.' + instance.cssClassName + ' li').each(function(li){
            
            var childMenu = li.getElement('ul');
            
            // Submenu classes
            if(li.hasClass(instance.cssClassName + '-button') == false & childMenu != null){
                li.getElement('a').addClass(instance.cssClassName + '-drop-right');
            }
            if (li.hasClass(instance.cssClassName + '-button') == true & childMenu != null) {
                li.getElement('a').addClass(instance.cssClassName + '-drop-down');
            }
            // Size Calculations
            if(childMenu != null) {
                var elWidth = 0;
                var elHeight = 0;
                childEls = childMenu.getChildren('li');
                childEls.each(function(item){
                    if (item.getSize().x > elWidth) {
                        elWidth = item.getSize().x;
                    }
                    elHeight += item.getElement('a').getSize().y;
                });
				if(li.hasClass(instance.cssClassName + '-button')) {
					if (elWidth < li.getSize().x) elWidth = li.getSize().x;
				}
                childMenu.store('elHeight', elHeight);
                childMenu.store('elWidth', elWidth);
            }
            
            // enter event
            li.addEvent('mouseenter', function(ev){
                li.store('over', 'true');
                li.getElement('a').addClass(instance.cssClassName + '-item-highlight');
                if (li.getElement('a').hasClass(instance.cssClassName + '-drop-right')) {
                    li.getElement('a').addClass(instance.cssClassName + '-drop-right-enter');
                }
                if (li.getElement('a').hasClass(instance.cssClassName + '-drop-down')) {
                    li.getElement('a').addClass(instance.cssClassName + '-drop-down-enter');
                }
                li.addClass(instance.cssClassName + '-item-highlight');
                
                var childMenu = li.getElement('ul');
                if(childMenu == null) return;
                
                var thisCoord = li.getCoordinates(li.getParent('ul'));
                var elWidth = childMenu.retrieve('elWidth');
                var elHeight = childMenu.retrieve('elHeight');

                // Visual Effect isntantiation
                childMenu.set('morph', {duration: 600, transition: Fx.Transitions.Pow.easeOut});
                
                // For top-level buttons
                if(li.hasClass(instance.cssClassName + '-button'))
                {
                    // Visual Effects
                    childMenu.setStyle('height', '0');
                    childMenu.setStyle('width', elWidth + 'px');
                    childMenu.setStyle('visibility', 'visible');
                    childMenu.setStyle('overflow', 'hidden');
                    childMenu.get('morph').start({height: [0, elHeight]}).chain(function() { childMenu.setStyle('overflow', '');} );
                    
                    // Positioning
                    childMenu.setStyle('top', (thisCoord.height + instance.topOffset) + 'px');
                    childMenu.setStyle('left', (thisCoord.left + instance.leftOffset - 2) + 'px');
                }
                // For n-level buttons
                else
                {
                    // Visual effects
                    childMenu.setStyle('width', '0');
                    childMenu.setStyle('height', elHeight + 'px');
                    childMenu.setStyle('visibility', 'visible');
                    childMenu.setStyle('overflow', 'hidden');
                    childMenu.get('morph').start({width: [0, elWidth]}).chain(function() { childMenu.setStyle('overflow', '');} );
                    
                    // Positioning
                    var docWidth = $(document.body).getCoordinates().width;
                    var thisCoordFromBody = li.getCoordinates($(document.body));
                    
                    var topOffset = instance.subTopOffset;
                    var leftOffset = instance.subLeftOffset;
                    if(li.hasClass(instance.cssClassName + '-button'))
                    {
                        topOffset = instance.topOffset;
                        leftOffset = instance.leftOffset;
                    }
                    
                    if(docWidth > (thisCoordFromBody.left + thisCoordFromBody.width + childMenu.getCoordinates().width))
                    {
                        childMenu.setStyle('top', (thisCoord.top + topOffset) + 'px');
                        childMenu.setStyle('left', (thisCoord.width + leftOffset) + 'px');
                    }
                    else
                    {
                        childMenu.setStyle('top', (thisCoord.top + topOffset - 1) + 'px');
                        childMenu.setStyle('left', (-thisCoord.width + leftOffset) + 'px');
                    }
                }
            });
    
            li.addEvent('mouseleave',function(ev){
                li.store('over', 'false');
                (function() {
                    li.getElement('a').removeClass(instance.cssClassName + '-item-highlight');
                    li.getElement('a').removeClass(instance.cssClassName + '-drop-right-enter');
                    li.getElement('a').removeClass(instance.cssClassName + '-drop-down-enter');
                    
                    li.removeClass(instance.cssClassName + '-item-highlight');
                    
                    if (li.retrieve('over') == 'true') return void(0);
                    childMenu = li.getElement('ul');
                    if(childMenu == null) return void(0);
                    
                    childMenu.setStyle('overflow', 'hidden');
                    childMenu.set('morph', {duration: 200, transition: Fx.Transitions.Pow.easeOut});
                    var elWidth = childMenu.retrieve('elWidth');
                    var elWidth = childMenu.retrieve('elHeight');
                    
                    if(li.hasClass(instance.cssClassName + '-button')) {
                        childMenu.get('morph').start({height: [elHeight, 0]}).addEvent('complete',
                            function (ev) {
                                childMenu.setStyle('width', 0);
                                childMenu.setStyle('height', 0);
                                childMenu.setStyle('overflow', '');
                                childMenu.setStyle('visibility', 'hidden');
                            } );
                    }
                    else {
                        childMenu.get('morph').start({width: [elWidth, 0]}).addEvent('complete',
                            function (ev) {
                                childMenu.setStyle('width', 0);
                                childMenu.setStyle('height', 0);
                                childMenu.setStyle('overflow', '');
                                childMenu.setStyle('visibility', 'hidden');
                            } );
                    }
		    return void(0);
                }).delay(0);
            });
        });
    }
});