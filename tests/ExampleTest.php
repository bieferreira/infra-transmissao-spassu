<?php

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testSomaBasica()
    {
        $resultado = 2 + 2;
        $this->assertEquals(4, $resultado);
    }
}
