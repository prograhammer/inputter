<?php namespace Prograhammer\Inputter;


use Prograhammer\LumenCQS\Contracts\CommandInterface;

interface InputterInterface {

	public function fillWith($input = []);
	public function withSettings($settings = "");
	public function getNamespace();
	public function getGlobalClasses();
	public function addField($name, $type);
	public function cascade($parent, $previousParent="0");
	public function toArray($useAlias = false, $includeHidden = false);
	public function addCommand($key, CommandInterface $command);
	public function fields($key = null);
	public function values();
	public function commands();
	public function render();
	public function getMessageBag();
	public function hasErrors();

}