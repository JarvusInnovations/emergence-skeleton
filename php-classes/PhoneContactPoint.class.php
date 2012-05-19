<?php



 class PhoneContactPoint extends ContactPoint
{

	public function save($deep = true)
	{
		parent::save($deep);
		
		if(!$this->Person->PrimaryPhone)
		{
			$this->Person->PrimaryPhoneID = $this->ID;
			$this->Person->save(false);
		}
	}
	
	public function destroy()
	{
		if($this->Person->PrimaryPhoneID == $this->ID)
		{
			$newDefault = static::getByWhere(array(
				'Class' => 'PhoneContactPoint'
				,'PersonID' => $this->PersonID
				,'ID != '.$this->ID
			), array(
				'order' => 'ID DESC'
			));

			$this->Person->PrimaryPhoneID = $newDefault ? $newDefault->ID : null;
			$this->Person->save(false);
		}
		
		return parent::destroy();
	}

}