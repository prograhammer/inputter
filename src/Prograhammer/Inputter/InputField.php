<?php namespace Prograhammer\Inputter;
use Illuminate\Contracts\Support\MessageBag as MessageBagInterface;

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
class InputField{

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

    private $delimiter = "-";

    private $attribType = "";

    private $inputter;

    private $messageBag;

    public $entityName = "";

    public $attribs = [];

    public $options = [];

    public $contents = [];

    public $onCommand;


    /**
     * @param string $name
     * @param string $namespace
     * @param string $type
     * @param InputterInterface $inputter
     */
    public function __construct($name, $type, $namespace){
        $this->name = $name;
        $this->type = $type;
        $this->id = empty($namespace) ? $name : $namespace."-".$name;

        // For convenience, set tag defaults for typical html inputs
        if($this->type == "text" || $this->type == "password" || $this->type == "hidden" || $this->type == "radio" || $this->type == "checkbox"){
            $this->tag = "input";
            $this->attribs['type'] = $this->attribType = $this->type;
        }
        elseif($this->type == "autocomplete" || $this->type == "datetimepicker"){
            $this->tag = "input";
            $this->attribs['type'] = $this->attribType = "text";
        }
        elseif($this->type == "select" || $this->type == "chosen" || $this->type = "select2"){
            $this->tag = "select";
        }
    }


    public function getValue(){
        return $this->value;
    }
    public function getId(){
        return $this->id;
    }

    public function setValue($value){
        $this->value = htmlentities($value, ENT_QUOTES,'UTF-8');
        return $this;
    }

    public function getType(){
        return $this->type;
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

    public function setDelimiter($delim){
        $this->delimiter = $delim;

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
        if(!isset($attribs['type']) && !empty($this->attribType)){
            $attribs['type'] = $this->attribType;
        }
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

    public function invokeContents($value, $field){
        if(!is_callable($this->contents)){
            return $this->contents;
        }
        else{
            $this->contents = call_user_func_array($this->contents, [$value, $field]);
            return $this->contents;
        }
    }

    public function setCommand($onCommand){
        $this->onCommand = $onCommand;
        return $this;
    }

    public function invokeCommand($value, $field){
        if(is_callable($this->onCommand)){
            call_user_func_array($this->onCommand, [$value, $field]);
        }
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

}