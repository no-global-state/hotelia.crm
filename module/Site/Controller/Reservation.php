<?php

namespace Site\Controller;

use Krystal\Iso\ISO3166\Country;
use Krystal\Validate\Pattern;
use Krystal\Stdlib\VirtualEntity;

class Reservation extends AbstractSiteController
{
	/**
	 * Creates a form
	 * 
	 * @param \Krystal\Stdlib\VirtualEntity $client
	 * @return string
	 */
	private function createForm($client)
	{
		$countries = new Country();
		$statuses = array(
			'r' => 'Regular',
			'v' => 'VIP'
		);

		$includes = array(
			'breakfast' => 'Breakfast',
			'dinner' => 'Dinner',
			'snack' => 'Afternoon snack'
		);

		return $this->view->render('reservation', array(
			'client' => $client,
			'countries' => $countries->getAll(),
			'statuses' => $statuses,
			'includes' => $includes,
			'genders' => array(
				'M' => 'Male',
				'F' => 'Female'
			)
		));
	}

	/**
	 * Default action
	 * 
	 * @return string
	 */
	public function indexAction()
	{
		if ($this->request->isPost()) {
			return $this->addAction();
		} else {
			$client = new VirtualEntity;
			return $this->createForm($client);
		}
	}

	/**
	 * Reservates a room
	 * 
	 * @return string
	 */
	public function addAction()
	{
		$data = $this->request->getPost();

		$formValidator = $this->createValidator(array(
			'input' => array(
				'source' => $data,
				'definition' => array(
					'first_name' => new Pattern\Name(),
					'last_name' => new Pattern\Name()
				)
			)
		));

		if ($formValidator->isValid()) {
			$mapper = $this->createMapper('\Site\Storage\MySQL\ReservationMapper');
			$mapper->persist($data);
			
            $this->flashBag->set('success', 'Your request has been sent!');
            return '1';
		} else {
			return $formValidator->getErrors();
		}
	}
}
