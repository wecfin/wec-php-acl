<?php
namespace Wec\Acl;

use Wec\Acl\Repo\AclRepo;
use Wec\Acl\Dto\AclDto;
use Gap\Db\Collection;

class AclServer
{
    private $cnn;
    private $cache;
    private $repo;

    public function __construct(array $opts)
    {
        $this->cnn = $opts['cnn'] ?? null;
        $this->cache = $opts['cache'] ?? null;

        if (!$this->cnn) {
            throw new \Exception('cnn cannot be null');
        }
    }

    public function isAllowed(string $companyId, array $roleIds, string $appId, string $resource): bool
    {
        $acls = $this->listByCompanyId($companyId);
        $aclProcess = new AclProcess($acls);
        return $aclProcess->isAllowed($roleIds, $appId, $resource);
    }

    public function listByCompanyId(string $companyId): array
    {
        if ($cachedAcls = $this->getCachedAcls($companyId)) {
            return $cachedAcls;
        }

        $aclList = $this->getRepo()
            ->listByCompany($companyId)
            ->limit(10000);
        $acls = [];
        foreach ($aclList as $acl) {
            $acls[] = $acl;
        }
        $this->cacheAcls($companyId, $acls);
        return $acls;
    }

    public function list(array $opts): Collection
    {
        return $this->getRepo()->list($opts);
    }

    public function create(AclDto $acl): void
    {
        $this->getRepo()->create($acl);
        $this->deleteCachedAcls($acl->companyId);
    }

    public function delete(array $opts): void
    {
        if (!$companyId = $opts['companyId'] ?? '') {
            throw new \Exception('companyId cannot be empty');
        }
        $this->getRepo()->delete($opts);
        $this->deleteCachedAcls($companyId);
    }

    public function fetch(array $opts): ?AclDto
    {
        return $this->getRepo()->fetch($opts);
    }


    private function getRepo(): AclRepo
    {
        if ($this->repo) {
            return $this->repo;
        }

        $this->repo = new AclRepo($this->cnn);
        return $this->repo;
    }

    private function getCacheKey(string $companyId): string
    {
        return 'acls-' . $companyId;
    }

    private function cacheAcls(string $companyId, array $acls): void
    {
        if (!$this->cache) {
            return;
        }
        $cacheKey = $this->getCacheKey($companyId);
        $this->cache->set($cacheKey, $acls);
    }

    private function getCachedAcls(string $companyId): ?array
    {
        if (!$this->cache) {
            return null;
        }
        $cacheKey = $this->getCacheKey($companyId);
        return $this->cache->get($cacheKey);
    }

    private function deleteCachedAcls(string $companyId): void
    {
        if (!$this->cache) {
            return;
        }
        $cacheKey = $this->getCacheKey($companyId);
        $this->cache->delete($cacheKey);
    }
}
