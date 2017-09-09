<?php
/*
   This file is part of Asternic Call Center Stats.

    Asternic Call Center Stats is free software: you can redistribute it 
    and/or modify it under the terms of the GNU General Public License as 
    published by the Free Software Foundation, either version 3 of the 
    License, or (at your option) any later version.

    Asternic Call Center Stats is distributed in the hope that it will be 
    useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Asternic Call Center Stats.  If not, see 
    <http://www.gnu.org/licenses/>.
*/

// Russian language locale (UTF-8)
// Nesterov Max
// braams@yandex.ru
// 13.08.2008
// v0.1 for v1.0
$dayp[0] = "Воскресенье";
$dayp[1] = "Понедельник";
$dayp[2] = "Вторник";
$dayp[3] = "Среда";
$dayp[4] = "Четверг";
$dayp[5] = "Пятница";
$dayp[6] = "Суббота";

$yearp[0] = "Январь";
$yearp[1] = "Февраль";
$yearp[2] = "Март";
$yearp[3] = "Апрель";
$yearp[4] = "Май";
$yearp[5] = "Июнь";
$yearp[6] = "Июль";
$yearp[7] = "Август";
$yearp[8] = "Сентябрь";
$yearp[9] = "Октябрь";
$yearp[10]= "Ноябрь";
$yearp[11]= "Декабрь";

// Menu options
$lang['ru']['menu_home']         = "Главная";
$lang['ru']['menu_answered']     = "Отвеченные";
$lang['ru']['menu_unanswered']   = "Неотвеченные";
$lang['ru']['menu_distribution'] = "Распределение";

// tooltips
$lang['ru']['pdfhelp'] = "Экспортировать в .pdf";
$lang['ru']['csvhelp'] = "Экспортировать в CSV файл для обработки в табличном редакторе";
$lang['ru']['gotop']   = "Перейти наверх страницы";

// Index page
$lang['ru']['ALL']               = "ВСЕ";
$lang['ru']['lower']             = "Ниже  ...";
$lang['ru']['higher']            = "Выше ...";
$lang['ru']['select_queue']      = "Выберите очереди";
$lang['ru']['select_agent']      = "Выберите агентов";
$lang['ru']['select_timeframe']  = "Ввберите интервал времени";
$lang['ru']['queue']   	         = "Очередь";
$lang['ru']['start']   	         = "Начальная дата";
$lang['ru']['end']   	         = "Конечная дата";
$lang['ru']['display_report']    = "Показать отчет";
$lang['ru']['shortcuts']         = "Шаблоны";
$lang['ru']['today']             = "Сегодня";
$lang['ru']['this_week']         = "Эта неделя";
$lang['ru']['this_month']        = "Этот месяц";
$lang['ru']['last_three_months'] = "Последние 3 месяца";
$lang['ru']['available']         = "Доступные";
$lang['ru']['selected']          = "Выбранные";
$lang['ru']['invaliddate']       = "Неправильный диапазон дат";

// Answered page
$lang['ru']['answered_calls_by_agent'] = "Отвеченные вызовы по агентам";
$lang['ru']['answered_calls_by_queue'] = "Отвеченные вызовы по очередям";
$lang['ru']['anws_unanws_by_hour']     = "Отвеченные/Неотвенные по часам";
$lang['ru']['report_info']       = "Информация об отчете";
$lang['ru']['period']            = "Период";
$lang['ru']['answered_calls']    = "Отвеченные вызовы";
$lang['ru']['transferred_calls'] = "Переадресованные вызовы";
$lang['ru']['secs']              = "сек";
$lang['ru']['minutes']           = "мин";
$lang['ru']['hours']             = "ч";
$lang['ru']['calls']             = "вызовы";
$lang['ru']['Calls']             = "Вызовы";
$lang['ru']['agent']             = "Агент";
$lang['ru']['avg']               = "Средн";
$lang['ru']['avg_calltime']      = "Средняя продолжительность";
$lang['ru']['avg_holdtime']      = "Среднее время удержания";
$lang['ru']['percent']           = "%";
$lang['ru']['total']             = "Итого";
$lang['ru']['calltime']          = "Длительность звонка";
$lang['ru']['holdtime']          = "Длтельность удержания";
$lang['ru']['total_time_agent']  = "Общая длительность по агентам (сек)";
$lang['ru']['no_calls_agent']    = "Количество звонков по агентам";
$lang['ru']['call_response']     = "Уровень обслуживания";
$lang['ru']['within']            = "за ";
$lang['ru']['answer']            = "Ответ";
$lang['ru']['count']             = "Количество";
$lang['ru']['delta']             = "Дельта";
$lang['ru']['disconnect_cause']  = "Причина разъединения";
$lang['ru']['cause']             = "Причина";
$lang['ru']['agent_hungup']      = "Агент отключился";
$lang['ru']['caller_hungup']     = "Звонящий отключился";
$lang['ru']['caller']            = "Звонящий";
$lang['ru']['transfers']         = "Переадресации";
$lang['ru']['to']                = "На";

// Unanswered page
$lang['ru']['unanswered_calls']    = "Неотвеченные вызовы";
$lang['ru']['number_unanswered']   = "Количество неотвеченных вызовов";
$lang['ru']['avg_wait_before_dis'] = "Среднее время ожидания перед разъединением";
$lang['ru']['avg_queue_pos_at_dis']= "Средняя позиция в очереди при разъединении";
$lang['ru']['avg_queue_start']     = "Средняя начальная позиция в очереди";
$lang['ru']['user_abandon']        = "Пользователь покинул";
$lang['ru']['abandon']             = "Покинул";
$lang['ru']['timeout']             = "Тайм-аут";
$lang['ru']['unanswered_calls_qu'] = "Неотвеченные вызовы по очередям";

// Distribution
$lang['ru']['totals']              = "Итоги";
$lang['ru']['number_answered']     = "Количество отвеченных вызовов";
$lang['ru']['number_unanswered']   = "Количество неотвеченных вызовов";
$lang['ru']['agent_login']         = "Входов агентов";
$lang['ru']['agent_logoff']        = "Выходов агентов";
$lang['ru']['call_distrib_day']    = "Распределение вызовов по дням";
$lang['ru']['call_distrib_hour']   = "Распределение вызовов по часам.";
$lang['ru']['call_distrib_week']   = "Распределение вызовов по дням недели";
$lang['ru']['date']                = "Дата";
$lang['ru']['day']                 = "День";
$lang['ru']['days']                = "Дней";
$lang['ru']['hour']                = "Час";
$lang['ru']['answered']            = "Отвеченных";
$lang['ru']['unanswered']          = "Неотвеченных";
$lang['ru']['percent_answered']    = "% Отв";
$lang['ru']['percent_unanswered']  = "% Неотв";
$lang['ru']['login']               = "Вход";
$lang['ru']['logoff']              = "Выход";
$lang['ru']['answ_by_day']         = "Отвеченные вызовы по дням недели";
$lang['ru']['unansw_by_day']       = "Неотвеченные вызовы по дням недели";
$lang['ru']['avg_call_time_by_day']= "Средняя длительность звонка по дням недели";
$lang['ru']['avg_hold_time_by_day']= "Среднее длительность удержания по дням недели";
$lang['ru']['answ_by_hour']        = "Отвеченные вызовы по часам";
$lang['ru']['unansw_by_hour']      = "Неотвеченные вызовы по часам";
$lang['ru']['avg_call_time_by_hr'] = "Средняя длительность звонка по часам";
$lang['ru']['avg_hold_time_by_hr'] = "Средняя длительность удержания по часам";
$lang['ru']['page']                = "Страница";
$lang['ru']['export']              = "Экспорт таблицы:";

$lang['en']['server_time']         = "Server Time:";
$lang['en']['php_parsed']          = "PHP parsed this page in ";
$lang['en']['seconds']             = "seconds";
$lang['en']['current_agent_status'] = "Current agent status";
$lang['en']['hide_loggedoff']       = "Hide Logged Off";
$lang['es']['agent_status']         = "Agent Status";
$lang['en']['state']                = "State";
$lang['en']['durat']                = "Durat.";
$lang['en']['clid']                 = "CLID";
$lang['en']['last_in_call']         = "Last In Call";
$lang['en']['not_in_use']           = "not in use";
$lang['en']['paused']               = "paused";
$lang['en']['busy']                 = "busy";
$lang['en']['unavailable']          = "unavailable";
$lang['en']['unknown']              = "unknown";
$lang['en']['dialout']              = "dialout";
$lang['en']['no_info']              = "no info available";
$lang['en']['min_ago']              = "min. ago";
$lang['en']['queue_summary']        = "Queue Summary";
$lang['en']['staffed']              = "Staffed";
$lang['en']['talking']              = "Talking";
$lang['en']['paused']               = "Paused";
$lang['en']['calls_waiting']        = "Calls Waiting";
$lang['en']['oldest_call_waiting']  = "Oldest Call Waiting";
$lang['en']['calls_waiting_detail'] = "Calls Waiting Detail";
$lang['en']['position']             = "Position";
$lang['en']['callerid']             = "Callerid";
$lang['en']['wait_time']            = "Wait time";




?>
