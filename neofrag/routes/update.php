<?php
/**
 * https://neofr.ag
 * @author: Michaël BILCOT <michael.bilcot@neofr.ag>
 */

namespace NF\NeoFrag\Routes;

class Update extends Route
{
	protected $_title;

	public function __construct($title)
	{
		$this->_title = $title;
	}

	public function __execute($model)
	{
		return $model	->form2()
						->success(function($model){
							$model->update();
							refresh();
						})
						->modal($this->_title, $model::$icon)
						->cancel();
	}
}
