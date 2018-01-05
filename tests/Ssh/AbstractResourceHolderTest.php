<?php

namespace Ssh;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ssh\AbstractResourceHolder
 */
class AbstractResourceHolderTest extends TestCase
{
    public function testResourceIsCreatedIfItDoesNotExist()
    {
        $holder = $this->getMockForAbstractClass('Ssh\AbstractResourceHolder');
        $holder->expects($this->once())
            ->method('createResource');

        $holder->getResource();
    }

    public function testResourceIsCreatedOnlyOne()
    {
        $holder = $this->getMockForAbstractClass('Ssh\AbstractResourceHolder');
        $holder->expects($this->never())
            ->method('createResource');

        $resource = tmpfile();

        $property = new \ReflectionProperty($holder, 'resource');
        $property->setAccessible(true);
        $property->setValue($holder, $resource);

        $this->assertEquals($resource, $holder->getResource());
    }
}
