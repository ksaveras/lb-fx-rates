<?php

declare(strict_types=1);
/*
 * This file is part of ksaveras/lb-fx-rates.
 *
 * (c) Ksaveras Sakys <xawiers@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Ksaveras\LBFxRates\Exception;

final class UnableToParseResponseException extends \RuntimeException
{
    public static function failedToLoadXML(): self
    {
        return new self('Failed to load XML response.');
    }

    public static function invalidDateValueProvided(string $date): self
    {
        return new self(\sprintf('Invalid date value provided: "%s".', $date));
    }
}