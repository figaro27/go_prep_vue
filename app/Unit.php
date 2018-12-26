<?php
namespace App;

use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class Unit
{
    public static function getType($unit)
    {
        try {
            $w = new Mass(1, $unit);
            $w->toNativeUnit();
            $type = 'mass';
        } catch (\Exception $e) {
            try {
                $v = new Volume(1, $unit);
                $v->toNativeUnit();
                $type = 'volume';
            } catch (\Exception $e) {
                $type= 'unit';
            }
        }

        return $type;
    }

    public static function isSameType($unitA, $unitB)
    {
      return self::getType($unitA) === self::getType($unitB);
    }
}
