<?php



 interface Sabre_DAV_ILockable
{


    /**
     * Returns an array with locks currently on the node 
     * 
     * @return Sabre_DAV_Locks_LockInfo[] 
     */
    function getLocks();

    /**
     * Creates a new lock on the file.  
     * 
     * @param Sabre_DAV_Locks_LockInfo $lockInfo The lock information 
     * @return void
     */
    function lock(Sabre_DAV_Locks_LockInfo $lockInfo);

    /**
     * Unlocks a file 
     * 
     * @param Sabre_DAV_Locks_LockInfo $lockInfo The lock information 
     * @return void 
     */
    function unlock(Sabre_DAV_Locks_LockInfo $lockInfo);


}