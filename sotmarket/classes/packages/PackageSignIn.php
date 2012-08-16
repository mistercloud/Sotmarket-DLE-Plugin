<?php
    /**
 * Пакет "Авторизация на сайте"
 *
 * @copyright   Copyright (c) 2010, SOTMARKET.RU
 * @version     0.0.1 изменения от 21.12.2010
 * @author      Ковылин Владимир ( k-v-n@inbox.ru )
 */

class PackageSignIn extends Package
{
    /**
     * Код ошибки "Email не заполнен"
     */

    const EMAIL_EMPTY = 200;

    /**
     * Код ошибки "Email указан не верно"
     */

    const EMAIL_INVALID = 201;

    /**
     * Код ошибки "Пароль не задан"
     */

    const PASSWORD_EMPTY = 210;

    /**
     * Код ошибки "Пароль не задан"
     */

    const PASSWORD_INVALID = 211;

    /**
     * Email - Логин покупателя
     *
     * @var string
     */

    public $Email;

    /**
     * Пароль покупателя
     *
     * @var string
     */

    public $Password;

    /**
     * Хеш для автоматической авторизации на сайте
     *
     * @var string
     */

    public $Hash;

    /**
     * Выполняет вылидацию пакета
     *
     * @throws    InfoException
     */

    public function Validate()
    {
        /**
         * Авторизация по хешу
         */

        if ($this->Hash && strlen($this->Hash) == 32) {
            return true;
        }

        /**
         * Авторизация по Email и паролю
         */

        if (!$this->Email) {
            throw new Exception("Email не заполнен", self::EMAIL_EMPTY);
        }
        elseif ($this->Email && !String::CheckForEmail($this->Email)) {
            throw new Exception("Email указан не верно", self::EMAIL_INVALID);
        }

        if (!$this->Password) {
            throw new Exception("Пароль не задан", self::PASSWORD_EMPTY);
        }
        elseif ($this->Password && strlen($this->Password) != 32) {
            throw new Exception("Пароль задан не верно", self::PASSWORD_INVALID);
        }

        return true;
    }
}

?>