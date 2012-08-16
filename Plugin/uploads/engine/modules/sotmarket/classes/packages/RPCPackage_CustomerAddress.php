<?php
    /**
 * RPC ����� "����� ����������"
 *
 * @copyright   Copyright (c) 2009, SOTMARKET.RU
 * @version     0.0.2 ��������� �� 12.01.2011
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class RPCPackage_CustomerAddress extends RPCPackage
{
    /**
     * ����� �� ��������
     */

    const CITY_EMPTY = 200;

    /**
     * ����� �� ��������
     */

    const ADDRESS_EMPTY = 210;

    /**
     * ����� �� ���������
     */

    const UNDERGROUND_EMPTY = 220;

    /**
     * ������ �� ���������
     */

    const POST_CODE_EMPTY = 230;

    /**
     * ������ �������� �� �����
     */

    const POST_CODE_WRONG = 231;

    /**
     *
     *
     **/
    const POSTCODE_MOSCOW = 109428;


    /**
     * ��� ������������� ����� "������"
     */

    const RUSSIA = "";

    /**
     * ������������� ������
     *
     * @var int
     */

    public $AddressID;

    /**
     * �������� ������
     *
     * @var int
     */

    public $PostCode;

    /**
     * �����
     *
     * @var string
     */

    public $City;

    /**
     * ������������� ������������� �����
     *
     * @var int
     */

    public $CityID;

    /**
     * �����, ��� � �.�.
     *
     * @var string
     */

    public $Street;

    /**
     * ������������� ������� �����
     *
     * @var int
     */

    public $UndergroundID;

    /**
     * ��� � �������� �����
     *
     * @var string
     */

    public $Type;

    /**
     * ������ ��������������� �������, ��� ������� ��������� ���� ������� �����
     *
     * @var int[]
     */

    protected $UndergroundRequired;

    /**
     * ������ ��������������� �������, ��� ������� ��������� ���� ��������� �������
     *
     * @var int[]
     */

    protected $PostCodeRequired;

    /**
     * �����������
     */

    public function __construct()
    {
        $this->UndergroundRequired = array('77-0-0-0' => 1, '78-0-0-0' => 1, '66-0-1-0' => 1, '52-0-1-0' => 1, '2-1-1-0' => 1);
    }

    /**
     *
     * ������������� ������� �����
     *
     * @var int
     */

    public function Validate()
    {
        if (empty($this->CityID) && !$this->City) {
            $this->Error = "�� ��������� ���� \"�����\"";
            $this->ErrorCode = self::CITY_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if (!$this->Street) {
            $this->Error = "�� ��������� ���� \"�����\"";
            $this->ErrorCode = self::ADDRESS_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if (isset($this->UndergroundRequired[$this->CityID]) && !$this->UndergroundID) {
            $this->Error = "�� ��������� ���� \"������� �����\"";
            $this->ErrorCode = self::UNDERGROUND_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if ($this->PostCode && !$this->isInteger($this->PostCode)) {
            $this->Error = "���� \"������\" ��������� �� �����";
            $this->ErrorCode = self::POST_CODE_WRONG;
            throw new InfoException($this->Error, $this->ErrorCode);
        }

        if (empty($this->CityID) && !$this->PostCode) {
            $this->Error = "���� \"������\" �� ���������";
            $this->ErrorCode = self::POST_CODE_EMPTY;
            throw new InfoException($this->Error, $this->ErrorCode);
        }
    }
}

?>