Библиотека для получения большого количества контента через бесплатные анонимные прокси серверы.
Классы:
- c_get_content основной класс построен на базе cURL отправляет запросы и обрабатывает ответы
- c_proxy класс для работы с списками прокси адресов, для выдачи адресов классу c_get_content и переодических обновлений
- c_string_work класс для работы со строками, используется для определения/изменения кодировки или вырезания необходимых данных

php v5.4
need extension:
- geoip http://pecl.php.net/package/geoip

Для коректной работы необходимо установить права на чтение и запись для пользователя от имени которого будут запускаться скрипты, папаки:
- ./get_content_files/cookie
- ./proxy_files/proxy_list