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

namespace Espo\ORM\Query;

use RuntimeException;

class UpdateBuilder implements Builder
{
    use SelectingBuilderTrait;

    /**
     * Create an instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Build a UPDATE query.
     */
    public function build(): Update
    {
        return Update::fromRaw($this->params);
    }

    /**
     * Clone an existing query for a subsequent modifying and building.
     */
    public function clone(Update $query): self
    {
        $this->cloneInternal($query);

        return $this;
    }

    /**
     * For what entity type to build a query.
     */
    public function in(string $entityType): self
    {
        if (isset($this->params['from'])) {
            throw new RuntimeException("Method 'in' can be called only once.");
        }

        $this->params['from'] = $entityType;

        return $this;
    }

    /**
     * Values to set. Column => Value map.
     */
    public function set(array $set): self
    {
        $this->params['set'] = $set;

        return $this;
    }

    /**
     * Apply LIMIT.
     */
    public function limit(?int $limit = null): self
    {
        $this->params['limit'] = $limit;

        return $this;
    }
}
