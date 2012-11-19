<?php
   /**
    * ���������������� ���� ��������
    * 
    * @copyright   Copyright (c) 2011, SOTMARKET.RU
    * @version     0.0.1 ��������� �� 06.07.2009
    * @author      ������� �������� ( k-v-n@inbox.ru )
    */

	$config = array(
		'dumpfile' => array(
			'fileName' => 'engine/modules/sotmarket/_data/tmp/dumpfile.log'
		),
		'rpc' => array(
          'serverUrl' => 'http://update.sotmarket.ru/api/rpc.php',
          'tmpPath'  => 'engine/modules/sotmarket/_data/tmp/',
          'tmpExpire' =>  24,
		  'encoding'  => 'utf-8',
		),
	);
?>