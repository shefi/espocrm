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

namespace Espo\Core\Job;

use Espo\Core\{
    Utils\Config,
};

class QueuePortionNumberProvider
{
    private $config;

    private $queueNumberMap = [
        QueueName::Q0 => self::Q0_PORTION_NUMBER,
        QueueName::Q1 => self::Q1_PORTION_NUMBER,
        QueueName::E0 => self::E0_PORTION_NUMBER,
    ];

    private $queueParamNameMap = [
        QueueName::Q0 => 'jobQ0MaxPortion',
        QueueName::Q1 => 'jobQ1MaxPortion',
        QueueName::E0 => 'jobE0MaxPortion',
    ];

    private const Q0_PORTION_NUMBER = 200;

    private const Q1_PORTION_NUMBER = 500;

    private const E0_PORTION_NUMBER = 100;

    private const DEFAULT_PORTION_NUMBER = 200;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function get(string $queue): int
    {
        $paramName = $this->queueParamNameMap[$queue] ?? 'job' . ucfirst($queue) . 'MaxPortion';

        return
            $this->config->get($paramName) ??
            $this->queueNumberMap[$queue] ??
            self::DEFAULT_PORTION_NUMBER;
    }
}