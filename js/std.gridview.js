/**
 * Standard class to handle HtmlGridView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var HtmlGridView = new Class
({
    Implements: HtmlView,
    
    updateSearchDialogCoords: function(searchDialog)
    {
		if (searchDialog.getStyle('visibility') == 'hidden')
		{
			searchDialog.setStyle('left', '-10000px');
			searchDialog.setStyle('top', '-10000px');
			return;
		}
		
        var instance = this;
        var gridCoords = $(instance.htmlViewId).getParent().getCoordinates();
        var dialogCoords = searchDialog.getCoordinates();
        var applyLeft = true;
	
	/*
        searchDialog.getParents().each(function(item) {
            if (item.getComputedStyle('float') == 'left' || item.getComputedStyle('float') == 'right') applyLeft = false;
        });
	*/
	
        if (instance.getPositionMode() == 'absolute')
        {
            var leftOffset = ((gridCoords.width / 2) - (dialogCoords.width / 2));
            var topOffset = gridCoords.top + 100;
        }
        else
        {
            var leftOffset = gridCoords.left + ((gridCoords.width / 2) - (dialogCoords.width / 2));
            var topOffset = gridCoords.top + 100;
        }
        
        if (applyLeft)
            searchDialog.setStyle('left', leftOffset + 'px');
        else
            searchDialog.setStyle('left', (leftOffset / 2) + 'px');
			
        searchDialog.setStyle('top', topOffset + 'px');
        searchDialog.setStyle('z-index', '600');
    },
    
    bindEvents: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
		
        formInstance.getElements('.' + instance.cssClassName + '-header-off[sortdirection]').each(
        function(item)
        {
            item.setProperty('class', instance.cssClassName + '-header-sorted-' + item.getAttribute('sortdirection'));
            var originalClassName = item.getProperty('class');
            
            item.addEvent('mouseover', function() {
                if (item.getAttribute('sortdirection') == 'asc')
                    item.setProperty('class', instance.cssClassName + '-header-sorted-desc');
                else
                    item.setProperty('class', instance.cssClassName + '-header-sorted-asc');
            });
			
            item.addEvent('mouseout', function() {
                item.setProperty('class', originalClassName);
            });
			
            item.addEvent('click', function() {
                return instance.raiseServerEvent(
                    $(instance.htmlViewId).getAttribute('id'),
                    $(instance.htmlViewId).getAttribute('name') + "Sorter",
                    'SortByColumn',
                    item.getAttribute('eventvalue'));
            });
        });
        
        // Column Sorting
        formInstance.getElements('.' + instance.cssClassName + '-header-off').each(function(item) {
            if (item.getAttribute('eventname') != 'SortByColumn') return;
            var originalClassName = item.getProperty('class');
            
            item.addEvent('mouseover', function() {
                item.setProperty('class', instance.cssClassName + '-header-sorted-asc');
            });
            item.addEvent('mouseout', function() { item.setProperty('class', originalClassName); });
            item.addEvent('click', function() {
                return instance.raiseServerEvent(
                    $(instance.htmlViewId).getAttribute('id'),
                    $(instance.htmlViewId).getAttribute('name') + "Sorter",
                    'SortByColumn',
                    item.getAttribute('eventvalue'));
            });
        });

        // Pager button events
        formInstance.getElements('a[eventname=GoPageIndex]').each(function (item) {
			item.addEvent('click', function() {
				return instance.raiseServerEvent(
					$(instance.htmlViewId).getAttribute('id'),
					$(instance.htmlViewId).getAttribute('name') + "Pager",
					'GoPageIndex',
					item.getAttribute('eventvalue'));
			});
        });
        
        // Pager dropdown events
        formInstance.getElements('select[eventname=GoPageIndex]').each(function (item) {
            item.addEvent('change', function() {
				return instance.raiseServerEvent(
					$(instance.htmlViewId).getAttribute('id'),
					$(instance.htmlViewId).getAttribute('name') + "Pager",
					'GoPageIndex',
					item.value);
            });
        });
        
        // Filter menu button events
		// @todo Remove this
		formInstance.getElements('a[eventname=ApplyFilter]').each(function (item) {
            item.addEvent('click', function() {
				var buttonMenu = item.getParent('div.' + instance.cssMasterClassName + '-toolbar-buttonmenu');
				buttonMenu.fade('hide');
				return instance.raiseServerEvent(
					$(instance.htmlViewId).getAttribute('id'),
					$(instance.htmlViewId).getAttribute('name') + "Filterer",
					item.getProperty('eventname'),
					item.getAttribute('eventvalue'));
            });
        });
        
        // Rowcommand events
        formInstance.getElements('a.rowcommand').each(function (item) {
			item.addEvent('click', function() {
                
                var message = item.getAttribute('confirmmessage');
                if ($chk(message)) {
                    if (!confirm(message)) return false;
                }
                
				return instance.raiseServerEvent(
					$(instance.htmlViewId).getAttribute('id'),
					item.getAttribute('name'),
					item.getAttribute('eventname'),
					item.getAttribute('eventvalue'));
			});
		});
        
        // Search menu button events
        formInstance.getElements('a[eventname=SearchByColumn]').each(function (item) {
			item.addEvent('click', function() {
				var buttonMenu = item.getParent(' div.' + instance.cssMasterClassName + '-toolbar-buttonmenu');
				buttonMenu.fade('hide');
				
				if (item.getAttribute('eventvalue') == '~')
				{
					// Remove search
					instance.raiseServerEvent(
						$(instance.htmlViewId).getAttribute('id'),
						$(instance.htmlViewId).getAttribute('name') + "Searcher",
						'SearchByColumn',
						item.getProperty('eventvalue'));
					return;
				}
				else
				{
					var searchDialog = $(item.getAttribute('searchdialog')).getParent();
					searchDialog.fade('show');
					instance.updateSearchDialogCoords(searchDialog);
					instance.showOverlay();
				}
			});
        });
        
        // Search Dialogs
        instance.modelName = $(instance.htmlViewId).getProperty('name');
        $$('form[targetmodel=' + instance.modelName + ']').each(function(dialog) {  
            window.addEvent('resize', function() {
                var searchDialog = dialog.getParent();
                if (searchDialog != undefined)
                    instance.updateSearchDialogCoords(searchDialog);
            });
            
            window.addEvent('domready', function() {
                var searchDialog = dialog.getParent();
				if (searchDialog != undefined)
					instance.updateSearchDialogCoords(searchDialog);
            });
            
            $$('#' + dialog.getProperty('id') + ' a.' + instance.cssMasterClassName + '-button').each(function (button) {
                var dialogEventName = button.getProperty('eventname');
                if (dialogEventName.substring(0,1) == '~')
                {
                    if (dialogEventName == '~dialog_button_ok')
                    {
                        button.addEvent('click', function() {
                            
                            // Get the stadard fields
                            var operatorsControl = $$('#' + dialog.getProperty('id') + ' *[name=operatorsControl]');
                            var argumentControl = $$('#' + dialog.getProperty('id') + ' *[name=argumentControl]');
                            var argumentAltControl = $$('#' + dialog.getProperty('id') + ' *[name=argumentAltControl]');
                            
                            // Create hidden fields for the parent form
                            var operatorsField = new Element('input', { 'type' : 'hidden', 'id' : operatorsControl.getProperty('id') + 'Searcher', 'name' : operatorsControl.getProperty('name'), 'value' : operatorsControl.getProperty('value')});
                            var argumentField = new Element('input', { 'type' : 'hidden', 'id' : argumentControl.getProperty('id') + 'Searcher', 'name' : argumentControl.getProperty('name'), 'value' : argumentControl.getProperty('value')});
                            var argumentAltField = new Element('input', { 'type' : 'hidden', 'id' : argumentAltControl.getProperty('id') + 'Searcher', 'name' : argumentAltControl.getProperty('name'), 'value' : argumentAltControl.getProperty('value')});
                            
                            // Append hidden fields to the parent form
                            var targetForm = $(instance.htmlViewId);
                            targetForm.appendChild(operatorsField);
                            targetForm.appendChild(argumentField);
                            if (argumentAltControl.getProperty('id') != '') { targetForm.appendChild(argumentAltField); }
                            
                            // Hide the dialog
                            dialog.getParent().setStyle('visibility', 'hidden');
                            dialog.getParent().setStyle('left', '-10000px');
			    
                            // Raise the Searcher event
                            instance.raiseServerEvent(
                                $(instance.htmlViewId).getAttribute('id'),
                                $(instance.htmlViewId).getAttribute('name') + "Searcher",
                                'SearchByColumn',
                                dialog.getProperty('columnname'));
                            
                            return false;
                        });
                    }
                    else if (dialogEventName == '~dialog_button_cancel')
                    {
                        button.addEvent('click', function() {
                            dialog.getParent().setStyle('visibility', 'hidden');
							dialog.getParent().setStyle('left', '-10000px');
                            instance.hideOverlay();
                            return false;
                        });
                    }
                    
                }
            });
        });
        
        // assign the corresponding classes and events to each tr
        var isContentRowEven = true;
        
        formInstance.getElements('div.' + instance.cssClassName + '-container table tbody tr').each(function (item) {
			if (item.hasClass('grouping'))
			{
				isContentRowEven = true;
				return;
			}
			
			if (isContentRowEven == false) item.addClass('alt');
			isContentRowEven = !isContentRowEven;
			var originalColor = item.getFirst('td').getStyle('background-color');
			
			item.addEvent('mouseover', function() {
				item.getElements('td').each( function(td) {
					td.setStyle('background-color', '#d8d8d8');
				});
			});
			
			item.addEvent('mouseout', function() {
				item.getElements('td').each( function(td) {
					td.setStyle('background-color', originalColor);
				});
			});
			
			item.addEvent('click', function() {
				item.getElements('td').each( function(td) {
					td.set('tween', {duration: 'long'});
					td.highlight('#d0d0d0', originalColor);
				});
			});   
        });
    }
    // DO NOT ADD A COMMA AT THE END OF THE LAST FUNCTION DECLARATION. IE is unable to parse the class properly!
});