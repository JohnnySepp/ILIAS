<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

const SHUFFLE_NO = "0";
const SHUFFLE_YES = "1";

/**
* QTI render choice class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIRenderChoice
{
    public string $shuffle;
    public ?string $minnumber;
    public ?string $maxnumber;
    /** @var ilQTIResponseLabel[] */
    public array $response_labels;
    /** @var ilQTIMaterial[] */
    public array $material;
    
    public function __construct()
    {
        $this->shuffle = SHUFFLE_NO;
        $this->minnumber = null;
        $this->maxnumber = null;
        $this->response_labels = [];
        $this->material = [];
    }

    public function setShuffle(string $a_shuffle) : void
    {
        switch (strtolower($a_shuffle)) {
            case "0":
            case "no":
                $this->shuffle = SHUFFLE_NO;
                break;
            case "1":
            case "yes":
                $this->shuffle = SHUFFLE_YES;
                break;
        }
    }

    public function getShuffle() : string
    {
        return $this->shuffle;
    }

    public function setMinnumber(string $a_minnumber) : void
    {
        $this->minnumber = $a_minnumber;
    }

    public function getMinnumber() : ?string
    {
        return $this->minnumber;
    }

    public function setMaxnumber(string $a_maxnumber) : void
    {
        $this->maxnumber = $a_maxnumber;
    }

    public function getMaxnumber() : ?string
    {
        return $this->maxnumber;
    }
    
    public function addResponseLabel(ilQTIResponseLabel $a_response_label) : void
    {
        $this->response_labels[] = $a_response_label;
    }

    public function addMaterial(ilQTIMaterial $a_material) : void
    {
        $this->material[] = $a_material;
    }
}
