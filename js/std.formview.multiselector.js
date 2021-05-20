/**
 * Standard class to handle Multiselector on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var Multiselector = new Class(
{
    /**
     * Constructor
     *
     * @param multiselectorId {string}
     * @param cssClassName {string}
     */
    initialize: function(multiselectorId, cssClassName)
    {
        this.cssClassName = cssClassName;

        multiselector = $(multiselectorId);
        input = multiselector.getElement('input');
        buttonAdd = $('multiselector_' + input.name + 'ButtonAdd');
        buttonRemove = $('multiselector_' + input.name + 'ButtonRemove');
        
        multiselector.set({
            'divSelected': $('multiselector_' + input.name + 'Selected').id,
            'divAvailable': $('multiselector_' + input.name + 'Available').id,
            'buttonAdd': buttonAdd.id,
            'buttonRemove': buttonRemove.id,
            'input': input.id
        });
        
        buttonAdd.addEvent('click', this.moveOptions);
        buttonRemove.addEvent('click', this.moveOptions);

        var instance = this;
        multiselector.getElements('.' + instance.cssClassName + '-multiselector-select').each(function (combobox){
            combobox.getElements('.' + instance.cssClassName + '-multiselector-option').each(function(option){
                instance.optionSetEvents(option);
                instance.optionMakeDraggable(option);
            });
        });
    },

    moveOptions: function()
    {
        multiselector = instance.getMultiselector(this);
        input = $(multiselector.get('input'));

        if(this == $(multiselector.get('buttonAdd')))
        {
            source = $(multiselector.get('divAvailable'));
            destination = $(multiselector.get('divSelected'));
        }
        else
        {
            source = $(multiselector.get('divSelected'));
            destination = $(multiselector.get('divAvailable'));
        }
        
        source.getElements('.' + instance.cssClassName + '-multiselector-option-selected').each(function(o){
            instance.insertOption(o, destination);
        });
        
        instance.updateValue(input);
    },
    
    optionSetEvents: function(el){
        el.addEvents({'mouseover': this.optionMouseOver,
                     'mouseout': this.optionMouseOut,
                     'mousedown': this.optionClick})
    },

    optionMouseOver: function() { this.addClass(instance.cssClassName + '-multiselector-option-hover'); },
    optionMouseOut: function() { this.removeClass(instance.cssClassName + '-multiselector-option-hover'); },
    
    optionClick: function() {
        className = instance.cssClassName + '-multiselector-option-selected';
        if(this.hasClass(className))
            this.removeClass(className);
        else
            this.addClass(className);
    },

    updateValue: function(input)
    {
        var arrayValues = new Array();
        multiselector = this.getMultiselector(input);

        selected = $(multiselector.get('divSelected'));

        selected.getElements('.' + this.cssClassName + '-multiselector-option').each(function(option){
            arrayValues.push(option.get('optionValue'));
        });
        
        input.value = JSON.encode(arrayValues);
    },
    
    insertOption: function(el, container)
    {
        el.removeClass(this.cssClassName + '-multiselector-option-hover');
        el.removeClass(this.cssClassName + '-multiselector-option-selected');
        el.removeEvents();
        
        this.optionSetEvents(el);

        var before = null;
        
        container.getElements('.' + this.cssClassName + '-multiselector-option').each(function(option){
            if(before != null) return;
            if(el.get('text').toUpperCase() < option.get('text').toUpperCase())
                before = option;
        });
        
        if(before == null) el.inject(container,'bottom');
        else el.inject(before, 'before');
        
        this.optionMakeDraggable(el);
    },
    
    optionGetParent: function(o)
    {
        return o.getParent('.' + this.cssClassName + '-multiselector-select');
    },
    
    getMultiselector: function(o)
    {   
        return o.getParent('.' + this.cssClassName + '-multiselector');
    },
    
    optionGetDroppable: function(o)
    {
        multiselector = this.getMultiselector(o);
        if(multiselector.get('divAvailable') == this.optionGetParent(o).id)
            return $(multiselector.get('divSelected'));
        return $(multiselector.get('divAvailable'));
    },

    optionMakeDraggable: function(o)
    {
        instance = this;
        container = instance.optionGetParent(o);
        droppable = instance.optionGetDroppable(o);

        o.addEvent('mousedown', function(event){
            newEvent = new Event(event).stop();
            
            var option = this;
            var multiselector = instance.getMultiselector(this);
            var droppable = instance.optionGetDroppable(this);
            var coords = this.getCoordinates();
            
            // IE can't handle the Drag when the draggable is injected to the body. so the draggable will be injected to this div
            var div = new Element('div')
                        .setStyles({position:'absolute',top:'0px',width:'0px',height:'0px'})
                        .inject($(document.body));
                                    
            var clone = this.clone()
                     .setStyles({'position':'absolute','opacity':0.7})
                     .setStyles(coords)
                     .addEvent('click', function(){ el.dispose(); })
                     .inject(div);
            
            new Drag.Move(clone, {
                'droppables': droppable,
                'container': multiselector,
                'onCancel': function(el) { el.dispose(); div.dispose();},
                'onDrop': function(el, dropDest, event){
                    if(dropDest) // null if the item was dropped outside the droppable
                    {
                        instance.insertOption(option, dropDest);
                        instance.updateValue($(multiselector.get('input')));
                        el.dispose();
                    }
                    else
                    {
                        new Fx.Morph(el)
                            .addEvent('onComplete', function(){ el.dispose(); })
                            .start({top:coords.top, left:coords.left});
                    }
                    div.dispose();
                }
            }).start(newEvent);
        });
    }
});
