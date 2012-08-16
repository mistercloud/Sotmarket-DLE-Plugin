<?php
    /**
 * ����� "��������� �� �����"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 ��������� �� 18.11.2010
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class PackageOrderPretension extends Package
{
    /**
     * ������� "�������"
     */

    const MARK_GOOD = 1;

    /**
     * ������� "�����"
     */

    const MARK_BAD = 2;

    /**
     * ������� "����������"
     */

    const MARK_INFO = 3;

    /**
     * ��� ������: "������������� ������ �� �����"
     */

    const ORDER_ID_EMPTY = 100;

    /**
     * ��� ������ "������� �� ��������"
     */

    const PHONE_EMPTY = 200;

    /**
     * ��� ������ "������� �������� �� �����"
     */

    const PHONE_WRONG = 201;

    /**
     * ��� ������: "����� ��������� �� �����"
     */

    const TEXT_EMPTY = 300;

    /**
     * ��� ������: "����� ��������� �� �����"
     */

    const MARK_WRONG = 400;

    /**
     * ��� ������: "Email �� ���������"
     */

    const EMAIL_EMPTY = 500;

    /**
     * ��� ������: "Email �������� �� �����"
     */

    const EMAIL_WRONG = 501;

    /**
     * ��� ������: "��� �� ���������"
     */

    const NAME_EMPTY = 600;

    /**
     * ������������� ������
     *
     * @var int
     */

    public $OrderID;

    /**
     * ���������� �������
     *
     * @var string
     */

    public $Phone;

    /**
     * ���� ���������
     *
     * @var string
     */

    public $Subject;

    /**
     * ����� ���������
     *
     * @var string
     */

    public $Text;

    /**
     * �������, ������
     *
     * @var const
     */

    public $Mark;

    /**
     * ��� ����������� ���������
     *
     * @var string
     */

    public $Name;

    /**
     * Email ����������� ���������
     *
     * @var string
     */

    public $Email;

    /**
     * ��������� ��������� ������
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        if ($this->OrderID && !(int)$this->OrderID) {
            throw new InfoException("������������� ������ �� ������", self::ORDER_ID_EMPTY);
        }

        if (!$this->Text) {
            throw new InfoException("����� ��������� �� ������", self::TEXT_EMPTY);
        }

        if ($this->Mark != self::MARK_GOOD && $this->Mark != self::MARK_BAD && $this->Mark != self::MARK_INFO) {
            throw new InfoException("����� ��������� ������ �� �����", self::MARK_WRONG);
        }

        if ($this->Phone && !String::CheckForTelephone($this->Phone)) {
            throw new InfoException("������� ��������� �� �����, ������� ��� ������, ��� ������ � ����� �������� ��� ������������, �������� 74957809898", self::PHONE_WRONG);
        }

        if ($this->Email && !String::CheckForEmail($this->Email)) {
            throw new InfoException("Email ����� �� �����", self::EMAIL_WRONG);
        }

        $check_contacts = ($this->Phone || $this->Name || $this->Email) ? true : false;

        if ($check_contacts && !$this->Phone) {
            throw new InfoException("������� �� ��������", self::PHONE_EMPTY);
        }

        if ($check_contacts && !$this->Email) {
            throw new InfoException("Email �� ��������", self::EMAIL_EMPTY);
        }

        if ($check_contacts && !$this->Name) {
            throw new InfoException("��� �� ���������", self::NAME_EMPTY);
        }

        return true;
    }

    public static function aGetFields()
    {
        $aResult[] = array(
            'type' => 'int',
            'name' => 'OrderID',
            'desc' => '������������� ������'
        );

        $aResult[] = array(
            'type' => 'text',
            'name' => 'Subject',
            'desc' => '���� ���������'
        );

        $aResult[] = array(
            'type' => 'text',
            'name' => 'Text',
            'desc' => '����� ���������'
        );
        $aResult[] = array(
            'type' => array(1 => '�������', 2 => '�����', 3 => '����������'),
            'name' => 'Mark',
            'desc' => '������ ���������'
        );
        $aResult[] = array(
            'type' => 'int',
            'name' => 'Phone',
            'desc' => '������� ��� ���������'
        );
        $aResult[] = array(
            'type' => 'text',
            'name' => 'Email',
            'desc' => '����������� ������'
        );
        $aResult[] = array(
            'type' => 'text',
            'name' => 'Name',
            'desc' => '���'
        );
        return $aResult;
    }
}

?>