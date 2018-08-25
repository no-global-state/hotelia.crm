<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Db\Filter\FilterInvoker;

final class Transaction extends AbstractCrmController
{
    /**
     * Renders the grid
     * 
     * @param bool $shared
     * @param array $params Extra filtering parameters
     * @return string
     */
    private function createGrid(bool $shared, array $params = []) : string
    {
        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Transactions');

        $route = $this->createUrl(sprintf('Site:Transaction@%s', !$shared ? 'indexAction' : 'listAction'), [null]);

        $service = $this->getModuleService('transactionService');

        $invoker = new FilterInvoker($this->request->getQuery(), $route);
        $data = $invoker->invoke($service, $this->getPerPageCount(), $params);

        return $this->view->render('helpers/transaction', array(
            'icon' => 'glyphicon glyphicon-credit-card',
            'shared' => $shared,
            'route' => $route,
            'query' => $this->request->getQuery(),
            'data' => $data,
            'paginator' => $service->getPaginator()
        ));
    }

    /**
     * Render all transactions
     * 
     * @return string
     */
    public function listAction() : string
    {
        return $this->createGrid(true);
    }

    /**
     * Renders the grid for current hotel ID
     * 
     * @return string
     */
    public function indexAction() : string
    {
        return $this->createGrid(false, [
            'hotel_id' => $this->getHotelId()
        ]);
    }
}
