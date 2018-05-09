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
                    'main' => ['fetchEmployee', 'listEmployee', 'createEmployee'],
                    'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder', 'createOrder'],
                    'product' => ['createEmployee']
                ]
            ],
            'acctant' => [
                'allow' => [
                    'main' => ['fetchEmployee', 'listEmployee'],
                    'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder']
                ]
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
            'main' => ['fetchEmployee', 'listEmployee', 'createEmployee'],
            'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder', 'createOrder'],
            'product' => ['createEmployee']
        ];

        $aclProcess->allow($role1, $resources1);
        $realResult1 = $aclProcess->acl();
        $dueResult1 = [
            'admin' => [
                'allow' => [
                    'main' => ['fetchEmployee', 'listEmployee', 'createEmployee'],
                    'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder', 'createOrder'],
                    'product' => ['createEmployee']
                ]
            ]
        ];
        $this->assertEquals($dueResult1, $realResult1);

        $role2 = 'acctant';
        $resources2 = [
            'main' => ['fetchEmployee', 'listEmployee'],
            'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder']
        ];

        $aclProcess->allow($role2, $resources2);
        $realResult2 = $aclProcess->acl();
        $dueResult2 = [
            'admin' => [
                'allow' => [
                    'main' => ['fetchEmployee', 'listEmployee', 'createEmployee'],
                    'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder', 'createOrder'],
                    'product' => ['createEmployee']
                ]
            ],
            'acctant' => [
                'allow' => [
                    'main' => ['fetchEmployee', 'listEmployee'],
                    'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder']
                ]
            ]
        ];
        $this->assertEquals($dueResult2, $realResult2);
    }

    public function testIsAllowed()
    {
        $acls = [
            'admin' => [
                'allow' => [
                    'main' => ['fetchEmployee', 'listEmployee', 'createEmployee'],
                    'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder', 'createOrder'],
                    'product' => ['createEmployee']
                ]
            ],
            'acctant' => [
                'allow' => [
                    'main' => ['fetchEmployee', 'listEmployee'],
                    'order' => ['listCsOrder', 'listCpOrder', 'fetchOrder']
                ]
            ]
        ];
        $aclProcess = new AclProcess($acls);

        $roles = ['admin'];
        $app = 'main';
        $resource = 'createEmployee';

        $this->assertFalse($aclProcess->isAllowed(['acctant'], 'order', 'createOrder'));
        $this->assertTrue($aclProcess->isAllowed($roles, $app, $resource));
    }
}
