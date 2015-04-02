<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Container/classes/class.ilContainerGUI.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/AccessControl/classes/class.ilPermissionGUI.php");
require_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
require_once("./Services/Object/classes/class.ilObjectAddNewItemGUI.php");
require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeTreeGUI.php");
require_once('./Services/Container/classes/class.ilContainerSortingSettings.php');

/**
 * Class ilObjTrainingProgrammeGUI class
 *
 * @author				Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjTrainingProgrammeGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjTrainingProgrammeGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjTrainingProgrammeGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTrainingProgrammeGUI: ilColumnGUI
 * @ilCtrl_Calls ilObjTrainingProgrammeGUI: ilObjTrainingProgrammeSettingsGUI
 * @ilCtrl_Calls ilObjTrainingProgrammeGUI: ilObjTrainingProgrammeTreeGUI
 * @ilCtrl_Calls ilObjTrainingProgrammeGUI: ilObjTrainingProgrammeMembersGUI
 */

class ilObjTrainingProgrammeGUI extends ilContainerGUI {
	/**
	 * @var ilCtrl
	 */
	public $ctrl;
	
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	
	/**
	 * @var ilTabsGUI
	 */
	public $tabs_gui;
	
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;
	
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	
	/**
	 * @var ilLocatorGUI
	 */
	protected $ilLocator;
	
	/**
	 * @var ilTree
	 */
	public $tree;
	
	/**
	 * @var ilObjOrgUnit
	 */
	public $object;
	
	/**
	 * @var ilLog
	 */
	protected $ilLog;
	
	/**
	 * @var Ilias
	 */
	public $ilias;


	public function __construct() {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias;

		parent::ilContainerGUI(array(), (int) $_GET['ref_id'], true, false);

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;
		$this->ilias = $ilias;

		$lng->loadLanguageModule("prg");
	}


	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		
		if ($cmd == "") {
			$cmd = "view";
		}

		parent::prepareOutput();

		switch ($next_class) {
			case "ilinfoscreengui":
				$this->tabs_gui->setTabActive(self::TAB_INFO);
				$this->denyAccessIfNotAnyOf(array("read", "visible"));
				$info = new ilInfoScreenGUI($this);
				$this->fillInfoScreen($info);
				$this->ctrl->forwardCommand($info);

				// I guess this is how it was supposed to work, but it doesn't... it won't respect our sub-id and sub-type when creating the objects!
				// So we reimplemented the stuff in the method parseInfoScreen()
				//                $info = new ilInfoScreenGUI($this);
				//                $amd_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
				//                $amd_gui->setInfoObject($info);
				//                $amd_gui->setSelectedOnly(true);
				//                $amd_gui->parse();
				//                $this->ctrl->forwardCommand($info);
				break;
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				$ilPermissionGUI = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($ilPermissionGUI);
				break;
			case "ilcommonactiondispatchergui":
				require_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilobjtrainingprogrammesettingsgui":
				$this->denyAccessIfNot("write");
				$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
				require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeSettingsGUI.php");
				$gui = new ilObjTrainingProgrammeSettingsGUI($this, $this->ref_id);
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilobjtrainingprogrammemembersgui":
				$this->denyAccessIfNot("manage_members");
				$this->tabs_gui->setTabActive(self::TAB_MEMBERS);
				require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeMembersGUI.php");
				$gui = new ilObjTrainingProgrammeMembersGUI($this, $this->ref_id);
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilobjtrainingprogrammetreegui":
				$this->denyAccessIfNot("write");

				$this->getSubTabs($cmd);
				$this->setContentSubTabs();
				$this->tabs_gui->setTabActive(self::TAB_VIEW_CONTENT);
				$this->tabs_gui->setSubTabActive(self::SUBTAB_VIEW_TREE);

				$gui = new ilObjTrainingProgrammeTreeGUI($this->id);
				$this->ctrl->forwardCommand($gui);
				break;
			case false:
				$this->getSubTabs($cmd);
				switch ($cmd) {
					case "create":
					case "save":
					case "view":
					case "cancel":
						$this->$cmd();
						break;
					case "delete":
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
						parent::deleteObject();
						break;
					case 'confirmedDelete':
						parent::confirmedDeleteObject();
						break;
					/*case '':
					case 'view':
					case 'render':
					case 'cancel':
					case 'cancelDelete':
						$this->view();
						break;
					case 'create':
					
						parent::createObject();
						break;
					case 'save':
						parent::saveObject();
						break;
					case 'delete':
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
						parent::deleteObject();
						break;
					case 'confirmedDelete':
						parent::confirmedDeleteObject();
						break;
					case 'cut':
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
						parent::cutObject();
						break;
					case 'clear':
						parent::clearObject();
						break;
					case 'enableAdministrationPanel':
						parent::enableAdministrationPanelObject();
						break;
					case 'disableAdministrationPanel':
						parent::disableAdministrationPanelObject();
						break;
					case 'getAsynchItemList':
						parent::getAsynchItemListObject();
						break;
					case 'editSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_settings');
						$this->editSettings();
						break;
					case 'updateSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_settings');
						$this->updateSettings();
						break;
					case 'editAdvancedSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_advanced_settings');
						$this->editAdvancedSettings();
						break;
					case 'updateAdvancedSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_advanced_settings');
						$this->updateAdvancedSettings();
						break;*/
					default:
						throw new ilException("ilObjTrainingProgrammeGUI: Command not supported: $cmd");
				}
				break;
			default:
				throw new ilException("ilObjTrainingProgrammeGUI: Can't forward to next class $next_class");
		}
	}


	protected function create() {
		parent::createObject();
	}
	
	protected function save() {
		parent::saveObject();

		if($this->ctrl->isAsynch()) {
			$form = $this->getAsyncCreationForm();
			$form->setValuesByPost();
			echo ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" =>$this->ctrl->getCmd(), "success"=>false, "errors"=>$form->getErrors()));
			exit();
		}
	}

	protected function cancel() {
		$async_response = ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" =>"cancel", "success"=>false));

		ilAsyncOutputHandler::handleAsyncOutput("", $async_response, false);

		parent::cancelCreation();
	}

	protected function afterSave(ilObject $a_new_object)
	{
		// set default sort to manual
		$settings = new ilContainerSortingSettings($a_new_object->getId());
		$settings->setSortMode(ilContainer::SORT_MANUAL);
		$settings->setSortDirection(ilContainer::SORT_DIRECTION_DESC);
		$settings->setSortNewItemsOrder(ilContainer::SORT_NEW_ITEMS_ORDER_CREATION);
		$settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM);
		$settings->save();

		$async_response = ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" =>"cancel", "success"=>true, "message"=>$this->lng->txt("object_added")));

		ilAsyncOutputHandler::handleAsyncOutput("", $async_response, false);

		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
		$this->ctrl->returnToParent($this);
	}
	
	protected function view() {
		$this->denyAccessIfNot("read");
		$this->tabs_gui->setTabActive(self::TAB_VIEW_CONTENT);

		parent::renderObject();
	}

	/**
	 * Overwritten from ilObjectGUI since copy and import are not implemented.
	 * 
	 * @param string $a_new_type
	 *
	 * @return array
	 */
	protected function initCreationForms($a_new_type) {
		return array( self::CFORM_NEW => $this->initCreateForm($a_new_type));
	}


	/**
	 * Method for implementing async windows-output
	 * Should be moved into core to enable async requests on creation-form
	 *
	 * @return ilAsyncPropertyFormGUI
	 */
	public function getAsyncCreationForm() {
		$asyncForm = new ilAsyncPropertyFormGUI();

		$asyncForm->cloneForm($this->initCreationForms('prg')[self::CFORM_NEW]);
		$asyncForm->setAsync(true);

		return $asyncForm;
	}
	
	////////////////////////////////////
	// HELPERS
	////////////////////////////////////

	protected function checkAccess($a_which) {
		return $this->ilAccess->checkAccess($a_which, "", $this->ref_id);
	}
	
	protected function denyAccessIfNot($a_perm) {
		return $this->denyAccessIfNotAnyOf(array($a_perm));
	}

	protected function denyAccessIfNotAnyOf($a_perms) {
		$deny = true;
		foreach ($a_perms as $perm) {
			if ($this->checkAccess($perm)) {
				$deny = false;
			}
			break;
		}
		
		if ($deny) {
			if ($this->checkAccess("visible")) {
				ilUtil::sendFailure($this->lng->txt("msg_no_perm_read"));
				$this->ctrl->redirectByClass('ilinfoscreengui', '');
			}

			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->WARNING);
		}
	}
	
	const TAB_VIEW_CONTENT = "view_content";
	const SUBTAB_VIEW_TREE = "view_tree";
	const TAB_INFO = "info_short";
	const TAB_SETTINGS = "settings";
	const TAB_MEMBERS = "members";
	
	public function getTabs() {
		if ($this->checkAccess("read")) {
			$this->tabs_gui->addTab( self::TAB_VIEW_CONTENT
								   , $this->lng->txt("content")
								   , $this->getLinkTarget("view"));
			$this->tabs_gui->addTab( self::TAB_INFO
								   , $this->lng->txt("info_short")
								   , $this->getLinkTarget("info_short")
								   );
		}
		
		if ($this->checkAccess("write")) {
			$this->tabs_gui->addTab( self::TAB_SETTINGS
								   , $this->lng->txt("settings")
								   , $this->getLinkTarget("settings")
								   );
		}
		
		if ($this->checkAccess("manage_members")) {
			$this->tabs_gui->addTab( self::TAB_MEMBERS
								   , $this->lng->txt("members")
								   , $this->getLinkTarget("members")
								   );
		}
		
		parent::getTabs($this->tabs_gui);
	}

	public function getSubTabs($a_parent_tab) {
		switch($a_parent_tab) {
			case self::TAB_VIEW_CONTENT:
			case self::SUBTAB_VIEW_TREE:
			case 'view':
				if($this->checkAccess("read")) {
					$this->tabs_gui->addSubTab(self::TAB_VIEW_CONTENT, $this->lng->txt("view"), $this->getLinkTarget("view"));
				}

				if($this->checkAccess("write")) {
					$this->tabs_gui->addSubTab(self::SUBTAB_VIEW_TREE, $this->lng->txt("cntr_manage"), $this->getLinkTarget(self::SUBTAB_VIEW_TREE));
				}
				break;
		}

	}


	/**
	 * Disable default content subtabs
	 */
	public function setContentSubTabs() {
		return;
	}
	
	protected function getLinkTarget($a_cmd) {
		if ($a_cmd == "info_short") {
			return $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
		}
		if ($a_cmd == "settings") {
			return $this->ctrl->getLinkTargetByClass("ilobjtrainingprogrammesettingsgui", "view");
		}
		if($a_cmd == self::SUBTAB_VIEW_TREE) {
			return $this->ctrl->getLinkTargetByClass("ilobjtrainingprogrammetreegui", "view");
		}
		if ($a_cmd == "members") {
			return $this->ctrl->getLinkTargetByClass("ilobjtrainingprogrammemembersgui", "view");
		}
		
		return $this->ctrl->getLinkTarget($this, $a_cmd);
	}
	
	protected function fillInfoScreen($a_info_screen) {
		// TODO: implement me
	}

	/**
	 * _goto
	 * Deep link
	 *
	 * @param string $a_target
	 */
	public static function _goto($a_target) {
		global $ilAccess, $ilErr, $ilCtrl;
		$id = explode("_", $a_target);
		$ilCtrl->setTargetScript("ilias.php");
		$ilCtrl->initBaseClass("ilRepositoryGUI");
		$ilCtrl->setParameterByClass("ilobjtrainingprogrammegui", "ref_id", $id[0]);

		$ilCtrl->redirectByClass(array( "ilRepositoryGUI", "ilobjtrainingprogrammegui" ), "view");
	}
}

?>