/**
 * Represents Controller Client-side class
 *
 * @namespace WebCore.Controller
 */
function Controller()
{
    this.isDebugEnabled = false,
    this.language = 'en-US';
    this.isDOMReady = false;
    this.timer = null;
    
    this.transfer = function(url, params)
    {
        var paramObj = eval('(' + params + ')');
        
        if (document.body == null)
            document.write("<html><head></head><body></body></html>");
        
        // Post form
        if (paramObj.length != 0)
        {
            var form = document.createElement('form');
            form.action = url;
            form.encoding = 'multipart/form-data';
            form.method = 'post';
            for(val in paramObj)
            {
                var hiddenField = document.createElement('input');
                hiddenField.value = paramObj[val];
                hiddenField.name = val;
                hiddenField.type = 'hidden';
                form.appendChild(hiddenField);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
        // Location change
        else
        {
            window.location = url;
        }
        
        return true;
    },
    
    this.download = function(url) { window.location.href = url; },
    
    this.log = function(msg)
    {
        if (this.isDebugEnabled) firebug.d.console.cmd.log(msg);
    },
    
    this.error = function(msg)
    {
        if (this.isDebugEnabled) firebug.d.console.cmd.error(msg);
    },
    
    this.warn = function(msg)
    {
        if (this.isDebugEnabled) firebug.d.console.cmd.warn(msg);
    },
    
    this.include = function(fileName)
    {
        var sc = "script", tp = "text/javascript";
        
        if (window.navigator.userAgent.indexOf("MSIE")!==-1)
        {
            document.write("<" + sc + " type=\"" + tp + "\" src=\"" + fileName + "\" defer></" + sc + ">");
        }
        else
        {
            var t = document.createElement(sc);
            t.setAttribute("src", fileName);
            t.setAttribute("type", tp);
            document.getElementsByTagName("head")[0].appendChild(t);
        }
    },
    
    this.addInit = function(func)
    {
        var instance = this;

        if (instance.isDOMReady)
        {
            func();
            return;
        }

        var callback = function() {
            if (instance.timer) clearInterval(instance.timer);
            instance.isDOMReady = true;
            func();
        };
        
        if(/AppleWebKit/i.test(navigator.userAgent))
        {
            instance.timer = setInterval(function() {
                if (/loaded|complete/.test(document.readyState)) callback();
            }, 10);
        }
        else if (window.addEventListener)
            document.addEventListener('DOMContentLoaded', callback, false);
        else if (window.attachEvent)
            window.attachEvent('onload', callback);   
        else
            window.onload = callback;
    }
}