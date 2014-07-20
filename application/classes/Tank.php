<?php
require_once dirname(dirname(__FILE__)) . "/classes/Gun.php";
require_once dirname(dirname(__FILE__)) . "/classes/Radio.php";
require_once dirname(dirname(__FILE__)) . "/classes/Turret.php";
require_once dirname(dirname(__FILE__)) . "/classes/Engine.php";
require_once dirname(dirname(__FILE__)) . "/classes/Suspension.php";
class Tank
{
    private $id;
    private $class;
    private $nation;
    private $name;
    private $tier;
    private $price;
    private $hp_stock;
    private $hp_elite;
    private $crew;
    private $speed_limit;
    private $pivot;
    private $armor_front;
    private $armor_side;
    private $armor_rear;
    private $gun_traverse_speed = 0;
    private $gun_arc_left;
    private $gun_arc_right = 0;
    private $view_range;
    private $chassis_weight = 0;
    private $stock_weight;
    private $premium = 0;

    private $gun_names = array();
    private $guns = array();
    private $turrets = array();
    private $suspensions = array();
    private $engines = array();
    private $radios = array();

    public function __construct($properties, $db)
    {
        $this->db = $db;
        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
        if (!$this->chassis_weight) {
            $this->chassis_weight = $this->stock_weight * 1000;
        }
    }

    public function __toString()
    {
        return $this->name.' '.$this->tier;
    }

    public function generateSqlAndInsert()
    {
        /* Write in such a way that it can be run multiple times,
         * each time merely overwriting/updating existing values
         */

        //echo '<br><br>Final weight: '.$this->chassis_weight;

        $sql = $this->toSql();

        $this->id = queryInsert($sql);

        $gun_names = array();
        foreach ($this->guns as $gun){
            $sql = $gun->toSql($this->id);
            queryInsert($sql);
        }

        foreach ($this->turrets as $turret){
            $sql = $turret->toSql($this->id);
            queryInsert($sql);
        }

        foreach ($this->suspensions as $suspension){
            $sql = $suspension->toSql($this->id);
            queryInsert($sql);
        }

        foreach ($this->engines as $engine){
            $sql = $engine->toSql($this->id);
            queryInsert($sql);
        }

        foreach ($this->radios as $radio){
            $sql = $radio->toSql($this->id);
            queryInsert($sql);
        }

    }

    public function toSql()
    {
        $sql = "
            INSERT INTO tanks
                (class, nation, name, tier, price,
                hp_stock, hp_elite, crew, speed_limit, pivot,
                armor_front, armor_side, armor_rear, gun_traverse_speed,
                gun_arc_left, gun_arc_right, view_range, chassis_weight)
            VALUES
                ('{$this->class}', '{$this->nation}', '{$this->name}', {$this->tier}, {$this->price},
                {$this->hp_stock}, {$this->hp_elite}, '{$this->crew}', {$this->speed_limit}, {$this->pivot},
                {$this->armor_front}, {$this->armor_side}, {$this->armor_rear}, {$this->gun_traverse_speed},
                {$this->gun_arc_left}, {$this->gun_arc_right}, {$this->view_range}, {$this->chassis_weight})
            ON DUPLICATE KEY UPDATE
                tier = {$this->tier},
                price = {$this->price},
                hp_stock = {$this->hp_stock},
                hp_elite = {$this->hp_elite},
                crew = '{$this->crew}',
                speed_limit = {$this->speed_limit},
                pivot = {$this->pivot},
                armor_front = {$this->armor_front},
                armor_side = {$this->armor_side},
                armor_rear = {$this->armor_rear},
                gun_traverse_speed = {$this->gun_traverse_speed},
                gun_arc_left = {$this->gun_arc_left},
                gun_arc_right = {$this->gun_arc_right},
                view_range = {$this->view_range},
                chassis_weight = {$this->chassis_weight},
                id = LAST_INSERT_ID(id)
        ";
        return $sql;
    }

    public function getJsonData(){
        unset($this->gun_names);
        $vars = get_object_vars($this);
        foreach($vars as &$var){
            if(is_array($var)){
                foreach ($var as &$array_var){
                    if(is_object($array_var) && method_exists($array_var,'getJsonData')){
                        $array_var = $array_var->getJsonData();
                    }
                }
            }
        }
        return $vars;
     }

    public function toJson()
    {
        return json_encode($this);
    }

    public function addGun($gun)
    {
        if(in_array($gun->getName(), $this->gun_names)){
            $gun->setElite(1);
        } else {
            $this->gun_names[] = $gun->getName();
        }
        if($gun->getXpCost() == 0 && $gun->getElite() == 0){
            //echo 'subtracting gun '.$gun->getWeight().' from '.$this->chassis_weight.'<br>';
            $this->chassis_weight -= $gun->getWeight();
        }
        $this->guns[] = $gun;
    }

    public function addTurret($turret)
    {
        if($turret->getXpCost() == 0){
            //echo 'subtracting turret '.$turret->getWeight().' from '.$this->chassis_weight.'<br>';
            $this->chassis_weight -= $turret->getWeight();
        }
        $this->turrets[] = $turret;
    }

    public function addSuspension($suspension)
    {
        if($suspension->getXpCost() == 0){
            //echo 'subtracting suspen '.$suspension->getWeight().' from '.$this->chassis_weight.'<br>';
            $this->chassis_weight -= $suspension->getWeight();
        }
        $this->suspensions[] = $suspension;
    }

    public function addEngine($engine)
    {
        if($engine->getXpCost() == 0){
            //echo 'subtracting engine '.$engine->getWeight().' from '.$this->chassis_weight.'<br>';
            $this->chassis_weight -= $engine->getWeight();
        }
        $this->engines[] = $engine;
    }

    public function addRadio($radio)
    {
        if($radio->getXpCost() == 0){
            //echo 'subtracting radio '.$radio->getWeight().' from '.$this->chassis_weight.'<br>';
            $this->chassis_weight -= $radio->getWeight();
        }
        $this->radios[] = $radio;
    }

    public function getGuns()
    {

        return $this->guns;
    }

    public function getTurrets()
    {
        return $this->turrets;
    }

    public function getSuspensions()
    {
        return $this->suspensions;
    }

    public function getEngines()
    {
        return $this->engines;
    }

    public function getRadios()
    {
        return $this->radios;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getNation()
    {
        return $this->nation;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTier()
    {
        return $this->tier;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getHpStock()
    {
        return $this->hp_stock;
    }

    public function getHpElite()
    {
        return $this->hp_elite;
    }

    public function getCrew()
    {
        return $this->crew;
    }

    public function getSpeedLimit()
    {
        return $this->speed_limit;
    }

    public function getPivot()
    {
        return $this->pivot;
    }

    public function getArmorFront()
    {
        return $this->armor_front;
    }

    public function getArmorSide()
    {
        return $this->armor_side;
    }

    public function getArmorRear()
    {
        return $this->armor_rear;
    }

    public function getGunTraverseSpeed()
    {
        return $this->gun_traverse_speed;
    }

    public function getGunArcLeft()
    {
        return $this->gun_arc_left;
    }

    public function getGunArcRight()
    {
        return $this->gun_arc_right;
    }

    public function getViewRange()
    {
        return $this->view_range;
    }

    public function getChassisWeight()
    {
        return $this->chassis_weight;
    }

    public function setChassisWeight($chassis_weight)
    {
        $this->chassis_weight = $chassis_weight;
    }

    public function loadModules()
    {
        $this->loadGuns();
        die('yay?');
        $this->loadEngines();
        $this->loadTurrets();
        $this->loadSuspensions();
        $this->loadRadios();
    }

    public function loadGuns()
    {
        $sql = "
            SELECT *
            FROM guns
            WHERE tank_id = {$this->id}
            ORDER BY tier, xp_cost
        ";

        // $guns = queryAll($sql);
        $query = $this->db->prepare($sql);
        $query->execute();
        $guns = $query->fetchAll();
        // var_dump($guns); die();

        foreach($guns as $gun){
            $gun = new Gun($gun);
            $this->addGun($gun);
        }
    }

    public function loadEngines()
    {
        $sql = "
            SELECT *
            FROM engines
            WHERE tank_id = {$this->id}
            ORDER BY tier, xp_cost
        ";

        $engines = queryAll($sql);

        foreach($engines as $engine){
            $engine = new Engine($engine);
            $this->addEngine($engine);
        }
    }

    public function loadTurrets()
    {
        $sql = "
            SELECT *
            FROM turrets
            WHERE tank_id = {$this->id}
            ORDER BY tier, xp_cost
        ";

        $turrets = queryAll($sql);

        foreach($turrets as $turret){
            $turret = new Turret($turret);
            $this->addTurret($turret);
        }
    }

    public function loadSuspensions()
    {
        $sql = "
            SELECT *
            FROM suspensions
            WHERE tank_id = {$this->id}
            ORDER BY tier, xp_cost
        ";

        $suspensions = queryAll($sql);

        foreach($suspensions as $suspension){
            $suspension = new Suspension($suspension);
            $this->addSuspension($suspension);
        }
    }

    public function loadRadios()
    {
        $sql = "
            SELECT *
            FROM radios
            WHERE tank_id = {$this->id}
            ORDER BY tier, xp_cost
        ";

        $radios = queryAll($sql);

        foreach($radios as $radio){
            $radio = new Radio($radio);
            $this->addRadio($radio);
        }
    }

    public function __destruct()
    {
        foreach ($this->guns as &$gun){
            $gun = null;
        }

        foreach ($this->turrets as &$turret){
            $turret = null;
        }

        foreach ($this->suspensions as &$suspension){
            $suspension = null;
        }

        foreach ($this->engines as &$engine){
            $engine = null;
        }

        foreach ($this->radios as &$radio){
            $radio = null;
        }
    }
}
