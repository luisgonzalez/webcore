/**
 * Standard class to handle HtmlFormView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var HtmlFormView = new Class
({
    Implements: HtmlView,
    
    bindEvents: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        
        //Handles tooltip absolute reposition upon resizing.
        window.addEvent('resize', function() {
            formInstance.getElements('.' + instance.cssClassName + '-field-container').each(function(item) {
                item.getElements(+ instance.cssClassName + '-textfield,.' + instance.cssClassName + '-textarea,.' + instance.cssClassName + '-select,.' + instance.cssClassName + '-compoundlist,.' + instance.cssClassName + '-calendar,.' + instance.cssClassName + '-time,').each(function(el) {
                    instance.updateToolTip(item, el);
                });
            });
            formInstance.getElements('.' + instance.cssMasterClassName + '-field-error').each(function(item){
                instance.updateToolTip(item, item);
            });
        });
        
        // Handles focus and blur effects (tooltips and css mods)
        formInstance.getElements('.' + instance.cssClassName + '-field-container').each(function(item) {
            instance.createToolTip(item, item.getProperty('tooltip'));
            item.getElements('.' + instance.cssClassName + '-textfield,.' + instance.cssClassName + '-textarea,.' + instance.cssClassName + '-select,.' + instance.cssClassName + '-compoundlist,.' + instance.cssClassName + '-calendar,.' + instance.cssClassName + '-time,').each(function(el) {
                if (el.tagName.toLowerCase() == 'option') return; // hack, mootools is returning the option elements for no reason
                
                el.addEvents(
                {   'focus': function()
                    { instance.showToolTip(item, el); el.tween('border-color', '#666'); },
                    'blur': function()
                    { instance.hideToolTip(item); el.tween('border-color', '#bbb'); },
                    'keydown': function(event)
                    { if (event.key == 'enter' && el.tagName.toLowerCase() == 'input' && el.hasClass(instance.cssClassName + '-compoundlist') == false)
                        { instance.raiseFirstButton(); }
                    }
                });
            });
        });

        // Handles image's popup
        formInstance.getElements('.' + instance.cssClassName + '-filefield-image').each(function(item) {
            item.addEvent('click', function() { instance.imagePopup(item); });
        });
        
        // Handles button clicks and server-side event firing
        formInstance.getElements('.' + instance.cssClassName + '-button').each(function(item) {
            if (item.getProperty('eventname').substring(0,1) != '~')
            {
                item.addEvent('click', function() {
                    instance.buttonPanelSlideOut();
                    
                    if (item.tagName.toLowerCase() == 'button')
                        return instance.raiseServerEvent(this.id, item.getAttribute('name'), item.getAttribute('eventname'), '1');
                    else
                        return instance.raiseServerEvent(this.id, this.name, item.getAttribute('eventname'), '1');
                });
            }
        });

        // Handles combobox clicks and server-side event firing
        formInstance.getElements('select.' + instance.cssClassName + '-select[eventname]').each(function(item) {
            item.addEvent('change', function() {
                instance.buttonPanelSlideOut();
                return instance.raiseServerEvent(this.id, this.name, item.getAttribute('eventname'), '1');
            });
        });
        
        // Handles file upload fields if Async is enabled
        if (instance.enableAsyncEvents == true)
        {
            formInstance.getElements('input.' + instance.cssClassName + '-filefield').each(
               function(item) { item.addEvent('change', function() { instance.uploadFile(item); } )}
            );
        }
         
        // Handles calendar reloading
        formInstance.getElements('.' + instance.cssClassName + '-calendar').each(
            function(item) {
                if (item.getAttribute('disabled') == 'disabled') return;
                
                var css = instance.cssClassName;
                var itemId = item.id;
                var options  = {
                        direction: 0,
                        tweak: { x: 6, y: 0 },
                        classes: [
                             css + '-calendar' //@todo C'mon Mario fix this shit!
                            ,css + '-prev' 
                            ,css + '-next'
                            ,css + '-month'
                            ,css + '-year'
                            ,css + '-today'
                            ,css + '-invalid'
                            ,css + '-valid'
                            ,css + '-inactive'
                            ,css + '-active'
                            ,css + '-hover'
                            ,css + '-hilite'
                        ]};
                var fieldObj = {};
                fieldObj[itemId] = "Y-m-d";
                new DateField(fieldObj , options);
            }
        );
        
        // Handles compound list reloading
        formInstance.getElements('.' + instance.cssClassName + '-compoundlist').each(
            function(item) { new CompoundList(item.id, instance.cssClassName + '-compoundlist'); }
        );
        
        // Handles time reloading
        formInstance.getElements('.' + instance.cssClassName + '-time').each(
            function(item) {
                if (item.getAttribute('disabled') == 'disabled') return;
                
                item.setAttribute('dbvalue', '');
                item.setDbFriendlyHour = function(){
                    match = /^(\d{2}):(\d{2}) (AM|PM)$/i.exec(item.value);
                    if (match == null) time = '00:00:00';
                    else
                    {
                        h = match[1];
                        m = match[2];
                        h = (h.charAt(0) == '0' ? h.charAt(1).toInt() : h.toInt());
                        if (match[3].toUpperCase() == 'PM') h += 12;
            
                        if (h % 12 == 0 && h > 0) h -= 12;
                        if (h < 10) h = '0' + h;
            
                        time = h + ':' + m + ':00';
                    }
                    item.setAttribute('dbvalue', time);
                };
                
                item.addEvent("keyup", function(event)
                {
                    var e = new Event(event);
                    var r_val = "", val = "", c = "", add_j = false;
                    var i=0, loop = item.value.length, j=1;
                    
                    for(i=0; i<loop; i++)
                    {
                        c = item.value.charAt(i);
                        add_j = false;
                        
                        if (val.lastIndexOf(" ") <= 3)
                        {
                            if (c.match(/\d/))
                            {
                                val += c;
                                add_j = true;
                            }
                            
                            if ((c == ":") || (c == " "))
                            {
                                val += " ";
                                j = 1;
                            }
                        }
                        else
                        {
                            if (c.toLowerCase() == "AM".substr(0,1).toLowerCase()){
                                val += " "+"AM".substr(0,1).toLowerCase()+" ";
                                j = 1;
                            }
                            if (c.toLowerCase() == "PM".substr(0,1).toLowerCase()){
                                val += " "+"PM".substr(0,1).toLowerCase()+" ";
                                j = 1;
                            }
                        }
                        
                        if (j%2 == 0) val += " ";
                        if (add_j) j++;
                    }
                    
                    val = val.replace(/(\s+)/g, " ").split(" ");
                    
                    val[0] = (val[0].toInt() % 13);
                    if (isNaN(val[0])) val[0] = null;
                    
                    if ($defined(val[1]))
                    {
                        if (val[0] < 10) r_val += "0";
                        r_val += val[0]+":";
                        val[1] = (val[1].toInt() % 60);
                        if (isNaN(val[1])) val[1] = null;
                    }
                    else
                    {
                        if ($defined(val[0])) r_val += val[0];
                    }
                    
                    if ($defined(val[2]))
                    {
                        if (val[1] < 10) r_val += "0";
                        r_val += val[1]+" ";
                        
                        if (val[2] == "AM".substr(0,1).toLowerCase())
                            r_val += "AM";
                        else if (val[2] == "PM".substr(0,1).toLowerCase())
                            r_val += "PM";
                    }
                    else
                    {
                        if ($defined(val[1])) r_val += val[1];
                    }
                    
                    item.value = r_val.trim();
                    item.setDbFriendlyHour();
                    e.stop();
                    return false;
                });
                
                item.addEvent("blur", function()
                {
                    var val = "";
                    var h_val = item.value.substr(0,2).toInt() || 12;
                    var m_val = item.value.substr(3,2).toInt() || 0;
                    var p_val = item.value.substr(6,2) || "";
        
                    if (h_val < 10) val += "0";
                    val += h_val+":";
                    
                    if (m_val < 10) val += "0";
                    val += m_val + " ";
                    
                    if (p_val == "") p_val = "AM";
                    val += p_val;
                    
                    item.value = val;
                    item.setDbFriendlyHour();
                    item.fireEvent('change');
                });
            }
        );
        
        formInstance.getElements('.' + instance.cssClassName + '-datetimefield').each(
        function (item) {
            name = item.get('name');
            timeField = formInstance.getElement('#' + name + '_time');
            dateField = formInstance.getElement('#' + name + '_date');
            [timeField, dateField].each(
                function(el){
                    el.addEvent('change', function(){
                        if (dateField.get('value') == '') item.set('value', '');
                        else item.set('value', (dateField.get('value') + ' ' + timeField.getAttribute('dbvalue')).trim());
                    });
                });
            }
        );
        // Handles rich text area reloading
        formInstance.getElements('.' + instance.cssClassName + '-richtext').each(
            function(item) {
                var rte = new nicEditor({fullPanel : true}).panelInstance(item.id);
                rte.addEvent('blur', function() {
                    var rti = rte.instanceById(item.id);
                    if ($chk(rti))
                    {
                        var content = rti.getContent();
                        content = content.trim();
                        if (content.toLowerCase() == '<br />' || content.toLowerCase() == '<br/>' || content.toLowerCase() == '<br>') content = '';
                        item.value = content;
                    }
                });
            }
        );
        
        // Handles all tabs
        formInstance.getElements('.' + instance.cssClassName + '-tabview').each(
            function(item) { new TabView(item.id, instance.cssClassName); }
        );
        
        // Handles checkbox group reloading
        formInstance.getElements('.' + instance.cssClassName + '-checkboxgroup').each(
            function(item) {
                var hiddenField = item.getElement('input.' + instance.cssClassName + '-checkboxgroup-hidden');
                
                item.getElements('input.' + instance.cssClassName + '-checkboxgroup-item').addEvent('click', function() {
                    var data = '';
                    
                    item.getElements('input.' + instance.cssClassName + '-checkboxgroup-item').each(function (checkItem) {
                        if (checkItem.get('checked'))
                            data += checkItem.get('value') + "|";
                    });
                    
                    hiddenField.set('value', data);
                });
                
                var data = '';
                
                item.getElements('input.' + instance.cssClassName + '-checkboxgroup-item').each(function (checkItem) {
                    if (checkItem.get('checked'))
                        data += checkItem.get('value') + "|";
                });
                
                hiddenField.set('value', data);
            }
        );
        
        // Show the buttons initially
        formInstance.getElements('.' + instance.cssClassName + '-buttonpanel').each(
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
    },
    
    raiseFirstButton: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        
        formInstance.getElements('.' + instance.cssClassName + '-button').each(function(item) {
            if (item.getProperty('eventname').substring(0,1) != '~')
            {
                instance.buttonPanelSlideOut();
                
                if (item.tagName.toLowerCase() == 'button')
                    return instance.raiseServerEvent(item.id, item.getAttribute('name'), item.getAttribute('eventname'), '1');
                else
                    return instance.raiseServerEvent(item.id, item.name, item.getAttribute('eventname'), '1');
            }
        });
    },
    
    /**
     * Hides the buttons panel and replaces it with an asynchronous progress bar
     */
    buttonPanelSlideOut: function()
    {
        var instance = this;
        // Handles the nice button panel effect upon form loading
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-buttonpanel').each(
            function (item)
            {
                // Nice animation to signal the user the response is being loaded.
                var slideFx = new Fx.Slide(item, {
                    duration: 300,
                    transition: Fx.Transitions.Cubic.easeOut
                });
                slideFx.addEvent('complete', function() { this.hide(); });
                slideFx.show().slideOut();
            }
        );
    },
    
    /**
     * Popups image
     */
    imagePopup: function(imageEl)
    {
        var instance = this;
        instance.showBodyOverlay();
        
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-filefield-pin').dispose();
       
        var pinDiv = new Element('div', { 'class': instance.cssClassName + '-filefield-pin',
                                         'style': 'z-index: 600; position: absolute; background-color: #FFF' });
        pinDiv.addEvent('click', function() { pinDiv.fade('out'); instance.hideOverlay(); });
        
        $(instance.htmlViewId).grab(pinDiv);
        instance.updatePin(pinDiv, false);
        window.addEvent('resize', function() { instance.updatePin(pinDiv, true); });
        window.addEvent('scroll', function() { instance.updatePin(pinDiv, true); });
       
       var imageAsset = new Asset.image(imageEl.src + '&option=normal', { onload: function(img) {
                var offsets = $(document.body).getScroll();
                var leftImg = ((window.getWidth() - img.width) / 2) + offsets.x;
                var topImg = ((window.getHeight() - img.height) / 2) + offsets.y;
                
                pinDiv.setStyle('background-image', 'url(' + img.src + ')');
                pinDiv.set('morph', {transition: 'linear', duration: 200});
                pinDiv.morph({ left: leftImg, top: topImg, height: img.height, width: img.width });
            }
        });
    },
    
    uploadFile: function (fileItem)
    {
        if (fileItem.value == null || fileItem.value == '') return;
        var instance = this;
        
        instance.buttonPanelSlideOut();
        instance.showOverlay();
        
        var eventId = 'eventValue_' + fileItem.name + '_' + fileItem.getAttribute('eventname');
        var eventEl = new Element('input', { 'type' : 'hidden', 'id' : eventId, 'name' : eventId, 'value' : '1'});
        $(instance.htmlViewId).appendChild(eventEl);
        
        var n = 'i' + instance.htmlViewId;
        var d = document.createElement('DIV');
        d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'"></iframe>';
        $(instance.htmlViewId).appendChild(d);
        
        $(n).addEvent('load', function()
        { 
            if (this.contentDocument)
               var d = this.contentDocument;
            else if (this.contentWindow)
               var d = this.contentWindow.document;
            else
               var d = this.document;
        
            if (d.location.href == "about:blank") return;
            
            var formParent = $(instance.htmlViewId).getParent();
            
            if (formParent.get('class') == 'view-workspace')
                formParent = formParent.getParent();
                
            if (formParent.get('class') == 'view-frame')
                formParent = formParent.getParent();
                
            if (formParent.get('tag') == 'body')
                controller.log("WARNING: The parent element is the document's body. Wrap the render() method inside a div for this view.");
            
            formParent.set('html', d.getElementsByTagName('head')[0].innerHTML + d.body.innerHTML);
            window.addEvent('domready', function() { instance.bindEvents(); });
        });
        
        $(instance.htmlViewId).setAttribute('target', n);
        $(instance.htmlViewId).submit();
    }
    
    // DO NOT ADD A COMMA AT THE END OF THE LAST FUNCTION DECLARATION. IE is unable to parse the class properly!
});
