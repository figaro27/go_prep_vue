<?php

namespace App;

class Model extends \Illuminate\Database\Eloquent\Model
{
  public function setAttributeVisibility() {}

  public function toJson($options = 0) {
    $this->setAttributeVisibility();
    return parent::toJson();
  }

  public function toArray() {
    $this->setAttributeVisibility();
    return parent::toArray();
  }
}
