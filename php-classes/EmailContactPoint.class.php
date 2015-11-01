<?php



 class EmailContactPoint extends ContactPoint
 {
     public function save($deep = true)
     {
         parent::save($deep);

         if (!$this->Person->PrimaryEmail) {
             $this->Person->PrimaryEmailID = $this->ID;
             $this->Person->save(false);
         }
     }

     public function destroy()
     {
         if ($this->Person->PrimaryEmailID == $this->ID) {
             $newDefault = static::getByWhere(array(
                'Class' => 'EmailContactPoint'
                ,'PersonID' => $this->PersonID
                ,'ID != '.$this->ID
            ), array(
                'order' => 'ID DESC'
            ));

             $this->Person->PrimaryEmailID = $newDefault ? $newDefault->ID : null;
             $this->Person->save(false);
         }

         return parent::destroy();
     }
 }