<?php namespace Prograhammer\Inputter\InputTypes;

/**
 * Links Class
 *
 * Sets up a "select" HTML input (ie. <select><option>...)
 *
 * Should be used in a client object that is "decorated" by the main EasyInput class.
 *
 * @author	David Graham <prograhammer@gmail.com>
 * @package	EasyInput
 * @license	http://www.opensource.org/licenses/mit-license.php MIT
 */
class Links{

	/**
	 * The type of input
	 */
	public $type = "links";

	/**
	 * The HTML id and name of the input tag.
	 *
	 * If found blank, EasyInput decorator will set it from the index of the array in the
	 * client object's $input array property. (see EasyInput docs)
	 *
	 * @var string
	 */
	public $id = "";

	/**
	 * The default or current value of the HTML input tag
	 *
	 * @var string
	 */
	public $value = "";

	/**
	 * If this input's value changes, then call ("cascade to") other inputs who have values that
	 * need to be updated.
	 * For example: If you change a select input called "states", then the select input called "cities" will
	 * 				need to be updated.
	 *
	 * @var string
	 */
	public $cascadeTo = "";

	/**
	 * If the selected value equals this, then do not include this input in a historyPush Url update.
	 * Useful for keeping Urls short and clean as possible. The value will still be passed to server side.
	 *
	 * @var string
	 */
	public $hideInUrl = null;

	/**
	 * Sets the style attribute of the HTML input tag
	 *
	 * @var string
	 */
	public $style = "";

	/**
	 * Sets the class attribute of the HTML input tag
	 *
	 * Note: The $this->render() method will append the class "inputter" to all input classes. If a prefix is
	 * set on the client object, then that prefix will also be appended as another class.
	 * (ie. <input class="your-prefix inputter etc..." ...</input>)
	 *
	 * @var string
	 */
	public $class = "";

	/**
	 * A prefix helps to prevent against collisions with other HTML inputs. For example, a popup with ajaxed
	 * content might contain inputs that are named the same as inputs on the parent web-page. The name "firstname"
	 * is such an example of a common name collision.
	 *
	 * Note: If the client object that is being decorated by EasyInput has provided an overall single prefix, then
	 * EasyInput will overwrite the value here (unless the overall prefix is empty).
	 *
	 * @var string
	 */
	public $prefix = "";


	/**
	 * An array to hold all custom attributes to be added to the input tag.
	 * For example:    ['custom']['data-my-custom-attr'] ="xyz"  will create <input data-my-custom-attr='xyz' ...
	 * 				or ['custom']['tabindex'] ="1" will create <input tabindex='1' ...
	 * 				etc...
	 *
	 * Note: The $this->render() method may add some of it's own custom attributes that are needed
	 * so that inputter.js will work. See comments for $this->render() method.
	 *
	 * @var array
	 */
	public $custom = array();
	protected $reservedCustom = array("data-id"=>"", "data-type"=>"");

	/**
	 * This can be an array or an anonymous function(closure) that returns an array. The purpose of this property
	 * is to give the list of possible values. For example, in the case of a "select" input, the possible values
	 * would be all the values contained in the drop-down (would also include the current value if found in
	 * the list and marked 'selected' in $this->render() method).
	 *
	 * @var string 	Can be an string or anonymous function(closure) that returns a string. It's a useful
	 * 				place to add some additional logic to respond to or affect other inputs in the
	 * 				client objects $input array.
	 */
	public $possibleValues = "";


	/**
	 * Generates HTML code for a text input (or similar inputs such as "hidden" and "password").
	 * List of settings that affect the rendering of this input are:
	 *
	 * @param	string	$outputAs	Allowable values are 'html','jquery'.
	 * 								'html' tells the method to create the element as html code.
	 * 								'jquery' tells the method to create the element as jQuery code. Script tags will
	 * 								not be added however. This is good for ajax calls that would need to update the
	 * 								value of an input that currently exists in the page.
	 * @return	string	The generated HTML or jQuery code
	 */
	public function render($outputAs='html'){
		$id = $this->id;
		$output = "";

		//Get possible values (used as more of like a 'hook' here...a place to add some extra logic to do stuff)
		if (is_callable($this->possibleValues)) {
			$contents = call_user_func($this->possibleValues);
		} else {
			$contents = $this->possibleValues;
		}

		// Add some required custom attributes
		$this->custom['data-id'] 	= $this->id;
		$this->custom['data-type'] 	= $this->type;

		// Append some required classes
		$this->class .= " ".$this->prefix." inputter";
		if(!empty($this->cascadeTo)){
			$this->class .= " cascade";
		}

		// Create string of all the attributes to be inserted into the HTML tag
		$attributes = "";
		foreach($this->custom as $attrName => $attrValue){
			$attributes .= $attrName."='".$attrValue."' ";
		}
		$attributes = $this->class." ".$attributes;

		// Create Links
		$contentLength = count($contents);
		for($i=0; $i < $contentLength; $i++){
			$selected = "";
			if($contents[$i]['value'] == $this->value){
				$selected = "class='selected'";
			}
			$output .= "<li ".$selected.">".(htmlentities($contents[$i]['text'],ENT_QUOTES,"UTF-8"))."<input type='hidden' name='".$id."[]' value='".(htmlentities($contents[$i]['value'],ENT_QUOTES,'UTF-8'))."'></li>";
		}

		$js = "$(function(){
				    $('#".$this->prefix.$id."').on('click', 'li', function(){
				    		$('#".$this->prefix.$id." > li').removeClass('selected');
				    		$(this).addClass('selected');
				    });
			   });";

		// Return HTML
		if($outputAs == 'html') {
			$output = "<script>".$js."</script> <ul id='".$this->prefix.$id."' ".$attributes.">".$output."</ul>";
			return $output;
		}
		// Return jQuery
		elseif($outputAs == 'jquery'){
			return $js.' $("#'.$this->prefix.$id.'").html("'.$output.'");';
		}
	}

}