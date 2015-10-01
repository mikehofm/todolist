<?php 

class Model_Task extends RedBean_SimpleModel {
	public function dispense() {
		$this->bean->created = R::isoDateTime();
	}
}