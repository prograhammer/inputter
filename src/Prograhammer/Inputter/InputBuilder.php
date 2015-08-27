<?php namespace Prograhammer\Inputter;

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
class InputBuilder{

    protected $type = "";

    private $name = "";

    private $value = "";

    private $text = "";

    private $tag = "";

    private $cascadeTo = "";

    private $hideInUrl = null;

    private $namespace = "";

    private $reservedAttribs = ["id"	   => "",
                                "name"     => "",
                                "data-type"=> "",
                                "data-hide-in-url"];

    private $multiDelim = "-";

    private $attribType = "";

    public $entityName = "";

    public $attribs = [];

    public $options = [];

    public $contents = [];


    public function __construct($name, $namespace = "", $type){
        $this->namespace = $namespace;
        $this->name = $name;
        $this->id = empty($namespace) ? $name : $namespace."-".$name;
        $this->type = $type;

        // For convenience, set tag defaults for typical html inputs
        if($this->type == "text" || $this->type == "password" || $this->type == "hidden" || $this->type == "radio" || $this->type == "checkbox"){
            $this->tag = "input";
            $this->attribType = $this->type;
        }
        elseif($this->type == "autocomplete" || $this->type == "datetimepicker"){
            $this->tag = "input";
            $this->attribType = "text";
        }
        elseif($this->type == "select" || $this->type == "chosen" || $this->type = "selectize"){
            $this->tag = "select";
        }
    }

    public function mapEntity($entityName){
        $this->entityName = $entityName;
        return $this;
    }

    public function getValue(){
        return $this->value;
    }
    public function setValue($value){
        $this->value = htmlentities($value, ENT_QUOTES,'UTF-8');
        return $this;
    }

    public function getName(){
        return $this->name;
    }

    public function getTag(){
        return $this->tag;
    }
    public function setTag($tag, $attribType = ""){
        $this->tag = $tag;
        $this->attribType = $attribType;

        return $this;
    }

    public function setMultiDelim($delim){
        $this->multiDelim = $delim;

        return $this;
    }

    public function getText(){
        return $this->text;
    }
    public function setText($text){
        $this->text = $text;
        return $this;
    }

    public function setNamespace($namespace = ""){
        $this->namespace = $namespace;
        return $this;
    }

    public function setAttribs($attribs = []){
        $this->attribs = $attribs;
        return $this;
    }

    public function getOptions(){
        return $this->options;
    }

    public function setOptions($options = []){
        $this->options = $options;
        return $this;
    }

    public function getContents(){
        return $this->contents;
    }

    public function setContents($contents){
        $this->contents = $contents;
        return $this;
    }

    public function getCascadeTo(){
        return $this->cascadeTo;
    }
    public function setCascadeTo($cascadeTo = ""){
        $this->cascadeTo = $cascadeTo;
        return $this;
    }

    public function getHideInUrl(){
        return $this->hideInUrl;
    }
    public function setHideInUrl($hideInUrl = null){
        $this->hideInUrl = $hideInUrl;
        return $this;
    }

    public function renderTag(){

        // Add attributes and classes
        if(!isset($this->attribs['class'])){
            $this->attribs['class'] = "";
        }
        if(!isset($this->attribs['type']) && !empty($this->attribType)){
            $this->attribs['type'] = $this->attribType;
        }
        if(!is_null($this->hideInUrl)){
            $this->attribs['data-hide-in-url'] = $this->hideInUrl;
        }
        if(!empty($this->cascadeTo)){
            $this->attribs['class'] = "cascade ".$this->attribs['class'];
        }
        $this->attribs['class'] = "inputter ".$this->namespace." ".$this->attribs['class'];

        // Create string of all the attributes to be inserted into the HTML tag
        $attributes = "";
        $attributes .= "id='".$this->id."' ";
        $attributes .= "name='".$this->name."' ";
        $attributes .= "data-name='".$this->name."' ";
        $attributes .= "data-type='".$this->type."' ";
        foreach($this->attribs as $attrName => $attrValue){
            $attributes .= $attrName."='".$attrValue."' ";
        }

        $tag = "";
        $numTags = 1;

        // Get number of tags if radio/checkbox
        if($this->type == "radio" || $this->type == "checkbox"){
            if (is_callable($this->contents)) {
                $numTags = count(call_user_func($this->contents));
            } else{
                $numTags = count($this->contents);
            }
        }

        // Loop through tag creation
        for($i = 0; $i < $numTags; $i++){
            $tempTag = "<".$this->tag." ".$attributes."></".$this->tag.">";

            // Add a suffix number to the tag's ID if there is more than one tag (radio/checkbox)
            if($numTags > 1){
                $tempTag = str_replace($this->id, $this->id.$i, $tempTag);
            }

            $tag .= $tempTag;
        }

        return $tag;
	}


    public function renderArray(){
        $data = [];

        $data['id'] = $this->id;
        $data['type'] = $this->type;
        $data['value'] = $this->value;
        $data['options'] = $this->options;
        $data['hideInUrl'] = $this->hideInUrl;

        if (is_callable($this->contents)) {
            $data['contents'] = call_user_func($this->contents);
        } else{
            $data['contents'] = $this->contents;
        }

        return $data;
    }

}