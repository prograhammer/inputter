(function($){
	// TODO: store each ajax object in history, so history runs exact same ajax (ie. POST vs. GET, script vs. ...etc)
	// TODO: check for cascade in get method (we did it with class .cascade so document it)
	// TODO: add prefixing (add a data-attribute for prefix stripping in url) NO NO let's do underscores
	// TODO: make sure data no url/history is added to history
	// TODO: test will all kinds of variations on url (? & #)
	// TODO: see how it acts with post
	// TODO: how to handle a redirect?
	// TODO: check if loader image is working for chosen.js / etc....otherwise we may have to specify it in inputter initilization
	var methods = {
		element : {},
		firstLoad : true,
		cascadeStatus : false,
		settings: {
			history : false,
			ajax	: 	{
				'url': 				'',
				'type': 			'GET',
				'dataType': 		'script',
				'data': 			{},
				'async': 			true,
				'cache': 			false,

				'loadImgSelector': "body",
				'dataNoUrl': 		{},
				'redirect': 		""
			},
			cascade : {
				'useLoadImg':		true,
				'url': 				'',
				'type': 			'GET',
				'dataType': 		'script',
				'data': 			{},
				'async': 			true,
				'cache': 			false,
				'success': 			function(response){}
			}
		},
		init : function(element, settings) {
			this.element = element;

			// Setup autocompletes
			this.autocomplete();

			// Create a new object and attach it to this element's data,
			// so we can do stuff like $('#selector').data('inputter').get();
			var newObject = $.extend(true, {}, this);  // <-- make a copy
			$.extend(true, newObject.settings, settings);
			$(this.element).data("inputter", newObject);

			// Now let's use a variable to make work easier (no confusion with "this" inside functions)
			var inputter = $(this.element).data("inputter");

			// If no URL given, then set URL's to current page (but remove the query string part)
			if(inputter.settings.ajax.url == ""){
				inputter.settings.ajax.url = [location.protocol, '//', location.host, location.pathname].join('');
			}
			if(inputter.settings.cascade.url == ""){
				inputter.settings.cascade.url = [location.protocol, '//', location.host, location.pathname].join('');
			}


			// Bind an ajax call on cascading input change
			$(inputter.element).filter(".cascade").bind("change autocompleteclose", function(){
				var cascade = inputter.settings.cascade;
				$.extend(cascade.data, inputter.get(false));
				cascade.data['cascade'] = $(this).data('id');

				inputter.cascadeStatus = true;  // cascading has started
				var _this = $(this); // Store the element so we can use it in ajax.success

				// If this is a file, we need to handle ajax differently
				if($(this).data("type") == "file") {
					// Create a FormData xhr2 object and append all of our data
					var formData = new FormData();
					// Let's append the file
					formData.append($(this).data('id'), $(this)[0].files[0]);
					// Now append all other form data
					$.each(cascade.data, function(key, value){
						formData.append(key, value);
					});

					var loadingSelector = $(this).data("loading-selector");

					$.ajax({
						url: cascade.url,
						type: "POST",
						dataType: cascade.dataType,
						data: formData,
						processData: false,
						contentType: false,
						beforeSend: function(){
							if(loadingSelector != ''){
								$(loadingSelector).html("<i class='fa fa-spinner fa-spin'></i> Uploading...");
							}
						},
						success: function (response) {
							_this.val("");
							inputter.cascadeStatus = false; // cascading is finished
							if(loadingSelector != ''){
								$(loadingSelector).html("");
							}
							$(inputter.element).filter(".chosen").trigger("chosen:updated");
							cascade.success(response);
						}
					});
				}
				// For all other cascades
				else {
					// Show loading image
					if(inputter.cascade.useLoadImg == true){
						inputter.loadImg("#".$(this).attr('id'));
					}

					$.ajax({
						url: cascade.url,
						type: cascade.type,
						dataType: cascade.dataType,
						data: cascade.data,
						async: cascade.async,
						cache: cascade.cache,
						success: function (response) {
							inputter.cascadeStatus = false; // cascading is finished
							$(inputter.element).filter(".chosen").trigger("chosen:updated");

							cascade.success(response);
							parent.unloadImg("#".$(this).attr('id'));

							//if(typeof window[config.callback] == 'function'){
							//	window[config.callback]();
							//}
						}
						//error: function() {
						//	console.log($.makeArray(arguments));
						//}
					});
				}
			});


			// Setup HTML5 history pushstate stuff and bind to the window
			var homestateUrl = window.location;
			$(window).off("popstate");
			$(window).on("popstate", function(event){
				var state = event.state;
				var data = {};

				if(inputter.firstLoad && !state){
					inputter.firstLoad = false;
				}
				else if(state && state.hasOwnProperty('inputter') && state.inputter.hasOwnProperty('url')){
					inputter.firstLoad = false;
					if(state.inputter.hasOwnProperty('data') == true){
						data = state.inputter.data;
					}

					if(state.inputter.hasOwnProperty('loadImgSelector') == true && state.inputter.loadImgSelector != ""){
						inputter.loadImg(state.inputter.loadImgSelector);
					}
					$.ajax({
						url: state.inputter.url,
						type: 'GET',
						dataType: 'script',
						data: data,
						async: true,
						cache: false,
						success: function(response){
							if(state.inputter.hasOwnProperty('loadImgSelector') == true && state.inputter.loadImgSelector != ""){
								inputter.unloadImg(state.inputter.loadImgSelector);
							}
						}
					});
				}
				else if(homestateUrl.length){
					inputter.firstLoad = false;

					inputter.loadImg(inputter.settings.ajax.loadImgSelector);

					$.ajax({
						url: $(document.createElement('div')).html(homestateUrl).text(),
						type: 'GET',
						data: data,
						dataType: 'script',
						async: true,
						cache: false,
						success: function(response){
							inputter.unloadImg(inputter.settings.ajax.loadImgSelector);
						}
					});
				}

			});
		},
		autocomplete : function(){
			var _this = this;
			$(this.element).filter("[data-type='autocomplete']").each(function(){
				$(this).autocomplete({
					minLength: 1,
					source: $(this).data("json"),
					appendTo: "body",
					focus: function (event, ui){
						$(this).val(ui.item.label);
						$(this).data("inputter-value", ui.item.value);
						return false;
					},
					select: function (event, ui){
						$(this).val(ui.item.label);
						$(this).data("inputter-value", ui.item.value);
						return false;
					},
					close: function (event, ui){
						$(this).autocomplete("option","appendTo","body"); // Fixes the autocomplete appearing behind dialogs
					}
				});
				$(this).change(function(){
					if($(this).val() == ''){
						$(this).data("inputter-value", "");
					}
				});
			});
		},
		autocompleteTypeahead : function(el){
			// Setup autocomplete using twitter's typeahead (w/Bloodhound)
			$(el).filter("[data-type='autocomplete']").each(function(){
				var states = $(this).data("json");
				// constructs the suggestion engine
				var states = new Bloodhound({
					datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
					queryTokenizer: Bloodhound.tokenizers.whitespace,
					// `states` is an array of state names defined in "The Basics"
					local: $.map(states, function(state) { return { id: state.value, value: state.text }; })
				});

				// kicks off the loading/processing of `local` and `prefetch`
				states.initialize();

				var parent = this;

				$(this).typeahead({
						hint: true,
						highlight: true,
						minLength: 1
					},
					{
						name: 'states',
						displayKey: 'value',
						// `ttAdapter` wraps the suggestion engine in an adapter that
						// is compatible with the typeahead jQuery plugin
						source: states.ttAdapter()
					}).on('typeahead:selected', function(e, data, dataSetName){
						// Add the value to the element's data attribute so we can grab it later (ie. using get())
						$(parent).data("value", data.id);
						alert($(parent).data("value"));
					});

			});
		},
		clear : function(data){
			var inputter = this;

			if(typeof(data) == "undefined"){
				$(inputter.element).each( function(){
					$(this).val("");
				});
			}
			else{
				var inputs =  data.split(",");

				$(inputter.element).each( function(){
					if(inputs.indexOf($(this).data("id")) > -1){
						$(this).val("");
						$(this).data("inputter-value", "");
					}
				});
			}

			$("select").trigger("chosen:updated");
		},
		// TODO: make a decision on how to pass extra data
		get : function(historyPush){
			var inputter = this;

			// Since we can't do this: function(historyPush = true)...
			historyPush = typeof historyPush !== 'undefined' ? historyPush : true;

			// Gather data (and Worf, Geordi, and the rest of the away team...)
			var data = {};
			$(inputter.element).each( function(){
				// Multi-selects: turn values into comma-delimited string
				if($(this).is('select') && $(this).attr('multiple') == 'multiple'){
					data[$(this).data('id')] = "";
					if($(this).val() !== null){
						data[$(this).data('id')] = ($(this).val()).toString();
					}
				}
				else if($(this).data('type') == "file" && $(this).attr('multiple') == 'multiple'){
					data[$(this).data('id')] = ($(this).val()).toString();
				}
				else if($(this).data('type') == "autocomplete"){
					data[$(this).data('id')] = $(this).data("inputter-value");
				}
				else if($(this).hasClass('ckeditor')){  //ckeditor textarea content
					var id = $(this).attr('id');
					data[$(this).data('id')] = CKEDITOR.instances[id].getData();
				}
				else if($(this).data('type') == "links"){
					data[$(this).data('id')] = $(this).find(".selected :input").val();
				}
				else if($(this).data('type') == "radio"){
					if(typeof data[$(this).data('id')] == 'undefined'){
						data[$(this).data('id')] = null;
					}
					if($(this).is(':checked')){
						data[$(this).data('id')] = $("#"+$(this).attr('id')).val();
					}
				}

				// All others, get value normally (except skip single file uploads,
				//	client's should just make an xhr2 ajax call for those, appending in this 'get' data)
				else if($(this).data('type') != "file" && $(this).data('type') != "label") {
					data[$(this).data('id')] = $(this).val();  // Use non-prefixed ID
				}
			});
			// Push to history
			if(historyPush && inputter.settings.history){
				inputter.historyPush(inputter.settings.ajax.url, data, inputter.settings.ajax.dataNoUrl, inputter.settings.ajax.loadImgSelector);
			}

			return data;
		},
		//objectSize : function(obj) {
		//    var size = 0, key;
		//    for (key in obj) {
		//        if (obj.hasOwnProperty(key)) size++;
		//    }
		//    return size;
		//},
		// Gets all the other params in a url
		unparam : function(url,excludeParams) {
			var params = {};
			var i, pieces, numPieces, pair, paramName;

			if(url.indexOf("?") != -1){
				url = url.split("?")[1];
				pieces = url.split('&');
				numPieces = pieces.length;
				for(i = 0; i < numPieces; i++){
					pair = pieces[i].split('=',2);
					paramName = decodeURIComponent(pair[0]);
					if(excludeParams != undefined && !excludeParams.hasOwnProperty(paramName)){
						params[paramName] = (pair.length == 2 ? decodeURIComponent(pair[1].replace(/\+/g, ' ')) : true);
					}
				}
			}

			return params
		},
		loadImg : function(selector) {
			if(selector.length == 0){ return false;}

			var top = '50';

			//Calculate and center loading image within the visable area of the container
			var docViewTop = $(window).scrollTop();
			var docViewBottom = docViewTop + $(window).height();

			var elem = selector;
			var elem_height = $(elem).height();
			var elemTop = $(elem).offset().top;
			var elemBottom = elemTop + elem_height;

			if((docViewBottom - elemTop) > 50 && (docViewBottom - elemTop) < (elem_height - 50)){   //50 is the height of the img
				top = parseInt((docViewBottom - elemTop)/elem_height * 100/2);
			}
			else if((elemBottom - docViewTop) > 50 && (elemBottom - docViewTop) < (elem_height - 50)){
				top = 100 - parseInt((elemBottom - docViewTop)/elem_height * 100/2);
			}
			alert('here');
			var img_src   = "ajax-loader.gif";

			var img_style = "position:absolute;" 		+
				"left:50%;" 				+
				"top:" + top + "%;"		+
				"margin-left:-25px;"		+
				"margin-right:-25px;" 	+
				"margin-top:-25px;" 		+
				"margin-bottom:-25px;"	+
				"display:block;"			+
				"width:50px;"				+
				"height:50px;";

			$(selector).css('opacity','0.3');
			$(selector).wrap("<div class='easy_ajax_tmp012345' style='position:relative;'>");
			$(selector).parent().append("<img class='inputter_tmp012345' style='" + img_style + "' src='" + img_src + "'>");
			$(selector).parent().append("<div class='inputter_tmp012345' style='clear:both;'></div>");


		},
		unloadImg : function(selector){
			if(selector.length == 0){ return false;}

			$(selector).css('opacity','');
			if($('.inputter-temp').length){
				$(selector).unwrap();
				$(selector).parent().children('.inputter-temp').remove();
			}
		},
		historyPush : function(url, data, dataNoUrl, loadImgSelector) {
			// Add to history
			if(typeof history.pushState === 'function'){
				if(!$.isEmptyObject(data)){
					var extractedParams = this.unparam(url, data);
					url = url.split("?")[0].split("#");
					if(!$.isEmptyObject(extractedParams)){
						url = url + '?' + $.param(extractedParams) + '&' + $.param(data);
					}
					else{
						url = url + '?' + $.param(data);
					}
				}
				history.pushState({inputter: {url: url, data: dataNoUrl, loadImgSelector: loadImgSelector}},'', url);
			}
		},
		ajax : function(settings) {
			var inputter = this;

			//Let's merge in any ajax settings passed, into our settings
			$.extend(true, inputter.settings, settings);

			var data = $.extend(inputter.get(), inputter.ajax.data);

			if(inputter.settings.history){
				inputter.historyPush(inputter.settings.ajax.url, data, inputter.settings.ajax.dataNoUrl, inputter.settings.ajax.loadImgSelector);
			}

			//Now that we've done the history stuff, let's merge in those dataNoUrl params
			$.extend(data, inputter.ajax.dataNoUrl);

			//Setup loading image
			if(inputter.ajax.loadImgSelector != ""){
				inputter.loadImg(inputter.ajax.loadImgSelector);
			}

			// Make ajax call
			$.ajax({
				url: inputter.ajax.url,
				type: inputter.ajax.type,
				dataType: inputter.ajax.dataType,
				data: data,
				async: inputter.ajax.async,
				cache: inputter.ajax.cache,
				success: function(response){
					inputter.unloadImg(inputter.ajax.loadImgSelector);
					//if(typeof window[config.callback] == 'function'){
					//	window[config.callback]();
					//}
				}
				//error: function() {
				//	console.log($.makeArray(arguments));
				//}
			});

		}
	};

	$.fn.inputter = function(methodOrOptions) {

		if ( methods[methodOrOptions] ) {
			return methods[methodOrOptions].apply(this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof methodOrOptions === 'object' || !methodOrOptions ) {
			// Default to "init"
			// return methods.init.apply( this, arguments );   this actually changes what this means in methods (this object to this selector)
			return methods.init(this, methodOrOptions);

		} else {
			return $.error( 'Method ' +  methodOrOptions + ' does not exist on jQuery.inputter' );
		}


	};


})(jQuery);