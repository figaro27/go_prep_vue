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
                $type = 'unit';
            }
        }

        return $type;
    }

    public static function isSameType($unitA, $unitB)
    {
        return self::getType($unitA) === self::getType($unitB);
    }

    public static function convert($value, $unitA, $unitB)
    {
        if (!self::isSameType($unitA, $unitB)) {
            return $value;
        }

        switch (self::getType($unitA)) {
            case 'mass':
                $value = new Mass($value, $unitA);
                break;
            case 'volume':
                $value = new Volume($value, $unitA);
                break;
            default:
                return $value;
        }

        return $value->toUnit($unitB);
    }

    public static function base($unitType)
    {
        switch ($unitType) {
            case "mass":
                return "g";
            case "volume":
                return "ml";
            case "unit":
                return "unit";
            default:
                throw new \Exception("Unrecognized unit type");
        }
    }
}
