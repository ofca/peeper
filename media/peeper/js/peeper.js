/**
 * Peeper class.
 * 
 * @author		Adam Sauveur <http://github.com/adam-sauveur>
 * @copyright	(c) 2012 Adam Sauveur <adam.sauveur@gmail.com> 
 */
var Peeper = Class.extend({
	/**
	 * Contructor.
	 */
	init: function(url){
		
		var me = this;
		
		/**
		 * @var  bool  Peeper are rendered and ready to suck milk?
		 */
		this.rendered = false;
				
		/**
		 * @var  object
		 */
		this.requests = {};
		
		/**
		 * @var  object  Ajax request.
		 */
		this.ajaxRequest = null;
							
		/**
		 * @var  object  Loaded data wrapped in jQuery.
		 */
		this.$data = null;
		
		/**
		 * @var  integer  How many requests display?
		 */
		this.requestsPerPage = 50;
		
		/**
		 * @var  string  Url to Peeper controller.
		 */		
		this.URL = url;
		
		/**
		 * @var  boolean  Stop receiving logs.
		 */
		this.stopMilk = false;
		
		/**
		 * @var  integer  Internal counter (used to generate unique id).
		 */
		this.lastId = 0;
		
		this.standalone = typeof PEEPER_STANDALONE == "undefined" ? false : PEEPER_STANDALONE;
		this.position = 2;
		
		/**
		 * @var  jQuery  Peeper container wrapper by jQuery.
		 */
		this.$peeper = null;
		
		this.render();
	}, // eo init
	
	render: function(){
		
		var me = this;
		
		// jQuery not loaded yet? wait next 100 miliseconds
		if (typeof jQuery == "undefined"){
						
			setTimeout(function(){ me.render(); }, 100);			
			return false;
		
		} 
		
		// Add styles
		var head = document.getElementsByTagName('head')[0],			
				css = [
					'redmond/jquery-ui-1.8.11.custom.css',
					'peeper.css'
				],
				node;
				
		for (var i = 0; i < css.length; ++i){
			
			node = document.createElement('link');
			node.type = 'text/css';
			node.rel = 'stylesheet';
			node.href = me.URL + 'peeper/media/css/' + css[i];
			node.media = 'screen';
			head.appendChild(node);
			
		}
		
		// jQuery is loaded, now we can create UI (of course after DOM is loaded)
		$(function(){
			
			// create toolbar and logs container
			var html =
				'<div id="peeper">' +
				'	<h1 id="peeper-logo"><a><span class="ko3">KO3</span><span class="peeper">Peeper</span></a></h1>' + 
				'	<div class="toolbar">' +
				'		<a class="peeper-stop">Stop</a>' +
				(me.standalone === false ? '<a class="peeper-up">up</a><a class="peeper-down">down</a>' : '') +
				'	</div>' +
				'	<div class="result"></div>' +
				'	<div class="footer"><span class="copyrights">developed by sauveur</span></div>' +
				'</div>';
			
			document.getElementsByTagName('html')[0].className += ' ' + (me.standalone ? 'peeper-standalone' : 'peeper-non-standalone');
			
			$('body').append(html);
			
			me.$peeper = $('#peeper');
			
			// bind actions
			$('.peeper-stop', me.$peeper).click(function(){
				;
				if (me.stopMilk == false){
					me.stop();
					this.innerHTML = 'Start';
				} else {
					me.start(); 
					this.innerHTML = 'Stop';
				}
			});
			
			$('.peeper-up', me.$peeper).click(function(){
				
				if (me.position == 1){
					
					me.position = 2;
					
					me.$peeper.css('bottom', '0px');
					$('.footer', me.$peeper).css('bottom', '0px');
					
					return;
				}
				
				if (me.position == 2){
					
					me.position = 3;
					
					me.$peeper.css('height', '400px');
					$('.result', me.$peeper).css('height', '330px');
				}
				
			});
			
			$('.peeper-down', me.$peeper).click(function(){
				
				if (me.position == 3){
					
					me.position = 2;
					
					me.$peeper.css('height', '200px');
					$('.result', me.$peeper).css('height', '140px');
					
					return;
				}
				
				if (me.position == 2){
					
					me.position = 1;
					
					me.$peeper.css('bottom', '-160px');
					$('.footer', me.$peeper).css('bottom', '-100px');
				}
				
			});
			
			me.rendered = true;
		});
		
	}, // eo render	
	/**
	 * Generates unique id.
	 * 
	 * @return  string
	 */
	id: function(){
		++this.lastId;
		return 'peeper-'+this.lastId;
	}, // id
	/**
	 * Adds request.
	 * 
	 * @param   integer  Request id.
	 * @param   object   Information about request.
	 * @return  this
	 * @chainable
	 */
	addRequest: function(id, data){
		this.requests[id] = data;
		return this;
	}, // eo addRequest
	/**
	 * Sets number of logs, to display on the page.
	 * 
	 * 		Peeper.setLogsNumber(50);
	 * 
	 * @return  this
	 * @chainable
	 */
	setLogsNumber: function(num){
		this.requestsPerPage = num;		
		return this;
	}, // eo setLogsNumber
	/**
	 * Stops receiving logs.
	 * 
	 * @return  this
	 * @chainable
	 */
	stop: function(){
		// stop active request
		this.ajaxRequest.abort();
		this.stopMilk = true;
		return this;
	}, // eo stop
	/**
	 * Starts receiving logs.
	 * 
	 * @return  this
	 * @chainable
	 */
	start: function(){
		this.stopMilk = false;
		this.suckMilk();
		
		return this;
	},
	/**
	 * Load data (long-polling).
	 * 
	 * @return	this
	 * @chainable
	 */
	suckMilk: function(){
				
		var me = this;
		
		// Wait until Peeper will be rendered.
		if (this.rendered === false){
			setTimeout(function(){ me.suckMilk(); }, 150);
			return false;
		}
		
		this.ajaxRequest = 
			$.ajax({
				url: this.URL + 'peeper/suckMilk', 
				success: function(data){ 
					me.processResult(data);
				}
			});
	
		return this;
	}, // eo init	
	/**
	 * Process loaded data and insert it to document.
	 * 
	 * @param	string
	 * @return	void
	 */
	processResult: function(data){
				
		// this should never happend, but...
		if ( ! data && ! this.stopMilk) {
			this.suckMilk();
			return;
		}
		
		this.$data = $(data);
		delete data;
		
		// make some magic things before inserting it to DOM
		this.makeMagic();
		
		this.insert();
		
		if ( ! this.stopMilk){
			// and continue cycle...
			this.suckMilk();
		}
		
	}, // eo processResult
	/**
	 * Insert requests in to document.
	 * 
	 * @return	void
	 */
	insert: function(){
		
		var me = this;
		
		var $result = $('.result', me.$peeper);
			
		$result.prepend(this.$data);
		
		var requests = $('.result > .request', me.$peeper).length;
		
		if (requests > this.requestsPerPage){
		
			var diff = requests - this.requestsPerPage,
				result = $result.get(0);
			
			for (var i = 0; i < diff; i++){

				// remove all text nodes
				while (result.lastChild.tagName !== 'DIV'){
					
					result.removeChild( result.lastChild );
					
				}
				
				result.removeChild( result.lastChild );
				
			}
		
		}
		
	}, // eo insert
	/**
	 * @return	void
	 */
	makeMagic: function(){
		
		// create json-trees
		this.initBasicActions();
		this.createJsonTree();
		this.createXMLTree();
		this.initDBQueryTester();
		this.initRequestsTester();
		
	}, // eo parse
	initBasicActions: function(){
		
		$('.request-title', this.$data).toggle(
			function(){
				$(this).next().slideDown();
			},
			function(){
				$(this).next().slideUp();
			}
		);
		
		$('.item-header', this.$data).click(function(){
			
			var $next = $(this).next();
			
			if ($next.css('display') == 'none'){
				$next.slideDown();
			} else {
				$next.slideUp();
			}
			
		});
		
	}, // initBasicActions
	initRequestsTester: function(){
		
		var me = this;
		
		$('.test-request', this.$data).click(function(e){
			
			e.stopPropagation();
			
			var id = $(this).parent().parent().attr('id');
			
			var req = me.requests[id],
				post = false;
			
			if ($.isArray(req.post) && req.post.length != 0){
				post = true;
			} else {
				if ( ! $.isEmptyObject(req.post)){
					post = true;
				}
			}
			console.log(req.url);
			if (req.ajax){
				$.ajax({
					url: me.URL + req.url,
					type: post ? 'POST' : 'GET',
					data: post ? req.post : req.get
				});
			} 
			
		});
		
	}, // eo initRequestsTester
	initDBQueryTester: function(){
		
		var me = this;
		
		$('.profilers .test-query', this.$data).click(function(){
			
			var $this = $(this);
				query = $('span', $this).text(),
				id = me.id(),			
				$modal = $('<div class="modal" id="' + id + '"></div>');
			
			$('body').append($modal);
			
			$.ajax({
				url: me.URL + 'peeper/testquery',
				type: 'POST',
				data: {
					query: query
				},
				dataType: 'json',
				success: function(data){
					
					var result = me.renderTable(data);
					
					if (result === false){
						alert("There are no rows to display :(");
					} else {
						
						$modal = $('#'+id);
						$modal.append(result);
						$modal.dialog({
							width: 800,
							height: 300
						});
						
					}
				}
			});
						
			
		});
		
	}, // eo initDBQueryTester
	renderTable: function(data){
		
		if (data.length == 0){
			return false;
		}
		
		var table = '<table class="stripes db-result">';
		var key = '';
		
		// generate headers
		table += '<tr>';
		
		for (key in data[0]){		
			table += '<th>' + key + '</th>';			
		}
		
		table += '</tr>';
		
		// generate rows
		for (var i = 0; i < data.length; i++){
			
			table += '<tr>';
			
			for (key in data[i]){
				table += '<td>' + data[i][key] + '</td>';
			}
			
			table += '</tr>';
			
		}
		
		table += '</table>';
		
		return table;
				
	}, // eo renderTable
	createXMLTree: function(){
		
		var me = this;
		
		$('.ajax-response td.text-html, .ajax-response td.text-xml', this.$data).each(function(){
			
			var $this = $(this),
				xml = $this.find('div :first');
				
				try
				{
					html = '<div class="xml-tree"><ul>' + me.XMLTree(xml.get(0)) + '</ul></div>';
				}
				catch (e)
				{
					return;	
				}
				
			var $next = $this.next();
				
			$next.append(html);
			
			// make tree expandable
			$('.expand', $next).toggle(
				function(){
					$(this).next().next().show();
				},
				function(){
					$(this).next().next().hide();
				}
			);
		});
		
	}, // eo createXMLTree
	XMLTree: function(el){
		
		var html = '';
		

		if (el instanceof Text){
			
			if (/[\n\r\t]+/.test(el.nodeValue)) return html;
			
			html += '<li>' + el.nodeValue + '</li>';

			return html;
		}

		var attrs = '';

		if (el.attributes){
			
			var attr;

			for (var i = 0; i < el.attributes.length; i++){							
				attr = el.attributes[i];
				attrs += ' <span class="attr-name">' + attr.nodeName + '</span>="<span class="attr-value">' + attr.nodeValue + '</span>"';
			}

		}
		
		var children = false;
		var temp = '';
		
		if (el.childNodes){
			
			if (el.childNodes.length == 1 && el.childNodes[0] instanceof Text){
			
				temp += '<span class="text">' + el.childNodes[0].nodeValue + '</span>';
			
			} else {
				
				temp += '<ul>';
				
				for (var i = 0; i < el.childNodes.length; i++){
					
					temp += this.XMLTree(el.childNodes[i]);
					children = true;

				}
				
				temp += '</ul>';
			}

		}

		
		html += '<li>' + ( children ? '<span class="expand">+</span>' : '' ) + '<span class="tag-name">&lt;' + el.tagName.toLowerCase() + attrs + '&gt;</span>';
		html += temp;

		html += '<span class="tag-name">&lt;/' + el.tagName.toLowerCase() + '&gt;</span></li>';

		return html;
		
	}, // eo XMLTree
	/**
	 * Create json-tree from ajax response.
	 */
	createJsonTree: function(){
	
		var me = this;
		
		$('.ajax-response .application-json', this.$data).each(function(){
			
			var $this = $(this);
			
			try {
				var json = $.parseJSON($this.find('p').text());
			} catch (e) {
				return;
			}
				
			var html = '<div class="json-tree">' + me.jsonTree(json) + '</div>',
				$next = $this.next(); 
			
			$next.append(html);
			
			// make tree expandable
			$('.expand', $next).toggle(
				function(){
					$(this).parent().next().show();
				},
				function(){
					$(this).parent().next().hide();
				}
			);
			
		});
	
	}, // eo createJsonTree
	/**
	 * Tree creator.
	 * 
	 * @param	object	json object
	 * @return	string
	 */
	jsonTree: function(json){
		
		var html = '<ul>',
			// is value object?
			valueIsObject = false,
			// value is Array (Array is a object too)
			valueIsArray = false,
			// value is Null (Null is a object too :()
			valueIsNull = false,
			// if the value is not an object, then what type it is? :>
			valueType = '',
			// css class for key
			keyClass = '';
													
		for (var key in json){
			
			valueIsObject = typeof json[key] == 'object' ? true : false;	
			valueIsArray = json[key] instanceof Array ? true : false;	
			valueIsNull = json[key] === null;										
			keyClass = typeof key;
									
			html += '<li>';
			// key
			html += 
				'<a class="key">' +
				(valueIsObject && ! valueIsNull ? '<span class="expand">+</span>' : '') +
				'<code class="'+keyClass+'">' + key + '</code>:</a>';
		
			
			if (valueIsObject && ! valueIsNull){
				
				html += (valueIsArray ? 'Array' + '('+ json[key].length +')' : 'Object') + this.jsonTree(json[key]);							
				
			} else {
				
				valueType = typeof json[key];
				
				if (valueType === 'string'){
					html += '<pre class="value string">"' + json[key] + '"</pre>';
				} else {
					html += '<pre class="value ' + valueType + '">' + json[key] + '</pre>';
				}
				
			}
			
			html += '</li>';
		}
		
		html += '</ul>';
		
		return html;
		
	} // eo jsonTree
	
});