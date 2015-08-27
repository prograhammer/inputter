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

    protected $type = "select";

    private $id = "";

    private $value = "";

    private $text = "";

    private $cascade = [];

    public $hideInUrl = null;

    private $prefix = "";

    private $reservedAttribs = ["id"	   => "",
                                "name"	   => "",
                                "value"	   => "",
                                "data-id"  => "",
                                "data-type"=> ""];
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

    public function getText(){
        return $this->text;
    }
    public function setText($text){
        $this->text = $text;
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
        if(!is_null($this->hideInUrl)){
            $this->attribs['data-hide-in-url'] = $this->hideInUrl;
        }
        if(!empty($this->cascade)){
            $this->attribs['class'] .= " cascade";
        }
        $this->attribs['class'] .= " ".$this->prefix." inputter";

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

		// Determine if this is a "multi-select" drop-down, and get the current value(s)
		$values = array();
		$numValues = 0;
		if(isset($this->attribs['multiple']) && $this->attribs['multiple'] == "multiple"){
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
        foreach($contents as $index=>$content){
			$foundFlag = false;
			$style = "";
			// Set style of a single <OPTION>...if found in this array element (ie: ['text'] ['value'] ['style'])
			if(!empty($content['style'])){
				$style = $content['style'];
			}
			// Determine if <OPTION> or set of <OPTION>'s should have attribute selected='selected'
			for($j=0; $j < $numValues; $j++){
				if($content['value'] == $values[$j]){
					$text = $content['text'];
                    $text = htmlentities($content['text'],ENT_QUOTES,"UTF-8");
                    if(empty($text)) {
                        $text = "&nbsp;";
                    }
					$output .= "<OPTION VALUE='".(htmlentities($content['value'],ENT_QUOTES,"UTF-8"))
							.  "' title='".$text
						    .  "' selected='selected' style='$style'>".$text."</OPTION>";
					$this->setText($content['text']); // If an <OPTION> match is found, let's set $this->text also.
					$foundFlag = true;
					$wasFound = true;
					break;
				}
			}
			// If <OPTION> was determined to not be 'selected', then create it normally
			if(!$foundFlag){
                $text = htmlentities($content['text'],ENT_QUOTES,"UTF-8");
                if(empty($text)) {
                    $text = "&nbsp;";
                }
				$output .= "<OPTION VALUE='".(htmlentities($content['value'],ENT_QUOTES,"UTF-8"))."' title='".$text."' style='$style'>".$text."</OPTION>";
			}
		}

		// @todo Flaw in this function, this is an incomplete fix because during an input change, the values of the
		// non-changed input get passed and the children probably should be set to empty.
		// (so that old value isn't by chance found in the new query results)
		if(!$wasFound){ $this->value = NULL; }


		// Return as jquery
		if($renderAs == 'jquery'){
			return '$("#'.$prefix.$id.'").html("'.$output.'");';
		}
		// Return as html
		else{
			return "<SELECT ".$attributes.">".$output."</SELECT>";
		}

	}

}