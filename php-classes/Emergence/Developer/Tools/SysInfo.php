<?php
    namespace Emergence\Developer\Tools;

class SysInfo
{
	public static function mounts()
	{
		// get server's hard drive info
		$mounts = explode("\n",`df -h`);
		foreach($mounts as $mount)
		{
			list($DevicePath,$ThousandKBlocks,$Used,$Available,$UsedPercentage,$MountPath) = preg_split('/[\s,]+/',$mount);
			$mount = array(
				'DevicePath'		=>	$DevicePath,
				'ThousandKBlocks'	=>	$ThousandKBlocks,
				'Used'				=>	$Used,
				'Available'		    =>	$Available,
				'UsedPercentage'	=>	$UsedPercentage,
				'MountPath'		    =>	$MountPath
			);
			if(strpos($mount['DevicePath'],'/dev/') === 0)
			{
				$t[] = $mount;
			}
		}

		return $t;
	}

	public static function CPULoad()
	{
		return sys_getloadavg();
	}

	public static function CPUInfo()
	{
		$CPUInfo = explode("\n\n",`cat /proc/cpuinfo`);
		$t = array();
		foreach($CPUInfo as $CPU) {
			$stats = explode("\n",$CPU);
			if(count($stats)>1)
			{
				$Processor = array();
				foreach($stats as $stat)
				{
					$stat = explode(':',$stat);
					$Processor[trim($stat[0])] = trim($stat[1]);
				}
				$t[] = $Processor;
			}
		}

		return $t;
	}

	public static function MemoryInfo()
	{
		$MemoryInfo = explode("\n",`cat /proc/meminfo`);
		foreach($MemoryInfo as $RAMInfo)
		{
			$stat = explode(':',$RAMInfo);
			$data['RAMInfo'][trim($stat[0])] = trim($stat[1]);
		}

		$data['RAMInfo']['Used'] = ($data['RAMInfo']['MemTotal'] - $data['RAMInfo']['MemFree']) . 'kB';
		$data['RAMInfo']['UsedPercentage'] = round(($data['RAMInfo']['Used'] / $data['RAMInfo']['MemTotal']) * 100,2);

		return $data['RAMInfo'];
	}
}