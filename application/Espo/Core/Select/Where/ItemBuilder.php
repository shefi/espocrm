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

namespace Espo\Core\Select\Where;

class ItemBuilder
{
    private $type = null;

    private $attribute = null;

    private $value = null;

    private $dateTime = false;

    private $timeZone = null;

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setAttribute(?string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function setIsDateTime(bool $isDateTime): self
    {
        $this->dateTime = $isDateTime;

        return $this;
    }

    public function setTimeZone(?string $timeZone): self
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /**
     * Set nested where item list.
     *
     * @param Item[] $itemList
     * @return self
     */
    public function setItemList(array $itemList): self
    {
        $this->value = array_map(
            function (Item $item): array {
                return $item->getRaw();
            },
            $itemList
        );

        return $this;
    }

    public function build(): Item
    {
        return Item::fromRaw([
            'type' => $this->type,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'dateTime' => $this->dateTime,
            'timeZone' => $this->timeZone,
        ]);
    }
}
