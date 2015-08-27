<?php namespace Prograhammer\Inputter;


/**
 * Inputter
 *
 * @author	David Graham <prograhammer@gmail.com>
 * @package	EasyInput
 * @license	http://www.opensource.org/licenses/mit-license.php MIT
 */
class InputterOLD {

	public $fields = array();

	/**
	 *
	 * @var string
	 */
	protected $namespace = "";

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

	public function addField($name, $type){
        $this->fields[$name] = new InputBuilder($name, $this->namespace, $type);
		return $this->fields[$name];
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
        $fieldData = [];

        $cascadeTo = $this->fields[$parent]->getCascadeTo();
        if(empty($cascadeTo)){
            return;
        }

        // Loop through and check all children, and their children, and so on...
        $explodedChildren = explode(",",$cascadeTo);
        foreach ($explodedChildren as $child) {
           if ($child != $previousParent) { // <--prevents infinite loop for inputs that parent each other
               $fieldData[$child] = $this->fields[$child]->renderArray();
               $this->cascade($child, $parent);
           }
        }

        return json_encode($fieldData);
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
			if(is_null($inputObj->getHideInUrl()) ||
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
			$data[$fieldName] = $field->renderTag();
            $data['fieldData'][$fieldName] = $field->renderArray();
		}
        $data['namespace'] = $this->namespace;
        $data['fieldData'] = substr(json_encode($data['fieldData']), 1, -1);  // <-- remove outer curly's for IDE

		return $data;
	}

}