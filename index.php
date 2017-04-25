<?php

/**
 * @author    -sanek-
 * @copyright 05.02.2017
 * @Skype     s.sanjok
 *
 */

define('_IN_JOHNCMS', 1);

$headmod = 'guests';
$textl   = 'Мои Гости';

require('../system/bootstrap.php');

/** @var Johncms\User $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Tools $tools */
$tools = $container->get('tools');

$out = '<div class="phdr"><b>Мои Гости</b></div>';

if (!$systemUser->isValid()) {
    $out .= $tools->displayError('Только для зарегистрированных посетителей');
}
else {
    $total = $db->query("SELECT COUNT(*) FROM `my_guests` WHERE `user_id` = '" . $systemUser->id . "'")->fetchColumn();
    if ($total > 0) {
        $out .= '<div class="gmenu"><p>' . $tools->image('guests/guest.gif') . 'Всего гостей ' . $total . '</p></div>';
        //Постраничная навигация
        if ($total > $kmess) {
            $out .= '<div class="topmenu">' . $tools->displayPagination('./index.php?', $start, $total, $kmess) . '</div>';
        }

        $sql = "SELECT `my_guests`.*, `my_guests`.`id` AS `gid`, `users`.`id`, `users`.`name`, `users`.`sex`, `users`.`datereg`, `users`.`lastdate`, `users`.`rights` 
                FROM `my_guests` 
                LEFT JOIN `users` 
                ON `my_guests`.`guest_id` = `users`.`id`  
                WHERE `my_guests`.`user_id` = '" . $systemUser->id . "' 
                ORDER BY `my_guests`.`time` 
                DESC LIMIT $start, $kmess";
        $req = $db->query($sql);

        $read = NULL;
        while ($res = $req->fetch()) {
            $out .= $i % 2 ? '<div class="list1">' : '<div class="list2">';
            // Новый гость или нет
            $header = '';
            if ($res['read'] == '0') {
                $header = $tools->image('guests/new.gif');
                $read[] = $res['gid'];
            }
            // Время
            $body = $tools->image('guests/clock.png') . $tools->displayDate($res['time']);
            // Ссылка на сообщение
            $sub = $tools->image('write.gif') . '<a href="../mail/index.php?act=write&id=' . $res['guest_id'] . '">Написать сообщение</a>';
            $out .= $tools->displayUser($res, [
                'stshide' => 1,
                'iphide'  => 1,
                'header'  => $header,
                'body'    => $body,
                'sub'     => $sub
            ]);
            $out .= '</div>';
            ++$i;
        }

        if (NULL !== $read) {
            $implode = implode(',', $read);
            $db->exec(sprintf("UPDATE `my_guests` SET `read` = '1' WHERE `id` IN (%s)"), $implode);
        }
        //Постраничная навигация
        if ($total > $kmess) {
            $out .= '<div class="topmenu">' . $tools->displayPagination('./index.php?', $start, $total, $kmess) . '</div>';
        }
    }
    else {
        $out .= '<div class="menu"><p><b>Гостей ещё небыло!</b></p></div>';
    }
}

require('../system/head.php');
echo $out;
require('../system/end.php');