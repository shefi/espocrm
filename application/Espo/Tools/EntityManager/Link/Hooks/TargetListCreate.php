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

namespace Espo\Tools\EntityManager\Link\Hooks;

use Espo\Tools\EntityManager\Link\CreateHook;
use Espo\Tools\EntityManager\Link\Params;
use Espo\Tools\EntityManager\Link\Type;
use Espo\Modules\Crm\Entities\TargetList;

use Espo\Core\Utils\Metadata;

class TargetListCreate implements CreateHook
{
    private Metadata $metadata;

    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function process(Params $params): void
    {
        $toProcess =
            (
                $params->getEntityType() === TargetList::ENTITY_TYPE ||
                $params->getForeignEntityType() === TargetList::ENTITY_TYPE
            ) &&
            $params->getType() === Type::MANY_TO_MANY;

        if (!$toProcess) {
            return;
        }

        [$entityType, $link, $foreignLink] = $params->getEntityType() === TargetList::ENTITY_TYPE ?
            [
                $params->getForeignEntityType(),
                $params->getForeignLink(),
                $params->getLink(),
            ] :
            [
                $params->getEntityType(),
                $params->getLink(),
                $params->getForeignLink(),
            ];

        $type = $this->metadata->get(['scopes', $entityType, 'type']);

        if (!in_array($type, ['Person', 'Company'])) {
            return;
        }

        if ($link !== 'targetLists') {
            return;
        }

        $this->processInternal($entityType, $link, $foreignLink);
    }

    private function processInternal(string $entityType, string $link, string $foreignLink): void
    {
        $this->metadata->set('entityDefs', TargetList::ENTITY_TYPE, [
            'links' => [
                $foreignLink => [
                    'additionalColumns' => [
                        'optedOut' => [
                            'type' => 'bool',
                        ]
                    ],
                    'columnAttributeMap' => [
                        'optedOut' => 'isOptedOut',
                    ],
                ],
            ],
        ]);

        $this->metadata->set('entityDefs', $entityType, [
            'links' => [
                $link => [
                    'columnAttributeMap' => [
                        'optedOut' => 'targetListIsOptedOut',
                    ],
                ],
            ],
            'fields' => [
                'targetListIsOptedOut' => [
                    'type' => 'bool',
                    'notStorable' => true,
                    'readOnly' => true,
                    'disabled' => true,
                ],
            ]
        ]);

        $this->metadata->set('clientDefs', TargetList::ENTITY_TYPE, [
            'relationshipPanels' => [
                $foreignLink => [
                    'actionList' => [
                        [
                            'label' => 'Unlink All',
                            'action' => 'unlinkAllRelated',
                            'acl' => 'edit',
                            'data' => [
                                'link' => $foreignLink,
                            ],
                        ],
                    ],
                    'rowActionsView' => 'crm:views/target-list/record/row-actions/default',
                    'view' => 'crm:views/target-list/record/panels/relationship',
                    'massSelect' => true,
                ],
            ],
        ]);

        $targetLinkList = $this->metadata->get(['scopes', TargetList::ENTITY_TYPE, 'targetLinkList']) ?? [];

        if (!in_array($foreignLink, $targetLinkList)) {
            $targetLinkList[] = $foreignLink;

            $this->metadata->set('scopes', TargetList::ENTITY_TYPE, [
                'targetLinkList' => $targetLinkList,
            ]);
        }

        $this->metadata->save();
    }
}
