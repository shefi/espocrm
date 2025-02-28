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

namespace Espo\Core\AclPortal;

use Espo\Core\Interfaces\Injectable;

use Espo\ORM\Entity;

use Espo\Entities\User;

use Espo\Core\AclManager;

use Espo\Core\{
    ORM\EntityManager,
    Portal\AclManager as PortalAclManager,
    Utils\Config,
    Acl\AccessChecker,
    Acl\ScopeData,
    Portal\Acl\Table,
    Portal\Acl\AccessChecker\ScopeChecker,
    Portal\Acl\AccessChecker\ScopeCheckerData,
    Portal\Acl\DefaultAccessChecker,
};

/**
 * @deprecated Use AccessChecker interfaces instead.
 */
class Base implements AccessChecker, Injectable
{
    protected $dependencyList = [];

    protected $dependencies = [];

    protected $injections = [];

    protected $entityManager;

    protected $aclManager;

    protected $config;

    protected $scopeChecker;

    protected $defaultChecker;

    public function __construct(
        EntityManager $entityManager,
        PortalAclManager $aclManager,
        Config $config,
        ScopeChecker $scopeChecker,
        DefaultAccessChecker $defaultChecker
    ) {
        $this->entityManager = $entityManager;
        $this->aclManager = $aclManager;
        $this->config = $config;
        $this->scopeChecker = $scopeChecker;
        $this->defaultChecker = $defaultChecker;

        $this->init();
    }

    public function check(User $user, ScopeData $data): bool
    {
        return $this->defaultChecker->check($user, $data);
    }

    public function checkEntity(User $user, Entity $entity, ScopeData $data, string $action): bool
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwnChecker(
                function () use ($user, $entity): bool {
                    return (bool) $this->checkIsOwner($user, $entity);
                }
            )
            ->setInAccountChecker(
                function () use ($user, $entity): bool {
                    return (bool) $this->checkInAccount($user, $entity);
                }
            )
            ->setInContactChecker(
                function () use ($user, $entity): bool {
                    return (bool) $this->checkIsOwnContact($user, $entity);
                }
            )
            ->build();

        return $this->scopeChecker->check($data, $action, $checkerData);
    }

    public function checkScope(User $user, ScopeData $data, ?string $action = null): bool
    {
        if (!$action) {
            return $this->defaultChecker->check($user, $data);
        }

        if ($action === Table::ACTION_CREATE) {
            return $this->defaultChecker->checkCreate($user, $data);
        }

        if ($action === Table::ACTION_READ) {
            return $this->defaultChecker->checkRead($user, $data);
        }

        if ($action === Table::ACTION_EDIT) {
            return $this->defaultChecker->checkEdit($user, $data);
        }

        if ($action === Table::ACTION_DELETE) {
            return $this->defaultChecker->checkDelete($user, $data);
        }

        if ($action === Table::ACTION_STREAM) {
            return $this->defaultChecker->checkStream($user, $data);
        }

        return false;
    }

    public function checkIsOwner(User $user, Entity $entity)
    {
        return $this->aclManager->checkOwnershipOwn($user, $entity);
    }

    public function checkInAccount(User $user, Entity $entity)
    {
        return $this->aclManager->checkOwnershipAccount($user, $entity);
    }

    public function checkIsOwnContact(User $user, Entity $entity)
    {
        return $this->aclManager->checkOwnershipContact($user, $entity);
    }

    protected function getConfig(): Config
    {
        return $this->config;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function getAclManager(): AclManager
    {
        return $this->aclManager;
    }

    public function inject($name, $object)
    {
        $this->injections[$name] = $object;
    }

    protected function init()
    {
    }

    protected function getInjection($name)
    {
        return $this->injections[$name] ?? $this->$name ?? null;
    }

    protected function addDependencyList(array $list)
    {
        foreach ($list as $item) {
            $this->addDependency($item);
        }
    }

    protected function addDependency($name)
    {
        $this->dependencyList[] = $name;
    }

    public function getDependencyList()
    {
        return array_merge($this->dependencyList, $this->dependencies);
    }
}
