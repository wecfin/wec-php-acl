<?php
namespace Wec\Acl;

use Wec\Acl\AclServer;
use Gap\Base\Exception\NoPermissionException;

class AclFilter extends \Gap\Base\RouteFilter\RouteFilterBase
{
    private $aclServer;
    private $cnnName = 'default';
    private $cacheName = 'default';

    public function filter(): void
    {
        $route = $this->getRoute();
        if ($route->getAccess() !== 'acl') {
            return;
        }

        $request = $this->getRequest();
        $accessToken = $request->attributes->get('accessToken');
        if (!$info = json_decode($accessToken->info)) {
            throw new \Exception('cannot find info from accessToken');
        }
        if (!$companyId = $info->companyId) {
            throw new \Exception('cannot find companyId from accessToken');
        }
        $roleIds = $info->roleIds;
        $openAppId = $this->getApp()->getConfig()
            ->config('open')->str('appId');

        if (!$this->getAclServer()->isAllowed($companyId, $roleIds, $openAppId, $route->name)) {
            throw new NoPermissionException('you have no access to ' . $route->name);
        }
    }

    private function getAclServer(): AclServer
    {
        if ($this->aclServer) {
            return $this->aclServer;
        }
        $app = $this->getApp();
        $this->aclServer = new AclServer([
            'cnn' => $app->getDmg()->connect($this->cnnName),
            'cache' => $app->getCmg()->connect($this->cacheName)
        ]);
        return $this->aclServer;
    }
}
