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

namespace Espo\Classes\Select\Email\Where\ItemConverters;

use Espo\Core\{
    Select\Where\ItemConverter,
    Select\Where\Item,
    Select\Helpers\RandomStringGenerator,
};

use Espo\{
    ORM\Query\SelectBuilder as QueryBuilder,
    ORM\Query\Part\WhereItem as WhereClauseItem,
    ORM\Query\Part\WhereClause,
    Classes\Select\Email\Helpers\EmailAddressHelper,
};

class ToEquals implements ItemConverter
{
    protected $emailAddressHelper;

    protected $randomStringGenerator;

    public function __construct(
        EmailAddressHelper $emailAddressHelper,
        RandomStringGenerator $randomStringGenerator
    ) {
        $this->emailAddressHelper = $emailAddressHelper;
        $this->randomStringGenerator = $randomStringGenerator;
    }

    public function convert(QueryBuilder $queryBuilder, Item $item): WhereClauseItem
    {
        $value = $item->getValue();

        if (!$value) {
            return WhereClause::fromRaw([
                'id' => null,
            ]);
        }

        $emailAddressId = $this->emailAddressHelper->getEmailAddressIdByValue($value);

        if (!$emailAddressId) {
            return WhereClause::fromRaw([
                'id' => null,
            ]);
        }

        $queryBuilder->distinct();

        $alias = 'emailEmailAddress' . $this->randomStringGenerator->generate();

        $queryBuilder->leftJoin(
            'EmailEmailAddress',
            $alias,
            [
                'emailId:' => 'id',
                'deleted' => false,
            ]
        );

        return WhereClause::fromRaw([
            $alias . '.emailAddressId' => $emailAddressId,
            $alias . '.addressType' => 'to',
        ]);
    }
}
