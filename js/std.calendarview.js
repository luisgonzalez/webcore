/**
 * Standard class to handle HtmlCalendarView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var Calendar = new Class({
    Implements: [HtmlView],
    
    options: {
        initialDate: new Date(),
        currentView: 'month'
    },
    
    bindEvents: function(){
        this.setOptions(this.options);
        this.enableAsyncEvents = true;

        this.initialDate = this.options.initialDate.set({'Hours':0,'Minutes':0,'Seconds':0,'Milliseconds':0});
        this.currentDate = this.initialDate;
        this.viewMode = this.options.viewMode;

        this.buildCalendar();
    },
    
    buildCalendar: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        var tableInstance = $(instance.htmlViewId + 'Table');
        
        $(instance.htmlViewId + 'Previous').addEvent('click', function(){ instance.setPrevious(); });
        $(instance.htmlViewId + 'Next').addEvent('click', function(){ instance.setNext(); });
        $(instance.htmlViewId + 'Today').addEvent('click', function(){ instance.setToday(); });
        $(instance.htmlViewId + 'ViewDay').addEvent('click',function(){instance.setView('day');});
        $(instance.htmlViewId + 'ViewWeek').addEvent('click',function(){instance.setView('week');});
        $(instance.htmlViewId + 'ViewMonth').addEvent('click',function(){instance.setView('month');});

        $(instance.htmlViewId + 'Title').addEvents({
            'click':function() { instance.showMonths(); },
            'mouseover':function() { this.addClass(instance.cssClassName + '-title-over'); },
            'mouseout':function() { this.removeClass(instance.cssClassName + '-title-over'); }
        });

        formInstance.getElements('.' + instance.cssClassName + '-button').each(function(button){
            button.addEvents({
                'mouseenter': function(){ this.addClass(instance.cssClassName + '-button-hover'); },
                'mouseleave': function(){ this.removeClass(instance.cssClassName + '-button-hover'); }
            });
        });

        tableInstance.getElements('.' + instance.cssClassName + '-day').each(function(td){
            td.addEvents({
                'click':function(){
                    instance.setDate(new Date(Date.parse(this.get('date'))));
                    if(instance.viewMode != 'day') instance.setSelectedDay(this);
                },
                'dblclick': function() { instance.setView('day'); }
            });
        });

        tableInstance.getElements('.' + instance.cssClassName + '-week-value').each(function(td){
            td.addEvents({
                'dblclick': function(){
                    weekFirstDayTd = this.getNext('td');
                    instance.setDate(new Date(Date.parse(weekFirstDayTd.get('date'))));
                    if(instance.viewMode != 'week') instance.setSelectedDay(weekFirstDayTd);
                    instance.setView('week');
                }
            });
        });

        var totalWeeks = tableInstance.getElements('.' + instance.cssClassName + '-week').length;
        var originalColumnWidth = tableInstance.getCoordinates().width * 0.139;  // 13.9%
        var originalRowHeight = ($(instance.htmlViewId + 'Container').getCoordinates().height - $(instance.htmlViewId + 'Controls').getCoordinates().height - $(instance.htmlViewId + 'Headers').getCoordinates().height) / totalWeeks;

        tableInstance.getElements('.' + instance.cssClassName + '-column').each(function(col){
            col.setStyle('width', originalColumnWidth);
        });
        
        tableInstance.store('originalRowHeight', originalRowHeight)
                .store('originalColumnWidth', originalColumnWidth)
                .store('openRowHeight', tableInstance.retrieve('originalRowHeight') * totalWeeks)
                .store('openHeaderWidth', tableInstance.retrieve('originalColumnWidth') * 7); // 7 days

        tableInstance.getElements('.' + instance.cssClassName + '-appointment-header').each(function(ap){
            tooltipText = ap.getAttribute('tooltip');
            if (tooltipText == '') tooltipText = ap.getNext('div').getElement('div.' + instance.cssClassName + '-appointment-description').get('text').replace(/\s+$/m,'');
            instance.createToolTip(ap, tooltipText);
        });

        tableInstance.getElements('.' + instance.cssClassName + '-appointment-header').each(function(apHeader){
            apHeader.addEvents({
                'mouseover': function(){
                    if (instance.viewMode != 'day') apHeader.addClass(instance.cssClassName + '-appointment-header-over');
                },
                'mouseout': function(){
                    if (instance.viewMode != 'day') apHeader.removeClass(instance.cssClassName + '-appointment-header-over');
                }
            });
        });
        
        instance.setAppointmentContentVisibility(false);
        instance.setView(instance.viewMode);
    },

    setDate: function(value)
    {
        var instance = this;
        instance.currentDate = value;
        $$('#' + instance.htmlViewId + ' input[name=initialDate]').set('value', instance.currentDate.format('%Y-%m-%d'));
    },
	
    setToday: function()
    {
        var instance = this;
        instance.setDate(new Date());
        instance.setView(instance.viewMode);
    },
    
    setNext: function()
    {
        var instance = this;
        forceRebuild = false;

        switch(instance.viewMode)
        {
            case 'day': instance.setDate(instance.currentDate.increment('day', 1)); break;
            case 'week': instance.setDate(instance.currentDate.increment('day', 7)); break;
            case 'month':
                instance.setDate(instance.initialDate.increment('month', 1));
                forceRebuild = true;
                break;
        }

        instance.setView(instance.viewMode, forceRebuild);
    },
    
    setPrevious: function()
    {
        var instance = this;
        forceRebuild = false;
        
        switch(instance.viewMode)
        {
            case 'day': instance.setDate(instance.currentDate.decrement('day', 1)); break;
            case 'week': instance.setDate(instance.currentDate.decrement('day', 7)); break;
            case 'month':
                instance.setDate(instance.initialDate.decrement('month', 1));
                forceRebuild = true;
                break;
        }
    
        instance.setView(instance.viewMode, forceRebuild);
    },
    
    unsetSelectedDay: function()
    {
        var instance = this;
        td = $(instance.htmlViewId + 'Table').getElement('.'+instance.cssClassName + '-day-selected');
        if($chk(td)) td.removeClass(instance.cssClassName + '-day-selected');
    },
    
    setSelectedDay: function(td)
    {
        var instance = this;
        instance.unsetSelectedDay();
        if ($chk(td)) td.addClass(instance.cssClassName + '-day-selected');
    },

    setAppointmentContentVisibility: function(visible){
        var instance = this;
        
        appsContent = $$('#' + instance.htmlViewId + 'Table .' + instance.cssClassName + '-appointment-content');
        appsContent.each(function(ap){
            ap.setStyle('display',visible ? 'block' : 'none');
        });
        
        appsHeaders = $$('#' + instance.htmlViewId + 'Table .' + instance.cssClassName + '-appointment-header');
        appsHeaders.each(function(ap){
            ap.addEvents({
                'mouseenter': function(){
                    if(instance.viewMode != 'day') instance.showToolTip(ap, ap);
                },
                'mouseleave': function(){ instance.hideToolTip(ap); }
            });
        });
    },
    
    setView: function(view, forceRebuild)
    {
        var instance = this;
        instance.viewMode = view;

        $$('#' + instance.htmlViewId + ' input[name=viewMode]').set('value', instance.viewMode);
    
        currentTd = $(instance.htmlViewId + 'Day' + instance.currentDate.format('%Y-%m-%d'))

        if($chk(currentTd) == false || $chk(forceRebuild) && forceRebuild)
        {
            instance.raiseServerEvent(instance.htmlViewId, 'ControlName', 'initialDateChanged', instance.currentDate.format('%Y-%m-%d'));
            return;
        }
    
        if(view != 'day')
            instance.setSelectedDay(currentTd);
        else
            instance.unsetSelectedDay();
		
		var months = MooTools.lang.setLanguage(controller.language).get('Date').months;
		
        switch(view){
            case 'month':
                $(instance.htmlViewId + 'Title').set('text', months[instance.currentDate.getMonth()] + ', ' + instance.currentDate.getFullYear());
                
                instance.resetColumns();
                (function(){instance.resetRows();}).delay(300);
                
                instance.setAppointmentContentVisibility(false);
                
                break;
            case 'week':
                $(instance.htmlViewId + 'Title').set('text', months[instance.currentDate.getMonth()] + ', ' + instance.currentDate.getFullYear());
                var td = $(instance.htmlViewId + 'Day' + instance.currentDate.format('%Y-%m-%d'))
                var tr = td.getParent('tr');
                
                instance.openRow(tr);
                (function(){instance.resetColumns();}).delay(300);
                instance.setAppointmentContentVisibility(false);
                
                break;
            case 'day':
                $(instance.htmlViewId + 'Title').set('text', months[instance.currentDate.getMonth()] + ' ' + instance.currentDate.getDate() + ', ' + instance.currentDate.getFullYear());
                var td = $(instance.htmlViewId + 'Day' + instance.currentDate.format('%Y-%m-%d'))
                var tr = td.getParent('tr');
                var th = $(instance.htmlViewId + 'Col' + td.get('column'));
                
                td.addClass(instance.cssClassName + '-day-selected');
                instance.openRow(tr);
                (function(){
                    instance.openColumn(th);
                    instance.setAppointmentContentVisibility(true);
                }).delay(300);
        }
    },

    openRow: function(row)
    {
        var instance = this;
        var rows = $$('#' + instance.htmlViewId + 'Table .' + instance.cssClassName + '-week');
    
        rows.each(function(currentRow){
            if(row == currentRow) return;

            new Fx.Morph(currentRow,{'duration':250,'transition':'sine'})
                .addEvent('complete', function(){ currentRow.setStyle('display','none'); })
                .start({'height': $(instance.htmlViewId + 'Table').retrieve('collapsedRowHeight')});
        });

        try { row.setStyle('display', 'table-row'); }
        catch(ex) { row.style.display = 'block'; }

        row.set('morph', {'duration':250,'transition':'sine'});
        row.morph({'height': $(instance.htmlViewId + 'Table').retrieve('openRowHeight')});
    },
    
    openColumn: function(column)
    {
        var instance = this;
        var columns = $$('#' + instance.htmlViewId + 'Table .' + instance.cssClassName + '-column');

        columns.each(function(currentColumn){
            if(column == currentColumn) return;
            
            new Fx.Morph(currentColumn,{'duration':250,'transition':'sine'})
                .addEvent('complete', function(){ instance.hideColumn(currentColumn); })
                .start({'width': 1});
        });
    
        instance.showColumn(column);
        column.set('morph', {'duration':250,'transition':'sine'});
        column.morph({'width': $(instance.htmlViewId + 'Table').retrieve('openColumnWidth')});
    },
    
    resetColumns: function(calendar)
    {
        var instance = this;
        var allColumns = $$('#' + instance.htmlViewId + 'Table .' + instance.cssClassName + '-column');
        
        allColumns.each(function(column){
            instance.showColumn(column);
            column.set('morph', {'duration':250,'transition':'sine'});
            column.morph({'width': $(instance.htmlViewId + 'Table').retrieve('originalColumnWidth')});
        });
    },

    hideColumn: function(column)
    {
        var instance = this;
        var index = column.get('column');

        try { column.setStyle('display','none'); }
        catch(ex) { column.style.display = 'none'; }

        $(instance.htmlViewId + 'Table').getElements('tr').each(function(row){
            try { row.cells[index].setStyle('display', 'none'); }
            catch(ex) { row.cells[index].style.display = 'none'; }
        });
    },

    showColumn: function(column)
    {
        var instance = this;
        var index = column.get('column');

        try{ column.setStyle('display','table-column'); }
        catch(ex) { column.style.display = 'block'; }

        $(instance.htmlViewId + 'Table').getElements('tr').each(function(row){
            try { row.cells[index].setStyle('display', 'table-cell'); }
            catch(ex) { row.cells[index].style.display = 'block'; } //hack IE
        });
    },

    resetRows: function()
    {
        var instance = this;
        var allRows = $$('#' + instance.htmlViewId + 'Table .' + instance.cssClassName + '-week');
    
        allRows.each(function(row){
            try { row.setStyle('display','table-row'); }
            catch(ex){ row.style.display = 'block'; }

            row.set('morph', {'duration':250,'transition':'sine'});
            row.morph({'height': $(instance.htmlViewId + 'Table').retrieve('originalRowHeight')});
        });
    },

    showMonths: function()
    {
        var instance = this;
        /*
         @todo Complete this?!
        month = instance.currentDate.getMonth();
        calendarCoords = $(instance.htmlViewId + 'Table').getCoordinates();
        tableYear = new Element('table')
            .addClass(instance.cssClassName + '-year');
    
        for(i = 0; i < 12; i++)
        {
            if(i % 4 == 0) tr = new Element('tr').inject(tableYear);
            
            tdMonth = new Element('td',{'class' : instance.cssClassName + '-month','text' : MooTools.lang.setLanguage(controller.language).get('Date').months[i]})
                    .inject(tr);
            if(i == month) tdMonth.addClass(instance.cssClassName + '-month-selected');
        }
        
        tableYear.inject(document.body);
        divCoords = tableYear.getCoordinates();
        
        tableYear.setStyles({
            'left': calendarCoords.left + (calendarCoords.width - divCoords.width) / 2,
            'top': calendarCoords.top + (calendarCoords.height - divCoords.height) / 2
        });
        */
    }
});