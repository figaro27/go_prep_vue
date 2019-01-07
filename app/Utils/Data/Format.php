<?php
namespace App\Utils\Data;

class Format {
  public static function baseUnit($unitType) {
    switch($unitType) {
      case 'mass':
        return 'g';
      case 'volume':
        return 'ml';
      case 'unit':
        return 'unit';
      default:
        return 'unit';
        throw new Exception('Unrecognized unit type');
    }
  }
}