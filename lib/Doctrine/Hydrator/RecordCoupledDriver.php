<?php
class Doctrine_Hydrator_RecordCoupledDriver extends Doctrine_Hydrator_RecordDriver
{
  protected $_looseObjects = array();

  public function hydrateResultSet($stmt)
  {
    $this->rootAlias = key($this->_queryComponents);
    $results = parent::hydrateResultSet($stmt);
    $this->_collectObjects();
    return $results;
  }

  protected function _gatherRowData(&$data, &$cache, &$id, &$nonemptyComponents)
  {
    $rowData = parent::_gatherRowData($data, $cache, $id, $nonemptyComponents);
    $this->_looseObjects[$rowData[$this->rootAlias]['obj_type']][$rowData[$this->rootAlias]['obj_id']] = $rowData[$this->rootAlias]['obj_id'];
    $rowData[$this->rootAlias]['Object'] = &$this->_looseObjects[$rowData[$this->rootAlias]['obj_type']][$rowData[$this->rootAlias]['obj_id']];
    return $rowData;
  }

  protected function _collectObjects()
  {
    foreach($this->_looseObjects as $type => $ids)
    {
      $objects = Doctrine_Core::getTable($type)->createQuery('o')->whereIn('o.id', $ids)->execute(array(), Doctrine_Core::HYDRATE_RECORD);
      foreach($objects as $object)
      {
        $this->_looseObjects[$type][$object->id] = $object;
      }
    }
  }
}
