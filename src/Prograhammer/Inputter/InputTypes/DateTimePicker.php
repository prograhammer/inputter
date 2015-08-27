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
class DateTimePicker{

    protected $type = "datetimepicker";

    private $id = "";

    private $value = "";

    private $text = "";

    private $cascade = [];

    public $hideInUrl = null;

    private $prefix = "";

    private $reservedAttribs = [
        "id"	   => "",
        "name"	   => "",
        "value"	   => "",
        "data-id"  => "",
        "data-type"=> ""
    ];

    public $attribs = [];

    public $options = "";

    public $jsonArray = [];

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

    public function setJSON($jsonArray = []){
        $this->jsonArray = $jsonArray;
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
        $id 		= $this->id;
        $prefix		= $this->prefix;

        // Add dash to prefix
        if(!empty($prefix)){
            $prefix .= "-";
        }

        // Get possible values (used as more of like a 'hook' here...a place to add some extra logic to do stuff)
        if (is_callable($this->options)) {
            call_user_func($this->options);
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

        // Create <INPUT> tag
        $output = "<input ".$attributes." data-json='".json_encode($this->jsonArray)."'>";

        // Return HTML
        if($renderAs == 'html') {
            return $output;
        }
        // Return jQuery (ie. for ajax)
        elseif($renderAs == 'jquery'){
            return '$("#'.$prefix.$id.'").'.
                        'val("'.(htmlentities($this->value, ENT_QUOTES,'UTF-8')).'").'.
                        'data("json",'.json_encode($this->jsonArray).');';
        }

	}

}