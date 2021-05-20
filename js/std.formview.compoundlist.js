/**
 * Standard class to handle CompoundList on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var CompoundList = new Class
({
    /**
     * Constructor
     *
     * @param compoundListId {string}
     * @param cssClassName {string}
     */
    initialize: function(compoundListId, cssClassName)
    {
        this.cssClassName = cssClassName;
        this.compoundListId = compoundListId;
        this.compoundContainer = new Element('div', {'class': this.cssClassName + '-listcontainer' });
        this.currentItems = new Hash();
        var instance = this;
        window.addEvent('domready', function() { instance.generateList(); });
    },
    
    generateList: function()
    {
        var instance = this;
        
        var button = new Element('a', { 'id' : instance.compoundListId + '_button',
                                            'class' : instance.cssClassName + '-button-add', 'href' : 'javascript: void(0);' });
        button.addEvent('click', function() { instance.addItem(); })
        button.injectAfter($(instance.compoundListId));
        $(instance.compoundListId).addEvent('keydown', function(ev){
            if (ev.key == 'enter') {
                instance.addItem();
                ev.stop();
            }
        });
        instance.compoundContainer.injectAfter(button);

        $(instance.compoundListId + '_items').value = $(instance.compoundListId + '_items').value.replace(/\\"/g, '"');
        var items = JSON.decode($(instance.compoundListId + '_items').value);
        
        $each(items, function(value, key) {
            instance.currentItems.set(key, value);
            instance.renderItem(key, value);
        });
    },
    
    addItem: function()
    {
        var instance = this;
        var value = $(instance.compoundListId).value;
        var ref = "_" + value + "_";
        
        if (value == "") return;
        
        $(instance.compoundListId).value = "";
        instance.renderItem(ref, value);
        instance.currentItems.set(ref, value);
        instance.updateList();
    },
    
    renderItem: function(itemRef, itemValue)
    {
        var instance = this;
        var itemDiv = new Element('div', { 'class' : instance.cssClassName + '-item' });
        itemDiv.set('itemRef', itemRef);
        
        var itemInput = new Element('input', { 'class' : instance.cssClassName,
                                    'type' : 'text', 'value' : itemValue });
        itemDiv.appendChild(itemInput);
        itemInput.addEvent('keydown', function(event) { event.stop(); } );
        var button = new Element('a', { 'class' : instance.cssClassName + '-button-delete', 'href' : 'javascript: void(0);' });
        button.addEvent('click', function() { instance.deleteItem(itemDiv); })
        itemDiv.appendChild(button);
        itemDiv.inject(instance.compoundContainer, 'bottom');
    },
    
    deleteItem: function(divEl)
    {
        var instance = this;
        var itemRef = divEl.get('itemRef');
        
        if (instance.currentItems.has(itemRef))
            instance.currentItems.erase(itemRef);
        
        instance.updateList();
        divEl.dispose();
    },
    
    updateList: function()
    {
        var instance = this;
        
        $(instance.compoundListId + '_items').value = instance.currentItems.toJSON();
    }
});