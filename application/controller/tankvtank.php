<?php

/**
 * Class Songs
 * This is a demo class.
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */
class TankVTank extends Controller
{
    /**
     * PAGE: index
     * This method handles what happens when you move to http://yourproject/songs/index
     */
    public function index()
    {
        $this->viewIs('tankvtank/index');
    }

    public function loadTank($tank_id)
    {
        if (!empty($tank_id)) {
            // load model, perform an action on the model
            $tanks_model = $this->loadModel('TanksModel');
            $tank = $tanks_model->loadTank($tank_id);
            echo json_encode($tank->getJsonData());
        }

        // header('location: ' . URL . 'tankvtank/index');
    }

    public function getList($nation, $class, $tier)
    {
        $tanks_model = $this->loadModel('TanksModel');
        $list = $tanks_model->getTankOptions($nation, $class, $tier);
        echo json_encode($list);
    }
}
