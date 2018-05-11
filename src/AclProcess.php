<?php
namespace Wec\Acl;

class AclProcess
{
    protected $acls;

    public function __construct(array $acls = [])
    {
        $this->acls = $acls;
    }

    public function acl(): array
    {
        return $this->acls;
    }

    public function allow(string $role, array $resources): void
    {
        $acls = $this->acl();

        if (!isset($acls[$role])) {
            $this->acls[$role] = $resources;
            return;
        }

        $oldResources = $acls[$role];
        $newResources =  array_keys(array_flip(array_merge($oldResources, $resources)));
        $this->acls[$role] = $newResources;
    }

    // todo forbid
    public function forbid(string $role, array $resources): void
    {
        $acls = $this->acl();

        if (!isset($acls[$role])) {
            return;
        }

        $this->acls[$role] = array_diff($acls[$role], $resources);
    }

    public function isAllowed(array $roles, string $resource): bool
    {
        $acls = $this->acl();

        foreach ($roles as $role) {
            if (!isset($acls[$role])) {
                continue;
            }
            if (in_array($resource, $acls[$role])) {
                return true;
            }
        }

        return false;
    }
}
