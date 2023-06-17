<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<table>
    <caption>Список диспетчеров</caption>
    <thead>
    <tr>
        <th>Фамилия</th>
        <th>Имя</th>
        <th>Уровень доступа</th>
        <th>Дата и время последнего входа в систему</th>
        <th>Комментарий</th>
        <th>Объект</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($arResult as $dispetcher) {
        echo '<tr>';
        echo "<td>{$dispetcher['USER_LAST_NAME']}</td><td>{$dispetcher['USER_NAME']}</td><td>{$dispetcher['ACCESS_LEVEL']}</td><td>{$dispetcher['LAST_LOGIN']}</td><td>{$dispetcher['COMMENT']}</td><td>{$dispetcher['OBJECT_NAME']}</td>";
        echo '</tr>';
    }
    ?>
    </tbody>
</table>
