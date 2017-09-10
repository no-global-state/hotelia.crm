<?php

namespace Site\Controller;

use Krystal\Db\Filter\InputDecorator;
use Krystal\Db\Filter\FilterInvoker;

final class Transaction extends AbstractSiteController
{
    /**
     * Renders the grid
     * 
     * @param array $query
     * @return string
     */
    public function indexAction()
    {
        $route = '/transaction/index/';

        $mapper = $this->createMapper('\Site\Storage\MySQL\TransactionMapper');

        $invoker = new FilterInvoker($this->request->getQuery(), $route);
        $data = $invoker->invoke($mapper, $this->getPerPageCount(), array(
            'hotel_id' => $this->getHotelId()
        ));

        return $this->view->render('transaction/index', array(
            'route' => $route,
            'query' => $this->request->getQuery(),
            'data' => $data,
            'paginator' => $mapper->getPaginator()
        ));
    }
}
