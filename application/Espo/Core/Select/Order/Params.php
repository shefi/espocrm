<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2022 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Core\Select\Order;

use Espo\Core\Select\SearchParams;

use InvalidArgumentException;

class Params
{
    private $forbidComplexExpressions = false;

    private $forceDefault = false;

    private $orderBy = false;

    private $order = false;

    private function __construct()
    {
    }

    public static function fromArray(array $params): self
    {
        $object = new self();

        $object->forbidComplexExpressions = $params['forbidComplexExpressions'] ?? false;
        $object->forceDefault = $params['forceDefault'] ?? false;
        $object->orderBy = $params['orderBy'] ?? null;
        $object->order = $params['order'] ?? null;

        foreach ($params as $key => $value) {
            if (!property_exists($object, $key)) {
                throw new InvalidArgumentException("Unknown parameter '{$key}'.");
            }
        }

        if ($object->orderBy && !is_string($object->orderBy)) {
            throw new InvalidArgumentException("Bad orderBy.");
        }

        if (
            $object->order &&
            $object->order !== SearchParams::ORDER_ASC &&
            $object->order !== SearchParams::ORDER_DESC
        ) {
            throw new InvalidArgumentException("Bad order.");
        }

        return $object;
    }

    public function forbidComplexExpressions(): bool
    {
        return $this->forbidComplexExpressions;
    }

    public function forceDefault(): bool
    {
        return $this->forceDefault;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getOrder(): ?string
    {
        return $this->order;
    }
}
