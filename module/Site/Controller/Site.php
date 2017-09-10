<?php

namespace Site\Controller;

final class Site extends AbstractSiteController
{
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
     * Shows a home page
     * 
     * @return string
     */
    public function indexAction()
    {
        $room = $this->createMapper('\Site\Storage\MySQL\RoomMapper')->fetchStatistic($this->getHotelId());
        $floorCount = $this->createMapper('\Site\Storage\MySQL\FloorMapper')->getFloorCount($this->getHotelId());

        // Statistic
        $stat = array(
            'Total room count' => $room['rooms_count'],
            'Total floors count' => $floorCount,
            'Taken rooms count' => $room['rooms_taken'],
            'Free rooms count' => $room['rooms_free'],
            'Rooms freeing today' => $room['rooms_leaving_today']
        );

        return $this->view->render('home', array(
            'stat' => $stat
        ));
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
