<?php

namespace Ssh;

use PHPUnit\Framework\TestCase;
use Ssh\Exception\InvalidArgumentException;

/**
 * @covers \Ssh\Subsystem
 */
class SubsystemTest extends TestCase
{
    public function testSessionResourceIsNotUsedOnCreation()
    {
        $session = $this->createMock(
            'Ssh\Session', array(), array(), '', false
        );

        $session->expects($this->never())->method('getResource');

        $subsystem = $this->getMockForAbstractClass(
            'Ssh\Subsystem', array($session)
        );
    }

    public function testInvalidContructorArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The session must be either a Session instance or a SSH session resource.');

        new Exec(false);
    }

    public function testGetSessionResourceWillReturnResource()
    {
        $resource = tmpfile();
        $exec = new Exec($resource);

        $this->assertEquals($resource, $exec->getSessionResource());
    }

    public function testGetSessionResourceWillCallSessionGetResource()
    {
        $session = $this->createMock(
            'Ssh\Session', array('getResource'), array(), '', false
        );
        $session->expects($this->once())->method('getResource')->will($this->returnValue('aResource'));

        $exec = new Exec($session);

        $this->assertEquals('aResource', $exec->getSessionResource());
    }
}
