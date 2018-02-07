<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Db\Filter\FilterInvoker;

final class Transaction extends AbstractCrmController
{
    /**
     * Renders the grid
     * 
     * @return string
     */
    public function indexAction()
    {
        // Append a breadcrumb
        $this->view->getBreadcrumbBag()
                   ->addOne('Transactions');

        $route = $this->createUrl('Site:Transaction@indexAction', [null]);

        $mapper = $this->createMapper('\Site\Storage\MySQL\TransactionMapper');

        $invoker = new FilterInvoker($this->request->getQuery(), $route);
        $data = $invoker->invoke($mapper, $this->getPerPageCount(), array(
            'hotel_id' => $this->getHotelId()
        ));

        return $this->view->render('helpers/transaction', array(
            'icon' => 'glyphicon glyphicon-credit-card',
            'route' => $route,
            'query' => $this->request->getQuery(),
            'data' => $data,
            'paginator' => $mapper->getPaginator()
        ));
    }
}
