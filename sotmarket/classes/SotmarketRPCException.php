<?php
/**
 * TODO Это зависимость от lib/common.
 */
class SotmarketRPCException extends SotmarketException implements Serializable {
  // class code
  const RPCTaskAdded = 'd333';

  public function serialize()
  {
    return serialize(array($this->errors, $this->warnings, $this->code, $this->message));
  }

  public function unserialize($serialized)
  {
    list($this->errors, $this->warnings, $this->code, $this->message) = unserialize($serialized);
  }
}
?>