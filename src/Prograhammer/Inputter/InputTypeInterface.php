<?php namespace Prograhammer\Inputter;


interface InputTypeInterface {

	public function setValue($value);

	public function setPrefix($prefix = "");

	public function setAttribs($attribs = []);

	public function setOptions($options);

	public function setCascade($cascade = []);

	public function setHideInUrl($hideInUrl = null);

	public function render($renderAs = 'html');

}