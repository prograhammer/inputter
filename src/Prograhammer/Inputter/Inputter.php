<?php namespace Prograhammer\Inputter;
use Illuminate\Contracts\Support\MessageBag as MessageBagInterface;
use Prograhammer\LumenCQS\Contracts\CommandInterface;


/**
 * Inputter
 *
 * @author	David Graham <prograhammer@gmail.com>
 * @package	EasyInput
 * @license	http://www.opensource.org/licenses/mit-license.php MIT
 */
class Inputter implements InputterInterface {

	private $fields = [];
	private $commands = [];
	private $settings = [
		"namespace"=>"",
		"globalClasses"=>""
	];

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

	private $messageBag;

	public function __construct(MessageBagInterface $messageBag){
		$this->messageBag = $messageBag;
	}

	public function withSettings($settings = []){
		$this->settings = array_replace_recursive($this->settings, $settings);
		return $this;
	}

	public function getNamespace(){
		return $this->settings["namespace"];
	}

	public function getGlobalClasses(){
		return $this->settings["globalClasses"];
	}

	public function addField($name, $type){
		// @todo Allow for setContents to be directly set to an array, string, or closure(fields,values)
		// @todo When do we invoke it? this is a problem
        $this->fields[$name] = new InputBuilder($this, $name, $type);
		return $this->fields[$name];
	}

	public function fields($key = null){
		if(empty($key)){
			return $this->fields;
		}
		return $this->fields[$key];
	}

	public function values(){
		$values = [];
		foreach($this->fields as $fieldName => $field){
			$values[$fieldName] = $field->getValue();
		}
		return $values;
	}

	public function fillWith($fill = array()){
		if(!empty($fill)){
			// Inputs
			foreach($this->fields as $inputName => $inputObj){
				if(isset($fill[$inputName])){
					$inputObj->setValue($fill[$inputName]);
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
			   $this->fields[$child]->runContents($this->fields(), $this->values());
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

	public function addCommand($key, CommandInterface $command){
		$this->commands[$key] = $command;
	}

	public function commands(){
		return $this->commands;
	}

	public function getMessageBag(){
		return $this->messageBag;
	}

	public function hasErrors(){
		return $this->messageBag->has("errors");
	}

	public function dispatchCommands(){
		foreach($this->fields as $fieldName => $field){
			$data[$fieldName] = $field->runCommand($this->fields(), $this->values(), $this->commands);
		}
		foreach($this->commands as $command){
			$command->handle();
			$this->getMessageBag()->merge($command);
		}
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
			$field->runContents($this->fields(), $this->values());
			$data[$fieldName] = $field->renderTag();
            $data['fieldData'][$fieldName] = $field->renderArray();
		}
        $data['namespace'] = $this->namespace;
		$data['errors'] = $this->getMessageBag()->get("errors");
		$data['messages'] = $this->getMessageBag()->get("messages");
        $data['fieldData'] = substr(json_encode($data['fieldData']), 1, -1);  // <-- remove outer curly's for IDE

		return $data;
	}


}