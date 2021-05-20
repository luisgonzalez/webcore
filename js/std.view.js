/**
 * Standard class to handle HtmlViews on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var HtmlView = new Class
({
    Implements: [Options],

    /**
     * Constructor
     *
     * @param htmlViewId {string}
     * @param cssClassName {string}
     * @param enableAsyncEvents {bool}
     * @param notBindEvents {bool}
     * @param cssMasterClassName {string}
     * @param options {object}
     */
    initialize: function(htmlViewId, cssClassName, enableAsyncEvents, notBindEvents, cssMasterClassName, options)
    {
        this.cssClassName = cssClassName;
        this.htmlViewId = htmlViewId;
        this.enableAsyncEvents = enableAsyncEvents;
        this.cssMasterClassName = (cssMasterClassName == undefined) ? 'view' : cssMasterClassName;

        if (options != undefined) this.setOptions(options);

        var instance = this;
        
        if (notBindEvents == undefined || notBindEvents == false)
            window.addEvent('domready', function() { instance.bindViewEvents(); });
    },
    
    bindViewEvents: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        formInstance.addEvent('submit', function (ev) { new Event(ev).stop() });
        window.addEvent('resize', function() {
            // hide buttonmenus on resize
            formInstance.getElements('div.' + instance.cssMasterClassName + '-toolbar-buttonmenu').each(function(item) {
                item.setStyle('visibility', 'hidden');
            });
        });


        formInstance.getElements('.' + instance.cssClassName + '-field-container').each(function(item){
            var el = item.getNext('.' + instance.cssMasterClassName + '-field-error');
            if (!$chk(el)) return;
            
            el.addEvents({
                'mouseover': function() { instance.showToolTip(el, el); },
                'mouseout': function() { instance.hideToolTip(el, el); }
            });
        });
    
        formInstance.getElements('.' + instance.cssMasterClassName + '-toolbar-button').each(
            function(item) {
                item.addEvent('click', function() {
                    return instance.raiseServerEvent(
                            item.getAttribute('id'),
                            item.getAttribute('name'),
                            item.getAttribute('eventname'),
                            item.getAttribute('eventvalue'));
                });
            }
        );
        
        //@todo Fix this classname
        formInstance.getElements('.menubutton-item').each(function (item) {
			item.addEvent('click', function() {
				var buttonMenu = item.getParent('div.' + instance.cssMasterClassName + '-toolbar-buttonmenu');
				buttonMenu.fade('hide');
                
				return instance.raiseServerEvent(
					item.getProperty('id'),
					$chk(item.getProperty('custommanager')) ? item.getProperty('custommanager') : item.getProperty('name'),
					item.getProperty('eventname'),
					item.getAttribute('eventvalue'));
			});
        });
        
        formInstance.getElements('div.' + instance.cssMasterClassName + '-toolbar-buttonmenu').each(function(item) {
            item.addEvent('mouseleave', function(event) { item.fade('out', 'fast'); }); 
            item.addEvent('mouseenter', function(event) { item.fade('in', 'fast');  });
        });
        
        formInstance.getElements('.' + instance.cssMasterClassName + '-toolbar-button-menu').each(
            function(item) {
                item.addEvent('click', function(event) {
                    var buttonMenu = item.getNext('div.' + instance.cssMasterClassName + '-toolbar-buttonmenu');
                    buttonMenu.setStyle('visibility', 'visible');
                    buttonMenu.setOpacity('1');
                    var coords = item.getCoordinates(window);
                    var applyLeft = true;
                    
		    /*
		    // Looks like this is no longer needed
                    item.getParents().each(function(pItem) {
                        if (pItem.getComputedStyle('float') == 'left' || pItem.getComputedStyle('float') == 'right') applyLeft = false;
                    });
		    */
                    
                    buttonMenu.setStyle('top', (coords.top + coords.height) + 'px');
                    
                    if (applyLeft)
                        buttonMenu.setStyle('left', (coords.left) + 'px');
                    else
                        buttonMenu.setStyle('left', (coords.left / 2) + 'px');
                        
                    buttonMenu.fade('in', 'fast');
                });
                
                item.addEvent('mouseleave', function(event) {
                    var buttonMenu = item.getNext('div.' + instance.cssMasterClassName + '-toolbar-buttonmenu');
                    var coords = item.getCoordinates(window);
                    var applyLeft = true;
                    /*
                    item.getParents().each(function(pItem) {
                        if (pItem.getComputedStyle('float') == 'left' || pItem.getComputedStyle('float') == 'right') applyLeft = false;
                    });
		    */
                    buttonMenu.setStyle('top', (coords.top + coords.height) + 'px');
                    
                    if (applyLeft)
                        buttonMenu.setStyle('left', (coords.left) + 'px');
                    else
                        buttonMenu.setStyle('left', (coords.left / 2) + 'px');
                        
                    if (buttonMenu.getStyle('visibility') != 'hidden')
                        buttonMenu.fade('out');
                    else
                        buttonMenu.fade('hide');
                });
            }
        );
        
        instance.bindEvents();
    
        formInstance.getElements('.' + instance.cssMasterClassName + '-error').each(
            function (item) { instance.displayMessageBox(item); }
        );
        
        formInstance.getElements('.' + instance.cssMasterClassName + '-message').each(
            function (item) { instance.displayMessageBox(item); }
        );
    },
    
    displayMessageBox: function(item)
    {
        var instance = this;
        var formInstanceControls = $(instance.htmlViewId).getElements('input,select,textarea');
        formInstanceControls.each(function (el) { el.store('prevdisabled', el.get('disabled')); el.set('disabled', 'disabled'); });

        item.setStyle('display', '');
        item.setStyle('z-index', '1000');
        instance.updateMessageBox(item);
        instance.showOverlay();
        item.fade('hide').fade('show');
        
        var closeEl = item.getElement('.' + instance.cssMasterClassName + '-closemessage');
        closeEl.focus();
        closeEl.addEvent('click', function(){ formInstanceControls.each(function (el) { el.set('disabled', el.retrieve('prevdisabled')); }); });

        if (closeEl.get('redirect') != null)
            closeEl.addEvent('click', function () { controller.transfer(closeEl.get('redirect'), '{}'); });
        else
            closeEl.addEvent('click', function () { item.fade('hide'); instance.hideOverlay(); });

        window.addEvent('resize', function () { instance.updateMessageBox(item); });
        window.addEvent('load', function () { instance.updateMessageBox(item); });
        instance.updateMessageBox(item);
        
    },
    
    getPositionMode: function()
    {
        var instance = this;
        
        return $(instance.htmlViewId).getParent().getStyle('position');
    },
    
    /**
     * Gets the base css class name to use.
     * @returns {string}
     */
    getCssClassName: function()
    {
        return this.cssClassName;
    },
    
    getPageSize: function()
    {
        var xScroll, yScroll;
        
        if (window.innerHeight && window.scrollMaxY) {    
            xScroll = window.innerWidth + window.scrollMaxX;
            yScroll = window.innerHeight + window.scrollMaxY;
        } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
            xScroll = document.body.scrollWidth;
            yScroll = document.body.scrollHeight;
        } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
            xScroll = document.body.offsetWidth;
            yScroll = document.body.offsetHeight;
        }
        
        var windowWidth, windowHeight;
        
        if (self.innerHeight) {    // all except Explorer
            if(document.documentElement.clientWidth){
            windowWidth = document.documentElement.clientWidth; 
            } else {
            windowWidth = self.innerWidth;
            }
            windowHeight = self.innerHeight;
        } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
            windowWidth = document.documentElement.clientWidth;
            windowHeight = document.documentElement.clientHeight;
        } else if (document.body) { // other Explorers
            windowWidth = document.body.clientWidth;
            windowHeight = document.body.clientHeight;
        }    
        
        // for small pages with total height less then height of the viewport
        if (yScroll < windowHeight) pageHeight = windowHeight;
        else pageHeight = yScroll;
    
        // for small pages with total width less then width of the viewport
        if (xScroll < windowWidth) pageWidth = xScroll;        
        else pageWidth = windowWidth;
    
        return [pageWidth,pageHeight];
    },
    
    //@todo Validate to use Mask in Document Body
    showBodyOverlay: function()
    {
        var instance = this;
        
        if ($chk(instance.overlay)) instance.overlay.destroy();
        instance.overlay = new Element('div', { 'style' : 'position: absolute; background-color: #000; z-index: 500;'});
        $(document.body).appendChild(instance.overlay);
        instance.overlay.setOpacity(0.01);
        instance.overlay.fade(0.9);
        window.addEvent('resize', function() { instance.updateBodyOverlay(); });
        instance.updateBodyOverlay()
    },
    
    updateBodyOverlay: function()
    {
        var instance = this;
        var arrayPageSize = instance.getPageSize();
        
        instance.overlay.setStyle('width', arrayPageSize[0] + 'px');
        instance.overlay.setStyle('height', arrayPageSize[1] + 'px');
        instance.overlay.setStyle('left', '0');
        instance.overlay.setStyle('top', '0');
    },
    
    /**
     * Displays an overlay on top of the form's content
     */
    showOverlay: function()
    {
        var instance = this;
        if ($chk(instance.overlay)) instance.overlay.destroy();
        instance.overlay = new Element('div', { 'style' : 'position: absolute; background-color: #000; z-index: 500;'});
        
        try {
            var content = $(instance.htmlViewId).getParent();
            if ($chk(content) == false) return;
            content.appendChild(instance.overlay);
            
            instance.overlay.fade('show');
            instance.overlay.setOpacity(0.12);
            //instance.overlay.fade(0.15);
            window.addEvent('resize', function() { instance.updateOverlay(); });
            instance.updateOverlay();
        }
        catch (ex) {}
    },
    
    updateOverlay: function()
    {
        var instance = this;
        
        try {
            var content = $(instance.htmlViewId).getParent();
            if ($chk(content) == false) return;
        
            var coords = content.getCoordinates();
            instance.overlay.setStyle('width', coords.width + 'px');
            instance.overlay.setStyle('height', coords.height + 'px');
            
            if (instance.getPositionMode() == 'absolute')
            {
                instance.overlay.setStyle('left', '0');
                instance.overlay.setStyle('top', '0');
            }
            else
            {
                var applyLeft = true;        
                instance.overlay.setStyle('top', coords.top + 'px');
		/*
                content.getParents().each(function(item) {
                    if (item.getComputedStyle('float') == 'left' || item.getComputedStyle('float') == 'right') applyLeft = false;
                });
		*/
                if (applyLeft) instance.overlay.setStyle('left', coords.left + 'px');
            }
        }
        catch (ex) {}
    },
    
    /**
     * Hides the semi-transparent overlay used to block fields during a server request.
     */
    hideOverlay: function()
    {
        var instance = this;
        if ($chk(instance.overlay)) instance.overlay.fade('hide');
    },

    updatePin: function(pinDiv, bounce)
    {
        var offsets = $(document.body).getScroll();
        var leftDiv = ((window.getWidth() - pinDiv.getCoordinates().width) / 2) + offsets.x;;
        var topDiv = ((window.getHeight() - pinDiv.getCoordinates().height) / 2) + offsets.y;
        
        if (bounce)
        {
            pinDiv.set('morph', {duration: 'short', transition: 'cubic:out'});
            pinDiv.morph({ left: leftDiv, top: topDiv });
        }
        else
        {
            pinDiv.setStyle('left', leftDiv + 'px');
            pinDiv.setStyle('top', topDiv + 'px');
        }
    },
    
    doAsyncRequest: function()
    {
        var instance = this;
        
        instance.showOverlay();
        
        var asyncRequest = new Request.HTML({
            url: $(instance.htmlViewId).action,
            method: 'post',
            async: true,
            encoding: 'UTF-8',
            evalScripts: false
        });
        
        // successful handler
        asyncRequest.addEvent ('success',
            function (responseTree, responseElements, responseHTML, responseJavaScript) {
                try {
                    var htmlResponse = new Element('div');
                    htmlResponse.set('html', responseHTML);
                    var htmlTree = htmlResponse.getChildren();
                    for (var elIx = 0; elIx < htmlTree.length; elIx++){
                        var el = htmlTree[elIx];
                        if ($chk(el) == false) continue;
                        if ($chk(el.id) == false) continue;
                        var target = $(el.id);
                        var parTarget = target.getParent();
                        
                        if ($chk(target) && $chk(parTarget)) {
                            var parEl = new Element('div');
                            parEl.grab(el);
                            if (parTarget.get('tag') == 'body')
                                controller.log("WARNING: The parent element is the document's body. Wrap the render() method inside a div for this view.");
                            parTarget.set('html', parEl.get('html'));
                        }
                    }
                }
                catch (ex) {
                    controller.log('ERROR: ' + ex);
                }
                finally {
                    eval(responseJavaScript);
                    instance.hideOverlay();
                    asyncRequest.removeEvents();
                }
            });
        
        // failure handler
        asyncRequest.addEvent ('failure',
            function (xhr) {
                instance.hideOverlay();
                controller.log('Error: ' + xhr.status + ' ' + xhr.statusText);
                asyncRequest.removeEvents();
            });
        
        // Send the ajax request
        asyncRequest.post($(instance.htmlViewId));
    },
    
    /**
     * Updates the location of the floating Error dialog on top of the control.
     */
    updateMessageBox: function(item)
    {
        var instance = this;
        
        try {
            var parentEl = $(instance.htmlViewId + '_content');
            if (parentEl == null) parentEl = $(instance.htmlViewId);
            
            var coords = parentEl.getCoordinates();
            var itemCoords = item.getCoordinates();
            
            if (itemCoords.width > coords.width - (coords.width / 3))
            {
                item.setStyle('width', (0.50 * coords.width) + 'px');
                itemCoords = item.getCoordinates();
            }
            
            if (instance.getPositionMode() == 'absolute')
            {
                item.setStyle('left', ((coords.width - itemCoords.width) / 2.0) + 'px');
                item.setStyle('top', (0.3 * coords.height) + 'px');
            }
            else
            {
                var applyLeft = true;
                item.setStyle('top', (coords.top + 0.3 * coords.height) + 'px');
                
		/*
		// Looks like the new Mootools correctly computes coordinates. applyLeft Logic no longer needed
                parentEl.getParents().each(function(item) {
                    if (item.getComputedStyle('float') == 'left' || item.getComputedStyle('float') == 'right') applyLeft = false;
                });
		*/
                if (applyLeft)
                    item.setStyle('left', (coords.left + (coords.width - itemCoords.width) / 2.0) + 'px');
                else
                    item.setStyle('left', ((itemCoords.left / 2.0) + (coords.width - itemCoords.width) / 2.0) + 'px');
            }
            
            try { item.getElement('input.' + instance.cssClassName + '-error-close').focus(); } catch (ex) {}
        }
        catch (ex2) { controller.log('ERROR: ' + ex2); }
    },
    
    /**
     * Creates a tooltip with the defined text
     * @param text {string}
     */
    createToolTip: function(el, text)
    {
        if (!text) return;
        
        var instance = this;
        
        var divTooltipTop = new Element('div', {'class': instance.cssMasterClassName + '-tooltip-top'});
        var divTooltipContent =  new Element('div', {'class': instance.cssMasterClassName + '-tooltip-content', 'text':text.trim()});
        var divTooltipBottom = new Element('div', {'class': instance.cssMasterClassName + '-tooltip-bottom'});
    
        new Element('div').addClass(instance.cssMasterClassName + '-tooltip')
                    .setStyle('display', 'none')
                    .grab(divTooltipTop)
                    .grab(divTooltipContent)
                    .grab(divTooltipBottom)
                    .inject(el, 'after');
    },
    
    /**
     * Updates the position of the tooltip when the viewport is resized.
     */
    updateToolTip: function(referenceElement, el)
    {
        var instance = this;
        
        var toolTip = referenceElement.getNext('.' + instance.cssMasterClassName + '-tooltip');

        if (!$chk(toolTip)) return;
        
        var coords = referenceElement.getCoordinates();
        var offsetTop = coords.top - 5;
        var offsetLeft = coords.left + coords.width + 6
                        + parseInt(referenceElement.getComputedStyle('margin-left').replace(/px/,''))
                        + parseInt(referenceElement.getComputedStyle('margin-right').replace(/px/,''));
        
        if (el.hasClass(instance.cssClassName + '-calendar'))
            offsetTop -= toolTip.getCoordinates().height;

        if (instance.getPositionMode() == 'absolute')
        {
            var absOffset = $(instance.htmlViewId).getParent().getCoordinates();
            toolTip.setStyle('left', (offsetLeft - absOffset.left) + 'px');
            toolTip.setStyle('top', (offsetTop - absOffset.top) + 'px');   
        }
        else
        {
            toolTip.setStyle('left', offsetLeft + 'px');
            toolTip.setStyle('top', offsetTop + 'px');
        }
        toolTip.setStyle('position', 'absolute');
        toolTip.setStyle('zindex', '9999');
    },
    
    /**
     * Displays the tooltip for the given field.
     */                    
    showToolTip: function(fieldContainer, el)
    {
    var toolTip = fieldContainer.getNext('.' + this.cssMasterClassName + '-tooltip');
        if (!$chk(toolTip)) return;
        toolTip.setStyle('display', '');
        this.updateToolTip(fieldContainer, el);
        toolTip.setOpacity(0);
        toolTip.tween('opacity', 1.0);
    },

    /**
     * Hides the tooltip for the given field.
     */                    
    hideToolTip: function(fieldEl)
    {
	var toolTip = fieldEl.getNext('.' + this.cssMasterClassName + '-tooltip');
        if ($chk(toolTip)) toolTip.tween('opacity', 0.00);
    },
    
    refreshView : function()
    {
        var instance = this;
        
        if (instance.enableAsyncEvents == true)
            instance.doAsyncRequest();
        else
            $(instance.htmlViewId).submit();
    },
    
    /**
     * Raises a server-side event
     * @argument sourceId {string} The id of the element that caused the event
     * @argument modelName {string} The name of the model in the server script
     * @argument eventName {string} The name of the event to raise
     * @argument eventValue {string} The value of the event to pass
     * @returns {bool} false if the request is asynchronous, true if a normal postback should be performed
     */
    raiseServerEvent : function(sourceId, modelName, eventName, eventValue)
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        formInstance.getElements('.' + instance.cssMasterClassName + '-tooltip').each(function(item) { item.tween('opacity', 0.00); });
    
        controller.log("Raising server event " + eventName + ": { sourceID = " + sourceId + "; modelName = " + modelName + ";  eventName = " + eventName + ";eventValue = " + eventValue + "}");
        if (!$chk($(sourceId))) return;
        // Check if the event needs a new window
        var windowprops = $(sourceId).getProperty('windowprops');
        
        // If necessary, inject the model name hidden field
        var modelEl = null;
        
        if (formInstance.getElements('input[name=' + modelName + ']').length == 0 ||
            $(sourceId).getProperty('type') == 'submit')
        {
            modelEl = new Element('input', { 'type' : 'hidden', 'id' : modelName, 'name' : modelName, 'value' : eventValue});
            formInstance.grab(modelEl);
        }
        
        // Add the event value to fire on the server-side
        var eventId = 'eventValue_' + modelName + '_' + eventName;
        var eventEl = null;
        if ($(eventId) == null)
        {
            eventEl = new Element('input', { 'type' : 'hidden', 'id' : eventId, 'name' : eventId, 'value' : eventValue});
            formInstance.grab(eventEl);
        }
        else
        {
            eventEl = $(eventId);
        }
        
        // Send the request
        if (instance.enableAsyncEvents == true && windowprops == null)
        {
            instance.doAsyncRequest();
            
            if (modelEl != null) modelEl.dispose();
            if (eventEl != null) eventEl.dispose();
        }
        else
        {
            var buttonEl = null;
            
            if ($(sourceId).getAttribute('type') == 'submit')
            {
                buttonEl = new Element('input', { 'type' : 'hidden', 'id' : $(sourceId).getAttribute('id') + '__', 'name' : $(sourceId).getAttribute('name'), 'value' : $(sourceId).getAttribute('value')});
                formInstance.grab(buttonEl);
            }
            
            var originalTarget = formInstance.getProperty('target');
            
            if (windowprops != null)
            {
                if (windowprops == 'windowprops')
                {
                    formInstance.setProperty('target', '_blank');
                }
                else
                {
                    formInstance.setProperty('target', eventName + '_window');
                    var popup = window.open('', eventName + '_window', windowprops);
                    popup.focus();
                }
            }
            else instance.showOverlay();
            
            formInstance.submit();
            formInstance.setProperty('target', originalTarget);
            
            if (windowprops != null)
            {
                if (modelEl != null) modelEl.dispose();
                if (eventEl != null) eventEl.dispose();
                if (buttonEl != null) buttonEl.dispose();
            }
            
            return true;
        }
        
        return false;
    }
    // DO NOT ADD A COMMA AT THE END OF THE LAST FUNCTION DECLARATION. IE is unable to parse the class properly!
});
