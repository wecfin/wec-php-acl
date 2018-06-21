<?php
namespace Wec\Acl\Dto;

class RoleDto extends DtoBase
{
    public $roleId;
    public $companyId;
    public $roleCode;
    public $roleName;
    public $desc;
    public $isSystem;
    public $isActive;
    public $created;
    public $changed;
}
