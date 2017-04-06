<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 05/04/2017
 * Time: 19:00
 */

namespace Marta\Silex\src\model\services;


class Calculator {

    public function add(int $firstNumber, int $secondNumber) {
        return $firstNumber + $secondNumber;
    }

}