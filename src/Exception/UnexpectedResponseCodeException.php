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

final class UnexpectedResponseCodeException extends \RuntimeException
{
    public static function withStatusCode(int $statusCode): self
    {
        return new self(\sprintf('Unexpected response status code: %d.', $statusCode));
    }
}
