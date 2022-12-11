<?php
use Shared\Utils;
use Shared\Services\Db;
use Framework\{Registry, TimeZone, ArrayMethods};
use ResqueJob\Task as ResqueJobTask;
use Cloudstuff\ApiUtil\Api;

class Integrate extends Admin {
	use Controllers\Traits\Common;

	
	protected function setBreadcrumbs($seo) {
		parent::setBreadcrumbs($seo);
		$breadcrumbs = $this->getBreadcrumbs();
		$router = Registry::get('router');
		$controller = Registry::get('controller');

		$key = sprintf('%s.%s', strtolower(get_class($controller)), $router->action);
		if (isset($breadcrumbs['pages'][$key])) {
			return;
		}
		$brc = $breadcrumbs['pages']['integrate.*'];
		$this->layoutView->set("breadcrumbs", $brc['breadcrumbs'])->set('_navLinks', $brc['nav_links'] ?? []);
	}

}
