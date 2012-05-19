<?php



 class AddressContactPoint extends ContactPoint
{

	public function save($deep = true)
	{
		parent::save($deep);
		
		if(!$this->Person->PrimaryAddress)
		{
			$this->Person->PrimaryAddressID = $this->ID;
			$this->Person->save(false);
		}
	}

	public function toString()
	{
		$s = $this->Data['Address'];
	
		return $s;
	}
	
	public function destroy()
	{
		if($this->Person->PrimaryAddressID == $this->ID)
		{
			$newDefault = static::getByWhere(array(
				'Class' => 'AddressContactPoint'
				,'PersonID' => $this->PersonID
				,'ID != '.$this->ID
			), array(
				'order' => 'ID DESC'
			));

			$this->Person->PrimaryAddressID = $newDefault ? $newDefault->ID : null;
			$this->Person->save(false);
		}
		
		return parent::destroy();
	}

}