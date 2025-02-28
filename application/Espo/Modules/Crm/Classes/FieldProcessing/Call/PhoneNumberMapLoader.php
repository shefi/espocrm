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

namespace Espo\Modules\Crm\Classes\FieldProcessing\Call;

use Espo\ORM\Entity;

use Espo\Core\ORM\Entity as CoreEntity;

use Espo\Core\{
    FieldProcessing\Loader,
    FieldProcessing\Loader\Params,
    ORM\EntityManager,
};

use stdClass;

class PhoneNumberMapLoader implements Loader
{
    private const ERASED_PART = 'ERASED:';

    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(Entity $entity, Params $params): void
    {
        $map = (object) [];

        assert($entity instanceof CoreEntity);

        $contactIdList = $entity->getLinkMultipleIdList('contacts');

        if (count($contactIdList)) {
            $this->populate($map, 'Contact', $contactIdList);
        }

        $leadIdList = $entity->getLinkMultipleIdList('leads');

        if (count($leadIdList)) {
            $this->populate($map, 'Lead', $leadIdList);
        }

        $entity->set('phoneNumbersMap', $map);
    }

    private function populate(stdClass $map, string $entityType, array $idList): void
    {
        $entityList = $this->entityManager
            ->getRDBRepository($entityType)
            ->where([
                'id' => $idList,
            ])
            ->select(['id', 'phoneNumber'])
            ->find();

        foreach ($entityList as $entity) {
            $phoneNumber = $entity->get('phoneNumber');

            if (!$phoneNumber) {
                continue;
            }

            if (strpos($phoneNumber, self::ERASED_PART) === 0) {
                continue;
            }

            $key = $entity->getEntityType() . '_' . $entity->getId();

            $map->$key = $phoneNumber;
        }
    }
}
