<?php
/**
 * ����� �����
 *
 * @copyright   Copyright (c) 2011, SOTMARKET.RU
 * @version     0.7 ��������� �� 05.05.2011
 * @author      ������ �������
 **/

class RPCAPIPackage_Order extends Package
{
    public $aProducts = array();
    public $iProductsTotal = 0;
    public $user;
    public $delivery_type;
    public $payment_type;
    public $promo_code;
    public $OrderID;

    function __construct()
    {
        $this->user = new RPCPackage_Customer();
        $this->aProducts = array();
    }
    /**
     * ��������� ������� � �������
     * @param int $iProductId  id �������� � ��������
     * @param int $iCount ���������� ���������
     * @param int $fPrice ���� 
     * @param string $sTitle �������� ���������
     * @return void
     */
    public function vAddProduct($iProductId, $iCount, $fPrice = 0, $sTitle = '')
    {
        if (empty($this->aProducts)) $this->aProducts = array();
        foreach ($this->aProducts as $i => $aProduct) {
            if ($aProduct['id'] == $iProductId) {
                $this->aProducts[$i]['quantity'] += $iCount;
                return;
            }
        }
        $this->aProducts[] = array(
            'id' => $iProductId,
            'quantity' => $iCount,
            'price' => $fPrice,
            'sum' => $fPrice * $iCount,
            'title' => $sTitle,
            'sotm_vids_id' => 0,
            'vids_value' => 0,
        );
        return;
    }
    /**
     * @param  $iProductId id �������� � ��������
     * @return void
     */
    public function vRemoveProduct($iProductId)
    {
        $iKeyRemove = null;
        foreach ($this->aProducts as $iKey => $aProduct) {
            if ($iProductId == $aProduct['id']) {
                $iKeyRemove = $iKey;
                break;
            }
        }
        if (!is_null($iKeyRemove)) {
            unset($this->aProducts[$iKeyRemove]);
        }
    }
    /**
     * ���������� ���������� ������ � �������
     * @param int $iProductId id ��������
     * @param int $iCnt ����������
     * @return void
     */
    public function vUpdateProduct($iProductId, $iCnt)
    {
        $iKeyUpdate = null;
        if ($iCnt == 0){
            // ���� ������������ ���������� 0 ������ �� ������� �������
            return $this->vRemoveProduct($iProductId);
        }
        foreach ($this->aProducts as $iKey => $aProduct) {
            if ($iProductId == $aProduct['id']) {
                $iKeyUpdate = $iKey;
                break;
            }
        }
        if (!is_null($iKeyUpdate)) {
            $this->aProducts[$iKeyUpdate]['quantity'] = $iCnt;
        }
    }

    /**
     * ��������� ��������� ������
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        if (!empty($this->OrderID)) {
            throw new InfoException("����� ��� ������: ".$this->OrderID);
        }
        if (empty($this->aProducts)) {
            throw new InfoException("��� ��������� � ������");
        }
        $this->user->Validate();
        return true;
    }

    /***
     *
     *  ������� ������ ������
     *  �������� - fio, contacts, address,
     *
     **/
    public function getStage()
    {
        if (!empty($_GET['post_action'])) return $_GET['post_action'];
        try {
            $this->user->Validate();
        } catch (Exception $e) {
            if (empty($this->user->FirstName) || empty($this->user->LastName)) {
                return 'fio';
            } else {
                return 'contacts';
            }
        }
        if (!($this->user->Addresses[0] instanceof RPCPackage_CustomerAddress)) {
            return 'address';
        }
        try {
            $this->user->Addresses[0]->Validate();
        } catch (Exception $e) {
            return 'address';
        }
        if (empty($this->delivery_type) && empty($this->payment_type)) {
            return 'delivery';
        }
        return 'order';
    }

    /**
     * ������, ��������� ������������� ������ � ��� ����� ��� ���������� �������
     **/
    public function __set($varName, $value)
    {
        if (preg_match("/([A-Za-z]+)\_([a-z]+)/", $varName, $aMatches)) {
            switch ($aMatches[2]) {
                case 'user':
                    $this->user->$aMatches[1] = $value;
                    break;
                case 'contact':
                    if (empty($value)) return;
                    $sContactType = $aMatches[1];
                    foreach ($this->user->Contacts as $oContact) {
                        if ($oContact->Type == $sContactType) {
                            // ����� ������������ �������� � ���
                            $oContact->Value = $value;
                            return;
                        }
                    }
                    $oNewContact = new RPCPackage_CustomerContact();
                    $oNewContact->Type = $sContactType;
                    $oNewContact->Value = $value;
                    $this->user->Contacts[] = $oNewContact;
                    break;
                case 'address':
                    // ������ 1 �����
                    if (!is_object($this->user->Addresses[0])) {
                        $this->user->Addresses[0] = new RPCPackage_CustomerAddress();
                    }
                    $this->user->Addresses[0]->$aMatches[1] = $value;
                    break;
            }
        }
    }


    /**
     * ������, ��������� ������������� ������ � ��� ����� ��� ���������� �������
     **/
    public function __get($varName)
    {
        if (preg_match("/([A-Za-z]+)\_([a-z]+)/", $varName, $aMatches)) {
            switch ($aMatches[2]) {
                case 'user':
                    return $this->user->$aMatches[1];
                    break;
                case 'contact':
                    $sContactType = $aMatches[1];
                    // ���������� ����� ������� ������ ����
                    foreach ($this->user->Contacts as $oContact) {
                        if ($oContact->Type == $sContactType) {
                            // ����� ���������� ��� ��������
                            return $oContact->Value;
                        }
                    }
                    return null;
                    break;
                case 'address':
                    // ������ 1 �����
                    return $this->user->Addresses[0]->$aMatches[1];
                    break;
            }
        }
        return null;
    }
    /**
     * ������� ��������� ������� ��������� ������� � ��������� ���� � ������� ��� ������� � �������
     *
     * @param  $aProductArray ���������� ���������� �� ������� ����� ���������� RPC ������� SotmarketRPCOrder::product_info_array �� ������� id ���������.
     * @return string ��������� ��� ������������
     **/
   public function sUpdateProductsWithServerData($aProductArray){
       $aDeleteIds = array();
       $bPriceUpdated = false;
       foreach ($this->aProducts as $iKey => $aProduct) {
           if (!isset($aProductArray[$aProduct['id']])){
               // ��������� � ������ �� ��������
               $aDeleteIds[] = $aProduct['id'];
               continue;
           }
           if ($this->aProducts[$iKey]['price'] == $aProductArray[$aProduct['id']]['price']){
               $bPriceUpdate = true;
           }
           $this->aProducts[$iKey]['price'] = $aProductArray[$aProduct['id']]['price'];
           $this->aProducts[$iKey]['status'] = $aProductArray[$aProduct['id']]['status'];
       }
       $sMessage = '';
       if ($bPriceUpdated){
        $sMessage .= '�������� ���� �� ������ ���� ���������! <br />';
       }
       foreach($aDeleteIds as $iKey => $iDeletedId){
           $sMessage .= '������� c id '.$iDeletedId .' �� ������ � ������ �� �������<br />';
           $this->vRemoveProduct($iDeletedId);
       }
       return $sMessage;
   }
}
