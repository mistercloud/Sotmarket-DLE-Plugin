<?php
    /**
     * ����� "������"
     *
     * @package     SC_CMS
     * @subpackage  CORE
     * @copyright   Copyright (c) 2009
     * @version     0.1 ��������� �� 17.01.2009
     * @author      ������� �������� ( k-v-n@inbox.ru )
     */
    
    class String
    {
        /**
         * ��������� ������� �� ������ �� ��������� �������� � ����
         * ����������� ��� �� �������:
         *  - ������������� "_"
         *
         * @param   string      $str        ������
         * @return  bool
         */
        
        public static function CheckForLatin($str)
        {
            if( !strlen($str) ) return true;
            
            return preg_match('/^([a-zA-Z]|\d|[_])*$/i', $str);
        }
        
        /**
         * ��������� ������� �� ������ �� ��������� ��������
         * ����������� ��� �� �������:
         *  - ������ " "
         *
         *  - �����  "-"
         *
         * @param   string      $str        ������
         * @return  bool
         */
        
        public static function CheckForSymbols($str)
        {
            if( !strlen($str) ) return true;
            return preg_match('/^([a-zA-Z�-��-�]|[ \-])*$/i', $str);
        }
        
        /**
         * ��������� ������� �� ������ �� ��������� ��������
         * ����������� ��� �� �������:
         *  - ������ " "
         *
         * @param   string      $str        ������
         * @return  bool
         */
        
        public static function CheckForTelephone($str)
        {
            if( !strlen($str) ) return true;
            
            return preg_match('/^([7]{1})([0-9]{10})*$/', $str);
        }
        
        /**
         * ��������� �������� �� ������ Email'��
         *
         * @param   string  $str    ������
         * @return  bool
         */
        
        public static function CheckForEmail($str)
        {
            if( !strlen($str) ) return false;
            
            return preg_match("/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$/i", $str);
        }
        
        /**
         * ��������� �������� �� ������ ����� ������
         *
         * @param   string  $str    ������
         * @return  bool
         */
        
        public static function CheckForInteger($str)
        {
            if( !strlen($str) ) return false;
            
            return preg_match("/^\d+$/i", $str);
        }

        /**
         * ��������� �������� �� ������ ������
         *
         * @param   string  $str    ������
         * @return  bool
         */
        
        public static function CheckForNumber($str)
        {
            if( !strlen($str) ) return false;
            
            return preg_match("/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/i", $str);
        }
        
        /**
         * ��������� �������� �� ������ ������ ���� � �������� �������
         *
         * @param   string  $str    ������
         * @return  bool
         */
        
        public static function CheckForPath($str)
        {
            if( !strlen($str) ) return false;
            
            return preg_match('/^([a-zA-Z]|\d|[_.\/])*$/i', $str);
        }
        
        /**
         * �������� ����� UTF-8 �������
         *
         * @param string $str
         */
        
        public static function CutBrockenUTF8($str)
        {
			$ret = '';
			for ($i = 0; $i < strlen($str); ) 
			{
				$tmp 	= $str{$i++};
				$ch 	= ord($tmp);
				
				if ($ch > 0x7F) 
				{
					if ($ch < 0xC0) continue;
					elseif ($ch < 0xE0) $di = 1;
					elseif ($ch < 0xF0) $di = 2;
					elseif ($ch < 0xF8) $di = 3;
					elseif ($ch < 0xFC) $di = 4;
					elseif ($ch < 0xFE) $di = 5;
					else continue;
			
					for ($j = 0;$j < $di;$j++) 
					{
						$tmp   .= $ch = $str{$i + $j};
						$ch 	= ord($ch);
						
						if ($ch < 0x80 || $ch > 0xBF) continue 2;
					}
					$i += $di;
				}
				
				$ret .= $tmp;
				
			}
			return $ret;
        }
    }
?>