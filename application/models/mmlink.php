<?php
namespace Application;

use FW\MMLink as FW_MMLink;

/**
* MMLink
*/
class MMLink extends FW_MMLink
{
	function __construct($init = true)
	{
		$this->has_sequence = $this->has_sequence === null ? true : $this->has_sequence;
		$this->has_history = $this->has_history === null ? true : $this->has_history;
		$this->has_active = $this->has_active === null ? true : $this->has_active;

		parent::__construct($init);
	}
}