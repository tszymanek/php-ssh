<?php


namespace Ssh\FunctionalTests;


use PHPUnit\Framework\TestCase;
use Ssh\Authentication\Password;
use Ssh\Configuration;
use Ssh\Exception\RuntimeException;
use Ssh\Session;

/**
 * @author Julius Beckmann
 *
 * @group functional
 *
 * @covers \Ssh\Exec
 */
class ExecTest extends TestCase
{
    public function testExecuteWhoami()
    {
        $configuration = new Configuration('localhost');
        $authentication = new Password(TEST_USER, TEST_PASSWORD);
        $session = new Session($configuration, $authentication);

        $exec = $session->getExec();
        $output = $exec->run('whoami');

        $this->assertEquals(TEST_USER, trim($output));
    }

    public function testExecuteMultilineOutput()
    {
        $configuration = new Configuration('localhost');
        $authentication = new Password(TEST_USER, TEST_PASSWORD);
        $session = new Session($configuration, $authentication);

        $exec = $session->getExec();
        $output = $exec->run('echo -e "a\nb\nc"');

        // In case our SystemUnderTest differs
        $output = str_replace("\r\n", "\n", $output);

        $this->assertEquals("a\nb\nc\n", $output);
    }

    public function testExecuteErrorOutput()
    {
        $this->expectException(RuntimeException::class);

        $configuration = new Configuration('localhost');
        $authentication = new Password(TEST_USER, TEST_PASSWORD);
        $session = new Session($configuration, $authentication);

        $exec = $session->getExec();
        $output = $exec->run('false');

        $this->assertEquals('', trim($output));
    }
}
 
