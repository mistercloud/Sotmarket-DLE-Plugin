<?php

$aTranslations = array(
	'INSTALL_MODULE' => 'Установка модуля Sotmarket' ,
	'INSTALL_MODULE_SUCCESS' => 'Установка модуля Sotmarket успешно завершена' ,
	'MODULE_TITLE' => 'Sotmarket:короткий заказ' ,
	'MODULE_DESC' => 'Возможность прямой покупки товара из статей' ,
	'UNINSTALL_MODULE_SKIP' => 'Не удалять модуль из CMS' ,
	'UNINSTALL_MODULE' => 'Удаление модуля Sotmarket' ,
	'UNINSTALL_MODULE_SUCCESS' => 'Модуль Sotmarket удален' ,
	'INSTALL_MODULE_UNSUCCESS' => 'Установка не была закончена. Вам необходимо самостоятельно добавить дополнительное поле sotmarket_product_id в дополнительных полях<br /><a href="admin.php?mod=xfields&xfieldsaction=configure">Редактор дополнительных полей</a>' ,
	'MODULE_TITLE' => 'Sotmarket: Партнерский плагин' ,
	'MODULE_DESCRIPTION' => 'описание плагина' ,
	'SETTINGS_UPDATE' => 'Обновить настройки' ,
	'SETTINGS_UPDATED' => 'Данные обновлены' ,
	'INSTALL_MODULE_SUCCESS' => '
		<div style="text-align:center; width:98%;">
			<h3>Установка модуля Sotmarket успешно завершена</h3>
			<span style="width:50%; text-align:left;">
				Для завершения установки:<br />
				<ul>
					<li>Введите Ваш site_id в партнерке sotmarket.ru в настройках модуля.</li>
					<li>Установите директории <b>engine/modules/sotmarket/_data/</b> и всем её поддиректориям права на запись. </li>
					<li>Добавьте строку
						<blockquote>{include file=&quot;engine/modules/sotmarket/order.php?product_id=[xfvalue_sotmarket_product_id]&quot;}</blockquote>
						в нужном месте шаблонов shortstory.tpl и(или) fullstory.tpl</li>
					<li>При создании и редактировании добавьте в дополнительное поле ID товара. ID товара вы можете получить в http://b2b.sotmarket.ru в разделе товары</li>
					<li>Если Вас не устраивает формат вывода формы заказа Вы можете всегда изменить шаблон в файле <b>templates/sotmarket.tpl</b></li>
				</ul>
			</span>
			<br /><a href="' . $config[http_home_url] . '/admin.php?mod=sotmarket">Sotmarket:короткий заказ</a>
        </div>' ,
);

$aMandatoryParams = array(
	'SOTMARKET_PARTNER_ID' => 'Id партнера sotmarket.ru',
	'SOTMARKET_SITE_ID' => 'Id сайта в партнерке sotmarket.ru',
	'SOTMARKET_FROM' => 'Метка',
    'SOTMARKET_LABEL_TYPE' => 'Тип метки',
	'SOTMARKET_BLOCK_TYPE' => 'Способ отображения блока' ,
	'SOTMARKET_LINK_TYPE' => 'Тип ссылок в блоках',
	'SOTMARKET_BLOCK_STATUSES' => 'Фильтр по статусам наличия товара',
    'SOTMARKET_CART_URL' => 'Адрес страницы оформления заказа',
);

$aMandatoryParamsAsSelect = array(
	'SOTMARKET_BLOCK_TYPE' => array(
		'informer' => 'Покупка на sotmarket' ,
		'shop' => 'Покупка на сайте партнера'
	),
	'SOTMARKET_LINK_TYPE' => array(
		'link' => 'Прямая ссылка на sotmarket',
		'redirect' => 'Ссылка с redirect'
	),
	'SOTMARKET_BLOCK_STATUSES' => array(
		'all' => 'Отображать все товары',
		'available' => 'Только товары в наличии'
	),
    'SOTMARKET_LABEL_TYPE' => array(
        'from' => 'Метка from',
        'subref' => 'Метка subref',
    ),
);

?>
