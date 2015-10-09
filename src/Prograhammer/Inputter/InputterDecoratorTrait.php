<?php namespace Prograhammer\Inputter;


use Prograhammer\LumenCQS\Contracts\CommandInterface;

trait InputterDecoratorTrait {

	public $inputter;
	private $messageBag;

	public function fillWith($input = []){
		return $this->inputter->fillWith($input);
	}

	public function withSettings($settings = []){
		return $this->inputter->withSettings($settings);
	}

	public function getNamespace(){
		return $this->inputter->getNamespace();
	}

	public function getGlobalClasses(){
		return $this->inputter->getGlobalClasses();
	}

	public function addField($name, $type){
		return $this->inputter->addField($name, $type);
	}

	public function cascade($parent, $previousParent="0"){
		return $this->inputter->cascade($parent, $previousParent);
	}

	public function toArray($useAlias = false, $includeHidden = false){
		return $this->inputter->toArray($useAlias, $includeHidden);
	}

	public function toCommand(){
		return $this->inputter->toCommand();
	}

	public function fields($key = null)
	{
		return $this->inputter->fields($key);
	}

	public function values()
	{
		return $this->inputter->values();
	}

	public function commands()
	{
		return $this->inputter->commands();
	}

	public function render(){
		return $this->inputter->render();
	}

	public function addCommand($key, CommandInterface $command){
		return $this->inputter->addCommand($key, $command);
	}

	public function getMessageBag(){
		return $this->inputter->getMessageBag();
	}

	public function hasErrors(){
		return $this->inputter->hasErrors();
	}
}