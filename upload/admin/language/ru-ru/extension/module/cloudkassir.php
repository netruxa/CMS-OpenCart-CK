<?php

// Heading
$_['heading_title'] = 'Онлайн-касса CloudKassir';

// Text
$_['text_extension'] = 'Расширения';
$_['text_success'] = 'Настройки успешно изменены!';
$_['text_edit'] = 'Редактирование';

$_['text_taxation_system_0'] = 'Общая система налогообложения';
$_['text_taxation_system_1'] = 'Упрощенная система налогообложения (Доход)';
$_['text_taxation_system_2'] = 'Упрощенная система налогообложения (Доход минус Расход)';
$_['text_taxation_system_3'] = 'Единый налог на вмененный доход';
$_['text_taxation_system_4'] = 'Единый сельскохозяйственный налог';
$_['text_taxation_system_5'] = 'Патентная система налогообложения';

$_['text_vat_none'] = 'НДС не облагается';
$_['text_vat_20'] = 'НДС 20%';
$_['text_vat_18'] = 'НДС 18%';
$_['text_vat_10'] = 'НДС 10%';
$_['text_vat_0'] = 'НДС 0%';
$_['text_vat_110'] = 'Расчетный НДС 10/110';
$_['text_vat_118'] = 'Расчетный НДС 18/118';

// Entry
$_['entry_status'] = 'Статус';
$_['entry_public_id'] = 'Идентификатор сайта';
$_['entry_secret_key'] = 'Секретный ключ';

$_['entry_inn'] = 'ИНН';
$_['entry_taxation_system'] = 'Система налогообложения';
$_['entry_vat'] = 'Ставка НДС';
$_['entry_vat_delivery'] = 'Ставка НДС для доставки';

$_['entry_order_status_for_pay'] = 'Статусы заказа для оплаты (приход)';
$_['entry_order_status_for_refund'] = 'Статусы заказа для возврата (возврат прихода)';

$_['entry_enabled_for_payments'] = 'Включить для методов оплаты';

// Error
$_['error_permission'] = 'У Вас нет прав для управления данным модулем!';
$_['error_public_id'] = 'Поле обязательно для заполнения';
$_['error_secret_key'] = 'Поле обязательно для заполнения';
$_['error_inn'] = 'Поле обязательно для заполнения';

//Help
$_['help_public_id'] = 'Данный параметр можно получить в личном кабинете CloudPayments (Public ID)';
$_['help_secret_key'] = 'Данный параметр можно получить в личном кабинете CloudPayments (Пароль для API)';

$_['help_inn'] = 'ИНН вашей организации или ИП, на который зарегистрирована касса. Используется при формировании онлайн-чека.';
$_['help_taxation_system'] = 'Более детальная информация в документации CloudPayments';
$_['help_vat'] = 'НДС для товаров корзины. Более детальная информация в документации CloudPayments';
$_['help_vat_delivery'] = 'НДС для доставки. Более детальная информация в документации CloudPayments';

$_['help_order_status_for_pay'] = 'Статусы заказа при которых будет отправлен запрос на печать чека прихода';
$_['help_order_status_for_refund'] = 'Статусы заказа при которых будет отправлен запрос на печать чека возврата прихода';

$_['help_enabled_for_payments'] = 'Формирование чека будет только для выбранных методов оплаты';
