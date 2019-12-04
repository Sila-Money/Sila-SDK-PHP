<?php

/**
 * Identity Alias
 * PHP version 7.2
 */

namespace Silamoney\Client\Domain;

use MyCLabs\Enum\Enum;

/**
 * Identity Alias
 * Enum used in Identity class.
 * @category Class
 * @package  Silamoney\Client
 * @author   José Morales <jmorales@digitalgeko.com>
 */
class IdentityAlias extends Enum
{
    public const SSN = "SSN";
    public const EIN = "EIN";
    public const ITIN = "ITIN";
}
