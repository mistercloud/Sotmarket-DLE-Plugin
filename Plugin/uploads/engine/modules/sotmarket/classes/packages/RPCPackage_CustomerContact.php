<?php
 /**
 * RPC ����� "������� ����������"
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.4 ��������� �� 05.04.2011
 * @author      ������ �������
 */

class RPCPackage_CustomerContact extends RPCPackage
{
    /**
     * ������� �������� �� �����
     */
    const WRONG_FORMAT = 200;
    const PHONE_LENGTH = 11;

    /**
     * ��� �������� "Email"
     */

    const EMAIL = 'email';

    /**
     * ��� �������� "ICQ"
     */

    const ICQ = 'icq';

    /**
     * ��� �������� "��������� �������"
     */

    const MOBILE = 'mobphone';

    /**
     * ��� �������� "�������"
     */

    const PHONE = 'phone';

    /**
     * ������������� ��������
     */

    public $ContactID;

    /**
     * ��� ��������
     */

    public $Type;

    /**
     * ��������
     */

    public $Value;

    /**
     * ������� "����� �����������"
     */

    public $HasComments;

    /**
     * �����������
     */

    public $Comments;

    /**
     * ������� "������� �� ���������"
     */

    public $IsDefault;

    /**
     * ����������� �������
     *
     * @return    bool
     */

    public function __construct()
    {
        $this->HasComments = 0;
        $this->IsDefault = 0;
    }

    /**
     * ��������� ��������� ������
     *
     * @throws    SotmarketRPCException
     */

    public function Validate()
    {
        switch ($this->Type) {
            case self::EMAIL:
                if ($this->Value && !$this->isEmail($this->Value)) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new InfoException("���� EMAIL ��������� �� �����, ������� email �����", self::WRONG_FORMAT);
                }
                break;
            case self::ICQ:
                if ($this->Value && !$this->isInteger($this->Value)) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("���� ICQ ��������� �� �����, ������� ����� ��� ������������!", self::WRONG_FORMAT);
                }
                break;
            case self::MOBILE:
            case self::PHONE:
                if ($this->Value && !$this->isInteger($this->Value)) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("���� ������� ��������� �� �����, ������� ����� ��� ������������!", self::WRONG_FORMAT);
                }
                if (strlen($this->Value) != self::PHONE_LENGTH) {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("���� ������� ��������� �� �����, ������� ������� � ������� 9123456789!", self::WRONG_FORMAT);
                }
                // ������ ������� �� �������
                if ($this->Value == '79123456789') {
                    $this->ErrorCode = self::WRONG_FORMAT;
                    throw new SotmarketRPCException("���� ������� ��������� �� �����, ������� ���� ����� ��������!", self::WRONG_FORMAT);
                }
                break;
        }
        return true;
    }
}

?>