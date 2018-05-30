<?php

namespace Softy;

interface CRUD {
  public function find($select, $where);
  public function save($insert);
  public function update($params, $where);
  public function delete($where);
}
?>
