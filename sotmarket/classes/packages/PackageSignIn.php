<?php
    /**
 * ����� "����������� �� �����"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 ��������� �� 21.12.2010
 * @author      ������� �������� ( k-v-n@inbox.ru )
 */

class PackageSignIn extends Package
{
    /**
     * ��� ������ "Email �� ��������"
     */

    const EMAIL_EMPTY = 200;

    /**
     * ��� ������ "Email ������ �� �����"
     */

    const EMAIL_INVALID = 201;

    /**
     * ��� ������ "������ �� �����"
     */

    const PASSWORD_EMPTY = 210;

    /**
     * ��� ������ "������ �� �����"
     */

    const PASSWORD_INVALID = 211;

    /**
     * Email - ����� ����������
     *
     * @var string
     */

    public $Email;

    /**
     * ������ ����������
     *
     * @var string
     */

    public $Password;

    /**
     * ��� ��� �������������� ����������� �� �����
     *
     * @var string
     */

    public $Hash;

    /**
     * ��������� ��������� ������
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        /**
         * ����������� �� ����
         */

        if ($this->Hash && strlen($this->Hash) == 32) {
            return true;
        }

        /**
         * ����������� �� Email � ������
         */

        if (!$this->Email) {
            throw new Exception("Email �� ��������", self::EMAIL_EMPTY);
        }
        elseif ($this->Email && !String::CheckForEmail($this->Email)) {
            throw new Exception("Email ������ �� �����", self::EMAIL_INVALID);
        }

        if (!$this->Password) {
            throw new Exception("������ �� �����", self::PASSWORD_EMPTY);
        }
        elseif ($this->Password && strlen($this->Password) != 32) {
            throw new Exception("������ ����� �� �����", self::PASSWORD_INVALID);
        }

        return true;
    }
}

?>