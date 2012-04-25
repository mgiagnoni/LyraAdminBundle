<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Util;

use Lyra\AdminBundle\Util\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testICUTojQueryDate()
    {
        $this->assertEquals('DD dd mm yy', Util::ICUTojQueryDate('EEEE dd MM y'));
        $this->assertEquals('D dd mm yy', Util::ICUTojQueryDate('EEE dd M y'));
        $this->assertEquals('D dd M yy', Util::ICUTojQueryDate('EE dd MMM yyyy'));
        $this->assertEquals('D dd MM y', Util::ICUTojQueryDate('E dd MMMM yy'));
    }
}

