<?php



 interface Sabre_DAV_IExtendedCollection
{


    /**
     * Creates a new collection
     *
     * @param string $name 
     * @param array $resourceType
     * @param array $properties
     * @return void
     */
    function createExtendedCollection($name, array $resourceType, array $properties);


}