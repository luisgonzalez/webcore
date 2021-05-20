/**
 * Standard class to handle GMapView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var GMapView = new Class
({
    Implements: [HtmlView, Options],
    
    options: {
        maxLabel: 'Maximizar',
        restoreLabel: 'Restaurar'
    },
    
    bindEvents: function()
    {
        var instance = this;
        
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-map').each(
            function(item) {
                instance.map = new google.maps.Map2(item);
                instance.map.setMapType(G_NORMAL_MAP);
                instance.map.enableScrollWheelZoom(true);
                
                var point = new google.maps.LatLng(item.get('latitude'), item.get('longitude'));
                instance.map.setCenter(point, 13);
            }
        );
        
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-maximize').each(
            function(item) {
                item.addEvent('click', function() {
                    var p = $(instance.htmlViewId);
                    
                    if (p.getParent().get('class') == 'view-frame')
                        p = p.getParent();
                    
                    if (item.get('text') == instance.options.restoreLabel)
                    {
                        p.setStyle('position', '');
                        p.setStyle('width', 'auto');
                        p.setStyle('height', 'auto');
                        item.set('text', instance.options.maxLabel);
                        instance.hideOverlay();
                    }
                    else
                    {
                        instance.showBodyOverlay();
                        
                        var coords = $(document.body).getCoordinates();
                        
                        p.setStyle('width', coords.width + "px");
                        p.setStyle('height', coords.height + "px");
                        p.setStyle('position', 'absolute');
                        p.setStyle('top', '0');
                        p.setStyle('left', '0');
                        p.setStyle('z-index', '501');
                        
                        window.scrollTo(132, 0);
                    
                        item.set('text', instance.options.restoreLabel);
                    }
                    
                    instance.map.checkResize();
                });
            }
        );
        
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-selector').each(
            function(item) {
                item.addEvent('change', function() {
                    switch (this.value)
                    {
                        case 'G_SATELLITE_MAP':
                            instance.map.setMapType(G_SATELLITE_MAP);
                            break;
                        case 'G_HYBRID_MAP':
                            instance.map.setMapType(G_HYBRID_MAP);
                            break;
                        default:
                            instance.map.setMapType(G_NORMAL_MAP);
                            break;
                    }
                });
            }
        );
        
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-marker').each(
            function(item) {
                var point = new google.maps.LatLng(item.get('latitude'), item.get('longitude'));
                var isDraggable = item.get('isdraggable') == '1' ? true : false;
                
                var marker = new google.maps.Marker(point, {draggable: isDraggable});
                
                instance.map.addOverlay(marker);
                instance.map.setCenter(point, 13);
                marker.openInfoWindowHtml(item.get('html'));
                
                GEvent.addListener(marker, 'click', function(latlng) {
                    marker.openInfoWindowHtml(item.get('html'));
                    if ($chk('pano') && !isDraggable) putStreetView(latlng.lat(), latlng.lng(), 0);
                });
                
                if (isDraggable)
                {
                    GEvent.addListener(marker, "dragend", function() {
                        var key = item.get('key');
                        
                        var data = new Object();
                        data.latitude = marker.getLatLng().lat();
                        data.longitude = marker.getLatLng().lng();
                        
                        $(key).value = JSON.encode(data);
                        
                        instance.showOverlay();
                        instance.doAsyncRequest();
                    });
                }
            }
        );
    },
    
    /** This function provide extra func */
    onClick: function(marker, latlng) { return; }
});