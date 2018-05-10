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

        if (!isset($acls[$role]) || !isset($acls[$role]['allow'])) {
            $this->acls[$role]['allow'] = $resources;
            return;
        }

        $oldResources = $acls[$role]['allow'];
        $newResources =  array_keys(array_flip(array_merge($oldResources, $resources)));
        $this->acls[$role]['allow'] = $newResources;
    }

    // todo forbid

    public function isAllowed(array $roles, string $resource): bool
    {
        $acls = $this->acl();

        foreach ($roles as $role) {
            if (!isset($acls[$role]) || !isset($acls[$role]['allow'])) {
                continue;
            }
            if (in_array($resource, $acls[$role]['allow'])) {
                return true;
            }
        }

        return false;
    }
}
