<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

declare(strict_types=1);

/**
* Remote group app class
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesRemoteGroup
*/

class ilObjRemoteGroup extends ilRemoteObjectBase
{
    public const DB_TABLE_NAME = "rgrp_settings";

    /** Fixed activation **/
    public const ACTIVATION_OFFLINE = 1;
    public const ACTIVATION_UNLIMITED = 0;
    public const ACTIVATION_LIMITED = 2;

    protected $availability_type;
    protected $end;
    protected $start;

    public function initType(): void
    {
        $this->type = "rgrp";
    }

    protected function getTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    protected function getECSObjectType(): string
    {
        return "/campusconnect/groups";
    }

    /**
     * Set Availability type
     *
     * @param int $a_type availability type
     */
    public function setAvailabilityType($a_type)
    {
        $this->availability_type = $a_type;
    }

    /**
     * get availability type
     *
     * @return int
     */
    public function getAvailabilityType()
    {
        return $this->availability_type;
    }

    /**
     * set starting time
     *
     * @param timestamp $a_time starting time
     */
    public function setStartingTime($a_time)
    {
        $this->start = $a_time;
    }

    /**
     * get starting time
     *
     * @return timestamp
     */
    public function getStartingTime()
    {
        return $this->start;
    }

    /**
     * set ending time
     *
     * @param timestamp $a_time ending time
     */
    public function setEndingTime($a_time)
    {
        $this->end = $a_time;
    }

    /**
     * get ending time
     *
     * @return timestamp
     */
    public function getEndingTime()
    {
        return $this->end;
    }

    /**
     * Lookup online
     *
     * @param int $a_obj_id obj_id
     * @return bool
     */
    public static function _lookupOnline($a_obj_id)
    {
        global $ilDB;

        $query = "SELECT * FROM " . self::DB_TABLE_NAME .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        switch ($row->availability_type) {
            case self::ACTIVATION_UNLIMITED:
                return true;

            case self::ACTIVATION_OFFLINE:
                return false;

            case self::ACTIVATION_LIMITED:
                return time() > $row->r_start && time < $row->r_end;

            default:
                return false;
        }

        return false;
    }

    protected function doCreateCustomFields(array &$a_fields): void
    {
        $a_fields["availability_type"] = array("integer", 0);
        $a_fields["availability_start"] = array("integer", 0);
        $a_fields["availability_end"] = array("integer", 0);
    }

    protected function doUpdateCustomFields(array &$a_fields): void
    {
        $a_fields["availability_type"] = array("integer", $this->getAvailabilityType());
        $a_fields["availability_start"] = array("integer", (int) $this->getStartingTime());
        $a_fields["availability_end"] = array("integer", (int) $this->getEndingTime());
    }

    protected function doReadCustomFields($a_row): void
    {
        $this->setAvailabilityType($a_row->availability_type);
        $this->setStartingTime($a_row->availability_start);
        $this->setEndingTime($a_row->availability_end);
    }

    protected function updateCustomFromECSContent(ilECSSetting $a_server, $a_ecs_content): void
    {
        // add custom values
        // $this->setAvailabilityType($a_ecs_content->status == 'online' ? self::ACTIVATION_UNLIMITED : self::ACTIVATION_OFFLINE);

        // :TODO: ACTIVATION_LIMITED is currently not supported in ECS yet
    }
}
