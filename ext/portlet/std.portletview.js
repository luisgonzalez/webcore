/**
 * Standard class to handle HtmlPortletView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var HtmlPortletView = new Class
({
    Implements: HtmlView,
    
    bindEvents: function()
    {
        var instance = this;

        var taskbar = $(instance.htmlViewId + '_workspace_taskbar');
        var dockFill = $$('#' + instance.htmlViewId + '_workspace .' + instance.cssClassName + '-dock-fill')[0];
        var container = $$('#' + instance.htmlViewId + '_workspace .' + instance.cssClassName + '-container')[0];
        
        if (container == null) return;
        
        $$('#' + instance.htmlViewId + '_workspace .' + instance.cssClassName + '-portlet').each(
            function(item) {
                var frame = item.getFirst();
                
                if (frame == null) return;
                
                var title = frame.getChildren('.' + instance.cssClassName + '-portlet-title')[0];
                var portletContent = frame.getChildren('.' + instance.cssClassName + '-portlet-content')[0];
                var resizable = frame.getChildren('.' + instance.cssClassName + '-portlet-resizable')[0];
                
                var taskBarEl = new Element('span', { 'class': instance.cssClassName + '-taskbar-element', text: title.get('text') });
                
                taskBarEl.addEvents({
                    'click': function() {
                        if (item.getStyle('display') == 'none')
                        {
                            item.setStyle('display', 'block');
                            item.fade('in');
                        }
                        
                        frame.highlight('#FFF');
                    },
                    'mouseover': function() { taskBarEl.setStyle('background-color', '#FFF'); },
                    'mouseout': function() { taskBarEl.setStyle('background-color', ''); }
                });
                
                taskbar.grab(taskBarEl);
                
                var closable = frame.getChildren('.' + instance.cssClassName + '-portlet-close')[0];
                closable.addEvents({
                    'mouseover': function() { closable.setOpacity(0.7); },
                    'mouseout': function() { closable.setOpacity(1); },
                    'click': function() { item.fade('out').dispose(); taskBarEl.dispose(); }
                });
                
                var minimize = frame.getChildren('.' + instance.cssClassName + '-portlet-minimize')[0];
                minimize.addEvents({
                    'mouseover': function() { minimize.setOpacity(0.7); },
                    'mouseout': function() { minimize.setOpacity(1); },
                    'click': function() { item.fade('out'); item.setStyle('display', 'none'); }
                });
                
                var maximize = frame.getChildren('.' + instance.cssClassName + '-portlet-maximize')[0];
                maximize.addEvents({
                    'mouseover': function() { maximize.setOpacity(0.7); },
                    'mouseout': function() { maximize.setOpacity(1); },
                    'click': function() {
                        frame.setStyle('top', '0');
                        frame.setStyle('left', '0');
                        frame.setStyle('width', container.getStyle('width'));
                        frame.setStyle('height', container.getStyle('height'));
                        frame.getParent().inject(dockFill);
                        resizable.setStyle('display', 'none');
                    }
                });
                
                frame.makeResizable({
                    handle: resizable,
                    limit: {x: [280, container.get('width')], y: [165, container.get('height')]},
                    onStart: function(el) {
                        frame.setOpacity(0.7).setStyle("z-index", 1);
                    },
                    onComplete: function(el) {
                        frame.setOpacity(1.0).setStyle("z-index", 0);
                    },
                    onDrag: function(el) {
                        var h = parseInt(frame.getStyle('height')) - parseInt(title.getStyle('height')) - parseInt(resizable.getStyle('height'));
                        h = h - parseInt(frame.getStyle('padding-bottom')) - 10;
                        portletContent.setStyle('height', h + 'px');
                    }
                });
                
                frame.makeDraggable({
                    handle: title,
                    container: container,
                    droppables: '.' + instance.cssClassName + '-dock',
                    onStart: function(el) {
                        frame.setOpacity(0.7).setStyle("z-index", 1);
                        title.setStyle('cursor', 'move');
                        frame.setStyle('position', 'absolute');
                        taskBarEl.setStyle('background-color', '#FFF');
                    },
                    onComplete: function(el) {
                        frame.setOpacity(1.0).setStyle("z-index", 0);
                        taskBarEl.setStyle('background-color', '');
                        title.setStyle('cursor', 'pointer');
                        
                        if (frame.hasClass(instance.cssClassName + '-portlet-docked'))
                        {
                            frame.setStyle('top', '0px');
                            frame.setStyle('left', '0px');
                            frame.setStyle('position', 'relative');
                            el.removeClass(instance.cssClassName + '-portlet-docked');
                        }
                    },
                    onEnter: function(el, droppable) { if (droppable) droppable.addClass(instance.cssClassName + '-dock-enter'); },
                    onLeave: function(el, droppable) { if (droppable) droppable.removeClass(instance.cssClassName + '-dock-enter'); },
                    onDrop: function(el, droppable) {
                        var portletEl = el.getParent()
                        
                        if (droppable)
                        {
                            portletEl.inject(droppable);
                            el.addClass(instance.cssClassName + '-portlet-docked');
                            droppable.removeClass(instance.cssClassName + '-dock-enter');
                            resizable.setStyle('display', 'none');
                        }
                        else
                        {
                            portletEl.inject(container);
                            resizable.setStyle('display', 'block');
                        }
                    }
                });
                
                var dockPlace = parseInt(item.get('dock'));
                
                switch(dockPlace)
                {
                    case 1:
                        frame.getParent().inject($(instance.htmlViewId + '_workspace_north'));
                        break;
                    case 2:
                        frame.getParent().inject($(instance.htmlViewId + '_workspace_south'));
                        break;
                    case 3:
                        frame.getParent().inject($(instance.htmlViewId + '_workspace_east'));
                        break;
                    case 4:
                        frame.getParent().inject($(instance.htmlViewId + '_workspace_west'));
                        break;
                    default:
                        frame.getParent().inject(dockFill);  
                }
                
                if (dockPlace != 0)
                {
                    resizable.setStyle('display', 'none');
                    frame.setStyle('top', '0');
                    frame.setStyle('left', '0');
                    frame.setStyle('position', 'relative');
                }
            }
        );
    }
});