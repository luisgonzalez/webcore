/*
Script: mootree.js
	My Object Oriented Tree
	- Developed by Rasmus Schultz, <http://www.mindplay.dk>
	- Tested with MooTools release 1.2, under Firefox 2, Opera 9 and Internet Explorer 6 and 7.
	
License:
	MIT-style license.
*/
/**
 * Standard class to handle HtmlTreeView on the client side.
 * @requires mootools 1.2
 *
 * @namespace WebCore.View
 */
var HtmlTreeView = new Class
({
    Implements: HtmlView,
    
    bindEvents: function()
    {
        var instance = this;
        var formInstance = $(instance.htmlViewId);
        
        var tree = new MooTreeControl({ cssClassName: instance.cssClassName, div: instance.htmlViewId + '_placeholder',
            mode: 'folders', 
            onSelect: function(node, state) {
                if (state && node.data.tag)
                {
                    instance.raiseServerEvent(
                    formInstance.getAttribute('id'), formInstance.getAttribute('name'),
                    'selectNode', node.data.tag);
                }
            } }, { text: '/', open: true });
        
        formInstance.getElements('.' + instance.cssClassName + '-rootnode').each(function(item) {
            tree.adopt(item);
        });
    }
});

var MooTreeControl = new Class
({	
	initialize: function(config, options) {		
		options.control = this;               // make sure our new MooTreeNode knows who it's owner control is
		options.div = config.div;             // tells the root node which div to insert itself into
        options.cssClassName = config.cssClassName;
		this.root = new MooTreeNode(options); // create the root node of this tree control
		this.index = new Object();            // used by the get() method
		this.enabled = true;                  // enable visual updates of the control
		this.selected = null;                 // set the currently selected node to nothing
		this.mode = config.mode;              // mode can be "folders" or "files", and affects the default icons
        this.cssClassName = config.cssClassName;
		
		this.onExpand = config.onExpand || new Function(); // called when any node in the tree is expanded/collapsed
		this.onSelect = config.onSelect || new Function(); // called when any node in the tree is selected/deselected
		
		this.root.update(true);
	},
	
	/*
	Property: insert
		Creates a new node under the root node of this tree.
	
	Parameters:
		options - an object containing the same options available to the <MooTreeNode> constructor.
		
	Returns:
		A new <MooTreeNode> instance.
	*/
	insert: function(options) {
		options.control = this;
		return this.root.insert(options);
	},
	
	/*
	Property: select
		Sets the currently selected node.
		This is called by <MooTreeNode> when a node is selected (e.g. by clicking it's title with the mouse).
	
	Parameters:
		node - the <MooTreeNode> object to select.
	*/
	select: function(node) {
		if (this.selected === node) return; // already selected
		if (this.selected) {
			// deselect previously selected node:
			this.selected.select(false);
			this.onSelect(this.selected, false);
		}
		// select new node:
		this.selected = node;
		node.select(true);
		this.onSelect(node, true);
	},
	
	expand: function() { this.root.toggle(true, true); },
	collapse: function() { this.root.toggle(true, false); },
	
	/*
	Property: get
		Retrieves the node with the given id - or null, if no node with the given id exists.
	
	Parameters:
		id - a string, the id of the node you wish to retrieve.
	*/
	get: function(id) { return this.index[id] || null; },
	
	adopt: function(id, parentNode) {
		if (parentNode === undefined) parentNode = this.root;
		this.disable();
		this._adopt(id, parentNode, 1);
		parentNode.update(true);
		$(id).destroy();
		this.enable();
	},
	
	_adopt: function(id, parentNode, level) {
        var instance = this;
        
		$(id).getElements('li.' + instance.cssClassName + '-node-' + level).each(function(c) {
            var con = { text: c.getElement('span').get('text'), data: { tag: c.getAttribute('tag') || '' } };
            var subs = c.getElements('ul');
            var node = parentNode.insert(con);
            
            if (subs.length == 0) node.icon = '_doc'
            else
            {
                subs.each(function(subul) { instance._adopt(subul, node, level + 1); });
                node.open = (c.getAttribute('isExpanded') == 'true') ? true : false;
            }
		});
	},
		
	/*
	Property: disable
		Call this to temporarily disable visual updates -- if you need to insert/remove many nodes
		at a time, many visual updates would normally occur. By temporarily disabling the control,
		these visual updates will be skipped.
		
		When you're done making changes, call <MooTreeControl.enable> to turn on visual updates
		again, and automatically repaint all nodes that were changed.
	*/
	disable: function() {
		this.enabled = false;
	},
	
	/*
	Property: enable
		Enables visual updates again after a call to <MooTreeControl.disable>
	*/
	enable: function() {
		this.enabled = true;
		this.root.update(true, true);
	}
});

/*
Class: MooTreeNode
	This class implements the functionality of a single node in a <MooTreeControl>.
	
Parameters:
	options - an object. See options below.

Options:
	text - this is the displayed text of the node, and as such as is the only required parameter.
	id - string, optional - if specified, must be a unique node identifier. Nodes with id can be retrieved using the <MooTreeControl.get> method.
	
	open - boolean value, defaults to false. Use true if you want the node open from the start.
	
	icon - use this to customize the icon of the node. The following predefined values may be used: '_open', '_closed' and '_doc'. Alternatively, specify the URL of a GIF or PNG image to use - this should be exactly 18x18 pixels in size. If you have a strip of images, you can specify an image number (e.g. 'my_icons.gif#4' for icon number 4).
	openicon - use this to customize the icon of the node when it's open.
	
	data - an object containing whatever data you wish to associate with this node (such as an url and/or an id, etc.)

Events:
	onExpand - called when the node is expanded or collapsed: function(state) - where state is a boolean meaning true:expanded or false:collapsed.
	onSelect - called when the node is selected or deselected: function(state) - where state is a boolean meaning true:selected or false:deselected.
*/

var MooTreeNode = new Class({
	
	initialize: function(options)
    {
        this.cssClassName = options.cssClassName;
		this.text = options.text;       // the text displayed by this node
		this.id = options.id || null;   // the node's unique id
		this.nodes = new Array();       // subnodes nested beneath this node (MooTreeNode objects)
		this.parent = null;             // this node's parent node (another MooTreeNode object)
		this.last = true;               // a flag telling whether this node is the last (bottom) node of it's parent
		this.control = options.control; // owner control of this node's tree
		this.selected = false;          // a flag telling whether this node is the currently selected node in it's tree
		this.data = options.data || {}; // optional object containing whatever data you wish to associate with the node (typically an url or an id)
		this.onExpand = options.onExpand || new Function(); // called when the individual node is expanded/collapsed
		this.onSelect = options.onSelect || new Function(); // called when the individual node is selected/deselected
		this.open = options.open ? true : false; // flag: node open or closed?
		this.icon = options.icon;
		this.openicon = options.openicon || this.icon;
		
		// add the node to the control's node index:
		if (this.id) this.control.index[this.id] = this;
		
		// create the necessary divs:
		this.div = {
			main: new Element('div').addClass(this.cssClassName + '-node'),
			indent: new Element('div'),
			gadget: new Element('div'),
			icon: new Element('div'),
			text: new Element('div').addClass(this.cssClassName + '-text'),
			sub: new Element('div')
		}
		
		// put the other divs under the main div:
		this.div.main.adopt(this.div.indent);
		this.div.main.adopt(this.div.gadget);
		this.div.main.adopt(this.div.icon);
		this.div.main.adopt(this.div.text);

		// put the main and sub divs in the specified parent div:
		$(options.div).adopt(this.div.main);
		$(options.div).adopt(this.div.sub);
		
		// attach event handler to gadget:
		this.div.gadget._node = this;
		this.div.gadget.onclick = this.div.gadget.ondblclick = function() { this._node.toggle(); }
		
		// attach event handler to icon/text:
		this.div.icon._node = this.div.text._node = this;
		this.div.icon.onclick = this.div.icon.ondblclick = this.div.text.onclick = this.div.text.ondblclick = function() {
			this._node.control.select(this._node);
		}
	},
	
	/*
	Property: insert
		Creates a new node, nested inside this one.
	
	Parameters:
		options - an object containing the same options available to the <MooTreeNode> constructor.

	Returns:
		A new <MooTreeNode> instance.
	*/
	insert: function(options) {
		// set the parent div and create the node:
		options.div = this.div.sub;
		options.control = this.control;
        options.cssClassName = this.cssClassName;
		var node = new MooTreeNode(options);
		node.parent = this;
		
		// mark this node's last node as no longer being the last, then add the new last node:
		var n = this.nodes;
		if (n.length) n[n.length-1].last = false;

		n.push(node);
		
		// repaint the new node:
		node.update();
		// repaint the new node's parent (this node):
		if (n.length == 1) this.update();
		// recursively repaint the new node's previous sibling node:
		if (n.length > 1) n[n.length-2].update(true);
		
		return node;
	},
	
	/*
	Property: remove
		Removes this node, and all of it's child nodes. If you want to remove all the childnodes without removing the node itself, use <MooTreeNode.clear>
	*/
	remove: function() {
		var p = this.parent;
		this._remove();
		p.update(true);
	},
	
	_remove: function() {
		// recursively remove this node's subnodes:
		var n = this.nodes;
		while (n.length) n[n.length-1]._remove();
		
		// remove the node id from the control's index:
		delete this.control.index[this.id];
		
		// remove this node's divs:
		this.div.main.destroy();
		this.div.sub.destroy();
		
		if (this.parent) {
			// remove this node from the parent's collection of nodes:
			var p = this.parent.nodes;
			p.erase(this);
			
			// in case we removed the parent's last node, flag it's current last node as being the last:
			if (p.length) p[p.length-1].last = true;
		}
	},
	
	/*
	Property: clear
		Removes all child nodes under this node, without removing the node itself.
		To remove all nodes including this one, use <MooTreeNode.remove>
	*/
	clear: function() {
		this.control.disable();
		while (this.nodes.length) this.nodes[this.nodes.length-1].remove();
		this.control.enable();
	},

	/*
	Property: update
		Update the tree node's visual appearance.
	
	Parameters:
		recursive - boolean, defaults to false. If true, recursively updates all nodes beneath this one.
		invalidated - boolean, defaults to false. If true, updates only nodes that have been invalidated while the control has been disabled.
	*/
	update: function(recursive, invalidated) {
		var draw = true;
		
		if (!this.control.enabled)
        {
			// control is currently disabled, so we don't do any visual updates
			this.invalidated = true;
			draw = false;
		}
		
		if (invalidated)
        {
			if (!this.invalidated)
				draw = false; // this one is still valid, don't draw
			else
				this.invalidated = false; // we're drawing this item now
		}
		
		if (draw)
        {
			var x;
			// make selected, or not:
			this.div.main.className = this.cssClassName + '-node' + (this.selected ? ' ' + this.cssClassName + '-selected' : '');
			
			// update indentations:
			var p = this, i = '';
			while (p.parent) {
				p = p.parent;
				i = this.getImg(p.last ? '' : 'I') + i;
			}
			this.div.indent.innerHTML = i;
			x = this.div.text;
			x.empty();
			x.appendText(this.text);
			
			// update the icon:
			this.div.icon.innerHTML = this.getImg( this.nodes.length ? ( this.open ? (this.openicon || this.icon || '_open') : (this.icon || '_closed') ) : ( this.icon || (this.control.mode == 'folders' ? '_closed' : '_doc') ) );
			// update the plus/minus gadget:
			this.div.gadget.innerHTML = this.getImg((this.control.root == this ? (this.nodes.length ? 'R' : '') : (this.last?'L':'T')) + (this.nodes.length ? (this.open?'minus':'plus') : ''));
			// show/hide subnodes:
			this.div.sub.style.display = this.open ? 'block' : 'none';
		}
		
		// if recursively updating, update all child nodes:
		if (recursive) this.nodes.forEach( function(node) { node.update(true, invalidated); });
	},
	
	/*
	Property: getImg
		Creates a new image, in the form of HTML for a DIV element with appropriate style.
		You should not need to manually call this method. (though if for some reason you want to, you can)
	
	Parameters:
		name - the name of new image to create, defined by <MooTreeIcon> or located in an external file.
	
	Returns:
		The HTML for a new div Element.
	*/
	getImg: function(name) {
		var html = '<div class="' + this.cssClassName + '-img"';
		
		if (name != '') {
            var MooTreeIcon = ['I','L','Lminus','Lplus','Rminus','Rplus','T','Tminus','Tplus','_closed','_doc','_open','minus','plus'];
			var i = MooTreeIcon.indexOf(name);
			html += ' style="background-position:-' + (i*18) + 'px 0px;"';
		}
		else { html += ' style="background-image: none;"'; }
		
		html += "></div>";
		
		return html;
	},
	
	/*
	Property: toggle
		By default (with no arguments) this function toggles the node between expanded/collapsed.
		Can also be used to recursively expand/collapse all or part of the tree.
	
	Parameters:
		recursive - boolean, defaults to false. With recursive set to true, all child nodes are recursively toggle to this node's new state.
		state - boolean. If undefined, the node's state is toggled. If true or false, the node can be explicitly opened or closed.
	*/
	toggle: function(recursive, state) {
		this.open = (state === undefined ? !this.open : state);
		this.update();

		this.onExpand(this.open);
		this.control.onExpand(this, this.open);

		if (recursive) this.nodes.forEach( function(node) { node.toggle(true, this.open); }, this);
	},
	
	/*
	Property: select
		Called by <MooTreeControl> when the selection changes.
		You should not manually call this method - to set the selection, use the <MooTreeControl.select> method.
	*/
	select: function(state) {
		this.selected = state;
		this.update();
		this.onSelect(state);
	}
});