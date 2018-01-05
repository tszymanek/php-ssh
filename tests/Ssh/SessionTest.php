<?php

namespace Ssh;

use PHPUnit\Framework\TestCase;
use Ssh\Exception\InvalidArgumentException;
use Ssh\Exception\RuntimeException;

/**
 * @covers \Ssh\Session
 */
class SessionTest extends TestCase
{
    protected $configuration;

    public function setUp()
    {
        $this->configuration = $this->createMock('Ssh\Configuration', ['asArguments'], ['my-host']);
        $this->configuration->expects($this->any())
            ->method('asArguments')
            ->will($this->returnValue(['my-host', 21, [], []]));
    }

    public function testAuthenticateOnResourceCreation()
    {
        $resource = tmpfile();

        $authentication = $this->createMock('Ssh\Authentication\Password', [], ['John', 's3cr3t']);
        $authentication->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($resource))
            ->will($this->returnValue(true));

        $session = $this
            ->getMockBuilder(Session::class)
            ->setConstructorArgs([$this->configuration, $authentication])
            ->setMethods(['connect'])
            ->getMock();

        $session->expects($this->once())
            ->method('connect')
            ->will($this->returnValue($resource));

        $session->getResource();
    }

    public function testAuthenticateOnAuthenticationDefinition()
    {
        $resource = tmpfile();

        $session = new Session($this->configuration);

        $property = new \ReflectionProperty($session, 'resource');
        $property->setAccessible(true);
        $property->setValue($session, $resource);

        $authentication = $this->createMock('Ssh\Authentication\Password', ['authenticate'], ['John', 's3cr3t']);
        $authentication->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($resource))
            ->will($this->returnValue(true));

        $session->setAuthentication($authentication);
    }

    public function testCreateResourceWillThrowAnExceptionOnConnectionFailure()
    {
        $this->expectException(RuntimeException::class);

        $session = $this
            ->getMockBuilder(Session::class)
            ->setConstructorArgs([$this->configuration])
            ->setMethods(['connect'])
            ->getMock();

        $session->expects($this->any())
            ->method('connect')
            ->will($this->returnValue(false));

        $method = new \ReflectionMethod($session, 'createResource');
        $method->setAccessible(true);

        $method->invoke($session);
    }

    public function testCreateResourceWillThrowAnExceptionOnAuthenticationFailure()
    {
        $this->expectException(RuntimeException::class);

        $authentication = $this->createMock('Ssh\Authentication\Password', ['authenticate'], ['John', 's3cr3t']);
        $authentication->expects($this->any())
            ->method('authenticate')
            ->will($this->returnValue(false));

        $session = $this
            ->getMockBuilder(Session::class)
            ->setConstructorArgs([$this->configuration, $authentication])
            ->setMethods(['connect'])
            ->getMock();

        $session->expects($this->any())
            ->method('connect')
            ->will($this->returnValue(true));

        $method = new \ReflectionMethod($session, 'createResource');
        $method->setAccessible(true);

        $method->invoke($session);
    }

    public function testCreateInvalidSubsystem()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The subsystem \'does_not_exist\' is not supported.');

        $session = new Session($this->configuration);

        $session->getSubsystem('does_not_exist');
    }

    public function testGetConfiguration()
    {
        $session = new Session($this->configuration);

        $this->assertEquals($this->configuration, $session->getConfiguration());
    }

    public function testGetSubsystemSftp()
    {
        $session = new Session($this->configuration);

        $this->assertInstanceOf('\Ssh\Sftp', $session->getSftp());
    }

    public function testGetSubsystemPublickey()
    {
        $session = new Session($this->configuration);

        $this->assertInstanceOf('\Ssh\Publickey', $session->getPublickey());
    }

    public function testGetSubsystemExec()
    {
        $session = new Session($this->configuration);

        $this->assertInstanceOf('\Ssh\Exec', $session->getExec());
    }

    public function testAuthenficationException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The authentication over the current SSH connection failed.');

        // A Authentication that will always fail.
        $authentication = $this->createMock('Ssh\Authentication\Password', [], ['John', 's3cr3t']);
        $authentication->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(false));

        $session = new Session($this->configuration);

        // We need to inject a resource, to trigger the authentification.
        $resource = tmpfile();
        $property = new \ReflectionProperty($session, 'resource');
        $property->setAccessible(true);
        $property->setValue($session, $resource);

        $session->setAuthentication($authentication);
    }
}
