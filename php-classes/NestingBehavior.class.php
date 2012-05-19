<?php


class NestingBehavior extends RecordBehavior
{

	static public function onSave($Record)
	{
		// set Left & Right nesting positions
		if(!$Record->Left && !$Record->Right)
		{
			if($Record->Parent)
			{
				// insert at right edge of parent
				$Record->Left = $Record->Parent->Right;
				$Record->Right = $Record->Left + 1;
				
				// push rest of set right by 2
				DB::nonQuery(
					'UPDATE `%s` SET `Right` = `Right` + 2 WHERE `Right` >= %u ORDER BY `Right` DESC'
					,array(
						$Record::$tableName
						,$Record->Left
					)
				);
				DB::nonQuery(
					'UPDATE `%s` SET `Left` = `Left` + 2 WHERE `Left` > %u ORDER BY `Left` DESC'
					,array(
						$Record::$tableName
						,$Record->Left
					)
				);
			}
			else
			{
				// append to end of set
				$Record->Left = 1 + DB::oneValue('SELECT MAX(`Right`) FROM `%s`', $Record::$tableName);
				$Record->Right = 1 + $Record->Left;
			}
		}
		elseif($Record->isFieldDirty('ParentID'))
		{
			// TODO: adjust nesting
			// also, this doesn't detect if relationship is dirty
		}
	}


	static public function repairTable($tableName)
	{
		// compile map
		$records = array();
		$backlog = array();
		$cursor = 1;
		
		$result = DB::query('SELECT ID, ParentID FROM `%s` ORDER BY ParentID, ID', $tableName);
		while( ($record = $result->fetch_assoc()) || ($record = array_shift($backlog)) )
		{
			if($record['ParentID'])
			{
				if(!$parent = &$records[$record['ParentID']])
				{
					// if parent not found yet, save to end of backlog and skip this record
					$backlog[] = $record;
					continue;
				}
				
				$record['Left'] = $parent['Right'];
				$record['Right'] = $record['Left'] + 1;

				foreach($records AS &$bAccount)
				{
					if($bAccount['Left'] > $record['Left'])
					{
						$bAccount['Left'] += 2;
					}
					if($bAccount['Right'] >= $record['Left'])
					{
						$bAccount['Right'] += 2;
					}
				}
				
				$cursor += 2;
			}
			else
			{
				$record['Left'] = $cursor++;
				$record['Right'] = $cursor++;
			}
			
			$records[$record['ID']] = $record;
		}

		// write results
		DB::nonQuery('UPDATE `%s` SET `Left` = NULL, `Right` = NULL', $tableName);
		foreach($records AS $record)
		{
			DB::nonQuery('UPDATE `%s` SET `Left` = %u, `Right` = %u WHERE ID = %u', array(
				$tableName
				,$record['Left']
				,$record['Right']
				,$record['ID']
			));
		}
	}

}