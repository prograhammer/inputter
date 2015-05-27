<?php namespace Prograhammer\Inputter\InputTypes;

/**
 * Select Class
 *
 * Sets up a "select" HTML input (ie. <select><option>...)
 *
 * Should be used in a client object that is "decorated" by the main EasyInput class.
 *
 * @author	David Graham <prograhammer@gmail.com>
 * @package	EasyInput
 * @license	http://www.opensource.org/licenses/mit-license.php MIT
 */
class Select{

	/**
	 * The type of input
	 */
	public $type = "select";

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
	 * This property is automatically set to the text that goes with the selected value found in
	 * $this->possibleValues() method. This is not a property that will be rendered out as a tag attribute.
	 *
	 * Use this feature to help with populating other inputs.
	 * For example, say you have two inputs:
	 * 		players (select input type)
	 * 		playerName	(text input type)
	 * Instead of performing a query in your playerName->possibleValues() method (where you use players->value to query
	 * for a player name) you can just grab players->text, which is the text of the value found/selected from
	 * players->possibleValues().
	 *
	 * @var string
	 */
	public $text = "";

	/**
	 * This property will be rendered out as an HTML tag attribute. It's also used to indicate to the rendering method
	 * $this->render() that multiple values will need to be selected.
	 *
	 * Use comma delimited values for multi-select inputs. For example: $this->value = "1,2,3";
	 * The rendering method will explode the comma delimited value into multiple values to select all the matching
	 * options for you. This creates a nicer/shorter URL experience. However, if you still prefer to use arrays
	 * instead, you can set this property to equal "multiple-array", ie. $this->multiple = "multiple-array".
	 *
	 * @var mixed 	Allowable type/[values] are: bool/[true,false] or string/["multiple","multiple-array"]
	 */
	public $multiple = "";

	/**
	 * This can be an array or an anonymous function(closure) that returns an array. The purpose of this property
	 * is to give the list of possible values. For example, in the case of a "select" input, the possible values
	 * would be all the values contained in the drop-down (would also include the current value if found in
	 * the list and marked 'selected' in $this->render() method).
	 *
	 * @var array 	Can be an array or anonymous function(closure) that returns an array. The array must contain
	 *				two-dimensional arrays with two elements with indexes 'text' and 'value'
	 *				For example: [0]['text']   [0]['value']
	 * 							 [1]['text']   [1]['value']
	 *							 ...
	 */
	public $possibleValues = array();


	/**
	 * Generates HTML code for a select input (a.k.a. drop-down/combo-box).
	 * List of settings that affect the rendering of this input are:
	 *
	 * @todo	Currently multi-select dropdown values are stored in comma-delimited form and passed in urls that way.
	 * 			Need to add ability to pass them as arrays as forms do: myarray=1,2,3 vs. myarray[]=1&myarray=2&myarray=3
	 * @todo	What happens if the possible_values content is empty???
	 * @todo	During an input change...should we clear the children first?
	 * @todo	Add validation ['validation'] for server side validation, because users can manually add stuff to url params?

	 * @param	string	$outputAs	Allowable values are 'html','jquery'.
	 * 								'html' tells the method to create the element as html code.
	 * 								'jquery' tells the method to create the element as jQuery code. Script tags will
	 * 								not be added however. This is good for ajax calls that would need to update the
	 * 								value of an input that currently exists in the page.
	 * @return	string	The generated HTML or jQuery code
	 */
	public function render($outputAs='html'){
		$output 	= "";
		$this->text = "";
		$id 		= $this->id;
		$prefix		= $this->prefix;


		// Add dash to prefix
		if(!empty($prefix)){
			$prefix .= "-";
		}

		// Get possible values (as an array or as an anonymous function that returns an array)
		// The array should be in the form of   [0]['text']	  [1]['text']   [...]['text']
		// 										[0]['value']  [1]['value']  [...]['value']
		if (is_callable($this->possibleValues)) {
			$contents = call_user_func($this->possibleValues);
		} else {
			$contents = $this->possibleValues;
		}

		// Create string of all the attributes to be inserted into the HTML tag
		$attributes = "";
		if(!empty($this->cascadeTo)){
			$this->class .= " cascade";
		}
		$attributes .= " class='".$this->prefix." inputter ".$this->class."'";
		$attributes .= " id='".$prefix.$id."'";
		$attributes .= " name='".$id."'";
		$this->custom['data-id'] 	= $this->id;
		$this->custom['data-type'] 	= $this->type;
		foreach($this->custom as $attrName => $attrValue){
			$attributes .= $attrName."='".$attrValue."' ";
		}

		// Determine if this is a "multi-select" drop-down, and get the current value(s)
		$values = array();
		$numValues = 0;
		if($this->multiple === true || $this->multiple == "multiple" || $this->multiple == "multiple-array"){
			$values = explode(",",$this->value);
			$numValues = count($values);
		}
		else{
			$values['0'] = $this->value;
			$numValues = 1;
		}

		// Loop through the contents of the possibleValues array, and create HTML <OPTION> tags for each
		$foundFlag = false;
		$wasFound  = false;
		$contentLength = count($contents);
		for($i=0; $i < $contentLength; $i++){
			$foundFlag = false;
			$style = "";
			// Set style of a single <OPTION>...if found in this array element (ie: ['text'] ['value'] ['style'])
			if(!empty($contents[$i]['style'])){
				$style = $contents[$i]['style'];
			}
			// Determine if <OPTION> or set of <OPTION>'s should have attribute selected='selected'
			for($j=0; $j < $numValues; $j++){
				if($contents[$i]['value'] == $values[$j]){
					$text = $contents[$i]['text'];
					if($text != "&nbsp;"){
						$text = htmlentities($contents[$i]['text'],ENT_QUOTES,"UTF-8");
					}
					$output .= "<OPTION VALUE='".(htmlentities($contents[$i]['value'],ENT_QUOTES,"UTF-8"))
							.  "' title='".htmlentities($contents[$i]['text'],ENT_QUOTES,'UTF-8')
						    .  "' selected='selected' style='$style'>".$text."</OPTION>";
					$this->text = $contents[$i]['text']; // If an <OPTION> match is found, let's set $this->text also.
					$foundFlag = true;
					$wasFound = true;
					break;
				}
			}
			// If <OPTION> was determined to not be 'selected', then create it normally
			if(!$foundFlag){
				$temp = $contents[$i]['text'];
				if($temp != "&nbsp;"){
					$temp = htmlentities($contents[$i]['text'],ENT_QUOTES,"UTF-8");
				}
				$output .= "<OPTION VALUE='".(htmlentities($contents[$i]['value'],ENT_QUOTES,"UTF-8"))."' title='".htmlentities($contents[$i]['text'],ENT_QUOTES,'UTF-8')."' style='$style'>".$temp."</OPTION>";
			}
		}

		// @todo Flaw in this function, this is an incomplete fix because during an input change, the values of the
		// non-changed input get passed and the children probably should be set to empty.
		// (so that old value isn't by chance found in the new query results)
		if(!$wasFound){ $this->value = NULL; }


		// Return as jquery
		if($outputAs == 'jquery'){
			return '$("#'.$prefix.$id.'").html("'.$output.'");';
		}
		// Return as html
		else{
			return "<SELECT ".$attributes.">".$output."</SELECT>";
		}

	}

}