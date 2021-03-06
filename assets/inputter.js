/**
 * @author David Graham
 * version: 1.0.0
 * https://github.com/prograhammer/inputter/
 *
 * @TODO Check on extend vs. deep extend
 * @TODO Add ability for building inputs in the background? (ie. using setTimeout)
 * @TODO History Ajax
 * @TODO Event callbacks
 * @TODO Check that data is appended in ajax data extends
 * @TODO Image loaders
 * @TODO Escaping window.location.href? For ajax urls?
 * @TODO Prevent pressing back twice on history home state? (maybe a global history count?)
 * @TODO SetSelect (for multiples), and also setAutocomplete, setFiles
 * @TODO Have jquery select the SELECT option instead of in loop?
 * @TODO Default delimeters for multiple
 * @TODO improve Select2 code (use underscore.js, dive deeper into codebase, AMD/decorators/etc.)
 */

$.fn.select2.amd.define('select2/data/customAdapter', ['select2/data/array', 'select2/utils'],
    function (ArrayData, Utils) {
        function CustomDataAdapter($element, options) {
            CustomDataAdapter.__super__.constructor.call(this, $element, options);
        }

        Utils.Extend(CustomDataAdapter, ArrayData);

        CustomDataAdapter.prototype.select = function (data) {
            var value = null;

            if (!this.options.get('multiple')) {
                value = data.id
            } else {
                value = [];
                if(this.$element.data('value') != null){
                    value = this.$element.data('value');
                }
                value.push(data.id);
            }

            this.$element.data('value', value);
            this.current(function (allData) { });
            this.$element.trigger('change');
        };

        CustomDataAdapter.prototype.unselect = function (data) {
            var value = this.$element.data('value');

            if (!this.options.get('multiple')) {
                value = [value];
            }

            // Remove value
            for(var i in value){
                if(value[i]==data.id){
                    value.splice(i,1);
                    break;
                }
            }

            this.$element.data('value', value);
            this.current(function (allData) { });
            this.$element.trigger('change');
        };

        // Looks to see if $element.data('value') can be a current selection
        CustomDataAdapter.prototype.current = function (callback) {
            var found = [],
                findValue = this.$element.data('value'),
                jsonData = this.$element.data('jsonData'),
                jsonMap = this.$element.data('jsonMap');

            if(!this.$element.prop('multiple')){
                findValue = [findValue];
            }

            // Prevent unnecessary repeated calls?
            var prevFound = this.$element.data('prevFound') || {};
            var matches = false;
            if(prevFound[0]){
                matches = true;
                if(findValue.length != prevFound.length){
                    matches = false;
                } else {
                    for (var v = 0; v < findValue.length; v++) {
                        if(findValue[v] != prevFound[v].id) {
                            matches = false;
                        }
                    }
                }
            }

            if(matches){
                callback(prevFound);
                return;
            }

            // Clear source select
            this.$element.html("");

            // Query value(s)
            var foundFlag = false;
            if(findValue !== null) {
                for (var v = 0; v < findValue.length; v++) {
                    for (var i = 0, len = jsonData.length; i < len; i++) {
                        foundFlag = false;
                        if(jsonData[i].children) {
                            for (var j = 0, lenDeep = jsonData[i].children.length; j < lenDeep; j++) {
                                if (findValue[v] == jsonData[i].children[j][jsonMap.id]){
                                    found.push({id: jsonData[i].children[j][jsonMap.id], text: jsonData[i].children[j][jsonMap.text]});
                                    if(this.$element.find("option[value='" + findValue[v] + "']").length == 0) {
                                        if(this.$element.find('optgroup[label="'+ jsonData[i][jsonMap.text] + '"]').length == 0){
                                            this.$element.append('<optgroup label="' + jsonData[i][jsonMap.text] + '"></optgroup>');
                                        }
                                        this.$element.find('optgroup[label="'+ jsonData[i][jsonMap.text] + '"]')
                                            .append(new Option(jsonData[i].children[j][jsonMap.text], jsonData[i].children[j][jsonMap.id]));
                                    }
                                    foundFlag = true;
                                    break;
                                }
                            }
                        }
                        else if (findValue[v] == jsonData[i][jsonMap.id]){
                            found.push({id: jsonData[i][jsonMap.id], text: jsonData[i][jsonMap.text]});
                            if(this.$element.find("option[value='" + findValue[v] + "']").length == 0) {
                                this.$element.append(new Option(jsonData[i][jsonMap.text], jsonData[i][jsonMap.id]));
                            }
                            foundFlag = true;
                            break;
                        }
                        if(foundFlag) { break; }
                    }
                }
            }

            // Set found matches as selected
            for (var v = 0; v < found.length; v++) {
                this.$element.find("option[value='" + found[v].id + "']").prop("selected", true).attr("selected","selected");
            }

            // If nothing was found, then set to top option (for single select)
            if (!found.length  && this.$element.prop('multiple')){
                this.$element.data('value',null);
            }
            if (!found.length && !this.$element.prop('multiple')) {  // default to top option
                if(jsonData[0].children){
                    found.push({id: jsonData[0].children[0][jsonMap.id], text: jsonData[0].children[0][jsonMap.text]});
                    if(this.$element.find('optgroup[label="'+ jsonData[0][jsonMap.text] + '"]').length == 0){
                        this.$element.append('<optgroup label="' + jsonData[0][jsonMap.text] + '"></optgroup>');
                    }
                    this.$element.find('optgroup[label="'+ jsonData[0][jsonMap.text] + '"]')
                        .append(new Option(jsonData[0].children[0][jsonMap.text], jsonData[0].children[0][jsonMap.id]));
                } else {
                    found.push({id: jsonData[0][jsonMap.id], text: jsonData[0][jsonMap.text]});
                    this.$element.html(new Option(jsonData[0][jsonMap.text], jsonData[0][jsonMap.id], true, true));
                    this.$element.data('value', jsonData[0][jsonMap.id]);
                }
            }

            this.$element.data('prevFound', found);
            callback(found);
        };

        CustomDataAdapter.prototype.query = function (params, callback) {
            if (!("page" in params)) {
                params.page = 1;
            }
            var jsonData = this.$element.data('jsonData'),
                pageSize = this.$element.data('pageSize'),
                jsonMap = this.$element.data('jsonMap');

            var results = $.map(jsonData, function(obj) {
                if(obj.children){
                    return {
                        text: obj.text,
                        children: $.map(obj.children, function(obj) {
                            if(new RegExp(params.term, "i").test(obj[jsonMap.text])) {
                                return {
                                    text: obj[jsonMap.text],
                                    id: obj[jsonMap.id]
                                };
                            }
                        })
                    }
                } else {
                    if(new RegExp(params.term, "i").test(obj[jsonMap.text])) {
                        return {
                            text: obj[jsonMap.text],
                            id: obj[jsonMap.id]
                        };
                    }
                }
            });
            callback({
                results:results.slice((params.page - 1) * pageSize, params.page * pageSize),
                pagination:{
                    more:results.length >= params.page * pageSize
                }
            });
        };

        return CustomDataAdapter;

    });

var jsonAdapter=$.fn.select2.amd.require('select2/data/customAdapter');

! function ($) {
    'use strict';

    // TOOLS DEFINITION
    // ======================

    // Call a method using a string
    function executeFunctionByName(functionName, context /*, args */) {
        var args = [].slice.call(arguments).splice(2);
        var namespaces = functionName.split(".");
        var func = namespaces.pop();
        for(var i = 0; i < namespaces.length; i++) {
            context = context[namespaces[i]];
        }

        // If exists, call it
        if (typeof context[func] == 'function'){
            return context[func].apply(this, args);
        }

        return null;
    }

    // Capitalize first letter in string
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Opposite of $.param (removes parameters from Url query string)
    function unparam(url, excludeParams) {
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
    };

    // Trim characters from the left
    String.prototype.trimLeft = function(charlist) {
        if (charlist === undefined)
            charlist = "\s";

        return this.replace(new RegExp("^[" + charlist + "]+"), "");
    };


    // INPUTTER CLASS DEFINITION
    // =========================
    var Inputter = function (el, options) {
        // Private Properties
        this.options = options;
        this.$el = $(el);
        this.firstLoad = true;
        this.cascadeStatus = false;
        this.hasFiles = false;
        this.errors = false;

        this.init();
    };

    Inputter.DEFAULTS = {
        fieldData: {},
        messagesSelector: "#messages",
        clearParentSelector: "",
        history: {
          active: true
        },
        cascade: {
            moreData: {},
            withFiles: true,
            useLoadImage: false,
            ajax: {
                url: '',
                type: 'GET',
                dataType: 'json',
                async: true,
                cache: false,
                success: function (response) {
                }
            }
        }
    };

    Inputter.prototype.init = function() {
        this.initFields();
        this.initClear();
        this.initCascade();
        this.initHistory();
    };


    Inputter.prototype.initFields = function(fields, cascading){
        fields = fields || this.options.fieldData;
        cascading = cascading || false;

        var field, method;

        for (var key in fields){
            field = fields[key];
            method = 'init' + capitalizeFirstLetter(field.type);

            if(field.type == 'file'){
                this.hasFiles = true;
            }

            executeFunctionByName(method, this, field, cascading, this);
        }
    };

    /*
    Inputter.prototype.cascadeFields = function cascadeFields (parent, cascadeTo, previousParent){
        var that = this;
        previousParent = previousParent || 0;
        var fields = [], field, method, child = '', recursive;

        var cascadeTo = that.options.fieldData[parent].cascadeTo;
        alert('here: ' + cascadeTo);
        console.log('parent: ' + parent + ', ' + previousParent);
        if(typeof cascadeTo == 'undefined' || cascadeTo == '' || cascadeTo == null){
            return false;
        }

        // Loop through all children, and their children, and so on...
        fields = cascadeTo.split(',');
        for (var i = 0, count = fields.length; i < count; i++) {
            child = fields[i];
            field = that.options.fieldData[child];
            method = 'init' + capitalizeFirstLetter(field.type);

            if(child != previousParent) {  // <-- prevents infinite loop for inputs that parent each other
                executeFunctionByName(method, that, field, true, that);
                this.cascadeFields(child, parent);
            }
        }
    };
*/

    Inputter.prototype.getAll = function(params) {
        if(typeof params === "undefined"){  params = {};  }

        // Optional parameters
        var withHideInUrl = params.hasOwnProperty('withHideInUrl') ?  params.withHideInUrl : false,
            withFiles     = params.hasOwnProperty('formData') ?  params.withFiles : false;

        // For files and FormData objects, see here for compatibility:
        // https://developer.mozilla.org/en-US/docs/Web/API/FormData

        var field, method;
        var data = {}, value;

        if(withFiles){
            data = new FormData();
        }

        for (var key in this.options.fieldData){
            field = this.options.fieldData[key];
            method = 'get' + capitalizeFirstLetter(field.type);
            value = executeFunctionByName(method, this, field, this);

            if(withHideInUrl || value != field.hideInUrl){
                if(withFiles) {
                    data.append(key, value);
                } else {
                    data[key] = value;
                }
            }
        }

        return data;
    };

    Inputter.prototype.get = function(param) {
        if(typeof param === "undefined"){  return;  }

        var field, method, value;

        field = this.options.fieldData[param];
        method = 'get' + capitalizeFirstLetter(field.type);
        value = executeFunctionByName(method, this, field, this);

        return value;
    };

    Inputter.prototype.set = function(params) {
        if(typeof params === "undefined"){  params = {};  }

        var field, method, newValue;

        for (var key in params){
            field = this.options.fieldData[key];
            method = 'set' + capitalizeFirstLetter(field.type);
            newValue = params[key];
            executeFunctionByName(method, this, field, newValue, this);
        }
    };

    Inputter.prototype.initClear = function() {
        var that = this;
        var selector = this.options.clearParentSelector;
        var fields = [];

        if(selector != ''){
            $(selector).on('click', '[data-clear-input]', function(){
                var fieldNames = $(this).data("clear-input").split(",");
                for (var i in fieldNames) {
                    fields[fieldNames[i]] = "";
                }
                that.set(fields);
            });
        }
    };


    Inputter.prototype.initCascade = function() {
        var that = this;

        // Bind an ajax call on cascading input change
        this.$el.filter(".cascade").bind("change", function () {
            var cascade = that.options.cascade;
            var cascadeParent = $(this).data('name');
            cascade.moreData.cascade = cascadeParent;

            // Extend cascade ajax options and data
            if(cascade.withFiles && that.hasFiles){
                var data = that.getAll({withFiles: true});
                for (var key in cascade.moreData){
                    data.append(key, cascade.moreData[key]);
                }
                $.extend(cascade.ajax, {
                    data: data,
                    type: 'POST',
                    processData: false,
                    contentType: false
                });
            } else {
                $.extend(cascade.ajax, {
                    data: $.extend({}, cascade.moreData, that.getAll())
                });
            }

            // Extend cascade ajax success method
            var cascadeAjax = $.extend({}, cascade.ajax, {
                success: function(response){
                    $.extend(that.fieldData, response);
                    that.initFields(response, true);

                    cascade.ajax.success(response);
                }
            });

            $.ajax(cascadeAjax);

        }); // end cascade binding

    };

    Inputter.prototype.initHistory = function() {
        var that = this;

        // Setup HTML5 history pushstate stuff and bind to the window
        var homestateUrl = window.location.href;

        if(that.options.history.active) {
            $(window).off("popstate").on("popstate", function (event) {
                var state = event.state;

                if (that.firstLoad && !state) {
                    that.firstLoad = false;
                }
                else if (state && state.hasOwnProperty('inputter')) {
                    that.firstLoad = false;
                    if (state.inputter.hasOwnProperty('ajax') == true) {
                        // Todo: loading image stuff
                        $.ajax(state.inputter.ajax);
                    }
                }
                else {
                    that.firstLoad = false;
                    that.options.history.ajax.url = $(document.createElement('div')).html(homestateUrl).text();
                    // Todo: loading image stuff
                    $.ajax(that.options.history.ajax);
                }
            });
        }
    };

    Inputter.prototype.updateUrl = function() {
        if (typeof history.replaceState === 'function') {
            var url = window.location.href,
                data = this.getAll(),
                extractedParams = unparam(url, this.options.fieldData);

            url = url.split("?")[0].split("#");
            if (extractedParams) {
                url = url + '?' + ($.param(extractedParams) + '&').trimLeft('&') + $.param(data);
            } else {
                url = url + '?' + $.param(data);
            }
            history.replaceState( {}, '', url);
        }
    };

    Inputter.prototype.pushHistory = function(options) {
        if(options){
            $.extend(this.options.history, options);
        }
        var data = this.getAll();
        var url = window.location.href;

        if (typeof history.pushState === 'function') {
            var extractedParams = unparam(url, data);
            url = url.split("?")[0].split("#");
            if (extractedParams) {
                url = url + '?' + $.param(extractedParams) + '&' + $.param(data);
            } else {
                url = url + '?' + $.param(data);
            }

            if(this.options.history.ajax.url = ''){
                this.options.history.ajax.url = url;
            }

            history.pushState({'inputter': {history: this.options.history}}, '', url);
        }
    };

    Inputter.prototype.handleResponse = function(response) {
        var that = this;
        this.errors = false;

        // Check for valid json response
        var isJson = true;
        try {
            var json = $.parseJSON(response);
        }
        catch (err) {
            isJson = false;
        }

        // Clear any previous messages/errors at selector
        $(this.options.messagesSelector).html("");

        // Output html to errors selector
        if (isJson && json.hasOwnProperty("errors")) {
            var index;
            var output = "";
            this.errors = true;
            for (index = 0; index < json.errors.length; ++index) {
                output += "<li>" + json.errors[index] + "</li>";
            }
            output = "<div class='alert alert-danger'><ul>" + output + "</ul></div>";
            $(this.options.messagesSelector).append(output);
        }

        // Output html to messages selector
        if (isJson && json.hasOwnProperty("messages")) {
            var index;
            var output = "";
            for (index = 0; index < json.messages.length; ++index) {
                output += "<li>" + json.messages[index] + "</li>";
            }
            output = "<div class='alert alert-info'><ul>" + output + "</ul></div>";
            $(this.options.messagesSelector).append(output);

            // Later...fade out the message and remove
            setTimeout(function () {
                $(that.options.messagesSelector).fadeOut("slow",
                    function () {
                        $(that.options.messagesSelector).html("");
                        $(that.options.messagesSelector).show();
                    }
                );
            }, 3500);
        }

        // Scroll to messages
        $('html, body').animate({
            scrollTop: $(that.options.messagesSelector).offset.top - 10
        }, 1000);
    };

    Inputter.prototype.hasErrors = function() {
        return this.errors;
    };


    // INITs
    // =========================

    Inputter.prototype.initText = function(field, cascading, that) {
        var $tag = $("#" + field.id);
        $tag.val(field.value);
    };

    Inputter.prototype.initTextarea = function(field, cascading, that) {
        var $tag = $("#" + field.id);
        $tag.val(field.value);
    };

    Inputter.prototype.initPassword = function(field, cascading, that) {
        that.initText(field);
    };

    Inputter.prototype.initHidden = function(field, cascading, that) {
        that.initText(field);
    };

    Inputter.prototype.initRadio = function(field, cascading, that) {
        var $radio, content;

        for (var key in field.contents){
            $radio = $("#" + field.id + key);
            content = field.contents[key];
            $radio.val(content.value);

            if(field.value == content.value){
                $radio.prop('checked', true);
            }

            if(!cascading){
                $radio.after('<label for="' + field.id + key + '" class="inputter-label">' + content.text + '</label>');
            }
        }
    };

    Inputter.prototype.initFile = function(field, cascading, that) {
        var $tag = $("#" + field.id);
        $tag.val(""); // <-- File inputs should always be set to empty when initialized/re-initialized
    };

    Inputter.prototype.initSelect = function(field, cascading, that) {
        var $selectTag = $("#" + field.id),
            optionTags = '',
            selectedValues = [],
            numSelected = 0,
            contents = field.contents,
            foundFlag = false,
            contentLength = field.contents.length;

        // Get value to be selected, or value(s) if "multiple"
        if ($selectTag.is("[multiple]")) {
            if(!field.options.hasOwnProperty('delimiter')){
                field.options['delimiter'] = '-';
            }
            selectedValues = field.value.split(field.options.delimiter);
            numSelected = selectedValues.length;
        }
        else {
            selectedValues[0] = field.value;
            numSelected = 1;
        }

        // Loop through contents array and create <OPTION> tags
        for (var index = 0; index < contentLength; ++index) {
            var style = '',
                txt = '';

            foundFlag = false;

            // Look for any styling in the contents array
            // that will be added to the <OPTION> tag
            if(typeof contents[index]['style'] !== 'undefined') {
                style = contents[index]['style'];
            }

            // Determine if <OPTION> should have attribute selected="selected"
            for (var i=0; i < numSelected; i++) {
                if (contents[index]['value'] == selectedValues[i]){

                    txt = contents[index]['text'];
                    if(txt == '') {
                        txt = '&nbsp;';
                    }

                    optionTags += '<OPTION value="' + contents[index]['value'] + '"'
                    + ' title="' + txt + '"'
                    + ' selected="selected"'
                    + ' style="' + style + '">'
                    + txt + '</OPTION>';

                    foundFlag = true;
                    break;
                }
            }

            // If <OPTION> was determined to not be "selected", then create it normally
            if(!foundFlag){
                txt = contents[index]['text'];
                if(txt == '') {
                    txt = '&nbsp;';
                }

                optionTags += '<OPTION value="' + contents[index]['value'] + '"'
                + ' title="' + txt + '"'
                + ' style="' + style + '">'
                + txt + '</OPTION>';
            }
        }

        $selectTag.html(optionTags);
    };

    Inputter.prototype.initAutocomplete = function(field, cascading, that) {
        var $tag = $("#" + field.id),
            contentLength = field.contents.length,
            foundFlag = false;

        var defaultFieldOptions = {
            minChars: 1,
            lookup: field.contents,
            appendTo: "body",
            onSelect: function (suggestion){
                $(this).val(suggestion.text);
                $(this).data("value", suggestion.value);
            }
        };

        // Set initial value if found in list of available values (contents)
        for (var index = 0; index < contentLength; ++index) {
            if(field.value == field.contents[index].value){
                $tag.data('value', field.value);
                $tag.val(field.contents[index].text);
                foundFlag = true;
                break;
            }
        }
        if(!foundFlag){
            $tag.data('value', '');
        }

        // Now make it an autocomplete
        $tag.autocomplete(
            $.extend(field.options, defaultFieldOptions)
        );
    };

    Inputter.prototype.initSelect2 = function(field, cascading, that) {
        var $tag = $("#" + field.id);

        var defaultFieldOptions = {
            ajax: {},
            theme: "bootstrap",
            width: '100%',
            escapeMarkup: function (text) { return text; },
            dataAdapter: jsonAdapter
        };

        var defaultDataOptions = {
            jsonData: field.contents,
            jsonMap: {id: "value", text: "text"},
            value: field.value.split('-'),
            pageSize: 50
        };

        if(cascading) {
            $tag.data('jsonData', field.contents).trigger('change');
        } else {
            $tag.data(defaultDataOptions).select2($.extend(field.options, defaultFieldOptions));
        }
    };

    Inputter.prototype.initChosen = function(field, cascading, that) {
        that.initSelect(field);

        var defaultFieldOptions = {
            disable_search_threshold: 15,
            search_contains: true
        }

        if(!cascading) {
            $("#" + field.id).chosen(
                $.extend(field.options, defaultFieldOptions)
            )
        }

        if(cascading){
            $("#" + field.id).trigger("chosen:updated");
        }
    };

    Inputter.prototype.initDatetimepicker = function(field, cascading, that) {
        that.initText(field);



        var defaultFieldOptions = {
            'fontAwesome': true,
            'bootcssVer': 3
        };

        if(!cascading) {
            $("#" + field.id).datetimepicker(
                $.extend(field.options, defaultFieldOptions)
            )
        }
    };



    // GETTERS
    // =========================

    Inputter.prototype.getText = function(field, that) {
        var $tag = $("#" + field.id);
        return $tag.val();
    };

    Inputter.prototype.getTextarea = function(field, that) {
        var $tag = $("#" + field.id);
        return $tag.val();
    };

    Inputter.prototype.getHidden = function(field, that) {
        return that.getText(field, that);
    };

    Inputter.prototype.getPassword = function(field, that) {
        return that.getText(field, that);
    };

    Inputter.prototype.getSelect = function(field, that) {
        var $tag = $("#" + field.id);

        if ($tag.is("[multiple]")) {
            if($tag.val() !== null){
                return ($tag.val()).join(field.options.delimiter);
            }
            return "";
        }

        return $tag.val();
    };

    Inputter.prototype.getSelect2 = function(field, that) {
        return that.getSelect(field, that);
    };

    Inputter.prototype.getChosen = function(field, that) {
        return that.getSelect(field, that);
    };

    Inputter.prototype.getFile = function(field, that) {
        var $tag = $("#" + field.id);
        return $tag[0].files[0];
    };

    Inputter.prototype.getDatetimepicker = function(field, that) {
        return that.getText(field, that);
    };

    Inputter.prototype.getRadio = function(field, that) {
        var $radio;
        for (var key in field.contents){
            $radio = $("#" + field.id + key);
            if($radio.prop('checked') == true){
                return  $radio.val();
            }
        }

        return "";
    };

    Inputter.prototype.getAutocomplete = function(field, that) {
        return $("#" + field.id).data("value");
    };


    // SETTERS
    // =========================

    Inputter.prototype.setText = function(field, newValue, that) {
        var $tag = $("#" + field.id);
        $tag.val(newValue);
    };

    Inputter.prototype.setTextarea = function(field, newValue, that) {
        var $tag = $("#" + field.id);
        $tag.val(newValue);
    };

    Inputter.prototype.setHidden = function(field, newValue, that) {
        that.setText(field, newValue, that);
    };

    Inputter.prototype.setPassword = function(field, newValue, that) {
        that.setText(field, newValue, that);
    };

    Inputter.prototype.setSelect = function(field, newValue, that) {
        var $tag = $("#" + field.id);

        $tag.val(newValue);
    };

    Inputter.prototype.setSelect2 = function(field, newValue, that) {
        var $tag = $("#" + field.id);

        $tag.data('value', newValue).trigger("change");
    };

    Inputter.prototype.setChosen = function(field, newValue, that) {
        that.setSelect(field, newValue, that);
    };

    Inputter.prototype.setFile = function(field, newValue, that) {
        var $tag = $("#" + field.id);

    };

    Inputter.prototype.setDatetimepicker = function(field, newValue, that) {
        that.setText(field, newValue, that);
    };

    Inputter.prototype.setRadio = function(field, newValue, that) {
        var $radio;

        for (var key in field.contents){
            $radio = $("#" + field.id + key);
            if(field.value == newValue){
                $radio.prop('checked','checked');
            } else {
                $radio.prop('checked','');
            }
        }
    };

    Inputter.prototype.setAutocomplete = function(field, newValue, that) {
        $("#" + field.id).data("value", newValue);
    };


    var allowedMethods = [
        'get', 'getAll', 'set',
        'updateUrl',
        'pushHistory',
        'handleResponse', 'hasErrors'
    ];

    $.fn.inputter = function (option) {
        var value;
        // first arg "0" is the method, all args "1" and above are the actual arguments for the method
        var args = Array.prototype.slice.call(arguments, 1);

        var $this = $(this);
        var data = $this.data('inputter');
        var options = $.extend(true, {}, Inputter.DEFAULTS, typeof option === 'object' && option);
        if (typeof option === 'string') {
            if ($.inArray(option, allowedMethods) < 0) {
                throw new Error("Unknown method: " + option);
            }

            if (!data) {
                return;
            }

            value = data[option].apply(data, args);

            if (option === 'destroy') {
                $this.removeData('inputter');
            }
        }

        if (!data) {
            $this.data('inputter', (data = new Inputter(this, options)));
        }

        return typeof value === 'undefined' ? this : value;
    };

}(jQuery);