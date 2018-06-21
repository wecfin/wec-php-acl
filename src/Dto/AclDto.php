<?php
namespace Wec\Acl\Dto;

use Gap\Open\Dto\AppDto;

class AclDto extends DtoBase
{
    public $companyId;
    public $role;
    public $app;
    public $allow;
    public $forbid;
    public $created;
    public $changed;

    public function init(): void
    {
        if (!$this->role) {
            $this->role = new RoleDto();
        }
        if (!$this->app) {
            $this->app = new AppDto();
        }
    }
}
