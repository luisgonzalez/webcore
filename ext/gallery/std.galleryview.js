/**
 * Standard class to handle HtmlGalleryView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 * @todo Include support for options and move with keyboard
 */
var HtmlGalleryView = new Class
({
    Implements: HtmlView,
    
    bindEvents: function()
    {
        var instance = this;
        instance.imagesList = new Hash();
        
        // Handles image's popup
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-thumbnail-image').each(function(item, i) {
            var imgMetadata = { 'title': item.getAttribute('alt'), 'id': i + 1, 'src': item.getAttribute('fullsize') };
            instance.imagesList.set(i, imgMetadata);
            item.addEvent('click', function() { instance.imagePopup(i); } );
        });
    },
    
    //@todo Fix this
    keyHandler: function(event)
    {
        event = new Event(event);
        
        if (event.key == 'left' && id > 0)
        {
            event.stop();
            instance.loadImage(pinDiv, id - 1);
            return;
        }
        else if (event.key == 'right' && id < instance.imagesList.getLength() - 1)
        {
            event.stop();
            instance.loadImage(pinDiv, id + 1);
            return;
        }
    },
    
    loadImage: function(pinDiv, id)
    {
        var instance = this;
        var picMetadata = instance.imagesList.get(id);
        
        pinDiv.empty();
        
        var imageAsset = new Asset.image(picMetadata.src, {
            onload: function(img)
            {
                instance.imageProperties =
                {
                    'width':img.width,
                    'height':img.height
                };
                
                if(window.getHeight() < img.height)
                {
                    var originalHeight = img.height;
                    var diff = parseFloat(window.getHeight());
                    img.height = window.getHeight() - 5;
                    var newWidth = ((img.width * diff) / originalHeight) - 5;
                    img.width = newWidth;
                }

                instance.currentImage = img;
                
                pinDiv.grab(img);

                var offsets = $(document.body).getScroll();
                var leftImg = ((window.getWidth() - img.width) / 2) + offsets.x;
                var topImg = ((window.getHeight() - img.height) / 2) + offsets.y;
                
                //pinDiv.setStyle('background-image', 'url(' + img.src + ')');
                pinDiv.set('morph', {transition: 'linear', duration: 200});
                pinDiv.morph({ left: leftImg, top: topImg, height: img.height, width: img.width });
                
                var divInfo = new Element('div', { 'class': instance.cssClassName + '-thumbnail-pin-details',
                                                'opacity' : 0,
                                                'html' : picMetadata.title  + '<br />' + picMetadata.id + " / " + instance.imagesList.getLength() });
                
                pinDiv.grab(divInfo);
                
                var controller = new Element('div', { 'class': instance.cssClassName + '-controller' });
                pinDiv.grab(controller);
                
                controller.addEvent('mouseover', function() { controller.fade(1); });
                var ul = new Element('ul').inject(controller);
                
                $H({'first': 0, 'prev': id - 1, 'next': id + 1, 'last': instance.imagesList.getLength() - 1 }).each(function(direction, action)
                {
                    var li = new Element('li', { 'class': instance.cssClassName + '-controller-' + action }).inject(ul);
                    var a = new Element('a', { 'title': action }).inject(li);
                    a.set('events', {
                        'mouseenter': function() { li.addClass(instance.cssClassName + '-controller-active'); },
                        'mouseleave': function() { li.removeClass(instance.cssClassName + '-controller-active'); },
                        'click': function(e) {
                            e.stopPropagation();
                            if (direction >= instance.imagesList.getLength() - 1 && id == instance.imagesList.getLength() -1) return;
                            if (direction <= 0 && id == 0) return;
                            
                            instance.loadImage(pinDiv, direction);
                        }
                    });
                });
                
                pinDiv.addEvent('click', function() {
                    //$(window).removeEvent('keypress', instance.keyHandler);
                    pinDiv.fade('out');
                    instance.hideOverlay();
                });
                
                divInfo.set('morph', {transition: 'linear', duration: 300});
                divInfo.morph({ width : img.width - 22, opacity: 0.5 });
                
                pinDiv.addEvent('mouseover', function() { divInfo.fade(0.5); controller.fade(0.3); });
                pinDiv.addEvent('mouseout', function() { divInfo.fade(0); controller.fade(0); });
                
                //$(window).addEvent('keypress', instance.keyHandler);
            }
        });
    },
    
    /**
     * Popups image
     */
    imagePopup: function(id)
    {
        var instance = this;
        instance.showBodyOverlay();
        
        $$('#' + instance.htmlViewId + ' .' + instance.cssClassName + '-thumbnail-pin').dispose();
        
        var pinDiv = new Element('div', { 'class': instance.cssClassName + '-thumbnail-pin' });

        if(instance.disableContextMenu == true)
        {
            pinDiv.addEvent('contextmenu',function(e) {
                    e.stop();
                });
        }

        $(instance.htmlViewId).grab(pinDiv);
        instance.updatePin(pinDiv, false);
        window.addEvent('resize', function() {
            var img = instance.currentImage;
            img.height = instance.imageProperties.height;
            img.width = instance.imageProperties.width;
            
                if(window.getHeight() < img.height)
                {
                    var originalHeight = img.height;
                    var diff = parseFloat(window.getHeight() - 5);
                    img.height = window.getHeight() - 5;
                    var newWidth = ((img.width * diff) / originalHeight);
                    img.width = newWidth;
                }

                var offsets = $(document.body).getScroll();
                var leftImg = ((window.getWidth() - img.width) / 2) + offsets.x;
                var topImg = ((window.getHeight() - img.height) / 2) + offsets.y;

                //pinDiv.setStyle('background-image', 'url(' + img.src + ')');
                pinDiv.set('morph', {transition: 'linear', duration: 200});
                pinDiv.morph({ left: leftImg, top: topImg, height: img.height, width: img.width });

            //instance.updatePin(pinDiv, true);
        });
        window.addEvent('scroll', function() { instance.updatePin(pinDiv, true); });
        
        instance.loadImage(pinDiv, id);
    },
    disableContextMenu: function(){
        var instance = this;
        instance.disableContextMenu = true;
    }
});
