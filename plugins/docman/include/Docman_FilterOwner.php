<?php
/*
 * Copyright (c) STMicroelectronics, 2006. All Rights Reserved.
 *
 * Originally written by Manuel Vacelet, 2006
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
class Docman_FilterOwner extends \Docman_Filter
{
    public function __construct(Docman_Metadata $md)
    {
        parent::__construct($md);
    }
    public function initFromRow($row)
    {
        $this->setValue($row['value_string']);
    }
    public function _urlMatchUpdate($request)  // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if (parent::_urlMatchUpdate($request)) {
            $user = \UserManager::instance()->findUser($this->getValue());
            if ($user) {
                $this->setValue($user->getUserName());
            }
            return \true;
        }
        return \false;
    }
}
