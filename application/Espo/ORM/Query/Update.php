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

/**
 * Update parameters.
 */
class Update implements Query
{
    use SelectingTrait;
    use BaseTrait;

    /**
     * Get an entity type.
     */
    public function getIn(): ?string
    {
        return $this->params['from'] ?? null;
    }

    /**
     * Get a LIMIT.
     */
    public function getLimit(): ?int
    {
        return $this->params['limit'] ?? null;
    }

    private function validateRawParams(array $params): void
    {
        $this->validateRawParamsSelecting($params);

        $from = $params['from'] ?? null;

        if (!$from || !is_string($from)) {
            throw new RuntimeException("Select params: Missing 'in'.");
        }

        $set = $params['set'] ?? null;

        if (!$set || !is_array($set)) {
            throw new RuntimeException("Update params: Bad or missing 'set' parameter.");
        }
    }
}
