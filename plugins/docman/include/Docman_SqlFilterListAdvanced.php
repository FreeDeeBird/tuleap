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
class Docman_SqlFilterListAdvanced extends \Docman_SqlFilter
{
    public function __construct($filter)
    {
        parent::__construct($filter);
    }
    public function _getSpecificSearchChunk()
    {
        $stmt = [];
        $v    = $this->filter->getValue();
        if ($v !== \null && (\count($v) > 0 || \count($v) == 1 && $v[0] != '')) {
            $stmt[] = $this->field . ' IN (' . \implode(',', $this->filter->getValue()) . ')';
        }
        return $stmt;
    }
}
