<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the COPYING and AUTHORS files
 * that were distributed with this source code.
 */

namespace Eventum\Extension\Provider;

interface FactoryProvider extends ExtensionProvider
{
    /**
     * Create instance of $className
     *
     * @param string $className
     * @return object|null
     * @since 3.6.6
     */
    public function factory($className);
}
