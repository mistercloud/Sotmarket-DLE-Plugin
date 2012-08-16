<?php
    /**
 * RPC ����� "����������"
 *
 * @copyright   Copyright (c) 2011 SOTMARKET.RU
 * @version     0.0.3 ��������� �� 12.03.2011
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class RPCPackage_Customer extends RPCPackage
{
    /**
     * ��� �� ���������
     */

    const FIRST_NAME_EMPTY = 210;

    /**
     * ��� ��������� �� �����
     */

    const FIRST_NAME_WRONG = 211;

    /**
     * ������� �� ���������
     */

    const LAST_NAME_EMPTY = 220;

    /**
     * ������� �� ���������
     */

    const LAST_NAME_WRONG = 221;

    /**
     * �������� ��������� �� �����
     */

    const CONTACTS_WRONG = 300;

    /**
     * ������������� ������������
     *
     * @var int
     */

    public $CustomerID;

    /**
     * ��� ������������
     *
     * @var    string
     */

    public $FirstName;

    /**
     * ������
     *
     * @var    string
     */

    public $Password;
    /**
     * ������� ������������
     *
     * @var    string
     */

    public $LastName;

    /**
     * ������ ��������� ������������
     *
     * @var    RPCPackage_CustomerContact
     */

    public $Contacts;

    /**
     * ������ ������� ������������
     *
     * @var    RPCPackage_CustomerAddress
     */

    public $Addresses;

    /**
     * ���� ���������
     *
     * @var    string[]
     */

    private $mContactTypes = array(
        RPCPackage_CustomerContact::EMAIL,
        RPCPackage_CustomerContact::ICQ,
        RPCPackage_CustomerContact::MOBILE,
        RPCPackage_CustomerContact::PHONE,
    );
    /**
     *
     * @var string[]
     **/
    private $mRequiredContactTypes = array(
        RPCPackage_CustomerContact::EMAIL,
        RPCPackage_CustomerContact::PHONE
    );

    function __construct()
    {
        $this->Contacts = array();
        $this->Addresses = array();
        $this->Addresses[0] = new RPCPackage_CustomerAddress();
    }

    /**
     * ��������� ��������� ������
     *
     * @return    bool
     */

    public function Validate()
    {
        /**
         * ������ ������
         */

        if (!$this->FirstName) {
            throw new InfoException("���� '���' �� ���������!", self::FIRST_NAME_EMPTY);
        }
        elseif ($this->FirstName && !$this->isSymbols($this->FirstName)) {
            throw new InfoException("���� '���' ��������� �� �����", self::FIRST_NAME_WRONG);
        }

        if (!$this->LastName) {
            throw new InfoException("���� '�������' �� ���������!", self::LAST_NAME_EMPTY);
        }
        elseif ($this->LastName && !$this->isSymbols($this->LastName)) {
            throw new InfoException("���� '�������' ��������� �� �����", self::LAST_NAME_WRONG);
        }
        if (count($this->Contacts) == 0) {
            throw new InfoException("�� ��������� ��������");
        }
        /**
         * ������ ��������� ������������
         */

        $errors = '';

        for ($i = 0; $i < count($this->Contacts); $i++)
        {
            try {
                $this->Contacts[$i]->Validate();
            }
            catch (Exception $e) {
                $errors .= $this->Contacts[$i]->Type . ':' . $e->getMessage() . '  <br />';
            }
        }

        if (!empty($errors)) {
            throw new InfoException($errors);
        }
        /**
         * �������� �� ���������
         */

        $this->checkForDublicates();
        $this->checkMandatoryContactTypes();
        return true;
    }

    /**
     * ���������� �������� �������� �� ���������
     *
     * @param    connst    $contact_type    ��� ��������
     * @return    string
     */

    public function getDefaultContactValue($contact_type)
    {
        for ($i = 0; $i < count($this->Contacts); $i++)
        {
            if ($this->Contacts[$i]->Type == $contact_type && $this->Contacts[$i]->IsDefault == 1) {
                return $this->Contacts[$i]->Value;
            }
        }

        return null;
    }

    /**
     * ��������� �������� �� ��������� � ���������� ������
     *
     * @throw    SotmarketRPCException
     */

    public function checkForDublicates()
    {
        for ($i = 0; $i < count($this->mContactTypes); $i++)
        {
            $Type =& $this->mContactTypes[$i];

            for ($j = 0; $j < count($this->Contacts); $j++)
            {
                if ($this->Contacts[$j]->Type != $Type) continue;

                for ($z = ($j + 1); $z < count($this->Contacts); $z++)
                {
                    if ($this->Contacts[$z]->Type != $Type) {
                        continue;
                    }
                    elseif ($this->Contacts[$z]->Type == $Type && $this->Contacts[$j]->Value == $this->Contacts[$z]->Value)
                    {
                        throw new SotmarketRPCException("���������� ������ �����������!");
                    }
                }
            }
        }
    }

    /**
     * ��������� ��� ���� ����� ��� �������
     **/
    private function checkMandatoryContactTypes()
    {
        $iPoints = 0;
        for ($i = 0; $i < count($this->Contacts); $i++)
        {
            if (in_array($this->Contacts[$i]->Type, $this->mRequiredContactTypes) && $this->Contacts[$i]->Validate()) {
                $iPoints++;
            }
        }
        if ($iPoints == 0) {
            throw new SotmarketRPCException("�� ��������� ������������ ��������!");
        }
    }

    /**
     * ������, ��������� �������� ������ �� ����������
     **/
    public function __get($varName)
    {
        if (preg_match("/([A-Za-z]+)\_([a-z]+)/", $varName, $aMatches)) {
            switch ($aMatches[2]) {
                case 'contact':
                    $sContactType = $aMatches[1];
                    // ���������� ����� ������� ������ ����
                    //var_dump($this);
                    foreach ($this->Contacts as $oContact) {
                        if ($oContact->Type == $sContactType) {
                            // ����� ������������ �������� � ���
                            return $oContact->Value;
                        }
                    }
                    return null;
                    break;
                case 'address':
                    // ������ 1 �����
                    $iId = (int)$aMatches[1];
                    if ($this->Addresses[$iId])
                        return $this->Addresses[$iId];
                    return null;
                    break;
            }
        }
        return '';
    }
}

?>