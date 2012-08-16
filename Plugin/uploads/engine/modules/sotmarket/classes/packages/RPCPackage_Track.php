<?php
/**
* ����� �������� �� �������������
*
* @copyright   Copyright (c) 2011, SOTMARKET.RU
* @version     0.1 ��������� �� 24.02.2011
* @author 		������ �������
**/

class RPCPackage_Track extends RPCPackage
{
    /**
     *  CustomerID, ���� ��� �������� ����������
     **/
    public  $CustomerID = null;
    public 	$sSessionHash;
    public 	$sUserAgent;
    private $aProducts;
    private $sIp;
    /**
     * ���� ��� �������� ������������
     * @var string $sTrackIdx
     */
    private $sTrackIdx;

    const PRODUCT_SEND  = 1;
    const PRODUCT_NOTSEND  = 2;
    /**
     * ����������
     **/
	public function __construct(){
		session_start();
		$this->sSessionHash = session_id();	
		$this->sIp = $_SERVER['REMOTE_ADDR'];
		$this->sUserAgent = $_SERVER['HTTP_USER_AGENT'];
	}
	/**
	 * ������ ����� ������������� ��������
	 * @return bool 
	 */
    public function Validate(){
        return true;
    }
    /**
     * ���������, ����������c�� �������� ������ �� ������
     **/
	public function bUpdateNeeded()
	{
        $aNotSendIds = $this->aReturnNotSendIds();
        return (count($aNotSendIds) > 0);
    }
    /**
     *
     *  ������� ���������� ������ �������, ������� ���� �������� � ���� ������
     *
     **/
    public function aReturnNotSendIds(){
        $aResult = array();
        foreach($this->aProducts as $id => $aProduct){
            if ($aProduct['status'] == RPCPackage_Track::PRODUCT_NOTSEND){
                $aResult[] = $id;
            }
        }
        return $aResult;
    }
    /**
     *  ��������� ������� � ������ ������������� ���������
     **/
    public function vAddProduct($iProductId, $sTitle){
        if (isset($this->aProducts[$iProductId])) return;
        $this->aProducts[$iProductId] = array('status' => RPCPackage_Track::PRODUCT_NOTSEND, 'title' => $sTitle);
    }
    /**
     * ������������� ��� �������� ������, ��� �� 
     **/
   public function vSetLogged($iProductId){
        if (!isset($this->aProducts[$iProductId])) return;
        $this->aProducts[$iProductId]['status'] = RPCPackage_Track::PRODUCT_SEND;
   }
    /**
     * ���������� ���� ��� �������� ������������
     * ���� ���� �� ������ ������� ���
     * @return string
     **/
    public function  sGetTrackIdx(){
        if (!empty($this->sTrackIdx)){
            return $this->sTrackIdx;
        }
        $oRecord = SmCustomersView::getByHash($this->sSessionHash);
        if ($oRecord !== false){
            $this->sTrackIdx = $oRecord->id;
            return $this->sTrackIdx;
        }
        $oRecord = new SmCustomersView();
        $oRecord->customer_id = $this->CustomerID;
        $oRecord->site_id = 0;
        $oRecord->affiliate_id = 0;
        $oRecord->session_hash = $this->sSessionHash;
        $oRecord->save();
        $this->sTrackIdx = $oRecord->id;
        return $this->sTrackIdx;
    }
    /***
     * ���������� ������ ���������
     * @return string ������ ������ ���� ��� ���������
     **/
    public function sGetProductsJs(){
        if (empty($this->aProducts)){
            return '';
        }
        $sResult =  '';
        foreach($this->aProducts as $id => $aProduct){
            $sResult .= 'aProducts['.$id.'] = \''.$aProduct['title']."';\r\n";
        }
        return $sResult;
    }
}
?>