<?php

namespace Site\Controller;

use Krystal\Db\Filter\FilterInvoker;

final class Property extends AbstractCrmController
{
    /**
     * Render all properties
     * 
     * @return string
     */
    public function indexAction() : string
    {
        $service = $this->getModuleService('hotelService');

        $route = $this->createUrl('Site:Property@indexAction', [null]);
        $query = $this->request->getQuery();

        $invoker = new FilterInvoker($query, $route);

        $data = $invoker->invoke($service, 20, [
            'lang_id' => $this->getCurrentLangId()
        ]);

        return $this->view->render('property/index', [
            'data' => $data,
            'query' => $query,
            'route' => $route,
            'paginator' => $service->getPaginator()
        ]);
    }
}
