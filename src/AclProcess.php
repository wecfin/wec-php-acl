<?php
namespace Wec\Acl;

class AclProcess
{
    private $acls = [];

    public function __construct($acls)
    {
        $companyIds = [];
        foreach ($acls as $acl) {
            $companyIds[$acl->companyId] = 1;
            $this->allow(
                $acl->roleId,
                $acl->appId,
                json_decode($acl->allow)
            );
            $this->forbid(
                $acl->roleId,
                $acl->appId,
                json_decode($acl->forbid)
            );
        }

        if (count($companyIds) > 1) {
            throw new \Exception('has more than one company');
        }
    }

    protected function preSetAcls($roleId, $appId): void
    {
        if (!isset($this->acls[$roleId])) {
            $this->acls[$roleId] = [];
        }
        if (!isset($this->acls[$roleId][$appId])) {
            $this->acls[$roleId][$appId] = [];
        }
        if (!isset($this->acls[$roleId][$appId]['allow'])) {
            $this->acls[$roleId][$appId]['allow'] = [];
        }
        if (!isset($this->acls[$roleId][$appId]['forbid'])) {
            $this->acls[$roleId][$appId]['forbid'] = [];
        }
    }

    public function allow(string $roleId, string $appId, array $resources): void
    {
        $this->preSetAcls($roleId, $appId);
        $this->acls[$roleId][$appId]['allow'] = array_merge(
            $this->acls[$roleId][$appId]['allow'],
            array_flip($resources)
        );
    }

    public function forbid(string $roleId, string $appId, array $resources): void
    {
        $this->preSetAcls($roleId, $appId);
        $this->acls[$roleId][$appId]['forbid'] = array_merge(
            $this->acls[$roleId][$appId]['forbid'],
            array_flip($resources)
        );
    }

    public function isAllowed(array $roleIds, string $appId, string $resource): bool
    {
        if ($this->isForbided($roleIds, $appId, $resource)) {
            return false;
        }

        foreach ($roleIds as $roleId) {
            if (!isset($this->acls[$roleId])) {
                continue;
            }
            if (!isset($this->acls[$roleId][$appId])) {
                continue;
            }
            if (!isset($this->acls[$roleId][$appId]['allow'])) {
                continue;
            }
            if (array_key_exists(
                $resource,
                $this->acls[$roleId][$appId]['allow']
            )) {
                return true;
            }
        }
        return false;
    }

    public function isForbided(array $roleIds, string $appId, string $resource): bool
    {
        foreach ($roleIds as $roleId) {
            if (!isset($this->acls[$roleId])) {
                continue;
            }
            if (!isset($this->acls[$roleId][$appId])) {
                continue;
            }
            if (!isset($this->acls[$roleId][$appId]['forbid'])) {
                continue;
            }
            if (array_key_exists(
                $resource,
                $this->acls[$roleId][$appId]['forbid']
            )) {
                return true;
            }
        }
        return false;
    }
}
