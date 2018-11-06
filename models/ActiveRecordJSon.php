<?php

namespace app\models;

use Yii;

class ActiveRecordJSon extends \yii\db\ActiveRecord
{

    private $_jsdata;
    private $_jsdata_loaded = false;

    private function decodeJSData() {
      if (!$this->_jsdata_loaded) {
        $this->_jsdata = json_decode($this->data, true);
        $this->_jsdata_loaded = true;
      }
    }

    public function getJSData($key) {

      $this->decodeJSData();

      if (isset($this->_jsdata[$key]))
        return $this->_jsdata[$key];
      else
        return null;

    }

    public function setJSData($key, $value) {

      $this->decodeJSData();

      $this->_jsdata[$key] = $value;

    }

    public function unsetJSData($key) {

      $this->decodeJSData();
      if (isset($this->_jsdata[$key])) {
        unset($this->_jsdata[$key]);
      }
      //print_r($this->_jsdata);

    }

    public function saveJSData() {

      $data = json_encode($this->_jsdata);

      if ($this->data != $data) {
        $this->data = $data;
        $this->save();
        return true;
      }

      return false;

    }

}
