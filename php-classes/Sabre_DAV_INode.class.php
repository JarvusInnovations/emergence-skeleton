<?php



 interface Sabre_DAV_INode
{


    /**
     * Deleted the current node
     *
     * @return void 
     */
    function delete();

    /**
     * Returns the name of the node 
     * 
     * @return string 
     */
    function getName();

    /**
     * Renames the node
     *
     * @param string $name The new name
     * @return void
     */
    function setName($name);

    /**
     * Returns the last modification time, as a unix timestamp 
     * 
     * @return int 
     */
    function getLastModified();


}