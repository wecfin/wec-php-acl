<?php
namespace Wec\Acl;

use PHPUnit\Framework\TestCase;
use Wec\Acl\AclProcess;

class AclProcessTest extends TestCase
{
    public function testAcl()
    {
        $acls = [
            'admin' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee', 'createEmployee','updateEmployee'
                ],
                'forbid' => []
            ],
            'acctant' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee'
                ],
                'forbid' => []
            ]
        ];
        $aclProcess = new AclProcess($acls);

        $this->assertEquals($acls, $aclProcess->acl());
    }

    public function testAllow()
    {
        $aclProcess = new AclProcess([]);
        $role1 = 'admin';
        $resources1 = [
            'fetchEmployee', 'listEmployee', 'createEmployee', 'updateEmployee'
        ];

        $aclProcess->allow($role1, $resources1);
        $realResult1 = $aclProcess->acl();
        $dueResult1 = [
            'admin' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee', 'createEmployee','updateEmployee'
                ]
            ]
        ];
        $this->assertEquals($dueResult1, $realResult1);

        $role2 = 'acctant';
        $resources2 = [
            'fetchEmployee'
        ];

        $aclProcess->allow($role2, $resources2);
        $realResult2 = $aclProcess->acl();
        $dueResult2 = [
            'admin' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee', 'createEmployee','updateEmployee'
                ]
            ],
            'acctant' => [
                'allow' => [
                    'fetchEmployee'
                ]
            ]
        ];
        $this->assertEquals($dueResult2, $realResult2);

        $role3 = 'acctant';
        $resources3 = [
            'listEmployee'
        ];

        $aclProcess->allow($role3, $resources3);
        $realResult3 = $aclProcess->acl();
        $dueResult3 = [
            'admin' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee', 'createEmployee','updateEmployee'
                ]
            ],
            'acctant' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee'
                ]
            ]
        ];
        $this->assertEquals($dueResult3, $realResult3);
    }

    public function testIsAllowed()
    {
        $acls = [
            'admin' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee', 'createEmployee','updateEmployee'
                ]
            ],
            'acctant' => [
                'allow' => [
                    'fetchEmployee', 'listEmployee'
                ]
            ]
        ];
        $aclProcess = new AclProcess($acls);

        $roles1 = ['admin'];
        $resource1 = 'createEmployee';
        $this->assertTrue($aclProcess->isAllowed($roles1, $resource1));

        $roles2 = ['acctant'];
        $resource2 = 'createEmployee';
        $this->assertFalse($aclProcess->isAllowed($roles2, $resource2));
    }
}
