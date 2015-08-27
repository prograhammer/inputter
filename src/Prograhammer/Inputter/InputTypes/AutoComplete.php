<?php namespace Prograhammer\Inputter\InputTypes;

use Prograhammer\Inputter\InputTypeInterface;

class AutoComplete implements InputTypeInterface{

	protected $type = "autocomplete";

	private $id = "";

	private $value = "";

	private $cascade = [];

	public $hideInUrl = null;

	private $prefix = "";

	private $reservedAttribs = ["id"	 	=> "",
								"name"	 	=> "",
								"value"	 	=> "",
								"data-id"	=> "",
								"data-type"	=> ""];
	public $attribs = [];

	public $options = "";


	public function __construct($id){
		$this->id = $id;
	}

	public function getValue(){
		return $this->value;
	}
	public function setValue($value){
		$this->value = $value;
		return $this;
	}

	public function setPrefix($prefix = ""){
		$this->prefix = $prefix;
		return $this;
	}

	public function setAttribs($attribs = []){
		$this->attribs = $attribs;
		return $this;
	}

	public function setOptions($options = []){
		$this->options = $options;
		return $this;
	}

    public function getCascade(){
        return $this->cascade;
    }

	public function setCascade($cascade = []){
		$this->cascade = $cascade;
		return $this;
	}

	public function getHideInUrl(){
		return $this->hideInUrl();
	}
	public function setHideInUrl($hideInUrl = null){
		$this->hideInUrl($hideInUrl);
		return $this;
	}

	public function render($renderAs = 'html'){
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
		if (is_callable($this->options)) {
			$contents = call_user_func($this->options);
		}
		else{
			$contents = $this->options;
		}

		// Add Classes
		if(!isset($this->attribs['class'])){
			$this->attribs['class'] = "";
		}
		if(!empty($this->cascade)){
			$this->attribs['class'] .= " cascade";
		}
		$this->attribs['class'] .= " ".$this->prefix." inputter";

		// Create string of all the attributes to be inserted into the HTML tag
		$attributes = "";
		$attributes .= " id='".$this->prefix.$this->id."'";
		$attributes .= " name='".$this->id."'";
		$attributes .= " type='".$this->type."'";
		$attributes .= " value='".htmlentities($this->value, ENT_QUOTES,'UTF-8')."'";
		$attributes .= " data-id='".$this->id."'";
		$attributes .= " data-type='".$this->type."'";
		foreach($this->attribs as $attrName => $attrValue){
			$attributes .= $attrName."='".$attrValue."' ";
		}

		// Check if value is found in possible values
		$foundText = "";
		foreach($contents as $index=>$content){
			if($this->value == $contents[$index]['value']){
				$foundText = $contents[$index]['text'];
			}
			// This will eventually become embedded JSON, so we need to protect the JSON from an XSS attack
			// Also we need to change the names of the passed array to names that work with the jquery autocomplete
			$contents[$index]['data'] = str_replace("</","<\\/",$contents[$index]['value']);
			$contents[$index]['value'] = str_replace("</","<\\/",$contents[$index]['text']);
			unset($contents[$index]['text']);
		}
		$foundText  = htmlentities($foundText,ENT_QUOTES,"UTF-8");
		$this->value = htmlentities($this->value,ENT_QUOTES,"UTF-8");

		// Create <INPUT> tag
		$output = "<input id='".$prefix.$this->id."' name='".$this->id."' type='text' value='".($foundText)."' ".$attributes.">";

		// Return just HTML... or wrap in jQuery(for Ajax)
		if($renderAs == 'html') {
			$output .= '<script>$("#'.$prefix.$this->id.'").data("json",'.
							json_encode($contents).'); $("#'.$prefix.$this->id.'").data("inputter-value","'.($this->value).
						'");</script>';
			return $output;
		}
		elseif($renderAs == 'jquery'){
			$output  = '$("#'.$prefix.$this->id.'").val("'.$foundText.'"); ".
					   "$("#'.$prefix.$this->id.'").data("json",'.json_encode($contents).'); ".
					   "$("#'.$prefix.$this->id.'").data("inputter-value","'.($this->value).'");';
			$output .= '$("#'.$prefix.$this->id.'").autocomplete("setOptions", { lookup: $("#'.$prefix.$this->id.'").data("json") });';
			return $output;
		}
	}

}