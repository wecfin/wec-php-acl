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
        if (!isset($this->acls[$role])) {
            $this->acls[$role]['allow'] = $resources;
            return;
        }

        foreach ($resources as $app => $resourceArr) {
            if (!isset($this->acls[$role]['allow'][$app])) {
                $this->acls[$role]['allow'][$app] = $resourceArr;
                continue;
            }

            $hadResourceArr = $this->acls[$role]['allow'][$app];
            $mergedResourceArr = array_merge($hadResourceArr, $resourceArr);
            $uniqueResourceArr = array_keys(array_flip($mergedResourceArr));
            $this->acls[$role]['allow'][$app] = $uniqueResourceArr;
        }
    }

    // todo forbid like allow

    public function isAllowed(array $roles, string $app, string $resource): bool
    {
        $mergedAcls = $this->mergeAclsByRolesAndApp($this->acl(), $roles, $app);

        return in_array($resource, $mergedAcls);
    }

    public function mergeAclsByRolesAndApp(array $acls, array $roles, string $app): array
    {
        $mergedAcls = [];

        foreach ($roles as $role) {
            if (!isset($acls[$role])) {
                continue;
            }
            if (!isset($acls[$role]['allow'][$app])) {
                continue;
            }
            $mergedAcls = array_merge($acls[$role]['allow'][$app], $mergedAcls);
        }

        return array_keys(array_flip($mergedAcls));
    }
}
