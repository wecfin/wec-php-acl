<?php
namespace phpunit\Wec\Acl;

use PHPUnit\Framework\TestCase;
use Wec\Acl\AclProcess;

class AclProcessTest extends TestCase
{
    public function testAcl()
    {
        $this->assertEquals('todo', 'todo');
    }
}
