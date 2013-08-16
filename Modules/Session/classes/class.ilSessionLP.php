<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Session to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesSession
 */
class ilSessionLP extends ilObjectLP
{
	public function getDefaultMode()
	{		
		return LP_MODE_EVENT;
	}
	
	public function getValidModes()
	{				
		return array(
			LP_MODE_EVENT
		);
	}		
}

?>