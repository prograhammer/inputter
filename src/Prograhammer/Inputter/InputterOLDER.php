<?php namespace Prograhammer\Inputter;


/**
 * Inputter
 *
 * @author	David Graham <prograhammer@gmail.com>
 * @package	EasyInput
 * @license	http://www.opensource.org/licenses/mit-license.php MIT
 */
class InputterOLDER {

	public $fields = array();

	/**
	 *
	 * @var string
	 */
	protected $prefix = "";

	/**
	 * We'll store the name of the input that changed here (during a cascading input request).
	 *
	 * @var string
	 */
	private $cascade = "";

	/**
	 *
	 * @var array
	 */
	public $originalInput = array();

	public $cascadeStatus = false;

	private $gridder = ["sort"=>"","order"=>"","limit"=>"","page"=>""];

	public function __construct(){

		// Setup inputs from extended child method
		$this->init();

		// If prefix is not defined (can be set to empty however) then generate one using the lowercase class name
		//if(!isset($this->prefix)){
		//	$reflect = new \ReflectionClass($this);
		//	$this->prefix = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $reflect->getShortName()));
		//}

		//$this->baseUrl = strtok($_SERVER["REQUEST_URI"],'?');
	}

	public function init(){
		// Create your own extended child class and add a method to override this one
	}

	public function addField($id, $type){

        $this->fields[$id] = (new InputBuilder($id, $type))->setPrefix($this->prefix);

        $field = "<".$this->

		if ($type == "select") {
			$this->fields[$id] = (new InputTypes\Select($id))->setPrefix($this->prefix);
		} elseif ($type == "autocomplete") {
			$this->fields[$id] = (new InputTypes\AutoComplete($id))->setPrefix($this->prefix);
		} elseif ($type == "text") {
			$this->fields[$id] = (new InputTypes\Text($id))->setPrefix($this->prefix);
		} elseif ($type == "hidden") {
			$this->fields[$id] = (new InputTypes\Hidden($id))->setPrefix($this->prefix);
		} elseif ($type == "password") {
			$this->fields[$id] = (new InputTypes\Password($id))->setPrefix($this->prefix);
		} elseif ($type == "links") {
			$this->fields[$id] = (new InputTypes\Links($id))->setPrefix($this->prefix);
		} elseif ($type == "datetimepicker") {
			$this->fields[$id] = (new InputTypes\DateTimePicker($id))->setPrefix($this->prefix);
		}

		return $this->fields[$id];
	}

	public function fill($fill = array()){

		if(!empty($fill)){
			// Inputs
			foreach($this->fields as $inputName => $inputObj){
				if(isset($fill[$inputName])){
					$inputObj->setValue($fill[$inputName]);
				}
			}
			// Gridder params
			foreach($this->gridder as $inputName => $inputValue){
				if(isset($fill[$inputName])){
					$this->gridder[$inputName] = $fill[$inputName];
				}
			}
		}
		
		return $this;	
	}

	/**
 	 * Updates input children (when a parent input has been changed)
	 *
	 * @param $parent
	 * @param string $previousParent
	 * @return string
     */
	public function cascade($parent, $previousParent="0"){
		// Set the cascadeStatus flag to true (helpful for client to know during getOptions closures)
		$this->cascadeStatus = true;
        $outputString = "";

		if(empty($parent)){ return ""; }

		// Loop through and check all children, and their children, and so on...
        foreach ($this->fields[$parent]->getCascade() as $child) {
           if ($child != $previousParent) { // <--prevents infinite loop for inputs that parent each other
                 $outputString .= $this->fields[$child]->render('jquery');
                 $outputString .= $this->cascade($child, $parent);
           }
        }

        return $outputString;
	}

	/**
	 * Generates an assoc array of the input names and their values.
	 *
	 * Note: if the input value is equal to its hideInUrl value, then
	 * the input is not included in the array.
	 * 
	 * @return	array	An assoc array of input names their values
	 */
	public function toArray($useAlias = false, $includeHidden = false){
		$data = array();
		
		foreach($this->fields as $inputName => $inputObj){
			// If input value is equal to its hideInUrl value, then input is not included in returned array
			if(is_null($inputObj->hideInUrl) ||
				($includeHidden == false && $inputObj->getValue() != $inputObj->getHideInUrl()))
			{
				// Substitute with the alias? (helpful for validation messages)
				if($useAlias && !empty($inputObj->alias)){
					$data[$inputObj->alias] = $inputObj->getValue();
				}
				else{
					$data[$inputName] = $inputObj->getValue();
				}
			}
		}

		return $data;
	}

	public function toArrayUseAlias(){
		return $this->toArray(true);
	}
	public function toGridder(){
		$data = $this->toArray();
		$data = array_merge($data, $this->gridder);
		return $data;
	}

	/**
	 * Renders out an array of HTML inputs ready be inserted into a View. Also adds 'js' to the array
	 * so the jQuery code for the inputs can easily be added to the View (within script tags).
	 *
	 * @todo	Add more input types
	 * @return	array	An assoc array of input names each holding HTML code to be inserted into a View
	 */
	public function render(){
		$data = array();

		foreach($this->fields as $fieldName => $field){
			$data[$fieldName] = $field->render();
		}

		return $data;
	}

}