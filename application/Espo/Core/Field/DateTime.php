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

namespace Espo\Core\Field;

use Espo\Core\Field\DateTime\DateTimeable;

use DateTimeImmutable;
use DateTimeInterface;
use DateInterval;
use DateTimeZone;
use RuntimeException;
use Throwable;

/**
 * A date-time value object. Immutable.
 */
class DateTime implements DateTimeable
{
    private $value;

    private $dateTime;

    private const SYSTEM_FORMAT = 'Y-m-d H:i:s';

    public function __construct(string $value)
    {
        if (!$value) {
            throw new RuntimeException("Empty value.");
        }

        $this->value = $value;

        try {
            $this->dateTime = DateTimeImmutable::createFromFormat(
                self::SYSTEM_FORMAT,
                $value,
                new DateTimeZone('UTC')
            );
        }
        catch (Throwable $e) {
            throw new RuntimeException("Bad value.");
        }

        if (!$this->dateTime) {
            throw new RuntimeException("Bad value.");
        }

        if ($this->value !== $this->dateTime->format(self::SYSTEM_FORMAT)) {
            throw new RuntimeException("Bad value.");
        }
    }

    /**
     * Get a string value in `Y-m-d H:i:s` format.
     */
    public function getString(): string
    {
        return $this->value;
    }

    /**
     * Get DateTimeImmutable.
     */
    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    /**
     * Get a timestamp.
     */
    public function getTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * Get a year.
     */
    public function getYear(): int
    {
        return (int) $this->dateTime->format('Y');
    }

    /**
     * Get a month.
     */
    public function getMonth(): int
    {
        return (int) $this->dateTime->format('n');
    }

    /**
     * Get a day (of month).
     */
    public function getDay(): int
    {
        return (int) $this->dateTime->format('j');
    }

    /**
     * Get a day of week. 0 (for Sunday) through 6 (for Saturday).
     */
    public function getDayOfWeek(): int
    {
        return (int) $this->dateTime->format('w');
    }

    /**
     * Get a hour.
     */
    public function getHour(): int
    {
        return (int) $this->dateTime->format('G');
    }

    /**
     * Get a minute.
     */
    public function getMinute(): int
    {
        return (int) $this->dateTime->format('i');
    }

    /**
     * Get a second.
     */
    public function getSecond(): int
    {
        return (int) $this->dateTime->format('s');
    }

    /**
     * Get a timezone.
     */
    public function getTimezone(): DateTimeZone
    {
        return $this->dateTime->getTimezone();
    }

    /**
     * Clones and modifies.
     */
    public function modify(string $modifier): self
    {
        $dateTime = $this->dateTime->modify($modifier);

        if (!$dateTime) {
            throw new RuntimeException("Modify failure.");
        }

        return self::fromDateTime($dateTime);
    }

    /**
     * Clones and adds an interval.
     */
    public function add(DateInterval $interval): self
    {
        $dateTime = $this->dateTime->add($interval);

        return self::fromDateTime($dateTime);
    }

    /**
     * Clones and subtracts an interval.
     */
    public function subtract(DateInterval $interval): self
    {
        $dateTime = $this->dateTime->sub($interval);

        return self::fromDateTime($dateTime);
    }

    /**
     * A difference between another object (date or date-time) and self.
     */
    public function diff(DateTimeable $other): DateInterval
    {
        return $this->getDateTime()->diff($other->getDateTime());
    }

    /**
     * Clones and apply a timezone.
     */
    public function withTimezone(DateTimeZone $timezone): self
    {
        $dateTime = $this->dateTime->setTimezone($timezone);

        return self::fromDateTime($dateTime);
    }

    /**
     * Create a current time.
     */
    public static function createNow(): self
    {
        return self::fromDateTime(new DateTimeImmutable());
    }

    /**
     * Create from a string with a date-time in `Y-m-d H:i:s` format.
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Create from a DateTimeInterface.
     */
    public static function fromDateTime(DateTimeInterface $dateTime): self
    {
        $value = $dateTime->format(self::SYSTEM_FORMAT);

        $utcValue = DateTimeImmutable
             ::createFromFormat(self::SYSTEM_FORMAT, $value, $dateTime->getTimezone())
             ->setTimezone(new DateTimeZone('UTC'))
             ->format(self::SYSTEM_FORMAT);

        $obj = new self($utcValue);

        $obj->dateTime = $obj->dateTime->setTimezone($dateTime->getTimezone());

        return $obj;
    }
}
