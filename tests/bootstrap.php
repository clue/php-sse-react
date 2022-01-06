<?php

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

require __DIR__ . '/../vendor/autoload.php';

class TestCase extends PHPUnitTestCase
{
    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    /**
     * @link https://github.com/reactphp/react/blob/master/tests/React/Tests/Socket/TestCase.php (taken from reactphp/react)
     */
    protected function createCallableMock()
    {
        return $this->getMockBuilder('CallableStub')->getMock();
    }
}

class CallableStub
{
    public function __invoke()
    {
    }
}
