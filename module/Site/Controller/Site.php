<?php

namespace Site\Controller;

use Site\Service\PhotoService;

final class Site extends AbstractSiteController
{
    /**
     * Renders payment page
     * 
     * @return string
     */
    public function paymentAction()
    {
        return $this->view->render('payment', [
            
        ]);
    }

    /**
     * Renders booking page
     * 
     * @param int $hotelId Hotel id
     * @return string
     */
    public function bookAction($hotelId)
    {
        $room = $this->getModuleService('architectureService')->getById(29);

        return $this->view->render('book', [
            'hotelId' => $hotelId,
            'room' => $room
        ]);
    }

    /**
     * Search action
     * 
     * @return string
     */
    public function searchAction()
    {
        $hotelMapper = $this->createMapper('\Site\Storage\MySQL\HotelMapper');

        return $this->view->render('search', [
            'hotels' => $hotelMapper->fetchAll($this->getCurrentLangId()),
            'facilities' => $this->getModuleService('facilitiyService')->getCollection(false, null)
        ]);
    }

    /**
     * Renders hotel information
     * 
     * @param string $id Hotel id
     * @return string
     */
    public function hotelAction($id)
    {
        $hotelMapper = $this->createMapper('\Site\Storage\MySQL\HotelMapper');
        $hotel = $hotelMapper->findByPk($id);

        $photoService = $this->getModuleService('photoService');

        return $this->view->render('hotel', [
            'hotel' => $hotel,
            'id' => $id,
            'facilities' => $this->getModuleService('facilitiyService')->getCollection(false, $this->getHotelId()),
            // Hotel images
            'images' => [
                'large' => $photoService->fetchAll($this->getHotelId(), PhotoService::PARAM_IMAGE_SIZE_LARGE),
                'small' => $photoService->fetchAll($this->getHotelId(), PhotoService::PARAM_IMAGE_SIZE_SMALL)
            ],

            'rooms' => $this->getModuleService('architectureService')->findAvailableRooms($this->getHotelId())
        ]);
    }

    /**
     * Renders home page
     * 
     * @return string
     */
    public function homeAction()
    {
        return $this->view->render('home', [
            'home' => true,
            'hotels' => $this->createMapper('\Site\Storage\MySQL\HotelMapper')->fetchAll($this->getCurrentLangId())
        ]);
    }

    /**
     * Renders a CAPTCHA
     * 
     * @return void
     */
    public function captchaAction()
    {
        $this->captcha->render();
    }

    /**
     * This action gets executed when a request to non-existing route has been made
     * 
     * @return string
     */
    public function notFoundAction()
    {
        return $this->view->render('404');
    }
}
