<?php
class EditorRequestHandler extends RequestHandler {
    
    static public $activitySessionThreshold = 3600;
    static public $activityPageSize = 100;
    
    public static function handleRequest() {
        
        static::$responseMode = 'json';
        
        switch($action ? $action : $action = static::shiftPath())
        {
            case 'viewSource': // Don't write verbs in the path, HTTP method is the verb!
            {
                return static::viewSourceRequest();
            }
            case 'getRevisions': // Don't write verbs in the path, HTTP method is the verb!
            {
                return static::handleRevisionsRequest();
            }
            case 'getCodeMap': // Don't write verbs in the path, HTTP method is the verb!
                return static::handleCodeMapRequest($_REQUEST['class']);
            case 'search':
                return static::searchRequest();
            case 'activity':
                return static::handleActivityRequest();
			case 'export':
				return static::handleExportRequest();
			case 'import':
				return static::handleImportRequest();

            default:
            {
                return static::respond('editor');
            }
        }
    } 
    
    public static function handleExportRequest()
    {
    		$tmp = EmergenceIO::export();
    		header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . $_SERVER["SERVER_NAME"] . '-export.zip');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($tmp));
			readfile($tmp);
    }
    
    public static function handleImportRequest()
    {
		if($_SERVER['REQUEST_METHOD'] == 'PUT') {
			$put = fopen("php://input", "r"); // open input stream
			
			$tmp = tempnam("/tmp", "emr");  // use PHP to make a temporary file
			$fp = fopen($tmp, "w"); // open write stream to temp file
			
			// write
			while ($data = fread($put, 1024)) {
			  fwrite($fp, $data);
			}
			
			// close handles
			fclose($fp);
			fclose($put);
			
			EmergenceIO::import($tmp);
		}
    }

    public static function viewSourceRequest()
    {
        $file = SiteFile::getByID($_POST['ID']);

        if(!$file) {
          return static::throwNotFoundError();
        }
        else {
          readfile($file->RealPath);
          exit();
        }
    }
    
	public static function searchRequest()
    {
        $files = DB::allRecords(
            'SELECT MAX(`ID`) as `RID`'
            .' FROM `_e_files`'
            //.' WHERE `Handle` LIKE \'%%.php\''
            .' GROUP BY  `Handle`,`CollectionID`'
        );
        
        $clc = sprintf('grep -nI "%s"',$_REQUEST['q']);
        
        foreach($files as $file)
        {
            $clc .= ' ' . $file['RID'];
        }
        
        //echo $clc . '<br>' . "\n";
        
        chdir(Site::$rootPath . '/data/');
        
        $results = shell_exec($clc);
        
        $results = explode("\n",$results);
        
        $output = array();
        
        foreach($results as $result)
        {
            $line = explode(':',$result,3);
            
            if(count($line) === 3)
            {
                $file = SiteFile::getByID($line[0]);
                
                if(is_object($file)) {
                    $fdata = $file->getData();  
                }
    
                $output[] = array(
                    'File'  =>  $fdata
                    ,'line' =>  $line[1]
                    ,'result' => $line[2]
                );
            }
        }
        
        header('Content-type: application/json');
        echo json_encode(array(
        	'success'=> true
        	,'data'	=>	$output
        ));
    }
    
    public static function handleRevisionsRequest() {
        $node = SiteFile::getByID($_REQUEST['ID']);
        if(!$node)
        {
            return static::throwNotFoundError();
        }
        else
        {
            $data = array();
            
            $Fields = array('ID','Class','Handle','Type','MIMEType','Size','SHA1','Status','Timestamp','AuthorID','AncestorID','CollectionID','FullPath');
            
            foreach($node->getRevisions() as $item)
            {
                $record = array();
                
                foreach($Fields as $Field) {
                    $record[$Field] = $item->$Field;
                    
                    if($Field == 'AuthorID')
                    {
                        $record['Author'] = $item->Author;
                    }
                    
                }
                $data['revisions'][] = $record;
            }
            
            return static::respond('revisions', $data);
        }
    }
    
    public static function handleCodeMapRequest($class=__CLASS__) {
        $Reflection = new ReflectionClass($class);
        
        $ReflectionMethods = $Reflection->getMethods();
        
        $methods = array();
        
        foreach($ReflectionMethods as $ReflectionMethod)
        {
            if($ReflectionMethod->class == $class)
            {
                $methods[] = array(
                    //'object' => $ReflectionMethod
                    'Method' => $ReflectionMethod->name
                    ,'Parameters' => $ReflectionMethod->getParameters()
                    ,'StartLine' => $ReflectionMethod->getStartLine()
                    ,'EndLine' => $ReflectionMethod->getEndLine()
                );
            }
        }
        
        $data = array(
            'Class' => $class
            ,'Parent' => $Reflection->getParentClass()
            ,'Methods' => $methods
        );
        
        return static::respond('codeMap', $data);
    }
    
    
    static public function handleActivityRequest()
    {
        if(static::peekPath() == 'all')
            static::$activityPageSize = false;
        
        $activity = array();
        $openFiles = array();
        $editResults = DB::query(
            'SELECT f.*'
            .' FROM _e_files f'
            .' JOIN _e_file_collections c ON(c.ID = f.CollectionID)'
            .' WHERE c.Site = "Local"'
            .' ORDER BY ID DESC'
        );
        
        $closeFile = function($path) use (&$activity, &$openFiles) {
            
            list($authorID, $collectionID, $handle) = explode('/', $path, 3);
            $Collection = SiteCollection::getByID($collectionID);
            
            $activity[] = array(
                'EventType' => 'save'
                ,'Author' => Person::getByID($authorID)->getData()
                ,'Collection' => $Collection->getData()
                ,'Handle' => $handle
                ,'CollectionPath' => $Collection->FullPath
                ,'Timestamp' => $openFiles[$path][count($openFiles[$path])-1]['Timestamp']
                ,'FirstTimestamp' => $openFiles[$path][0]['Timestamp']
                ,'RevisionID' => $openFiles[$path][count($openFiles[$path])-1]['ID']
                ,'FirstRevisionID' => $openFiles[$path][0]['ID']
                ,'FirstAncestorID' => $openFiles[$path][0]['AncestorID']
                ,'revisions' => $openFiles[$path]
            );
            
            unset($openFiles[$path]);
        };
        
        while((!static::$activityPageSize || (count($activity)+count($openFiles) < static::$activityPageSize)) && ($editRecord = $editResults->fetch_assoc()))
        {
            $editRecord['Timestamp'] = strtotime($editRecord['Timestamp']);
            $path = $editRecord['AuthorID'].'/'.$editRecord['CollectionID'].'/'.$editRecord['Handle'];
            

    		if($editRecord['Status'] == 'Deleted')
            {
            	if(array_key_exists($path, $openFiles))
	            	$closeFile($path);
                    
                $Author = Person::getByID($editRecord['AuthorID']);
                $Collection = SiteCollection::getByID($editRecord['CollectionID']);
                    
                $lastActivity = count($activity) ? $activity[count($activity)-1] : null;
                if($lastActivity && $lastActivity['EventType'] == 'delete' && $lastActivity['Author']['ID'] == $Author->ID)
                {
                    // compound into last activity entry if it was a delete by the same person
                    $activity[count($activity)-1]['FirstTimestamp'] = $editRecord['Timestamp'];
                    $activity[count($activity)-1]['files'][] = array(
                        'Collection' => $Collection->getData()
                        ,'Handle' => $editRecord['Handle']
                        ,'CollectionPath' => $Collection->FullPath
                        ,'Timestamp' => $editRecord['Timestamp']
                    );
                }
                else
                {
                    // push new activity
                	$activity[] = array(
    	                'EventType' => 'delete'
    	                ,'Author' => $Author->getData()
                        ,'Timestamp' => $editRecord['Timestamp']
                        ,'files' => array(
                            array(
                                'Collection' => $Collection->getData()
            	                ,'Handle' => $editRecord['Handle']
            	                ,'CollectionPath' => $Collection->FullPath
            	                ,'Timestamp' => $editRecord['Timestamp']
                            )
                        )
    	            );
                }
                
            	

            }
            elseif(array_key_exists($path, $openFiles))
            {
                if($editRecord['Timestamp'] < $openFiles[$path][0]['Timestamp'] - static::$activitySessionThreshold)
                {
                    $closeFile($path);
                    $openFiles[$path] = array($editRecord);
                }
                else
                {
                    array_unshift($openFiles[$path], $editRecord);
                }
            }
            else
            {
                $openFiles[$path] = array($editRecord);
            }
        }
        
            
        // close any files still open
        array_walk(array_keys($openFiles), $closeFile);
        
        // sort activity by last edit
        usort($activity, function($a, $b) {
            return ($a['Timestamp'] > $b['Timestamp']) ? -1 : 1;
        });
        
        return static::respond('activity', array(
            'success' => true
            ,'data' => $activity
        ));
    }
}
