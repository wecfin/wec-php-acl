<?php
namespace Wec\Acl\Repo;

use Gap\Dto\DateTime;
use Gap\Db\Collection;
use Wec\Acl\Dto\AclDto;
use Gap\Db\Contract\CnnInterface;

class AclRepo
{
    private $cnn;

    public function __construct(CnnInterface $cnn)
    {
        $this->cnn = $cnn;
    }

    public function fetch(array $opts): ?AclDto
    {
        if (!$companyId = $opts['companyId'] ?? '') {
            throw new \Exception('companyId cannot be empty');
        }
        if (!$roleId = $opts['roleId'] ?? '') {
            throw new \Exception('roleId cannot be empty');
        }
        if (!$appId = $opts['appId'] ?? '') {
            throw new \Exception('appId cannot be empty');
        }

        $ssb = $this->buildSsb();
        $ssb->where()
                ->expect('ca.companyId')->equal()->str($companyId)
                ->andExpect('ca.roleId')->equal()->str($roleId)
                ->andExpect('ca.appId')->equal()->str($appId);

        return $ssb->fetch(AclDto::class);
    }

    public function create(AclDto $acl): void
    {
        if (!$companyId = $acl->companyId) {
            throw new \Exception('companyId cannot be empty');
        }
        if (!$roleId = $acl->roleId) {
            throw new \Exception('roleId cannot be empty');
        }
        if (!$appId = $acl->appId) {
            throw new \Exception('appId cannot be empty');
        }

        $now = new DateTime();
        $acl->created = $now;
        $acl->changed = $now;

        $this->cnn->trans()->begin();
        try {
            $this->deleteIfExisted($companyId, $roleId, $appId);

            $this->cnn->isb()
                ->insert('company_acl')
                ->field(
                    'companyId',
                    'roleId',
                    'appId',
                    'allow',
                    'forbid',
                    'created',
                    'changed'
                )
                ->value()
                    ->addStr($companyId)
                    ->addStr($roleId)
                    ->addStr($appId)
                    ->addStr($acl->allow)
                    ->addStr($acl->forbid)
                    ->addDateTime($acl->created)
                    ->addDateTime($acl->changed)
                ->end()
                ->execute();
        } catch (\Exception $e) {
            $this->cnn->trans()->rollback();
            throw $e;
        }
        $this->cnn->trans()->commit();
    }

    public function listByCompany(string $companyId): Collection
    {
        $ssb = $this->buildSsb();
        $where = $ssb->where();
        $where->expect('ca.companyId')->equal()->str($companyId);

        $ssb->descOrderBy('ca.changed');

        return $ssb->list(AclDto::class);
    }

    public function list(array $opts): Collection
    {
        $ssb = $this->buildSsb();

        if (!$companyId = $opts['companyId'] ?? '') {
            throw new \Exception('companyId cannot be empty');
        }
        $appId = $opts['appId'] ?? '';

        $where = $ssb->where();
        $where->expect('ca.companyId')->equal()->str($companyId);

        if ($appId) {
            $where->andExpect('ca.appId')->equal()->str($appId);
        }

        $roleIds = $opts['roleIds'] ?? [];
        if ($roleId = $opts['roleId'] ?? '') {
            $roleIds[] = $roleId;
        }

        if (!empty($roleIds)) {
            $where->andExpect('ca.roleId')->withIn()->strArr($roleIds);
        }

        $ssb->descOrderBy('ca.changed');

        return $ssb->list(AclDto::class);
    }

    public function delete(array $opts): void
    {
        if (!$companyId = $opts['companyId'] ?? '') {
            throw new \Exception('companyId cannot be empty');
        }
        if (!$roleId = $opts['roleId'] ?? '') {
            throw new \Exception('roleId cannot be empty');
        }
        if (!$appId = $opts['appId'] ?? '') {
            throw new \Exception('appId cannot be empty');
        }

        $this->cnn->dsb()
            ->delete()
            ->from('company_acl')
            ->end()
            ->where()
                ->expect('companyId')->equal()->str($companyId)
                ->andExpect('roleId')->equal()->str($roleId)
                ->andExpect('appId')->equal()->str($appId)
            ->end()
            ->execute();
    }

    private function buildSsb()
    {
        $ssb = $this->cnn->ssb()
            ->select(
                'ca.companyId',
                'ca.allow',
                'ca.forbid',
                'ca.created',
                'ca.changed',
                'ca.appId',
                'ca.roleId'
            )
            ->from('company_acl ca')
            ->end();
        return $ssb;
    }

    private function deleteIfExisted($companyId, $roleId, $appId): void
    {
        $opts = [
                'companyId' => $companyId,
                'roleId' => $roleId,
                'appId' => $appId
            ];
        $existed = $this->fetch($opts);
        if ($existed) {
            $this->delete($opts);
        }
    }
}
