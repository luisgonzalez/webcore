/**
 * Standard class to handle HtmlEditableRepeaterView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var HtmlEditableRepeaterView = new Class
({
    Implements: HtmlView,
    
    bindEvents: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        
        formInstance.getElements('.' + instance.cssClassName + '-additem').each(
            function(item) { item.addEvent('click', function () {
                return instance.raiseServerEvent($(instance.htmlViewId).getAttribute('id'), item.name, 'addItem', '1');
            }); }
        );
        
        formInstance.getElements('.' + instance.cssClassName + '-action').each(
            function(item) { item.addEvent('click', function () {
                var message = item.getAttribute('confirmmessage');
                if ($chk(message))
                {
                    if (confirm(message))
                    {
                        return instance.raiseServerEvent($(instance.htmlViewId).getAttribute('id'),
                            item.name, item.get('eventname'), item.get('eventvalue'));
                    }
                    else
                    {
                        return null;
                    }
                }
                else
                {
                    return instance.raiseServerEvent($(instance.htmlViewId).getAttribute('id'),
                            item.name, item.get('eventname'), item.get('eventvalue'));
                }
            });}
        );
        
        // Rowcommand events
        formInstance.getElements('a.rowcommand').each(function (item) {
			item.addEvent('click', function() {
				return instance.raiseServerEvent(
					$(instance.htmlViewId).getAttribute('id'),
					item.getAttribute('name'),
					item.getAttribute('eventname'),
					item.getAttribute('eventvalue'));
			});
		});
        
        // assign the corresponding classes and events to each tr
        var isContentRowEven = true;
        
        formInstance.getElements('div.' + instance.cssClassName + '-container table tbody tr').each(function (item) {
            if (isContentRowEven == false) item.addClass('alt');
            isContentRowEven = !isContentRowEven;
            
            var originalColor = item.getFirst('td').getStyle('background-color');
            item.addEvent('mouseover', function() {
                item.getElements('td').each(function(td) {
                    td.setStyle('background-color', '#d8d8d8');
                });
            });
            
            item.addEvent('mouseout', function() {
                item.getElements('td').each( function(td) {
                    td.setStyle('background-color', originalColor);
                });
            });
        });
    }
});