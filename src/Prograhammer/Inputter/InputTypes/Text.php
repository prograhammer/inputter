<?php namespace Prograhammer\Inputter\InputTypes;

use Prograhammer\Inputter\InputTypeInterface;

class Text implements InputTypeInterface{


	protected $type = "text";

    protected $id = "";

    protected $value = "";

    protected $cascade = [];

	public $hideInUrl = null;

    protected $prefix = "";

    protected $reservedAttribs = ["id"	 	 => "",
								  "name"	 => "",
								  "value"	 => "",
								  "data-id"	 => "",
								  "data-type"=> "",
                                  "date-hide-in-url" => ""];
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
		return $this->hideInUrl;
	}
	public function setHideInUrl($hideInUrl = null){
		$this->hideInUrl = $hideInUrl;
		return $this;
	}

	public function render($renderAs = 'html'){
		$output = "";

		// Add dash to prefix
		$prefix = $this->prefix;
		if(!empty($prefix)){
			$prefix .= "-";
		}

		// Get possible values (used as more of like a 'hook' here...a place to add some extra logic to do stuff)
		if (is_callable($this->options)) {
			call_user_func($this->options);
		}

		// Add "class" and "data-hide-in-url" attributes
		if(!isset($this->attribs['class'])){
			$this->attribs['class'] = "";
		}
        if(!is_null($this->hideInUrl)){
			$this->attribs['data-hide-in-url'] = $this->hideInUrl;
		}
		if(!empty($this->cascade)){
			$this->attribs['class'] .= " cascade";
		}
		$this->attribs['class'] .= " ".rtrim($prefix,"-")." inputter";

		// Create string of all the attributes to be inserted into the HTML tag
		$attributes = "";
		$attributes .= " id='".$prefix.$this->id."'";
		$attributes .= " name='".$this->id."'";
		$attributes .= " type='".$this->type."'";
		$attributes .= " value='".htmlentities($this->value, ENT_QUOTES,'UTF-8')."'";
		$attributes .= " data-id='".$this->id."'";
		$attributes .= " data-type='".$this->type."'";
		foreach($this->attribs as $attrName => $attrValue){
			$attributes .= $attrName."='".$attrValue."' ";
		}

		// Create <INPUT> tag
		$output = "<input ".$attributes.">";

		// Return HTML
		if($renderAs == 'html') {
			return $output;
		}
		// Return jQuery (ie. for ajax)
		elseif($renderAs == 'jquery'){
			return '$("#'.$prefix.$id.'").val("'.(htmlentities($this->value, ENT_QUOTES,'UTF-8')).'");';
		}
	}

}